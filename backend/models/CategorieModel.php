<?php
// models/CategorieModel.php

require_once __DIR__ . '/../config/database.php';

class CategorieModel
{
    private PDO $pdo;
    public function __construct() { $this->pdo = getDB(); }

    /** Retourne les catégories globales + celles de l'utilisateur */
    public function listerPourUser(int $uid): array
    {
        $s = $this->pdo->prepare(
            'SELECT * FROM categories WHERE user_id IS NULL OR user_id=? ORDER BY nom ASC'
        );
        $s->execute([$uid]);
        return $s->fetchAll();
    }

    public function trouverParId(int $id): array|false
    {
        $s = $this->pdo->prepare('SELECT * FROM categories WHERE id=? LIMIT 1');
        $s->execute([$id]); return $s->fetch();
    }

    public function creer(string $nom, string $icone, string $couleur, int $uid): int
    {
        $s = $this->pdo->prepare(
            'INSERT INTO categories (nom,icone,couleur,user_id) VALUES (?,?,?,?)'
        );
        $s->execute([$nom,$icone,$couleur,$uid]);
        return (int)$this->pdo->lastInsertId();
    }

    public function modifier(int $id, string $nom, string $icone, string $couleur): void
    {
        $this->pdo->prepare('UPDATE categories SET nom=?,icone=?,couleur=? WHERE id=?')
            ->execute([$nom,$icone,$couleur,$id]);
    }

    public function supprimer(int $id): void
    {
        $this->pdo->prepare('DELETE FROM categories WHERE id=? AND user_id IS NOT NULL')->execute([$id]);
    }
}
