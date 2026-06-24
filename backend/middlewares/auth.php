<?php
// backend/middelwares/auth.php — Session, CSRF, helpers sécurité

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function estConnecte(): bool
{
    return isset($_SESSION['user_id']);
}

// function requiertConnexion(): void
// {
//     if (!estConnecte()) {
//         header('Location: index.php?page=login');
//         exit;
//     }
// }

function requiertConnexion(): void
{
    if (!estConnecte()) {
        header('Location: index.php?page=login');
        exit;
    }
    require_once __DIR__ . '/../models/UserModel.php';
    $model = new UserModel();
    $user = $model->trouverParId($_SESSION['user_id']);
    // 🔥 user deleted from DB → force logout
    if (!$user) {
        $_SESSION = [];
        session_destroy();
        flashMessage('warning', 'Votre compte a été supprimé.');
        header('Location: index.php?page=login');
        exit;
    }
}

function estAdmin(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requiertAdmin(): void
{
    requiertConnexion();
    if (!estAdmin()) {
        flashMessage('danger', 'Accès réservé aux administrateurs.');
        header('Location: index.php?page=dashboard');
        exit;
    }
}

function utilisateurConnecte(): array
{
    return [
        'id'     => $_SESSION['user_id'] ?? null,
        'nom'    => $_SESSION['nom']     ?? '',
        'prenom' => $_SESSION['prenom']  ?? '',
        'email'  => $_SESSION['email']   ?? '',
        'role'   => $_SESSION['role']    ?? 'utilisateur',
    ];
}

// ── CSRF ─────────────────────────────────────────────
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifier_csrf(string $token): bool
{
    return isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

// ── XSS ──────────────────────────────────────────────
function nettoyer(string $val): string
{
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

// ── Flash messages ────────────────────────────────────
function flashMessage(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}