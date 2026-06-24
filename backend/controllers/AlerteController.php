<?php
// controllers/AlerteController.php

require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../models/AlerteModel.php';

class AlerteController
{
    private AlerteModel $model;

    public function __construct()
    {
        requiertConnexion();
        $this->model = new AlerteModel();
    }

    public function liste(): void
    {
        $uid = utilisateurConnecte()['id'];
        $pageTitle = 'Alertes';
        $alertes   = $this->model->listerPourUser($uid);
        require_once __DIR__ . '/../../frontend/pages/alertes/liste.php';
    }

    public function marquerLue(int $id): void
    {
        $uid = utilisateurConnecte()['id'];
        $this->model->marquerLue($id, $uid);
        header('Location: index.php?page=alertes'); exit;
    }

    public function toutMarquerLu(): void
    {
        $uid = utilisateurConnecte()['id'];
        $this->model->toutMarquerLu($uid);
        flashMessage('success', 'Toutes les alertes ont été marquées comme lues.');
        header('Location: index.php?page=alertes'); exit;
    }
}
