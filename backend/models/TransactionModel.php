<?php
// backend/models/TransactionModel.php

require_once __DIR__ . '/../config/database.php';

class TransactionModel
{
    private PDO $pdo;
    public function __construct()
    {
        $this->pdo = getDB();
    }

    public function lister(int $uid, string $type = '', int $catId = 0, string $mois = ''): array
    {
        $sql = 'SELECT t.*, c.nom AS categorie_nom, c.couleur AS categorie_couleur,
                       c.icone AS categorie_icone, b.nom AS budget_nom
                FROM transactions t
                LEFT JOIN categories c ON c.id=t.categorie_id
                LEFT JOIN budgets b ON b.id=t.budget_id
                WHERE t.user_id=?';
        $params = [$uid];
        if ($type) {
            $sql .= ' AND t.type=?';
            $params[] = $type;
        }
        if ($catId > 0) {
            $sql .= ' AND t.categorie_id=?';
            $params[] = $catId;
        }
        if ($mois) {
            $sql .= ' AND DATE_FORMAT(t.date_transaction,"%Y-%m")=?';
            $params[] = $mois;
        }
        $sql .= ' ORDER BY t.date_transaction DESC, t.id DESC';
        $s = $this->pdo->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    }

    public function trouverParId(int $id): array|false
    {
        $s = $this->pdo->prepare('SELECT * FROM transactions WHERE id=? LIMIT 1');
        $s->execute([$id]);
        return $s->fetch();
    }

    public function creer(int $uid, array $d): int
    {
        $s = $this->pdo->prepare(
            'INSERT INTO transactions (user_id,budget_id,categorie_id,type,montant,description,date_transaction,commentaire)
             VALUES (?,?,?,?,?,?,?,?)'
        );
        $s->execute([
            $uid,
            $d['budget_id'],
            $d['cat_id'],
            $d['type'],
            $d['montant'],
            $d['description'],
            $d['date'],
            $d['commentaire']
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function modifier(int $id, array $d): void
    {
        $this->pdo->prepare(
            'UPDATE transactions SET budget_id=?,categorie_id=?,type=?,montant=?,
             description=?,date_transaction=?,commentaire=? WHERE id=?'
        )->execute([
                    $d['budget_id'],
                    $d['cat_id'],
                    $d['type'],
                    $d['montant'],
                    $d['description'],
                    $d['date'],
                    $d['commentaire'],
                    $id
                ]);
    }

    public function supprimer(int $id): void
    {
        $this->pdo->prepare('DELETE FROM transactions WHERE id=?')->execute([$id]);
    }

    public function statsParMois(int $uid, string $mois): array
    {
        $s = $this->pdo->prepare(
            "SELECT
                SUM(CASE WHEN type='revenu'  THEN montant ELSE 0 END) AS revenus,
                SUM(CASE WHEN type='depense' THEN montant ELSE 0 END) AS depenses
             FROM transactions WHERE user_id=? AND DATE_FORMAT(date_transaction,'%Y-%m')=?"
        );
        $s->execute([$uid, $mois]);
        $r = $s->fetch();
        $r['revenus'] = (float) ($r['revenus'] ?? 0);
        $r['depenses'] = (float) ($r['depenses'] ?? 0);
        $r['solde'] = $r['revenus'] - $r['depenses'];
        return $r;
    }

    public function checkCategory(int $uid, int $idc): bool
{
    $s = $this->pdo->prepare(
        "SELECT COUNT(*) FROM transactions WHERE user_id = ? AND categorie_id = ?"
    );
    $s->execute([$uid, $idc]);
    return (int) $s->fetchColumn() === 0; 
}

    public function repartitionParCategorie(int $uid, string $mois): array
    {
        $s = $this->pdo->prepare(
            "SELECT c.nom, c.couleur, SUM(t.montant) AS total
             FROM transactions t
             LEFT JOIN categories c ON c.id=t.categorie_id
             WHERE t.user_id=? AND t.type='depense' AND DATE_FORMAT(t.date_transaction,'%Y-%m')=?
             GROUP BY t.categorie_id ORDER BY total DESC"
        );
        $s->execute([$uid, $mois]);
        return $s->fetchAll();
    }

    public function evolutionMensuelle(int $uid, int $nbMois = 6): array
    {
        $s = $this->pdo->prepare(
            "SELECT DATE_FORMAT(date_transaction,'%Y-%m') AS mois,
                    SUM(CASE WHEN type='depense' THEN montant ELSE 0 END) AS depenses,
                    SUM(CASE WHEN type='revenu'  THEN montant ELSE 0 END) AS revenus
             FROM transactions WHERE user_id=?
               AND date_transaction >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
             GROUP BY mois ORDER BY mois ASC"
        );
        $s->execute([$uid, $nbMois]);
        return $s->fetchAll();
    }

    public function dernieres(int $uid, int $limit = 5): array
    {
        $s = $this->pdo->prepare(
            'SELECT t.*, c.nom AS categorie_nom, c.couleur, c.icone
             FROM transactions t LEFT JOIN categories c ON c.id=t.categorie_id
             WHERE t.user_id=? ORDER BY t.date_transaction DESC, t.id DESC LIMIT ?'
        );
        $s->execute([$uid, $limit]);
        return $s->fetchAll();
    }

    public function listerParBudget(int $budgetId): array
    {
        $s = $this->pdo->prepare(
            'SELECT t.*, u.prenom, u.nom AS user_nom, c.nom AS categorie_nom, c.couleur
             FROM transactions t
             JOIN utilisateurs u ON u.id=t.user_id
             LEFT JOIN categories c ON c.id=t.categorie_id
             WHERE t.budget_id=? ORDER BY t.date_transaction DESC'
        );
        $s->execute([$budgetId]);
        return $s->fetchAll();
    }

    public function totalDepensesBudget(int $budgetId): float
    {
        $s = $this->pdo->prepare(
            "SELECT SUM(montant) FROM transactions WHERE budget_id=? AND type='depense'"
        );
        $s->execute([$budgetId]);
        return (float) $s->fetchColumn();
    }

    public function totalRevenusBudget(int $budgetId): float
    {
        $s = $this->pdo->prepare(
            "SELECT SUM(montant) FROM transactions WHERE budget_id=? AND type='revenu'"
        );
        $s->execute([$budgetId]);
        return (float) $s->fetchColumn();
    }

    public function compterTotal(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM transactions')->fetchColumn();
    }

    public function statsGlobales(): array
    {
        $r = $this->pdo->query(
            "SELECT SUM(CASE WHEN type='revenu' THEN montant ELSE 0 END) AS total_revenus,
                    SUM(CASE WHEN type='depense' THEN montant ELSE 0 END) AS total_depenses
             FROM transactions"
        )->fetch();
        return [
            'revenus' => (float) ($r['total_revenus'] ?? 0),
            'depenses' => (float) ($r['total_depenses'] ?? 0),
        ];
    }

    public function getAllExport(int $uid): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM transactions where user_id = ?");
        $stmt->execute([$uid]);
        return $stmt->fetchAll();

    }

    public function depensesParCategorieBudget(int $budgetId): array
    {
        $s = $this->pdo->prepare(
            "SELECT c.id AS categorie_id, c.nom, c.couleur, c.icone,
                    COALESCE(SUM(t.montant),0) AS total
             FROM categories c
             JOIN transactions t ON t.categorie_id=c.id
               AND t.budget_id=? AND t.type='depense'
             GROUP BY c.id, c.nom, c.couleur, c.icone
             ORDER BY total DESC"
        );
        $s->execute([$budgetId]);
        return $s->fetchAll();
    }
}
