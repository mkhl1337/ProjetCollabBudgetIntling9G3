<?php
// models/AlerteModel.php

require_once __DIR__ . '/../config/database.php';

class AlerteModel
{
    private PDO $pdo;
    public function __construct()
    {
        $this->pdo = getDB();
    }

    public function creer(int $budgetId, int $uid, string $type, int $seuil, string $message): void
    {
        // Ne pas dupliquer une alerte du même type pour le même budget dans la même journée
        $s = $this->pdo->prepare(
            'SELECT id FROM alertes WHERE budget_id=? AND user_id=? AND type=? AND DATE(date_alerte)=CURDATE()'
        );
        $s->execute([$budgetId, $uid, $type]);
        if ($s->fetch())
            return;

        $this->pdo->prepare(
            'INSERT INTO alertes (budget_id,user_id,type,seuil_pourcentage,message) VALUES (?,?,?,?,?)'
        )->execute([$budgetId, $uid, $type, $seuil, $message]);
    }

    public function listerPourUser(int $uid): array
    {
        $s = $this->pdo->prepare(
            'SELECT a.*, b.nom AS budget_nom FROM alertes a
             JOIN budgets b ON b.id=a.budget_id
             WHERE a.user_id=? ORDER BY a.lue ASC, a.date_alerte DESC'
        );
        $s->execute([$uid]);
        return $s->fetchAll();
    }

    public function compterNonLues(int $uid): int
    {
        $s = $this->pdo->prepare('SELECT COUNT(*) FROM alertes WHERE user_id=? AND lue=0');
        $s->execute([$uid]);
        return (int) $s->fetchColumn();
    }

    public function marquerLue(int $id, int $uid): void
    {
        $this->pdo->prepare('UPDATE alertes SET lue=1 WHERE id=? AND user_id=?')->execute([$id, $uid]);
    }

    public function toutMarquerLu(int $uid): void
    {
        $this->pdo->prepare('UPDATE alertes SET lue=1 WHERE user_id=?')->execute([$uid]);
    }

    public function verifierBudgets(int $uid): void
    {
        $pdo = $this->pdo;
 
        // Récupérer les budgets actifs où l'utilisateur est membre
        $s = $pdo->prepare(
            "SELECT b.* FROM budgets b
             JOIN budget_membres bm ON bm.budget_id=b.id AND bm.user_id=?
             WHERE b.date_fin >= CURDATE() AND b.plafond_global > 0"
        );
        $s->execute([$uid]);
        $budgets = $s->fetchAll();
 
        foreach ($budgets as $b) {
            $dep = $pdo->prepare(
                "SELECT COALESCE(SUM(montant),0) FROM transactions
                 WHERE budget_id=? AND type='depense'"
            );
            $dep->execute([$b['id']]);
            $total = (float)$dep->fetchColumn();
            $pct   = round(($total / $b['plafond_global']) * 100, 1);
            $seuil = (int)($b['seuil_alerte'] ?? 80);
 
            // Récupérer tous les membres du budget pour les notifier
            $membres = $pdo->prepare(
                'SELECT user_id FROM budget_membres WHERE budget_id=?'
            );
            $membres->execute([$b['id']]);
 
            foreach ($membres->fetchAll() as $m) {
                $mid = $m['user_id'];
                if ($pct >= 100) {
                    $this->creer($b['id'], $mid, 'depassement', $seuil, "⚠️ Budget \"{$b['nom']}\" dépassé ! ({$pct}% consommé)");
                    $pdo->prepare("UPDATE budgets SET statut='depasse' WHERE id=?")
                        ->execute([$b['id']]);
                } elseif ($pct >= $seuil) {
                    $this->creer($b['id'], $mid, 'seuil', $seuil, "🔔 Budget \"{$b['nom']}\" à {$pct}% — seuil d'alerte atteint.");
                    $pdo->prepare("UPDATE budgets SET statut='proche_limite' WHERE id=?")
                        ->execute([$b['id']]);
                } else {
                    $pdo->prepare("UPDATE budgets SET statut='actif' WHERE id=?")
                        ->execute([$b['id']]);
                }
            }
        }
    }
}
