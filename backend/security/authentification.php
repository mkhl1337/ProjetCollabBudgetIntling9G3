<?php
// includes/auth.php — Session, CSRF, helpers sécurité

session_start(); // Doit être appelé AVANT tout output HTML

// ── Connexion ────────────────────────────────────────────────────

/** Retourne true si un utilisateur est connecté */
function estConnecte(): bool
{
    return isset($_SESSION['user_id']);
}

/** Redirige vers la page de connexion si non connecté */
function requiertConnexion(): void
{
    if (!estConnecte()) {
        header('Location: /../../frontend/pages/index.php?page=login');
        exit;
    }
}

/** Retourne true si l'utilisateur connecté est admin */
function estAdmin(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/** Retourne un tableau avec les infos de l'utilisateur connecté */
function utilisateurConnecte(): array
{
    return [
        'id'     => $_SESSION['user_id'] ?? null,
        'nom'    => $_SESSION['nom']     ?? '',
        'prenom' => $_SESSION['prenom']  ?? '',
        'email'  => $_SESSION['email']   ?? '',
        'numtel' => $_SESSION['numtel'] ?? '',
        'role'   => $_SESSION['role']    ?? 'utilisateur',
    ];
}

// ── Protection CSRF ──────────────────────────────────────────────

/**
 * Génère un token CSRF et le stocke en session.
 * À placer dans chaque formulaire : <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Vérifie que le token soumis correspond au token en session.
 * hash_equals() protège contre les attaques par timing.
 */
function verifier_csrf(string $token): bool
{
    return isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

// ── Sécurisation des sorties (anti-XSS) ─────────────────────────

/**
 * Nettoie une valeur avant de l'afficher dans le HTML.
 * Équivalent de htmlspecialchars() avec ENT_QUOTES.
 */
function nettoyer(string $val): string
{
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

// ── Messages flash ───────────────────────────────────────────────

/** Stocke un message à afficher une seule fois (type: success|danger|warning|info) */
function flashMessage(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Récupère le message flash et le supprime de la session */
function getFlash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
