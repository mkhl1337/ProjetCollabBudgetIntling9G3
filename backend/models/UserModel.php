<?php
// models/UserModel.php — Requêtes SQL liées aux utilisateurs

require_once __DIR__ . '/../config/database.php';

class UserModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = getDB();
    }

    /**
     * Recherche un utilisateur par son email.
     * Retourne un tableau associatif ou false si introuvable.
     */
    public function trouverParEmail(string $email): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM utilisateurs WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Vérifie si un email est déjà utilisé.
     */
    public function emailExiste(string $email): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT id FROM utilisateurs WHERE email = ?'
        );
        $stmt->execute([$email]);
        return (bool) $stmt->fetch();
    }

    /**
     * Insère un nouvel utilisateur (statut 'en_attente' par défaut).
     * Reçoit le mot de passe déjà haché (le hachage se fait dans le Controller).
     */
    public function creer(
        string $nom,
        string $prenom,
        string $email,
        string $mdpHash,
        string $numtel
        
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe,numtel)
             VALUES (?, ?, ?, ?,?)'
        );
        $stmt->execute([$nom, $prenom, $email, $mdpHash,$numtel]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Met à jour la date de dernière connexion.
     */
    public function mettreAJourConnexion(int $id): void
    {
        $this->pdo->prepare(
            'UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?'
        )->execute([$id]);
    }

    /**
     * Retourne tous les utilisateurs (admin uniquement).
     */
    public function tousLesUtilisateurs(): array
    {
        return $this->pdo
            ->query('SELECT * FROM utilisateurs ORDER BY date_inscription DESC')
            ->fetchAll();
    }

    /* 
    Modifier utilisateur
    */
    public function modifierUtilisateur(int $id,string $nom,string $prenom,string $email,string $numtel):void{

        $this->pdo->prepare('UPDATE utilisateurs SET nom=?,prenom=?,email=?,numtel=? where id=?')->execute([$nom,$prenom,$email,$numtel,$id]);

        
    }

    /**
     * Change le statut d'un utilisateur (actif / suspendu).
     */
    public function changerStatut(int $id, string $statut): void
    {
        $this->pdo->prepare(
            "UPDATE utilisateurs SET statut = ? WHERE id = ? AND role != 'admin'"
        )->execute([$statut, $id]);
    }

    /**
     * Retourne un utilisateur par son id.
     */
    public function trouverParId(int $id): array|false
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM utilisateurs WHERE id = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Met à jour le profil (nom, prénom, email).
     */
    public function mettreAJourProfil(
        int $id,
        string $nom,
        string $prenom,
        string $email,
        string $numtel
    ): void {
        $this->pdo->prepare(
            'UPDATE utilisateurs SET nom = ?, prenom = ?, email = ? , numtel=? WHERE id = ?'
        )->execute([$nom, $prenom, $email,$numtel, $id]);
    }

    /**
     * Supprime définitivement un utilisateur.
     * Impossible de supprimer un admin.
     */
    public function supprimer(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            "DELETE FROM utilisateurs WHERE id = ? AND role != 'admin'"
        );
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0; // true si la suppression a eu lieu
    }

    /**
     * Met à jour le mot de passe (reçoit le hash).
     */
    public function changerMotDePasse(int $id, string $hash): void
    {
        $this->pdo->prepare(
            'UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?'
        )->execute([$hash, $id]);
    }
  /* 
  rechercher un utilisateur par un champ saisie 

  */
  public function rechercherUtilisateur(string $keyword): array
  {
      $stmt = $this->pdo->prepare(
          'SELECT *
           FROM utilisateurs
           WHERE nom LIKE ?
              OR prenom LIKE ?
              OR email LIKE ?
              OR numtel LIKE ?
           ORDER BY date_inscription DESC'
      );
  
      $search = "%{$keyword}%";
  
      $stmt->execute([$search, $search, $search, $search]);
  
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
