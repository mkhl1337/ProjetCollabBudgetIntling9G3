<?php
// backend/controllers/InvitationController.php

require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../models/InvitationModel.php';
require_once __DIR__ . '/../models/BudgetModel.php';
require_once __DIR__ . '/../config/mailer.php';

class InvitationController
{
    private InvitationModel $model;

    public function __construct()
    {
        requiertConnexion();
        $this->model = new InvitationModel();
    }

    // ── Page invitations ─────────────────────────────────────────

    public function liste(): void
    {
        $user      = utilisateurConnecte();
        $uid       = $user['id'];
        $email     = $user['email'];
        $pageTitle = 'Invitations';

        $recues   = $this->model->recuesPourEmail($email);
        $envoyees = $this->model->envoyeesPar($uid);
        $budgets  = (new BudgetModel())->budgetsProprietaire($uid);

        require_once __DIR__ . '/../../frontend/pages/invitations/index.php';
    }

    // ── Envoyer une invitation ────────────────────────────────────

    public function handleInviter(): void
    {
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token invalide.');
            header('Location: index.php?page=invitations'); exit;
        }
        $uid          = utilisateurConnecte()['id'];
        $budgetId     = (int)($_POST['budget_id']   ?? 0);
        $emailInvite  = trim($_POST['email_invite'] ?? '');

        if (!filter_var($emailInvite, FILTER_VALIDATE_EMAIL)) {
            flashMessage('danger', 'Adresse email invalide.');
            header('Location: index.php?page=invitations'); exit;
        }

        // Seul le propriétaire peut inviter
        $budget = (new BudgetModel())->trouverParId($budgetId);
        if (!$budget || $budget['proprietaire_id'] != $uid) {
            flashMessage('danger', 'Accès refusé : vous n\'êtes pas propriétaire de ce budget.');
            header('Location: index.php?page=invitations'); exit;
        }

        // Empêcher de s'inviter soi-même
        if ($emailInvite === utilisateurConnecte()['email']) {
            flashMessage('danger', 'Vous ne pouvez pas vous inviter vous-même.');
            header('Location: index.php?page=invitations'); exit;
        }

        // Éviter les doublons
        if ($this->model->invitationExiste($budgetId, $emailInvite)) {
            flashMessage('warning', 'Une invitation est déjà en attente pour cet email.');
            header('Location: index.php?page=invitations'); exit;
        }

        $token = bin2hex(random_bytes(32));
        $this->model->creer($budgetId, $uid, $emailInvite, $token);

        // Envoyer l'email d'invitation
        $user   = utilisateurConnecte();
        $lien   = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
                . dirname($_SERVER['SCRIPT_NAME'] ?? '/') . '/index.php'
                . '?page=invitations&action=accepter_token&token=' . $token;
        $corps  = $this->emailInvitation(
            $user['prenom'] . ' ' . $user['nom'],
            $budget['nom'],
            $lien
        );
        envoyerEmail($emailInvite, '📩 Invitation à rejoindre le budget "' . $budget['nom'] . '"', $corps);

        flashMessage('success', 'Invitation envoyée à ' . htmlspecialchars($emailInvite) . ' !');
        header('Location: index.php?page=invitations'); exit;
    }

    // ── Accepter via lien token (email) ──────────────────────────

    public function accepterParToken(string $token): void
    {
        $uid   = utilisateurConnecte()['id'];
        $email = utilisateurConnecte()['email'];
        $inv   = $this->model->trouverParToken($token);

        if (!$inv || $inv['statut'] !== 'en_attente') {
            flashMessage('danger', 'Lien d\'invitation invalide ou expiré.');
            header('Location: index.php?page=dashboard'); exit;
        }
        if ($inv['email_invite'] !== $email) {
            flashMessage('danger', 'Cette invitation n\'est pas destinée à votre compte.');
            header('Location: index.php?page=dashboard'); exit;
        }

        $this->model->changerStatut($inv['id'], 'accepte');
        (new BudgetModel())->ajouterMembre($inv['budget_id'], $uid, 'membre');

        flashMessage('success', 'Invitation acceptée ! Vous avez rejoint le budget.');
        header('Location: index.php?page=budgets&action=detail&id=' . $inv['budget_id']); exit;
    }

    // ── Accepter via bouton (interface) ──────────────────────────

    public function accepter(int $id): void
    {
        $uid   = utilisateurConnecte()['id'];
        $email = utilisateurConnecte()['email'];
        $inv   = $this->model->trouverParId($id);

        if (!$inv || $inv['email_invite'] !== $email || $inv['statut'] !== 'en_attente') {
            flashMessage('danger', 'Invitation invalide.');
            header('Location: index.php?page=invitations'); exit;
        }

        $this->model->changerStatut($id, 'accepte');
        (new BudgetModel())->ajouterMembre($inv['budget_id'], $uid, 'membre');

        flashMessage('success', 'Invitation acceptée ! Vous avez rejoint le budget.');
        header('Location: index.php?page=budgets&action=detail&id=' . $inv['budget_id']); exit;
    }

    // ── Refuser ──────────────────────────────────────────────────

    public function refuser(int $id): void
    {
        $email = utilisateurConnecte()['email'];
        $inv   = $this->model->trouverParId($id);

        if ($inv && $inv['email_invite'] === $email && $inv['statut'] === 'en_attente') {
            $this->model->changerStatut($id, 'refuse');
            flashMessage('info', 'Invitation refusée.');
        } else {
            flashMessage('danger', 'Action impossible.');
        }
        header('Location: index.php?page=invitations'); exit;
    }

    // ── Annuler (par l'expéditeur) ────────────────────────────────

    public function annuler(int $id): void
    {
        $uid = utilisateurConnecte()['id'];
        if ($this->model->annuler($id, $uid)) {
            flashMessage('success', 'Invitation annulée.');
        } else {
            flashMessage('danger', 'Impossible d\'annuler cette invitation.');
        }
        header('Location: index.php?page=invitations'); exit;
    }

    // ── Template email ────────────────────────────────────────────

    private function emailInvitation(string $expéditeur, string $budgetNom, string $lien): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
    <tr><td align="center">
      <table width="560" cellpadding="0" cellspacing="0"
             style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);">
        <tr>
          <td style="background:linear-gradient(135deg,#0f172a,#2563eb);padding:32px;text-align:center;">
            <span style="font-size:2rem;">💰</span>
            <h1 style="color:#fff;margin:12px 0 0;font-size:1.5rem;font-weight:700;">Budget Sync</h1>
          </td>
        </tr>
        <tr>
          <td style="padding:36px;">
            <h2 style="color:#1e293b;margin:0 0 16px;font-size:1.2rem;">Vous êtes invité(e) !</h2>
            <p style="color:#475569;line-height:1.7;margin:0 0 20px;">
              <strong>{$expéditeur}</strong> vous invite à rejoindre le budget collaboratif
              <strong>« {$budgetNom} »</strong> sur Budget Sync.
            </p>
            <div style="text-align:center;margin:28px 0;">
              <a href="{$lien}"
                 style="background:#2563eb;color:#fff;padding:14px 36px;border-radius:10px;
                        text-decoration:none;font-weight:600;font-size:1rem;display:inline-block;">
                Accepter l'invitation
              </a>
            </div>
            <p style="color:#94a3b8;font-size:.85rem;margin:0;">
              Ou connectez-vous sur Budget Sync et acceptez l'invitation depuis la page Invitations.
            </p>
          </td>
        </tr>
        <tr>
          <td style="background:#f8fafc;padding:20px;text-align:center;">
            <p style="color:#94a3b8;font-size:.8rem;margin:0;">
              © Budget Sync — Application de gestion collaborative de budget
            </p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
    }
}
