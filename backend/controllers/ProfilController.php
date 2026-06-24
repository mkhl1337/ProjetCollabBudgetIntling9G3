<?php
// controllers/ProfilController.php

require_once __DIR__ . '/../middlewares/auth.php';
require_once __DIR__ . '/../models/UserModel.php';

class ProfilController
{
    private UserModel $model;

    public function __construct()
    {
        requiertConnexion();
        $this->model = new UserModel();
    }

    public function show(): void
    {
        $user      = utilisateurConnecte();
        $profil    = $this->model->trouverParId($user['id']);
        $aDejaDemandeSupp = $this->model->aDemandeSuppression($user['id']);
        $pageTitle = 'Mon profil';
        require_once __DIR__ . '/../../frontend/pages/profil.php';
    }

    public function handleModifier(): void
    {
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token invalide.'); $this->show(); return;
        }
        $uid    = utilisateurConnecte()['id'];
        $nom    = trim($_POST['nom']    ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email  = trim($_POST['email']  ?? '');

        if (empty($nom) || empty($prenom) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flashMessage('danger', 'Données invalides.'); $this->show(); return;
        }
        $this->model->mettreAJourProfil($uid, $nom, $prenom, $email);
        $_SESSION['nom']    = $nom;
        $_SESSION['prenom'] = $prenom;
        $_SESSION['email']  = $email;
        flashMessage('success', 'Profil mis à jour avec succès.');
        header('Location: index.php?page=profil'); exit;
    }

    public function handleChangerMdp(): void
    {
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token invalide.'); $this->show(); return;
        }
        $uid    = utilisateurConnecte()['id'];
        $profil = $this->model->trouverParId($uid);
        $ancien = $_POST['ancien_mdp']  ?? '';
        $new1   = $_POST['nouveau_mdp'] ?? '';
        $new2   = $_POST['confirm_mdp'] ?? '';

        if (!password_verify($ancien, $profil['mot_de_passe'])) {
            flashMessage('danger', 'Mot de passe actuel incorrect.');
        } elseif (strlen($new1) < 8) {
            flashMessage('danger', 'Nouveau mot de passe : 8 caractères minimum.');
        } elseif ($new1 !== $new2) {
            flashMessage('danger', 'Les mots de passe ne correspondent pas.');
        } else {
            $this->model->changerMotDePasse($uid, password_hash($new1, PASSWORD_BCRYPT));
            flashMessage('success', 'Mot de passe modifié avec succès.');
        }
        header('Location: index.php?page=profil'); exit;
    }

    public function handleDemandeSuppression(): void
    {
        if (!verifier_csrf($_POST['csrf_token'] ?? '')) {
            flashMessage('danger', 'Token invalide.'); $this->show(); return;
        }

        if (utilisateurConnecte()['role']=='admin'){
            flashMessage('danger', 'En tant qu\'administratuer vous ne pouvez pas éffectuer une demande de suppression de compte ! '); $this->show(); return;
        }
        $uid   = utilisateurConnecte()['id'];
        $mdp   = $_POST['mot_de_passe_confirm'] ?? '';
        $motif = trim($_POST['motif'] ?? '');

        // Vérifier mot de passe
        $profil = $this->model->trouverParId($uid);
        if (!password_verify($mdp, $profil['mot_de_passe'])) {
            flashMessage('danger', 'Mot de passe incorrect. Demande annulée.');
            header('Location: index.php?page=profil'); exit;
        }

        // Une seule demande possible
        if ($this->model->aDemandeSuppression($uid)) {
            flashMessage('warning', 'Vous avez déjà une demande de suppression en cours.');
            header('Location: index.php?page=profil'); exit;
        }

        $this->model->demanderSuppression($uid, $motif);
        flashMessage('info', 'Demande de suppression envoyée à l\'administrateur. Elle ne peut pas être annulée.');
        header('Location: index.php?page=profil'); exit;
    }
}