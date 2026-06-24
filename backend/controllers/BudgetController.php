<?php
// backend/controllers/BudgetController.php

require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../models/BudgetModel.php';
require_once __DIR__ . '/../models/CategorieModel.php';
require_once __DIR__ . '/../models/TransactionModel.php';
require_once __DIR__ . '/../models/AlerteModel.php';

class BudgetController
{
    private BudgetModel $model;

    public function __construct()
    {
        requiertConnexion();
        $this->model = new BudgetModel();
    }

    // ── Liste des budgets ─────────────────────────────────────────

    public function liste(): void
    {
        $uid       = utilisateurConnecte()['id'];
        $pageTitle = 'Mes Budgets';
        $budgets   = $this->model->budgetsPourUser($uid);
        $txModel   = new TransactionModel();

        foreach ($budgets as &$b) {
            $dep           = $txModel->totalDepensesBudget($b['id']);
            $b['depenses'] = $dep;
            $b['taux']     = ($b['plafond_global'] > 0)
                ? min(100, round(($dep / $b['plafond_global']) * 100, 1)) : 0;
            // Expiration automatique
            if ($b['date_fin'] < date('Y-m-d') && $b['statut'] !== 'depasse') {
                $this->model->mettreAJourStatut($b['id'], 'expire');
                $b['statut'] = 'expire';
            }
        }
        unset($b);

        require_once __DIR__ . '/../../frontend/pages/budgets/index.php';
    }

    // ── Formulaire création ───────────────────────────────────────

    public function formulaireCreer(): void
    {
        $uid        = utilisateurConnecte()['id'];
        $pageTitle  = 'Nouveau budget';
        $categories = (new CategorieModel())->listerPourUser($uid);
        $budget     = null;
        $plafonds   = [];
        require_once __DIR__ . '/../../frontend/pages/budgets/create.php';
    }

    // ── Formulaire édition ────────────────────────────────────────

    public function formulaireEditer(int $id): void
    {
        $uid    = utilisateurConnecte()['id'];
        $budget = $this->model->trouverParId($id);

        if (!$budget || $budget['proprietaire_id'] != $uid) {
            flashMessage('danger', 'Budget introuvable ou accès refusé.');
            header('Location: index.php?page=budgets'); exit;
        }

        $pageTitle  = 'Modifier le budget';
        $categories = (new CategorieModel())->listerPourUser($uid);
        $plafonds   = $this->model->plafondsCategoriesPourBudget($id);
        require_once __DIR__ . '/../../frontend/pages/budgets/create.php';
    }

    // ── Détail / budget partagé ───────────────────────────────────

    public function detail(int $id): void
    {
        $uid    = utilisateurConnecte()['id'];
        $budget = $this->model->trouverParId($id);

        if (!$budget || !$this->model->aAcces($id, $uid)) {
            flashMessage('danger', 'Budget introuvable ou accès refusé.');
            header('Location: index.php?page=budgets'); exit;
        }

        $pageTitle    = 'Détail : ' . $budget['nom'];
        $txModel      = new TransactionModel();
        $transactions = $txModel->listerParBudget($id);
        $depenses     = $txModel->totalDepensesBudget($id);
        $revenus      = $txModel->totalRevenusBudget($id);
        $plafonds     = $this->model->plafondsCategoriesPourBudget($id);
        $membres      = $this->model->membres($id);
        $depCat       = $txModel->depensesParCategorieBudget($id);
        $taux         = ($budget['plafond_global'] > 0)
            ? min(100, round(($depenses / $budget['plafond_global']) * 100, 1)) : 0;
        $monRole      = '';
        foreach ($membres as $m) {
            if ($m['id'] == $uid) { $monRole = $m['role']; break; }
        }

        require_once __DIR__ . '/../../frontend/pages/budgets/partage.php';
    }

    // ── Traitement création ───────────────────────────────────────

    public function handleCreer(): void
    {
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token invalide.');
            header('Location: index.php?page=budgets&action=nouveau'); exit;
        }
        $uid  = utilisateurConnecte()['id'];
        $data = $this->validerFormulaire();
        if ($data === null) {
            header('Location: index.php?page=budgets&action=nouveau'); exit;
        }

        $budgetId = $this->model->creer($uid, $data);
        $this->model->ajouterMembre($budgetId, $uid, 'proprietaire');
        $this->sauvegarderPlafonds($budgetId);

        flashMessage('success', 'Budget créé avec succès !');
        header('Location: index.php?page=budgets'); exit;
    }

    // ── Traitement modification ───────────────────────────────────

    public function handleModifier(): void
    {
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token invalide.');
            header('Location: index.php?page=budgets'); exit;
        }
        $uid      = utilisateurConnecte()['id'];
        $budgetId = (int)($_POST['id'] ?? 0);
        $budget   = $this->model->trouverParId($budgetId);

        if (!$budget || $budget['proprietaire_id'] != $uid) {
            flashMessage('danger', 'Accès refusé.');
            header('Location: index.php?page=budgets'); exit;
        }

        $data = $this->validerFormulaire();
        if ($data === null) {
            header('Location: index.php?page=budgets&action=edit&id=' . $budgetId); exit;
        }

        $this->model->modifier($budgetId, $data);
        $this->model->supprimerPlafonds($budgetId);
        $this->sauvegarderPlafonds($budgetId);

        // Recalculer le statut
        (new AlerteModel())->verifierBudgets($uid);

        flashMessage('success', 'Budget modifié.');
        header('Location: index.php?page=budgets'); exit;
    }

    // ── Suppression ───────────────────────────────────────────────

    public function supprimer(int $id): void
    {
        $uid    = utilisateurConnecte()['id'];
        $budget = $this->model->trouverParId($id);

        if ($budget && $budget['proprietaire_id'] == $uid) {
            $this->model->supprimer($id);
            flashMessage('success', 'Budget supprimé.');
        } else {
            flashMessage('danger', 'Accès refusé.');
        }
        header('Location: index.php?page=budgets'); exit;
    }

    // ── Retirer un membre ─────────────────────────────────────────

    public function retirerMembre(): void
    {
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token invalide.');
            header('Location: index.php?page=budgets'); exit;
        }
        $uid      = utilisateurConnecte()['id'];
        $budgetId = (int)($_POST['budget_id'] ?? 0);
        $membreId = (int)($_POST['membre_id'] ?? 0);
        $budget   = $this->model->trouverParId($budgetId);

        if (!$budget || $budget['proprietaire_id'] != $uid) {
            flashMessage('danger', 'Accès refusé.'); 
            header('Location: index.php?page=budgets'); exit;
        }
        if ($membreId == $uid) {
            flashMessage('danger', 'Vous ne pouvez pas vous retirer (propriétaire).');
            header('Location: index.php?page=budgets&action=detail&id=' . $budgetId); exit;
        }
        $this->model->retirerMembre($budgetId, $membreId);
        flashMessage('success', 'Membre retiré du budget.');
        header('Location: index.php?page=budgets&action=detail&id=' . $budgetId); exit;
    }

    // ── Quitter un budget (pour les membres non-propriétaires) ────

    public function quitter(int $budgetId): void
    {
        $uid    = utilisateurConnecte()['id'];
        $budget = $this->model->trouverParId($budgetId);

        if (!$budget || $budget['proprietaire_id'] == $uid) {
            flashMessage('danger', 'Le propriétaire ne peut pas quitter son propre budget.');
            header('Location: index.php?page=budgets'); exit;
        }
        $this->model->retirerMembre($budgetId, $uid);
        flashMessage('info', 'Vous avez quitté le budget "' . $budget['nom'] . '".');
        header('Location: index.php?page=budgets'); exit;
    }

    // ── Helpers privés ────────────────────────────────────────────

    private function validerFormulaire(): ?array
    {
        $nom         = trim($_POST['nom']         ?? '');
        $description = trim($_POST['description'] ?? '');
        $type        = in_array($_POST['type'] ?? '', ['individuel', 'partage'])
                       ? $_POST['type'] : 'individuel';
        $periode     = in_array($_POST['periode'] ?? '', ['mensuel', 'hebdomadaire', 'personnalise'])
                       ? $_POST['periode'] : 'mensuel';
        $date_debut  = $_POST['date_debut'] ?? '';
        $date_fin    = $_POST['date_fin']   ?? '';
        $plafond     = is_numeric($_POST['plafond_global'] ?? '') && (float)$_POST['plafond_global'] > 0
                       ? (float)$_POST['plafond_global'] : null;
        $seuil       = min(100, max(10, (int)($_POST['seuil_alerte'] ?? 80)));

        if (empty($nom)) {
            flashMessage('danger', 'Le nom du budget est obligatoire.');
            return null;
        }
        if (empty($date_debut) || empty($date_fin)) {
            flashMessage('danger', 'Les dates de début et de fin sont obligatoires.');
            return null;
        }
        if ($date_fin < $date_debut) {
            flashMessage('danger', 'La date de fin doit être après la date de début.');
            return null;
        }
        return compact('nom','description','type','periode','date_debut','date_fin','plafond_global','seuil_alerte')
             + ['plafond_global' => $plafond, 'seuil_alerte' => $seuil];
    }

    private function sauvegarderPlafonds(int $budgetId): void
    {
        $cats    = $_POST['plafond_cat']    ?? [];
        $montants = $_POST['plafond_montant'] ?? [];
        foreach ($cats as $i => $catId) {
            $catId   = (int)$catId;
            $montant = (float)($montants[$i] ?? 0);
            if ($catId > 0 && $montant > 0) {
                $this->model->ajouterPlafond($budgetId, $catId, $montant);
            }
        }
    }
}
