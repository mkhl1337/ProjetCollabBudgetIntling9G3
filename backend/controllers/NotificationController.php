<?php
// controllers/NotificationController.php

require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../models/NotificationModel.php';

class NotificationController
{
    private NotificationModel $model;

    public function __construct()
    {
        requiertConnexion();
        $this->model = new NotificationModel();
    }

    public function liste(): void
    {
        $uid = utilisateurConnecte()['id'];
        $filtre = $_GET['filtre'] ?? 'toutes';
        $pageTitle = 'Mes notifications';

        $notifications = match ($filtre) {
            'non_lues' => $this->model->nonLuesParUser($uid),
            'lues' => $this->model->luesParUser($uid),
            default => $this->model->toutesParUser($uid),
        };

        require_once __DIR__ . '/../../frontend/pages/utilisateur/notification.php';
    }

    public function marquerLue(): void
    {
        $uid = utilisateurConnecte()['id'];
        $id = (int) ($_GET['id'] ?? 0);
        if ($id)
            $this->model->marquerLue($id, $uid);
        $redirect = $_GET['redirect'] ?? 'notifications';
        header('Location: index.php?page=' . $redirect);
        exit;
    }

    public function toutMarquerLu(): void
    {
        $uid = utilisateurConnecte()['id'];
        $this->model->toutMarquerLu($uid);
        flashMessage('success', 'Toutes les notifications ont été marquées comme lues.');
        header('Location: index.php?page=notifications');
        exit;
    }

    public function supprimer(): void
    {
        $uid = utilisateurConnecte()['id'];
        $id = (int) ($_GET['id'] ?? 0);
        $notif = $this->model->trouverParId($id, $uid);
        if ($notif) {
            $this->model->supprimer($id, $uid);
            flashMessage('success', 'Notification supprimée.');
        } else {
            flashMessage('danger', 'Erreur de suppression');
        }
        header('Location: index.php?page=notifications');
        exit;


    }
}