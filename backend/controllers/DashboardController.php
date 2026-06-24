<?php
// controllers/DashboardController.php

require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class DashboardController
{
    public function show(): void
    {
        requiertConnexion();

        if (estAdmin()) {
            require_once __DIR__ . '/AdminController.php';
            (new AdminController())->dashboard();
            return;
        }

        $user = utilisateurConnecte();
        $uid  = $user['id'];
        $pdo  = getDB();

        $pageTitle = 'Tableau de bord';

        // Statistiques du mois en cours
        $stats = $pdo->prepare(
            "SELECT
                COALESCE(SUM(CASE WHEN type='revenu'  THEN montant ELSE 0 END),0) AS revenus,
                COALESCE(SUM(CASE WHEN type='depense' THEN montant ELSE 0 END),0) AS depenses
             FROM transactions
             WHERE user_id=?
               AND MONTH(date_transaction)=MONTH(NOW())
               AND YEAR(date_transaction)=YEAR(NOW())"
        );
        $stats->execute([$uid]);
        $stats = $stats->fetch();
        $stats['solde'] = $stats['revenus'] - $stats['depenses'];

        // Taux de dépenses (vs revenus)
        $stats['taux'] = $stats['revenus'] > 0
            ? round(($stats['depenses'] / $stats['revenus']) * 100, 1)
            : 0;

        // Évolution 6 mois
        $evoStmt = $pdo->prepare(
            "SELECT DATE_FORMAT(date_transaction,'%b %Y') AS mois,
                    COALESCE(SUM(CASE WHEN type='depense' THEN montant ELSE 0 END),0) AS depenses,
                    COALESCE(SUM(CASE WHEN type='revenu'  THEN montant ELSE 0 END),0) AS revenus
             FROM transactions
             WHERE user_id=? AND date_transaction >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY DATE_FORMAT(date_transaction,'%Y-%m'), DATE_FORMAT(date_transaction,'%b %Y')
             ORDER BY MIN(date_transaction)"
        );
        $evoStmt->execute([$uid]);
        $evolution = $evoStmt->fetchAll();

        // Répartition dépenses par catégorie (mois en cours)
        $repStmt = $pdo->prepare(
            "SELECT c.nom, c.couleur,
                    COALESCE(SUM(t.montant),0) AS total
             FROM categories c
             JOIN transactions t ON t.categorie_id=c.id AND t.user_id=? AND t.type='depense'
                AND MONTH(t.date_transaction)=MONTH(NOW())
                AND YEAR(t.date_transaction)=YEAR(NOW())
             GROUP BY c.id, c.nom, c.couleur
             HAVING total > 0
             ORDER BY total DESC LIMIT 6"
        );
        $repStmt->execute([$uid]);
        $repartition = $repStmt->fetchAll();

        // Dernières transactions
        $txStmt = $pdo->prepare(
            "SELECT t.*, c.nom AS categorie_nom, c.icone, c.couleur
             FROM transactions t
             LEFT JOIN categories c ON c.id=t.categorie_id
             WHERE t.user_id=?
             ORDER BY t.date_transaction DESC, t.date_creation DESC
             LIMIT 5"
        );
        $txStmt->execute([$uid]);
        $dernieresTx = $txStmt->fetchAll();

        // Budgets actifs de l'utilisateur
        $budgStmt = $pdo->prepare(
            "SELECT b.* FROM budgets b
             LEFT JOIN budget_membres bm ON bm.budget_id=b.id AND bm.user_id=?
             WHERE (b.proprietaire_id=? OR bm.user_id=?)
               AND b.date_fin >= CURDATE()
             ORDER BY b.date_debut DESC LIMIT 5"
        );
        $budgStmt->execute([$uid, $uid, $uid]);
        $budgets = $budgStmt->fetchAll();

        // Budget consommé (tous budgets actifs)
        $budgetConsomme = 0;
        $budgetTotal    = 0;
        foreach ($budgets as $b) {
            $budgetTotal += (float)$b['plafond_global'];
            $depB = $pdo->prepare(
                "SELECT COALESCE(SUM(montant),0) FROM transactions WHERE budget_id=? AND type='depense'"
            );
            $depB->execute([$b['id']]);
            $budgetConsomme += (float)$depB->fetchColumn();
        }
        $budgetPct = $budgetTotal > 0 ? round(($budgetConsomme / $budgetTotal) * 100, 1) : 0;

        // Notifications non lues
        $notifModel = new NotificationModel();
        $nbNotifs   = $notifModel->compterNonLues($uid);
        $notifRecentes = $notifModel->nonLuesParUser($uid);

        require_once __DIR__ . '/../../frontend/pages/utilisateur/dashboard.php';
    }
}