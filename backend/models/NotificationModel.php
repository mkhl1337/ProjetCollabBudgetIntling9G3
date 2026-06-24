<?php
// models/NotificationModel.php

require_once __DIR__ . '/../config/database.php';

class NotificationModel
{
    private PDO $pdo;
    public function __construct() { $this->pdo = getDB(); }

    public function creer(int $userId, string $message): void
    {
        $this->pdo->prepare(
            'INSERT INTO notifications (user_id, message) VALUES (?, ?)'
        )->execute([$userId, $message]);
    }
    public function supprimer(int $id,int $uid):void{

        $this->pdo->prepare(
            "Delete from notifications WHERE id=? and user_id=?"
        )->execute([$id,$uid]);

    }
    /*  Toutes les notifications d'un utilisateur (desc) */
    public function toutesParUser(int $userId): array
    {
        $s = $this->pdo->prepare(
            'SELECT * FROM notifications WHERE user_id=? ORDER BY date_notif DESC'
        );
        $s->execute([$userId]);
        return $s->fetchAll();
    }
    public function trouverParId(int $id,int $uid): array|false
    {
        $s = $this->pdo->prepare('SELECT * FROM notifications WHERE id=? and user_id =? LIMIT 1');
        $s->execute([$id,$uid]);
        return $s->fetch();
    }

    /** Notifications non lues d'un utilisateur */
    public function nonLuesParUser(int $userId): array
    {
        $s = $this->pdo->prepare(
            'SELECT * FROM notifications WHERE user_id=? AND lue=0 ORDER BY date_notif DESC'
        );
        $s->execute([$userId]);
        return $s->fetchAll();
    }

    /** Compte des non lues */
    public function compterNonLues(int $userId): int
    {
        $s = $this->pdo->prepare(
            'SELECT COUNT(*) FROM notifications WHERE user_id=? AND lue=0'
        );
        $s->execute([$userId]);
        return (int)$s->fetchColumn();
    }

    /** Marquer une notification comme lue */
    public function marquerLue(int $id, int $userId): void
    {
        $this->pdo->prepare(
            'UPDATE notifications SET lue=1 WHERE id=? AND user_id=?'
        )->execute([$id, $userId]);
    }

    /** Marquer toutes comme lues */
    public function toutMarquerLu(int $userId): void
    {
        $this->pdo->prepare(
            'UPDATE notifications SET lue=1 WHERE user_id=?'
        )->execute([$userId]);
    }

    /** Lues d'un utilisateur */
    public function luesParUser(int $userId): array
    {
        $s = $this->pdo->prepare(
            'SELECT * FROM notifications WHERE user_id=? AND lue=1 ORDER BY date_notif DESC'
        );
        $s->execute([$userId]);
        return $s->fetchAll();
    }
}