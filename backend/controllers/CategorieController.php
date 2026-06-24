<?php
// backend/controllers/CategorieController.php

require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../models/CategorieModel.php';
require_once __DIR__ . '/../models/TransactionModel.php';


class CategorieController
{
    private CategorieModel $model;
    private TransactionModel $tmod;

    public function __construct()
    {
        requiertConnexion();
        $this->model = new CategorieModel();
        $this->tmod = new TransactionModel();

    }

    public function liste(): void
    {
        $uid = utilisateurConnecte()['id'];
        $pageTitle = 'Catégories';
        $categories = $this->model->listerPourUser($uid);
        require_once __DIR__ . '/../../frontend/pages/categories/liste.php';
    }

    public function handleAjouter(): void
    {
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token invalide.');
            header('Location: index.php?page=categories');
            exit;
        }
        $uid = utilisateurConnecte()['id'];
        $nom = trim($_POST['nom'] ?? '');
        $icone = trim($_POST['icone'] ?? 'bi-tag');
        $couleur = trim($_POST['couleur'] ?? '#6366f1');
        if (empty($nom)) {
            flashMessage('danger', 'Le nom est obligatoire.');
            header('Location: index.php?page=categories');
            exit;
        }
        $this->model->creer($nom, $icone, $couleur, $uid);
        flashMessage('success', 'Catégorie crée.');
        header('Location: index.php?page=categories');
        exit;
    }

    public function handleModifier(): void
    {
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token invalide.');
            header('Location: index.php?page=categories');
            exit;
        }
        $uid = utilisateurConnecte()['id'];
        $id = (int) ($_POST['id'] ?? 0);
        $nom = trim($_POST['nom'] ?? '');
        $icone = trim($_POST['icone'] ?? 'bi-tag');
        $couleur = trim($_POST['couleur'] ?? '#6366f1');
        $cat = $this->model->trouverParId($id);
        if (!$cat || ($cat['user_id'] && $cat['user_id'] != $uid)) {
            flashMessage('danger', 'Action non autorisée.');
            header('Location: index.php?page=categories');
            exit;
        }
        $this->model->modifier($id, $nom, $icone, $couleur);
        flashMessage('success', 'Catégorie modifiée.');
        header('Location: index.php?page=categories');
        exit;
    }

    public function supprimer(): void
    {
        $uid = utilisateurConnecte()['id'];
        $id = (int) ($_GET['id'] ?? 0);
        $cat = $this->model->trouverParId($id);

        if (!$cat || $cat['user_id'] != $uid) {
            // Check ownership FIRST
            flashMessage('danger', 'Impossible de supprimer cette catégorie.');
        } elseif (!$this->tmod->checkCategory($uid, $id)) {
            // THEN check for linked transactions
            flashMessage('danger', 'Impossible de supprimer une catégorie associée à une transaction.');
        } else {
            $this->model->supprimer($id);
            flashMessage('success', 'Catégorie supprimée.');
        }

        header('Location: index.php?page=categories');
        exit;
    }
}
