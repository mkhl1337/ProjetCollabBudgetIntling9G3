<?php
// backend/models/UserModel.php

require_once __DIR__ . '/../config/database.php';

class UserModel
{
    private PDO $pdo;

    public function __construct() { $this->pdo = getDB(); }

    public function trouverParEmail(string $email): array|false
    {
        $s = $this->pdo->prepare('SELECT * FROM utilisateurs WHERE email=? LIMIT 1');
        $s->execute([$email]);
        return $s->fetch();
    }

    public function emailExiste(string $email, int $excludeId = 0): bool
    {
        $s = $this->pdo->prepare('SELECT id FROM utilisateurs WHERE email=? AND id!=?');
        $s->execute([$email, $excludeId]);
        return (bool)$s->fetch();
    }

    public function creer(string $nom, string $prenom, string $email, string $hash): int
    {
        $s = $this->pdo->prepare(
            'INSERT INTO utilisateurs (nom,prenom,email,mot_de_passe) VALUES (?,?,?,?)'
        );
        $s->execute([$nom, $prenom, $email, $hash]);
        return (int)$this->pdo->lastInsertId();
    }

    public function mettreAJourConnexion(int $id): void
    {
        $this->pdo->prepare('UPDATE utilisateurs SET derniere_connexion=NOW() WHERE id=?')
            ->execute([$id]);
    }

    public function tousLesUtilisateurs(): array
    {
        return $this->pdo->query(
            'SELECT * FROM utilisateurs ORDER BY date_inscription DESC'
        )->fetchAll();
    }

    public function utilisateursParStatut(string $statut): array
    {
        $s = $this->pdo->prepare('SELECT * FROM utilisateurs WHERE statut=? ORDER BY date_inscription DESC');
        $s->execute([$statut]);
        return $s->fetchAll();
    }

    public function rechercherUtilisateurs(string $q): array
    {
        $like = "%{$q}%";
        $s = $this->pdo->prepare(
            'SELECT * FROM utilisateurs
             WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ?
             ORDER BY date_inscription DESC'
        );
        $s->execute([$like, $like, $like]);
        return $s->fetchAll();
    }

    public function changerStatut(int $id, string $statut): void
    {
        $this->pdo->prepare(
            "UPDATE utilisateurs SET statut=? WHERE id=? AND role!='admin'"
        )->execute([$statut, $id]);
    }

    public function trouverParId(int $id): array|false
    {
        $s = $this->pdo->prepare('SELECT * FROM utilisateurs WHERE id=? LIMIT 1');
        $s->execute([$id]);
        return $s->fetch();
    }

    public function mettreAJourProfil(int $id, string $nom, string $prenom, string $email): void
    {
        $this->pdo->prepare('UPDATE utilisateurs SET nom=?,prenom=?,email=? WHERE id=?')
            ->execute([$nom, $prenom, $email, $id]);
    }

    public function mettreAJourParAdmin(int $id, string $nom, string $prenom, string $email, string $role, string $statut): void
    {
        $this->pdo->prepare(
            "UPDATE utilisateurs SET nom=?,prenom=?,email=?,role=?,statut=? WHERE id=? AND role!='admin'"
        )->execute([$nom, $prenom, $email, $role, $statut, $id]);
    }

    public function changerMotDePasse(int $id, string $hash): void
    {
        $this->pdo->prepare('UPDATE utilisateurs SET mot_de_passe=? WHERE id=?')
            ->execute([$hash, $id]);
    }

    public function supprimer(int $id): bool
    {
        $s = $this->pdo->prepare("DELETE FROM utilisateurs WHERE id=? AND role!='admin'");
        $s->execute([$id]);
        return $s->rowCount() > 0;
    }

    public function compterTotal(): int
    {
        return (int)$this->pdo->query('SELECT COUNT(*) FROM utilisateurs')->fetchColumn();
    }

    // ── Demandes de suppression ──────────────────────

    public function aDemandeSuppression(int $uid): bool
    {
        $s = $this->pdo->prepare(
            "SELECT id FROM demandes_suppression WHERE user_id=? AND statut='en_attente'"
        );
        $s->execute([$uid]);
        return (bool)$s->fetch();
    }

    public function demanderSuppression(int $uid, string $motif): void
    {
        $user = $this->trouverParId($uid);
        $this->pdo->prepare(
            'INSERT INTO demandes_suppression (user_id, motif, nom_utilisateur, prenom_utilisateur)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
               motif=VALUES(motif),
               nom_utilisateur=VALUES(nom_utilisateur),
               prenom_utilisateur=VALUES(prenom_utilisateur),
               statut="en_attente",
               date_demande=NOW()'
        )->execute([$uid, $motif, $user['nom'] ?? '', $user['prenom'] ?? '']);
    }

    /** Retourne toutes les demandes (filtrées par statut si fourni) avec infos utilisateur */
    public function demandesSuppressionParStatut(string $statut = 'en_attente'): array
    {
        $s = $this->pdo->prepare(
            "SELECT ds.*,
                    COALESCE(u.nom,    ds.nom_utilisateur)    AS nom,
                    COALESCE(u.prenom, ds.prenom_utilisateur) AS prenom,
                    COALESCE(u.email,  '(compte supprimé)')   AS email
             FROM demandes_suppression ds
             LEFT JOIN utilisateurs u ON u.id = ds.user_id
             WHERE ds.statut = ?
             ORDER BY ds.date_demande DESC"
        );
        $s->execute([$statut]);
        return $s->fetchAll();
    }

    public function toutesLesDemandesSuppression(): array
{
    $s = $this->pdo->prepare(
        "SELECT ds.*,
                COALESCE(u.nom, ds.nom_utilisateur) AS nom,
                COALESCE(u.prenom, ds.prenom_utilisateur) AS prenom,
                COALESCE(u.email, '(compte supprimé)') AS email
         FROM demandes_suppression ds
         LEFT JOIN utilisateurs u ON u.id = ds.user_id
         ORDER BY ds.date_demande DESC"
    );
    $s->execute();
    return $s->fetchAll();
}
    /** Alias pour compatibilité (retourne uniquement en_attente) */
    public function demandesSuppression(): array
    {
        return $this->demandesSuppressionParStatut('en_attente');
    }

    public function trouverDemande(int $id): array|false
    {
        $s = $this->pdo->prepare('SELECT * FROM demandes_suppression WHERE id=? LIMIT 1');
        $s->execute([$id]);
        return $s->fetch();
    }

    public function changerStatutDemande(int $id, string $statut): void
    {
        $this->pdo->prepare('UPDATE demandes_suppression SET statut=? WHERE id=?')
            ->execute([$statut, $id]);
    }
}