<?php
// backend/models/BudgetModel.php

require_once __DIR__ . '/../config/database.php';

class BudgetModel
{
    private PDO $pdo;
    public function __construct() { $this->pdo = getDB(); }

    // ── Lecture ───────────────────────────────────────────────────

    /** Tous les budgets où l'utilisateur est propriétaire OU membre */
    public function budgetsPourUser(int $uid): array
    {
        $s = $this->pdo->prepare(
            'SELECT b.*, u.prenom AS proprio_prenom, u.nom AS proprio_nom,
                    bm.role AS mon_role
             FROM budgets b
             JOIN utilisateurs u  ON u.id = b.proprietaire_id
             JOIN budget_membres bm ON bm.budget_id = b.id AND bm.user_id = ?
             ORDER BY b.date_creation DESC'
        );
        $s->execute([$uid]);
        return $s->fetchAll();
    }

    /** Budgets dont l'utilisateur est propriétaire (pour les invitations) */
    public function budgetsProprietaire(int $uid): array
    {
        $s = $this->pdo->prepare(
            'SELECT * FROM budgets WHERE proprietaire_id=? ORDER BY nom ASC'
        );
        $s->execute([$uid]);
        return $s->fetchAll();
    }

    /** Budgets actifs (date_fin >= aujourd'hui), limité */
    public function budgetsActifs(int $uid, int $limit = 5): array
    {
        $s = $this->pdo->prepare(
            'SELECT b.* FROM budgets b
             JOIN budget_membres bm ON bm.budget_id=b.id AND bm.user_id=?
             WHERE b.date_fin >= CURDATE()
             ORDER BY b.date_fin ASC LIMIT ' . (int)$limit
        );
        $s->execute([$uid]);
        return $s->fetchAll();
    }

    public function trouverParId(int $id): array|false
    {
        $s = $this->pdo->prepare('SELECT * FROM budgets WHERE id=? LIMIT 1');
        $s->execute([$id]);
        return $s->fetch();
    }

    public function aAcces(int $budgetId, int $uid): bool
    {
        $s = $this->pdo->prepare(
            'SELECT id FROM budget_membres WHERE budget_id=? AND user_id=?'
        );
        $s->execute([$budgetId, $uid]);
        return (bool)$s->fetch();
    }

    // ── Écriture ──────────────────────────────────────────────────

    public function creer(int $uid, array $d): int
    {
        $s = $this->pdo->prepare(
            'INSERT INTO budgets
               (nom, description, type, periode, date_debut, date_fin,
                plafond_global, seuil_alerte, proprietaire_id)
             VALUES (?,?,?,?,?,?,?,?,?)'
        );
        $s->execute([
            $d['nom'], $d['description'], $d['type'], $d['periode'],
            $d['date_debut'], $d['date_fin'],
            $d['plafond_global'], $d['seuil_alerte'], $uid
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    public function modifier(int $id, array $d): void
    {
        $this->pdo->prepare(
            'UPDATE budgets SET nom=?, description=?, type=?, periode=?,
             date_debut=?, date_fin=?, plafond_global=?, seuil_alerte=? WHERE id=?'
        )->execute([
            $d['nom'], $d['description'], $d['type'], $d['periode'],
            $d['date_debut'], $d['date_fin'],
            $d['plafond_global'], $d['seuil_alerte'], $id
        ]);
    }

    public function supprimer(int $id): void
    {
        $this->pdo->prepare('DELETE FROM budgets WHERE id=?')->execute([$id]);
    }

    public function mettreAJourStatut(int $id, string $statut): void
    {
        $this->pdo->prepare('UPDATE budgets SET statut=? WHERE id=?')
            ->execute([$statut, $id]);
    }

    // ── Membres ───────────────────────────────────────────────────

    public function ajouterMembre(int $budgetId, int $uid, string $role = 'membre'): void
    {
        $this->pdo->prepare(
            'INSERT IGNORE INTO budget_membres (budget_id, user_id, role) VALUES (?,?,?)'
        )->execute([$budgetId, $uid, $role]);
    }

    public function retirerMembre(int $budgetId, int $uid): void
    {
        $this->pdo->prepare(
            'DELETE FROM budget_membres WHERE budget_id=? AND user_id=? AND role!=\'proprietaire\''
        )->execute([$budgetId, $uid]);
    }

    public function membres(int $budgetId): array
    {
        $s = $this->pdo->prepare(
            'SELECT bm.role, u.id, u.prenom, u.nom, u.email
             FROM budget_membres bm
             JOIN utilisateurs u ON u.id = bm.user_id
             WHERE bm.budget_id=?
             ORDER BY FIELD(bm.role,\'proprietaire\',\'membre\'), u.prenom'
        );
        $s->execute([$budgetId]);
        return $s->fetchAll();
    }

    public function estMembre(int $budgetId, int $uid): bool
    {
        $s = $this->pdo->prepare(
            'SELECT id FROM budget_membres WHERE budget_id=? AND user_id=?'
        );
        $s->execute([$budgetId, $uid]);
        return (bool)$s->fetch();
    }

    // ── Plafonds par catégorie ────────────────────────────────────

    public function plafondsCategoriesPourBudget(int $budgetId): array
    {
        $s = $this->pdo->prepare(
            'SELECT bp.*, c.nom AS categorie_nom, c.couleur, c.icone
             FROM budget_plafonds bp
             JOIN categories c ON c.id = bp.categorie_id
             WHERE bp.budget_id=?'
        );
        $s->execute([$budgetId]);
        return $s->fetchAll();
    }

    public function ajouterPlafond(int $budgetId, int $catId, float $montant): void
    {
        $this->pdo->prepare(
            'INSERT INTO budget_plafonds (budget_id, categorie_id, plafond) VALUES (?,?,?)
             ON DUPLICATE KEY UPDATE plafond=VALUES(plafond)'
        )->execute([$budgetId, $catId, $montant]);
    }

    public function supprimerPlafonds(int $budgetId): void
    {
        $this->pdo->prepare('DELETE FROM budget_plafonds WHERE budget_id=?')
            ->execute([$budgetId]);
    }

    // ── Statistiques admin ────────────────────────────────────────

    public function tousLesBudgets(): array
    {
        return $this->pdo->query(
            'SELECT b.*, u.prenom, u.nom AS user_nom
             FROM budgets b
             JOIN utilisateurs u ON u.id=b.proprietaire_id
             ORDER BY b.date_creation DESC'
        )->fetchAll();
    }

    public function compterTotal(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM budgets')->fetchColumn();
    }
}
