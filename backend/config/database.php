<?php
// config/database.php — Connexion PDO (Singleton)
function loadEnv($path)
{
    $env = [];

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        $parts = explode('=', $line, 2);

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        $env[$key] = $value;
    }

    return $env;
}

$env = loadEnv(__DIR__ . '/../../.env');

define('DB_HOST', $env['DB_HOST']);
define('DB_NAME', $env['DB_NAME']);
define('DB_USER', $env['DB_USER']);
define('DB_PASS', $env['DB_PASS']);
define('DB_CHARSET', $env['DB_CHARSET']);
define('APP_NAME', $env['APP_NAME']);

// ── SMTP ────────────────────────────────────────────
define('SMTP_HOST', $env['SMTP_HOST']);
define('SMTP_PORT', $env['SMTP_PORT']);
define('SMTP_USER', $env['SMTP_USER']);   // choisir le compte google a utiliser pour renvoyer les emails 
define('SMTP_PASS', $env['SMTP_PASS']);  // saisir le code fournit par google apres avoir choisit le nom d'application
define('SMTP_FROM', $env['SMTP_USER']);
define('SMTP_FROM_NAME', $env['SMTP_FROM_NAME']);

function getDB(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('Erreur de connexion à la base de données.');
        }
    }
    return $pdo;
}