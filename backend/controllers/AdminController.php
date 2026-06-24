<?php
// backend/controllers/AdminController.php

require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../models/NotificationModel.php';
require_once __DIR__ . '/../config/mailer.php';
require_once __DIR__ . '/../config/database.php';

class AdminController
{
    private UserModel $userModel;
    private NotificationModel $notifModel;

    public function __construct()
    {
        requiertAdmin();
        $this->userModel = new UserModel();
        $this->notifModel = new NotificationModel();
    }

    // ── Dashboard admin ───────────────────────────────
    public function dashboard(): void
    {
        $pdo = getDB();
        $pageTitle = 'Dashboard Administrateur';
        $nbUsers = $this->userModel->compterTotal();
        $nbAttente = count($this->userModel->utilisateursParStatut('en_attente'));
        $nbActifs = count($this->userModel->utilisateursParStatut('actif'));
        $nbDemandes = count($this->userModel->demandesSuppression());

        $statsGlobales = $pdo->query(
            "SELECT
                COALESCE(SUM(CASE WHEN type='revenu'  THEN montant ELSE 0 END),0) AS revenus,
                COALESCE(SUM(CASE WHEN type='depense' THEN montant ELSE 0 END),0) AS depenses
             FROM transactions"
        )->fetch();

        $evolution = $pdo->query(
            "SELECT DATE_FORMAT(date_transaction,'%b %Y') AS mois,
                    COALESCE(SUM(CASE WHEN type='depense' THEN montant ELSE 0 END),0) AS depenses,
                    COALESCE(SUM(CASE WHEN type='revenu'  THEN montant ELSE 0 END),0) AS revenus
             FROM transactions
             WHERE date_transaction >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY DATE_FORMAT(date_transaction,'%Y-%m'), DATE_FORMAT(date_transaction,'%b %Y')
             ORDER BY MIN(date_transaction)"
        )->fetchAll();

        $repartition = $pdo->query(
            "SELECT c.nom, c.couleur, COALESCE(SUM(t.montant),0) AS total
             FROM categories c
             LEFT JOIN transactions t ON t.categorie_id=c.id AND t.type='depense'
                AND MONTH(t.date_transaction)=MONTH(NOW())
                AND YEAR(t.date_transaction)=YEAR(NOW())
             GROUP BY c.id, c.nom, c.couleur
             HAVING total > 0
             ORDER BY total DESC LIMIT 6"
        )->fetchAll();

        require_once __DIR__ . '/../../frontend/pages/admin/dashboard.php';
    }

    // ── Liste utilisateurs (avec filtre statut) ───────
    public function utilisateurs(): void
    {
        $pageTitle = 'Gestion des utilisateurs';
        $recherche = trim($_GET['q'] ?? '');
        $filtreStatut = $_GET['statut'] ?? '';

        if ($recherche) {
            $utilisateurs = $this->userModel->rechercherUtilisateurs($recherche);
        } elseif ($filtreStatut) {
            $utilisateurs = $this->userModel->utilisateursParStatut($filtreStatut);
        } else {
            $utilisateurs = $this->userModel->tousLesUtilisateurs();
        }

        require_once __DIR__ . '/../../frontend/pages/admin/utilisateurs.php';
    }

    // ── Ajouter utilisateur (traitement POST) ─────────
    public function handleAjouter(): void
    {
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token de sécurité invalide.');
            header('Location: index.php?page=admin_utilisateurs');
            exit;
        }
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = in_array($_POST['role'] ?? '', ['admin', 'utilisateur']) ? $_POST['role'] : 'utilisateur';
        $statut = in_array($_POST['statut'] ?? '', ['actif', 'en_attente', 'suspendu', 'bloque']) ? $_POST['statut'] : 'actif';
        $mdp = $_POST['mot_de_passe'] ?? 'ChangeMe123!';

        if (empty($nom) || empty($prenom) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flashMessage('danger', 'Veuillez remplir tous les champs obligatoires correctement.');
            header('Location: index.php?page=admin_utilisateurs');
            exit;
        }
        if ($this->userModel->emailExiste($email)) {
            flashMessage('danger', 'Cet email est déjà utilisé par un autre compte.');
            header('Location: index.php?page=admin_utilisateurs');
            exit;
        }

        $id = $this->userModel->creer($nom, $prenom, $email, password_hash($mdp, PASSWORD_BCRYPT));
        $pdo = getDB();
        $pdo->prepare("UPDATE utilisateurs SET role=?, statut=? WHERE id=?")->execute([$role, $statut, $id]);

        flashMessage('success', "Utilisateur {$prenom} {$nom} créé avec succès.");
        header('Location: index.php?page=admin_utilisateurs');
        exit;
    }

    // ── Modifier utilisateur (traitement POST) ────────
    public function handleModifier(): void
    {
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token de sécurité invalide.');
            header('Location: index.php?page=admin_utilisateurs');
            exit;
        }
        $id = (int) ($_POST['id'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $role = in_array($_POST['role'] ?? '', ['admin', 'utilisateur']) ? $_POST['role'] : 'utilisateur';
        $statut = in_array($_POST['statut'] ?? '', ['actif', 'en_attente', 'suspendu', 'bloque']) ? $_POST['statut'] : 'actif';

        if (!$id || empty($nom) || empty($prenom) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flashMessage('danger', 'Données invalides. Vérifiez les champs du formulaire.');
            header('Location: index.php?page=admin_utilisateurs');
            exit;
        }
        if ($id === (int) $_SESSION['user_id']) {
            flashMessage('danger', 'Vous ne pouvez pas modifier votre propre compte depuis cette interface.');
            header('Location: index.php?page=admin_utilisateurs');
            exit;
        }

        $this->userModel->mettreAJourParAdmin($id, $nom, $prenom, $email, $role, $statut);
        $this->notifModel->creer($id, 'Une modification a été effectuée par l\'administrateur sur votre compte.');

        flashMessage('success', "Le compte de {$prenom} {$nom} a été mis à jour.");
        header('Location: index.php?page=admin_utilisateurs');
        exit;
    }

    // ── Validation des comptes (en_attente) ──────────
    public function validationComptes(): void
    {
        $pageTitle = 'Validation des comptes';
        $recherche = trim($_GET['q'] ?? '');
        if ($recherche) {
            $all = $this->userModel->rechercherUtilisateurs($recherche);
            $utilisateurs = array_filter($all, fn($u) => $u['statut'] === 'en_attente');
        } else {
            $utilisateurs = $this->userModel->utilisateursParStatut('en_attente');
        }
        require_once __DIR__ . '/../../frontend/pages/admin/validation_comptes.php';
    }

    // ── Valider un compte ─────────────────────────────
    public function valider(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: index.php?page=admin_validation');
            exit;
        }

        $user = $this->userModel->trouverParId($id);
        if (!$user) {
            flashMessage('danger', 'Utilisateur introuvable.');
            header('Location: index.php?page=admin_validation');
            exit;
        }

        $this->userModel->changerStatut($id, 'actif');
        $this->notifModel->creer($id, 'Votre compte a été activé et validé par l\'administrateur. Vous pouvez maintenant vous connecter et gérer vos budgets.');

        $sujet = 'Votre compte Budget Sync est activé !';
        $corps = emailActivationCompte($user['prenom'], $user['nom']);
        envoyerEmail($user['email'], $sujet, $corps);

        flashMessage('success', 'Compte de ' . htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . ' validé. Un email de confirmation a été envoyé.');
        header('Location: index.php?page=admin_validation');
        exit;
    }

    // ── Rejeter un compte (supprimer) ────────────────
    public function rejeter(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id) {
            header('Location: index.php?page=admin_validation');
            exit;
        }

        $user = $this->userModel->trouverParId($id);
        if ($user && $this->userModel->supprimer($id)) {
            flashMessage('warning', 'Le compte de ' . htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . ' a été rejeté et supprimé.');
        } else {
            flashMessage('danger', 'Impossible de rejeter ce compte.');
        }
        header('Location: index.php?page=admin_validation');
        exit;
    }

    // ── Bloquer ───────────────────────────────────────
    public function bloquer(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id || $id === (int) $_SESSION['user_id']) {
            flashMessage('danger', 'Action non autorisée.');
            header('Location: index.php?page=admin_utilisateurs');
            exit;
        }
        $user = $this->userModel->trouverParId($id);
        if (!$user || $user['statut'] !== 'actif') {
            flashMessage('danger', 'Vous ne pouvez bloquer que les comptes actifs.');
            header('Location: index.php?page=admin_utilisateurs');
            exit;
        }
        $this->userModel->changerStatut($id, 'bloque');
        $this->notifModel->creer($id, 'Votre compte a été bloqué par l\'administrateur. Veuillez le contacter pour plus d\'informations.');
        flashMessage('warning', 'Compte bloqué avec succès.');
        header('Location: index.php?page=admin_utilisateurs');
        exit;
    }

    // ── Suspendre ─────────────────────────────────────
    public function suspendre(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if (!$id || $id === (int) $_SESSION['user_id']) {
            flashMessage('danger', 'Action non autorisée.');
            header('Location: index.php?page=admin_utilisateurs');
            exit;
        }
        $user = $this->userModel->trouverParId($id);
        if (!$user || $user['statut'] !== 'actif') {
            flashMessage('danger', 'Vous ne pouvez suspendre que les comptes actifs.');
            header('Location: index.php?page=admin_utilisateurs');
            exit;
        }
        $this->userModel->changerStatut($id, 'suspendu');
        $this->notifModel->creer($id, 'Votre compte a été suspendu par l\'administrateur. Veuillez le contacter pour plus d\'informations.');
        flashMessage('warning', 'Compte suspendu avec succès.');
        header('Location: index.php?page=admin_utilisateurs');
        exit;
    }

    // ── Réactiver ─────────────────────────────────────
    public function activer(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $user = $this->userModel->trouverParId($id);
        if ($id) {
            $this->userModel->changerStatut($id, 'actif');
            $this->notifModel->creer($id, 'Votre compte a été réactivé par l\'administrateur.');
            $sujet = 'Votre compte Budget Sync est réactivé !';
            $corps = emailActivationCompte($user['prenom'], $user['nom']);
            envoyerEmail($user['email'], $sujet, $corps);
            flashMessage('success', 'Compte réactivé avec succès.');
        }
        header('Location: index.php?page=admin_utilisateurs');
        exit;
    }

    // ── Supprimer ─────────────────────────────────────
    public function supprimer(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id === (int) $_SESSION['user_id']) {
            flashMessage('danger', 'Vous ne pouvez pas supprimer votre propre compte.');
            header('Location: index.php?page=admin_utilisateurs');
            exit;
        }
        if ($id && $this->userModel->supprimer($id)) {
            flashMessage('success', 'Compte supprimé définitivement.');
        } else {
            flashMessage('danger', 'Suppression impossible (compte administrateur protégé ou introuvable).');
        }
        header('Location: index.php?page=admin_utilisateurs');
        exit;
    }

    // ── Demandes de suppression ───────────────────────
    public function demandesSuppression(): void
    {
        $pageTitle = 'Demandes de suppression';
    
     
    
        $filtreStatut = $_GET['statut'] ?? 'all';

        if ($filtreStatut === 'all') {
            $demandes = $this->userModel->toutesLesDemandesSuppression();
        } else {
            $demandes = $this->userModel->demandesSuppressionParStatut($filtreStatut);
        }
    
        require_once __DIR__ . '/../../frontend/pages/admin/demandes_suppression.php';
    }

    public function validerSuppression(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id) {
            $demande = $this->userModel->trouverDemande($id);
            if ($demande) {
                // 1. Récupérer infos user avant suppression (sécurité supplémentaire)
                $user = $this->userModel->trouverParId($demande['user_id']);
                $nomAffiche = $user
                    ? htmlspecialchars($user['prenom'] . ' ' . $user['nom'])
                    : htmlspecialchars(($demande['prenom_utilisateur'] ?? '') . ' ' . ($demande['nom_utilisateur'] ?? ''));
    
                // 2. Supprimer l'utilisateur
                $this->userModel->supprimer($demande['user_id']);
    
                // 3. Mettre le statut à "validee" (la demande est conservée)
                $this->userModel->changerStatutDemande($demande['id'], 'validee');
    
                flashMessage('success', "Compte de {$nomAffiche} supprimé suite à sa demande.");
            }
        }
        header('Location: index.php?page=admin_demandes');
        exit;
    }

    public function refuserSuppression(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id) {
            $this->userModel->changerStatutDemande($id, 'refusee');
            $demande = $this->userModel->trouverDemande($id);
            if ($demande) {
                $this->notifModel->creer($demande['user_id'], 'Votre demande de suppression de compte a été refusée par l\'administrateur.');
            }
            flashMessage('info', 'Demande de suppression refusée.');
        }
        header('Location: index.php?page=admin_demandes');
        exit;
    }
}