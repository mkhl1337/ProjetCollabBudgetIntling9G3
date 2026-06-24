<?php
// controllers/AuthController.php

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../middlewares/auth.php';

class AuthController
{
    private UserModel $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    public function showLogin(): void
    {
        $pageTitle = 'Connexion';
        require_once __DIR__ . '/../../frontend/pages/auth/login.php';
    }

    public function handleLogin(): void
    {
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token invalide.');
            $this->showLogin(); return;
        }
        $email = trim($_POST['email'] ?? '');
        $mdp   = $_POST['mot_de_passe'] ?? '';

        if (empty($email) || empty($mdp)) {
            flashMessage('danger', 'Tous les champs sont obligatoires.');
            $this->showLogin(); return;
        }

        $user = $this->model->trouverParEmail($email);
        if (!$user || !password_verify($mdp, $user['mot_de_passe'])) {
            flashMessage('danger', 'Email ou mot de passe incorrect.');
            $this->showLogin(); return;
        }

        if ($user['statut'] === 'en_attente') {
            flashMessage('warning', 'Votre compte est en attente de validation par l\'administrateur.');
            $this->showLogin(); return;
        }
        if ($user['statut'] === 'bloque') {
            flashMessage('danger', 'Compte bloqué. Veuillez contacter l\'administrateur.');
            $this->showLogin(); return;
        }
        if ($user['statut'] === 'suspendu') {
            flashMessage('danger', 'Compte suspendu. Veuillez contacter l\'administrateur.');
            $this->showLogin(); return;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nom']     = $user['nom'];
        $_SESSION['prenom']  = $user['prenom'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['role']    = $user['role'];

        $this->model->mettreAJourConnexion($user['id']);

        flashMessage('success', 'Bienvenue ' . htmlspecialchars($user['prenom']) . ' !');
        header('Location: index.php?page=dashboard');
        exit;
    }

    public function showRegister(): void
    {
        $pageTitle = 'Inscription';
        require_once __DIR__ . '/../../frontend/pages/auth/register.php';
    }

    public function handleRegister(): void
    {
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token invalide.');
            $this->showRegister(); return;
        }
        $nom    = trim($_POST['nom']    ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email  = trim($_POST['email']  ?? '');
        $mdp    = $_POST['mot_de_passe']  ?? '';
        $mdp2   = $_POST['mot_de_passe2'] ?? '';

        if (empty($nom) || empty($prenom) || empty($email) || empty($mdp)) {
            flashMessage('danger', 'Tous les champs sont obligatoires.');
            $this->showRegister(); return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flashMessage('danger', 'Email invalide.');
            $this->showRegister(); return;
        }
        if (strlen($mdp) < 8) {
            flashMessage('danger', 'Mot de passe : 8 caractères minimum.');
            $this->showRegister(); return;
        }
        if ($mdp !== $mdp2) {
            flashMessage('danger', 'Les mots de passe ne correspondent pas.');
            $this->showRegister(); return;
        }
        if ($this->model->emailExiste($email)) {
            flashMessage('danger', 'Cet email est déjà utilisé.');
            $this->showRegister(); return;
        }

        $this->model->creer($nom, $prenom, $email, password_hash($mdp, PASSWORD_BCRYPT));
        flashMessage('success', 'Inscription réussie ! Vous receverez un e-mail dès que l\'administrateur valide votre compte !');
        header('Location: index.php?page=login');
        exit;
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    }
}