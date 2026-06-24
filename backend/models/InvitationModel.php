<?php
// backend/models/InvitationModel.php

require_once __DIR__ . '/../config/database.php';

class InvitationModel
{
    private PDO $pdo;
    public function __construct() { $this->pdo = getDB(); }

    public function creer(int $budgetId, int $inviteParId, string $email, string $token): int
    {
        $s = $this->pdo->prepare(
            'INSERT INTO invitations (budget_id, invite_par, email_invite, token)
             VALUES (?,?,?,?)'
        );
        $s->execute([$budgetId, $inviteParId, $email, $token]);
        return (int)$this->pdo->lastInsertId();
    }

    /** Invitations reçues pour un email donné */
    public function recuesPourEmail(string $email): array
    {
        $s = $this->pdo->prepare(
            'SELECT i.*, b.nom AS budget_nom, b.type AS budget_type,
                    u.prenom AS envoyeur_prenom, u.nom AS envoyeur_nom
             FROM invitations i
             JOIN budgets b      ON b.id = i.budget_id
             JOIN utilisateurs u ON u.id = i.invite_par
             WHERE i.email_invite=?
             ORDER BY i.date_invitation DESC'
        );
        $s->execute([$email]);
        return $s->fetchAll();
    }

    /** Invitations envoyées par un utilisateur */
    public function envoyeesPar(int $uid): array
    {
        $s = $this->pdo->prepare(
            'SELECT i.*, b.nom AS budget_nom
             FROM invitations i
             JOIN budgets b ON b.id = i.budget_id
             WHERE i.invite_par=?
             ORDER BY i.date_invitation DESC'
        );
        $s->execute([$uid]);
        return $s->fetchAll();
    }

    public function trouverParId(int $id): array|false
    {
        $s = $this->pdo->prepare('SELECT * FROM invitations WHERE id=? LIMIT 1');
        $s->execute([$id]);
        return $s->fetch();
    }

    public function trouverParToken(string $token): array|false
    {
        $s = $this->pdo->prepare('SELECT * FROM invitations WHERE token=? LIMIT 1');
        $s->execute([$token]);
        return $s->fetch();
    }

    public function changerStatut(int $id, string $statut): void
    {
        $this->pdo->prepare('UPDATE invitations SET statut=? WHERE id=?')
            ->execute([$statut, $id]);
    }

    public function invitationExiste(int $budgetId, string $email): bool
    {
        $s = $this->pdo->prepare(
            "SELECT id FROM invitations
             WHERE budget_id=? AND email_invite=? AND statut='en_attente'"
        );
        $s->execute([$budgetId, $email]);
        return (bool)$s->fetch();
    }

    public function annuler(int $id, int $inviteParId): bool
    {
        $s = $this->pdo->prepare(
            "DELETE FROM invitations WHERE id=? AND invite_par=? AND statut='en_attente'"
        );
        $s->execute([$id, $inviteParId]);
        return $s->rowCount() > 0;
    }
}
