<?php
// backend/controllers/TransactionController.php

require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../models/TransactionModel.php';
require_once __DIR__ . '/../models/CategorieModel.php';
require_once __DIR__ . '/../models/BudgetModel.php';
require_once __DIR__ . '/../models/AlerteModel.php';

class TransactionController
{
    private TransactionModel $model;

    public function __construct()
    {
        requiertConnexion();
        $this->model = new TransactionModel();
    }

    public function liste(): void
    {
        $uid = utilisateurConnecte()['id'];
        $pageTitle = 'Mes transactions';
        $filtre_type = $_GET['type'] ?? '';
        $filtre_cat = (int) ($_GET['categorie_id'] ?? 0);
        $filtre_mois = $_GET['mois'] ?? '';

        $transactions = $this->model->lister($uid, $filtre_type, $filtre_cat, $filtre_mois);
        $categories = (new CategorieModel())->listerPourUser($uid);
        $budgets = (new BudgetModel())->budgetsPourUser($uid);
        require_once __DIR__ . '/../../frontend/pages/transactions/liste.php';
    }

    public function formulaireAjouter(): void
    {
        $uid = utilisateurConnecte()['id'];
        $pageTitle = 'Nouvelle transaction';
        $categories = (new CategorieModel())->listerPourUser($uid);
        $budgets = (new BudgetModel())->budgetsPourUser($uid);
        $transaction = null;
        require_once __DIR__ . '/../../frontend/pages/transactions/form.php';
    }

    public function formulaireModifier(int $id): void
    {
        $uid = utilisateurConnecte()['id'];
        $transaction = $this->model->trouverParId($id);
        if (!$transaction || $transaction['user_id'] != $uid) {
            flashMessage('danger', 'Transaction introuvable.');
            header('Location: index.php?page=transactions');
            exit;
        }
        $pageTitle = 'Modifier la transaction';
        $categories = (new CategorieModel())->listerPourUser($uid);
        $budgets = (new BudgetModel())->budgetsPourUser($uid);
        require_once __DIR__ . '/../../frontend/pages/transactions/form.php';
    }

    public function handleAjouter(): void
    {
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token invalide.');
            header('Location: index.php?page=transactions&action=nouveau');
            exit;
        }
        $uid = utilisateurConnecte()['id'];
        $data = $this->validerFormulaire();
        if ($data === null) {
            header('Location: index.php?page=transactions&action=nouveau');
            exit;
        }
        $id = $this->model->creer($uid, $data);
        $this->verifierAlertes($uid, $data['budget_id'] ?? null);
        flashMessage('success', 'Transaction ajoutée.');
        header('Location: index.php?page=transactions');
        exit;
    }

    public function handleModifier(): void
    {
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token invalide.');
            header('Location: index.php?page=transactions');
            exit;
        }
        $uid = utilisateurConnecte()['id'];
        $id = (int) ($_POST['id'] ?? 0);
        $tx = $this->model->trouverParId($id);
        if (!$tx || $tx['user_id'] != $uid) {
            flashMessage('danger', 'Accès refusé.');
            header('Location: index.php?page=transactions');
            exit;
        }
        $data = $this->validerFormulaire();
        if ($data === null) {
            header('Location: index.php?page=transactions&action=edit&id=' . $id);
            exit;
        }
        $this->model->modifier($id, $data);
        $this->verifierAlertes($uid, $data['budget_id'] ?? null);
        flashMessage('success', 'Transaction modifiée.');
        header('Location: index.php?page=transactions');
        exit;
    }

    public function supprimer(int $id): void
    {
        $uid = utilisateurConnecte()['id'];
        $tx = $this->model->trouverParId($id);
        if ($tx && $tx['user_id'] == $uid) {
            $this->model->supprimer($id);
            flashMessage('success', 'Transaction supprimée.');
        } else {
            flashMessage('danger', 'Accès refusé.');
        }
        header('Location: index.php?page=transactions');
        exit;
    }

    private function validerFormulaire(): ?array
    {
        $type = $_POST['type'] ?? '';
        $montant = (float) ($_POST['montant'] ?? 0);
        $date = $_POST['date_transaction'] ?? '';
        $description = trim($_POST['description'] ?? '');
        $cat_id = (int) ($_POST['categorie_id'] ?? 0) ?: null;
        $budget_id = (int) ($_POST['budget_id'] ?? 0) ?: null;
        $commentaire = trim($_POST['commentaire'] ?? '');

        if (!in_array($type, ['revenu', 'depense']) || $montant <= 0 || empty($date)) {
            flashMessage('danger', 'Données invalides. Vérifiez le type, le montant et la date.');
            return null;
        }
        return compact('type', 'montant', 'date', 'description', 'cat_id', 'budget_id', 'commentaire');
    }

    private function verifierAlertes(int $uid, ?int $budgetId): void
    {
        if (!$budgetId)
            return;
        $alerteModel = new AlerteModel();
        $budgetModel = new BudgetModel();
        $budget = $budgetModel->trouverParId($budgetId);
        if (!$budget || !$budget['plafond_global'])
            return;

        $txModel = new TransactionModel();
        $depenses = $txModel->totalDepensesBudget($budgetId);
        $pct = $budget['plafond_global'] > 0
            ? ($depenses / $budget['plafond_global']) * 100 : 0;

        if ($pct >= 100) {
            $alerteModel->creer(
                $budgetId,
                $uid,
                'depassement',
                100,
                "Dépassement du budget « {$budget['nom']} » !"
            );
        } elseif ($pct >= 80) {
            $alerteModel->creer(
                $budgetId,
                $uid,
                'seuil',
                80,
                "Budget « {$budget['nom']} » atteint à " . round($pct) . '%'
            );
        }
    }

    // private function exportCSV(array $transactionData): void
    // {
    //     $filename = 'Transactions_' . date('Y-m-d_His') . '.csv';

    //     // 🔴 IMPORTANT : nettoyer TOUT buffer
    //     if (ob_get_length()) {
    //         ob_end_clean();
    //     }

    //     header('Content-Type: text/csv; charset=UTF-8');
    //     header('Content-Disposition: attachment; filename="' . $filename . '"');
    //     header('Pragma: no-cache');
    //     header('Expires: 0');

    //     $out = fopen('php://output', 'w');

    //     // 🔴 BOM UTF-8 (OBLIGATOIRE pour Excel)
    //     fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

    //     // Excel FR = ;
    //     $delimiter = ';';

    //     // En-têtes
    //     fputcsv($out, ['type', 'montant', 'description', 'date_transaction', 'date_creation'], $delimiter);

    //     foreach ($transactionData as $e) {
    //         fputcsv($out, [
    //             $e['type'] ?? '',
    //             $e['montant'] ?? '',
    //             $e['description'] ?? '',
    //             !empty($e['date_transaction']) ? date('d/m/Y', strtotime($e['date_transaction'])) : '',
    //             !empty($e['date_creation']) ? date('d/m/Y H:i', strtotime($e['date_creation'])) : ''
    //         ], $delimiter);
    //     }

    //     fclose($out);
    //     exit;
    // }
    // public function export(int $uid): void
    // {
    //     $transactionData = $this->model->getAllExport($uid);

    //     $format = $_GET['format'] ?? 'csv';

    //     switch ($format) {
    //         case 'csv':
    //         default:
    //             $this->exportCSV($transactionData);
    //             break;
    //     }
    // }

    public function export(int $uid): void
    {

        $transactionData = $this->model->getAllExport($uid);
        $date = date('Y-m-d');

        header('Content-Type: text/csv; charset=UTF-8');
        header("Content-Disposition: attachment; filename=\"Transactions_{$date}.csv\"");
        // header('Pragma: no-cache');
        // header('Expires: 0');

        $out = fopen('php://output', 'w');

        fputs($out, "\xEF\xBB\xBF");

        fputcsv($out, ['Type', 'Montant', 'Description', 'Date Transaction', 'Date Création']);

        foreach ($transactionData as $e) {
            fputcsv($out, [
                $e['type'],
                $e['montant'],
                $e['description'],
                !empty($e['date_transaction'])
                ? date('d/m/Y', strtotime($e['date_transaction']))
                : '',
                !empty($e['date_creation'])
                ? date('d/m/Y', strtotime($e['date_creation']))
                : '',
            ]);
        }

        fclose($out);
        exit;
    }
}
