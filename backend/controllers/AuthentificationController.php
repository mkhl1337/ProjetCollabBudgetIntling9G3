<?php
// controllers/AuthController.php — Gestion login / register / logout

require_once __DIR__ . '/../models/UserModel.php';
require_once __DIR__ . '/../security/authentification.php';

class AuthentificationController
{
    private UserModel $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    // ════════════════════════════════════════════════════════════
    //  CONNEXION
    // ════════════════════════════════════════════════════════════

    /** Affiche le formulaire de connexion */
    public function showLogin(): void
    {
        $pageTitle = 'Connexion';
        require_once __DIR__ . '/../../frontend/pages/views/login.php';
    }

    /** Traite la soumission du formulaire de connexion */
    public function handleLogin(): void
    {
        // 1. Vérifier le token CSRF
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token invalide, veuillez réessayer.');
            $this->showLogin();
            return;
        }

        // 2. Récupérer les champs
        $email = trim($_POST['email'] ?? '');
        $mdp   = $_POST['mot_de_passe'] ?? '';

        // 3. Validation basique
        if (empty($email) || empty($mdp)) {
            flashMessage('danger', 'Tous les champs sont obligatoires.');
            $this->showLogin();
            return;
        }

        // 4. Chercher l'utilisateur en base
        $user = $this->model->trouverParEmail($email);

        if (!$user) {
            flashMessage('danger', 'Email ou mot de passe incorrect.');
            $this->showLogin();
            return;
        }

        // 5. Vérifier le statut du compte
        if ($user['statut'] === 'en_attente') {
            flashMessage('warning', 'Votre compte est en attente de validation par l\'administrateur.');
            $this->showLogin();
            return;
        }

        if ($user['statut'] === 'suspendu') {
            flashMessage('danger', 'Votre compte a été suspendu. Contactez l\'administrateur.');
            $this->showLogin();
            return;
        }

        // 6. Vérifier le mot de passe (hash bcrypt)
        if (!password_verify($mdp, $user['mot_de_passe'])) {
            flashMessage('danger', 'Email ou mot de passe incorrect.');
            $this->showLogin();
            return;
        }

        // 7. Créer la session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nom']     = $user['nom'];
        $_SESSION['prenom']  = $user['prenom'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['numtel'] = $user['numtel'];
        $_SESSION['role']    = $user['role'];

        // 8. Mettre à jour la date de dernière connexion
        $this->model->mettreAJourConnexion($user['id']);

        flashMessage('success', 'Bienvenue ' . $user['nom'] . ' !');
        header('Location: index.php?page=dashboard');
        exit;
    }

    // ════════════════════════════════════════════════════════════
    //  INSCRIPTION
    // ════════════════════════════════════════════════════════════

    /** Affiche le formulaire d'inscription */
    public function showRegister(): void
    {
        $pageTitle = 'Inscription';
        require_once __DIR__ . '/../../frontend/views/register.php';
    }

    /** Traite la soumission du formulaire d'inscription */
    public function handleRegister(): void
    {
        // 1. CSRF
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token invalide.');
            $this->showRegister();
            return;
        }

        // 2. Récupérer les champs
        $nom    = trim($_POST['nom']    ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email  = trim($_POST['email']  ?? '');
        $numtel =trim($_POST['numtel'] ?? '');
        $mdp    = $_POST['mot_de_passe']  ?? '';
        $mdp2   = $_POST['mot_de_passe2'] ?? '';

        // 3. Validations
        if (empty($nom) || empty($prenom) || empty($email) || empty($mdp)) {
            flashMessage('danger', 'Tous les champs sont obligatoires.');
            $this->showRegister();
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flashMessage('danger', 'Adresse email invalide.');
            $this->showRegister();
            return;
        }

        if (strlen($mdp) < 8) {
            flashMessage('danger', 'Le mot de passe doit contenir au moins 8 caractères.');
            $this->showRegister();
            return;
        }

        if ($mdp !== $mdp2) {
            flashMessage('danger', 'Les mots de passe ne correspondent pas.');
            $this->showRegister();
            return;
        }

        if ($this->model->emailExiste($email)) {
            flashMessage('danger', 'Cet email est déjà utilisé.');
            $this->showRegister();
            return;
        }

        // 4. Hacher le mot de passe AVANT de l'envoyer au Model
        $hash = password_hash($mdp, PASSWORD_BCRYPT);
        $this->model->creer($nom, $prenom, $email,$numtel, $hash);

        flashMessage(
            'success',
            'Inscription réussie ! Votre compte est en attente de validation par l\'administrateur.'
        );
        header('Location: index.php?page=login');
        exit;
    }

    // ════════════════════════════════════════════════════════════
    //  DÉCONNEXION
    // ════════════════════════════════════════════════════════════

    public function logout(): void
    {
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    }
}
