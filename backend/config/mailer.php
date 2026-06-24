<?php
// config/mailer.php — Envoi SMTP natif (sans dépendance externe)

require_once __DIR__ . '/database.php';

/**
 * Envoie un email via SMTP avec authentification TLS/STARTTLS.
 * Compatible Gmail (port 587) et la plupart des serveurs SMTP.
 *
 * @param string $to      Adresse destinataire
 * @param string $subject Objet du mail
 * @param string $body    Corps HTML du mail
 * @return bool
 */
function envoyerEmail(string $to, string $subject, string $body): bool
{
    $host     = SMTP_HOST;
    $port     = SMTP_PORT;
    $user     = SMTP_USER;
    $pass     = SMTP_PASS;
    $from     = SMTP_FROM;
    $fromName = SMTP_FROM_NAME;

    try {
        // Connexion TCP
        $socket = fsockopen($host, $port, $errno, $errstr, 10);
        if (!$socket) return false;

        $read = function() use ($socket): string {
            $r = '';
            while ($line = fgets($socket, 515)) {
                $r .= $line;
                if ($line[3] === ' ') break;
            }
            return $r;
        };
        $send = function(string $cmd) use ($socket): string {
            fwrite($socket, $cmd . "\r\n");
            $r = '';
            while ($line = fgets($socket, 515)) {
                $r .= $line;
                if ($line[3] === ' ') break;
            }
            return $r;
        };

        $read(); // banner
        $send("EHLO " . gethostname());

        // STARTTLS
        $r = $send("STARTTLS");
        if (!str_starts_with($r, '220')) { fclose($socket); return false; }

        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);

        $send("EHLO " . gethostname());
        $send("AUTH LOGIN");
        $send(base64_encode($user));
        $r = $send(base64_encode($pass));
        if (!str_starts_with($r, '235')) { fclose($socket); return false; }

        $send("MAIL FROM:<{$from}>");
        $send("RCPT TO:<{$to}>");
        $send("DATA");

        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$from}>\r\n";
        $headers .= "To: <{$to}>\r\n";
        $headers .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $headers .= "Date: " . date('r') . "\r\n";

        fwrite($socket, $headers . "\r\n" . $body . "\r\n.\r\n");
        $r = $read();

        $send("QUIT");
        fclose($socket);

        return str_starts_with($r, '250');
    } catch (Throwable $e) {
        return false;
    }
}

/**
 * Template HTML pour l'email d'activation de compte.
 */
function emailActivationCompte(string $prenom, string $nom): string
{
    return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:'Segoe UI',Arial,sans-serif;">
  <table width="100%" cellpadding="0" cellspacing="0" style="padding:40px 20px;">
    <tr><td align="center">
      <table width="560" cellpadding="0" cellspacing="0" style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);">
        <tr>
          <td style="background:linear-gradient(135deg,#0f172a,#2563eb);padding:32px;text-align:center;">
            <span style="font-size:2rem;">💰</span>
            <h1 style="color:#fff;margin:12px 0 0;font-size:1.6rem;font-weight:700;">Budget Sync</h1>
          </td>
        </tr>
        <tr>
          <td style="padding:36px;">
            <h2 style="color:#1e293b;margin:0 0 16px;font-size:1.3rem;">Votre compte est activé !</h2>
            <p style="color:#475569;line-height:1.7;margin:0 0 20px;">
              Bonjour <strong>{$prenom} {$nom}</strong>,
            </p>
            <p style="color:#475569;line-height:1.7;margin:0 0 28px;">
              Votre compte <strong>Budget Sync</strong> a été <strong>validé par l'administrateur</strong>.
              Vous pouvez dès à présent vous connecter et commencer à gérer vos budgets.
            </p>
            <div style="text-align:center;margin:28px 0;">
              <a href="#" style="background:#2563eb;color:#fff;padding:14px 36px;border-radius:10px;text-decoration:none;font-weight:600;font-size:1rem;display:inline-block;">
                Se connecter
              </a>
            </div>
            <p style="color:#94a3b8;font-size:.85rem;margin:0;">
              Si vous n'avez pas créé de compte sur Budget Sync, ignorez cet email.
            </p>
          </td>
        </tr>
        <tr>
          <td style="background:#f8fafc;padding:20px;text-align:center;">
            <p style="color:#94a3b8;font-size:.8rem;margin:0;">© Budget Sync — Application de gestion collaborative de budget</p>
          </td>
        </tr>
      </table>
    </td></tr>
  </table>
</body>
</html>
HTML;
}