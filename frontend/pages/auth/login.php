<?php
// frontend/pages/auth/login.php
require_once __DIR__ . '/../../../backend/middlewares/auth.php';
$flash = getFlash();

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — Budget Sync</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="icon" type="image/png" sizes="16x16" href="/BudgetSync/frontend/assets/favicon-16x16.png">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #f8faff;
        }

        /* ── PANNEAU GAUCHE (illustration) ──────────────── */
        .auth-visual {
            background: linear-gradient(160deg, #0a1628 0%, #0f2952 40%, #1a4480 70%, #0d3a6e 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        /* Cercles décoratifs */
        .auth-visual::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59, 130, 246, .18) 0%, transparent 70%);
            top: -100px;
            right: -100px;
        }

        .auth-visual::after {
            content: '';
            position: absolute;
            width: 350px;
            height: 350px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 185, 129, .12) 0%, transparent 70%);
            bottom: -80px;
            left: -60px;
        }

        /* Grille de points décorative */
        .dots-grid {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, .04) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        .visual-top {
            position: relative;
            z-index: 2;
        }

        .visual-brand {
            display: flex;
            align-items: center;
            gap: .75rem;
        }

        .visual-brand-icon {
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, .1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, .15);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #60a5fa;
        }

        .visual-brand-name {
            font-size: 1.2rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -.02em;
        }

        .visual-brand-name span {
            color: #60a5fa;
        }

        .visual-middle {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            padding: 2rem 0;
        }

        /* ── Conteneur image animée ── */
        .img-scene {
            position: relative;
            width: 100%;
            max-width: 380px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Halo lumineux derrière l'image */
        .img-halo {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle,
                    rgba(59, 130, 246, .22) 0%,
                    rgba(16, 185, 129, .1) 50%,
                    transparent 75%);
            animation: haloPulse 4s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes haloPulse {

            0%,
            100% {
                transform: scale(1);
                opacity: .7;
            }

            50% {
                transform: scale(1.15);
                opacity: 1;
            }
        }

        /* Anneau rotatif extérieur */
        .img-ring {
            position: absolute;
            width: 320px;
            height: 320px;
            border-radius: 50%;
            border: 1.5px dashed rgba(59, 130, 246, .25);
            animation: ringRotate 18s linear infinite;
            pointer-events: none;
        }

        @keyframes ringRotate {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        /* Points sur le ring */
        .img-ring::before,
        .img-ring::after {
            content: '';
            position: absolute;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #3b82f6;
            box-shadow: 0 0 8px #3b82f6;
        }

        .img-ring::before {
            top: -4px;
            left: 50%;
            transform: translateX(-50%);
        }

        .img-ring::after {
            bottom: -4px;
            left: 50%;
            transform: translateX(-50%);
        }

        /* Anneau intérieur contre-rotatif */
        .img-ring-inner {
            position: absolute;
            width: 240px;
            height: 240px;
            border-radius: 50%;
            border: 1px solid rgba(16, 185, 129, .2);
            animation: ringRotate 12s linear infinite reverse;
            pointer-events: none;
        }

        .img-ring-inner::before {
            content: '';
            position: absolute;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 6px #10b981;
            top: -3px;
            left: 50%;
            transform: translateX(-50%);
        }

        /* L'image elle-même */
        .img-main {
            position: relative;
            z-index: 2;
            width: 220px;
            height: 220px;
            object-fit: contain;
            border-radius: 24px;
            /* Lévitation douce */
            animation: levitate 5s ease-in-out infinite;
            /* Glow subtil */
            filter: drop-shadow(0 20px 40px rgba(0, 0, 0, .3)) drop-shadow(0 0 30px rgba(59, 130, 246, .2));
        }

        @keyframes levitate {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            30% {
                transform: translateY(-12px) rotate(.5deg);
            }

            60% {
                transform: translateY(-6px) rotate(-.3deg);
            }
        }

        /* Particules flottantes */
        .particles {
            position: absolute;
            inset: -40px;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            opacity: 0;
            animation: particleFly var(--dur, 6s) ease-in-out infinite var(--delay, 0s);
        }

        @keyframes particleFly {
            0% {
                transform: translate(0, 0) scale(0);
                opacity: 0;
            }

            20% {
                opacity: .8;
            }

            80% {
                opacity: .4;
            }

            100% {
                transform: translate(var(--tx, 20px), var(--ty, -80px)) scale(0);
                opacity: 0;
            }
        }

        /* Badges flottants autour de l'image */
        .img-badge {
            position: absolute;
            z-index: 3;
            background: rgba(255, 255, 255, .1);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, .15);
            border-radius: 12px;
            padding: .5rem .8rem;
            display: flex;
            align-items: center;
            gap: .45rem;
            font-size: .73rem;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            box-shadow: 0 8px 24px rgba(0, 0, 0, .2);
        }

        .img-badge i {
            font-size: .85rem;
        }

        .badge-tl {
            top: 8%;
            left: -8%;
            animation: badgeFloat1 6s ease-in-out infinite;
        }

        .badge-tr {
            top: 15%;
            right: -10%;
            animation: badgeFloat2 7s ease-in-out infinite .8s;
        }

        .badge-bl {
            bottom: 18%;
            left: -12%;
            animation: badgeFloat1 5.5s ease-in-out infinite 1.2s;
        }

        .badge-br {
            bottom: 10%;
            right: -8%;
            animation: badgeFloat2 6.5s ease-in-out infinite .4s;
        }

        @keyframes badgeFloat1 {

            0%,
            100% {
                transform: translateY(0) translateX(0);
            }

            40% {
                transform: translateY(-8px) translateX(3px);
            }

            70% {
                transform: translateY(-4px) translateX(-2px);
            }
        }

        @keyframes badgeFloat2 {

            0%,
            100% {
                transform: translateY(0) translateX(0);
            }

            35% {
                transform: translateY(-6px) translateX(-3px);
            }

            65% {
                transform: translateY(-10px) translateX(2px);
            }
        }

        .visual-bottom {
            position: relative;
            z-index: 2;
        }

        .visual-tagline {
            color: rgba(255, 255, 255, .35);
            font-size: .78rem;
            text-align: center;
        }

        .visual-tagline strong {
            color: rgba(255, 255, 255, .6);
        }

        /* ── PANNEAU DROIT (formulaire) ─────────────────── */
        .auth-form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #f8faff;
        }

        .auth-form-wrap {
            width: 100%;
            max-width: 420px;
        }

        .form-header {
            margin-bottom: 2rem;
        }

        .form-header-eyebrow {
            font-size: .72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: #3b82f6;
            margin-bottom: .5rem;
        }

        .form-header h1 {
            font-size: 1.75rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -.03em;
            line-height: 1.15;
            margin-bottom: .5rem;
        }

        .form-header p {
            color: #64748b;
            font-size: .88rem;
            line-height: 1.6;
        }

        /* Champs */
        .field-wrap {
            margin-bottom: 1.1rem;
        }

        .field-label {
            display: block;
            font-size: .8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: .45rem;
        }

        .field-input-wrap {
            position: relative;
        }

        .field-icon {
            position: absolute;
            left: .9rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: .95rem;
            pointer-events: none;
            transition: color .15s;
        }

        .field-input {
            width: 100%;
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: .75rem 1rem .75rem 2.65rem;
            font-size: .9rem;
            font-family: inherit;
            color: #1e293b;
            transition: border-color .15s, box-shadow .15s;
            outline: none;
        }

        .field-input::placeholder {
            color: #c4cdd8;
        }

        .field-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, .1);
        }

        .field-input:focus~.field-icon,
        .field-input-wrap:focus-within .field-icon {
            color: #3b82f6;
        }

        .field-eye {
            position: absolute;
            right: .85rem;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            cursor: pointer;
            font-size: .95rem;
            background: none;
            border: none;
            padding: 4px;
            transition: color .15s;
        }

        .field-eye:hover {
            color: #475569;
        }

        /* Bouton principal */
        .btn-primary-auth {
            width: 100%;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none;
            color: #fff;
            padding: .85rem 1rem;
            border-radius: 12px;
            font-size: .92rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: transform .15s, box-shadow .15s, opacity .15s;
            box-shadow: 0 4px 16px rgba(37, 99, 235, .35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
        }

        .btn-primary-auth:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(37, 99, 235, .4);
            opacity: .96;
        }

        .btn-primary-auth:active {
            transform: translateY(0);
        }

        /* Séparateur */
        .auth-divider {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin: 1.25rem 0;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .auth-divider span {
            font-size: .78rem;
            color: #94a3b8;
            white-space: nowrap;
        }

        /* Lien register */
        .auth-link-row {
            text-align: center;
            font-size: .86rem;
            color: #64748b;
        }

        .auth-link {
            color: #2563eb;
            font-weight: 600;
            text-decoration: none;
            transition: color .15s;
        }

        .auth-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }

        /* Flash */
        .flash-box {
            border-radius: 12px;
            padding: .75rem 1rem;
            font-size: .85rem;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: .6rem;
            border: 1px solid;
        }

        .flash-box.success {
            background: #f0fdf4;
            border-color: #bbf7d0;
            color: #166534;
        }

        .flash-box.danger {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }

        .flash-box.warning {
            background: #fffbeb;
            border-color: #fde68a;
            color: #92400e;
        }

        .flash-box.info {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1e40af;
        }

        /* Sécurité badge */
        .security-note {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            margin-top: 1.5rem;
            color: #94a3b8;
            font-size: .75rem;
        }

        .security-note i {
            color: #10b981;
        }

        /* Responsive */
        @media (max-width: 900px) {
            body {
                grid-template-columns: 1fr;
            }

            .auth-visual {
                display: none;
            }
        }
    </style>
</head>

<body>

    <!-- ── Panneau gauche : illustration ─────────────────── -->
    <div class="auth-visual">
        <div class="dots-grid"></div>



        <div class="visual-middle">
            <div class="img-scene">

                <!-- Halo + rings -->
                <div class="img-halo"></div>
                <div class="img-ring"></div>
                <div class="img-ring-inner"></div>

                <!-- Particules flottantes -->
                <div class="particles">
                    <div class="particle"
                        style="width:6px;height:6px;background:#3b82f6;left:20%;top:70%;--dur:7s;--delay:0s;--tx:15px;--ty:-90px;">
                    </div>
                    <div class="particle"
                        style="width:4px;height:4px;background:#10b981;left:75%;top:60%;--dur:5.5s;--delay:.8s;--tx:-20px;--ty:-70px;">
                    </div>
                    <div class="particle"
                        style="width:5px;height:5px;background:#f59e0b;left:35%;top:80%;--dur:6.5s;--delay:1.5s;--tx:25px;--ty:-85px;">
                    </div>
                    <div class="particle"
                        style="width:3px;height:3px;background:#8b5cf6;left:65%;top:75%;--dur:8s;--delay:.3s;--tx:-10px;--ty:-100px;">
                    </div>
                    <div class="particle"
                        style="width:5px;height:5px;background:#60a5fa;left:50%;top:85%;--dur:6s;--delay:2s;--tx:-30px;--ty:-80px;">
                    </div>
                    <div class="particle"
                        style="width:4px;height:4px;background:#34d399;left:25%;top:55%;--dur:7.5s;--delay:1s;--tx:20px;--ty:-95px;">
                    </div>
                </div>

                <!-- IMAGE PRINCIPALE — remplacer la src par votre fichier -->
                <img src="/BudgetSync/frontend/assets/img.png" alt="Budget Sync illustration" class="img-main"
                    onerror="this.style.display='none'; document.getElementById('img-placeholder').style.display='flex';">

                <!-- Placeholder affiché si image absente -->
                <div id="img-placeholder" style="display:none;width:200px;height:200px;border-radius:24px;
                        background:rgba(255,255,255,.06);border:2px dashed rgba(255,255,255,.15);
                        flex-direction:column;align-items:center;justify-content:center;gap:.5rem;
                        color:rgba(255,255,255,.35);font-size:.78rem;text-align:center;
                        animation:levitate 5s ease-in-out infinite;">
                    <i class="bi bi-image" style="font-size:2.5rem;opacity:.4;"></i>
                    <span>Votre image ici</span>
                </div>

                <!-- Badges flottants -->
                <div class="img-badge badge-tl">
                    <i class="bi bi-graph-up-arrow" style="color:#34d399;"></i>
                    +18% ce mois
                </div>
                <div class="img-badge badge-tr">
                    <i class="bi bi-shield-check" style="color:#60a5fa;"></i>
                    Sécurisé
                </div>
                <div class="img-badge badge-bl">
                    <i class="bi bi-people-fill" style="color:#f59e0b;"></i>
                    Collaboratif
                </div>
                <div class="img-badge badge-br">
                    <i class="bi bi-bell-fill" style="color:#f87171;"></i>
                    Alertes actives
                </div>

            </div>
        </div>

        <div class="visual-bottom">
            <p class="visual-tagline">
                <strong>Gérez vos finances</strong> avec clarté et sérénité.<br>
                Budget collaboratif, alertes intelligentes, statistiques détaillées.
            </p>
        </div>
    </div>

    <!-- ── Panneau droit : formulaire ────────────────────── -->
    <div class="auth-form-panel">
        <div class="auth-form-wrap">

            <div class="form-header">
                <div class="form-header-eyebrow"><i class="bi bi-shield-check me-1"></i>Connexion sécurisée</div>
                <h1>Bon retour<br>parmi nous 👋</h1>
                <p>Accédez à votre espace de gestion budgétaire personnel et collaboratif.</p>
            </div>

            <?php if ($flash): ?>
                <div class="flash-box <?= $flash['type'] ?>">
                    <i class="bi bi-<?= match ($flash['type']) {
                        'success' => 'check-circle-fill', 'danger' => 'exclamation-circle-fill',
                        'warning' => 'exclamation-triangle-fill', default => 'info-circle-fill'
                    } ?> flex-shrink-0 mt-1" style="font-size:.9rem;"></i>
                    <span><?= htmlspecialchars($flash['message'], ENT_QUOTES) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="index.php?page=login&action=submit" novalidate>
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <!-- Email -->
                <div class="field-wrap">
                    <label class="field-label" for="email">Adresse email</label>
                    <div class="field-input-wrap">
                        <i class="bi bi-envelope field-icon"></i>
                        <input type="email" id="email" name="email" class="field-input" placeholder="vous@exemple.com"
                            value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>" autocomplete="email"
                            required autofocus>
                    </div>
                </div>

                <!-- Mot de passe -->
                <div class="field-wrap">
                    <label class="field-label" for="mdpField">Mot de passe</label>
                    <div class="field-input-wrap">
                        <i class="bi bi-lock field-icon"></i>
                        <input type="password" id="mdpField" name="mot_de_passe" class="field-input"
                            placeholder="••••••••" autocomplete="current-password" required>
                        <button type="button" class="field-eye" onclick="toggleMdp()" tabindex="-1">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-primary-auth" style="margin-top:.5rem;">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Se connecter
                </button>
            </form>

            <div class="auth-divider"><span>Vous n'avez pas encore de compte ?</span></div>

            <div class="auth-link-row">
                <a href="index.php?page=register" class="auth-link">
                    <i class="bi bi-person-plus me-1"></i>Créer un compte gratuitement
                </a>
            </div>

            <div class="security-note">
                <i class="bi bi-shield-lock-fill"></i>
                Connexion chiffrée et sécurisée
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleMdp() {
            const f = document.getElementById('mdpField');
            const i = document.getElementById('eyeIcon');
            f.type = f.type === 'password' ? 'text' : 'password';
            i.className = f.type === 'text' ? 'bi bi-eye-slash' : 'bi bi-eye';
        }
    </script>
</body>

</html>