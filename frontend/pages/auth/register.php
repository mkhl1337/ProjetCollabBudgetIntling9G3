<?php
// frontend/pages/auth/register.php
require_once __DIR__ . '/../../../backend/middlewares/auth.php';
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — Budget Sync</title>
    <link rel="icon" type="image/png" sizes="16x16" href="/BudgetSync/frontend/assets/favicon-16x16.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #f8faff;
        }

        /* ══════════════════════════════════════════════════
           PANNEAU GAUCHE
        ══════════════════════════════════════════════════ */
        .auth-visual {
            background: linear-gradient(160deg, #0a1628 0%, #0f2952 40%, #1a4480 70%, #0d3a6e 100%);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        /* Halos décoratifs fond */
        .auth-visual::before {
            content: '';
            position: absolute;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59,130,246,.18) 0%, transparent 70%);
            top: -100px; right: -100px;
        }
        .auth-visual::after {
            content: '';
            position: absolute;
            width: 350px; height: 350px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16,185,129,.12) 0%, transparent 70%);
            bottom: -80px; left: -60px;
        }

        /* Grille de points */
        .dots-grid {
            position: absolute; inset: 0;
            background-image: radial-gradient(rgba(255,255,255,.04) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        .visual-top { position: relative; z-index: 2; }
        .visual-brand {
            display: flex; align-items: center; gap: .75rem;
        }
        .visual-brand-icon {
            width: 44px; height: 44px;
            background: rgba(255,255,255,.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem; color: #60a5fa;
        }
        .visual-brand-name {
            font-size: 1.2rem; font-weight: 800;
            color: #fff; letter-spacing: -.02em;
        }
        .visual-brand-name span { color: #60a5fa; }

        /* ── Scène image animée ── */
        .visual-middle {
            position: relative; z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            flex: 1;
            padding: 2rem 0;
        }
        .img-scene {
            position: relative;
            width: 100%; max-width: 380px;
            display: flex;
            align-items: center; justify-content: center;
        }

        /* Halo pulsant */
        .img-halo {
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle,
                rgba(59,130,246,.22) 0%,
                rgba(16,185,129,.1) 50%,
                transparent 75%);
            animation: haloPulse 4s ease-in-out infinite;
        }
        @keyframes haloPulse {
            0%,100% { transform: scale(1);    opacity: .7; }
            50%      { transform: scale(1.15); opacity: 1;  }
        }

        /* Ring extérieur rotatif */
        .img-ring {
            position: absolute;
            width: 320px; height: 320px;
            border-radius: 50%;
            border: 1.5px dashed rgba(59,130,246,.25);
            animation: ringRotate 18s linear infinite;
        }
        @keyframes ringRotate {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }
        .img-ring::before, .img-ring::after {
            content: '';
            position: absolute;
            width: 8px; height: 8px;
            border-radius: 50%;
            background: #3b82f6;
            box-shadow: 0 0 8px #3b82f6;
        }
        .img-ring::before { top: -4px;    left: 50%; transform: translateX(-50%); }
        .img-ring::after  { bottom: -4px; left: 50%; transform: translateX(-50%); }

        /* Ring intérieur contre-rotatif */
        .img-ring-inner {
            position: absolute;
            width: 240px; height: 240px;
            border-radius: 50%;
            border: 1px solid rgba(16,185,129,.2);
            animation: ringRotate 12s linear infinite reverse;
        }
        .img-ring-inner::before {
            content: '';
            position: absolute;
            width: 6px; height: 6px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 6px #10b981;
            top: -3px; left: 50%;
            transform: translateX(-50%);
        }

        /* Image principale */
        .img-main {
            position: relative; z-index: 2;
            width: 220px; height: 220px;
            object-fit: contain;
            border-radius: 24px;
            animation: levitate 5s ease-in-out infinite;
            filter: drop-shadow(0 20px 40px rgba(0,0,0,.3))
                    drop-shadow(0 0 30px rgba(59,130,246,.2));
        }
        @keyframes levitate {
            0%,100% { transform: translateY(0)    rotate(0deg);  }
            30%      { transform: translateY(-12px) rotate(.5deg); }
            60%      { transform: translateY(-6px)  rotate(-.3deg); }
        }

        /* Particules flottantes */
        .particles { position: absolute; inset: -40px; pointer-events: none; }
        .particle {
            position: absolute;
            border-radius: 50%;
            opacity: 0;
            animation: particleFly var(--dur,6s) ease-in-out infinite var(--delay,0s);
        }
        @keyframes particleFly {
            0%   { transform: translate(0,0) scale(0); opacity: 0; }
            20%  { opacity: .8; }
            80%  { opacity: .4; }
            100% { transform: translate(var(--tx,20px),var(--ty,-80px)) scale(0); opacity: 0; }
        }

        /* Badges flottants */
        .img-badge {
            position: absolute; z-index: 3;
            background: rgba(255,255,255,.1);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 12px;
            padding: .5rem .8rem;
            display: flex; align-items: center; gap: .45rem;
            font-size: .73rem; font-weight: 600; color: #fff;
            white-space: nowrap;
            box-shadow: 0 8px 24px rgba(0,0,0,.2);
        }
        .img-badge i { font-size: .85rem; }
        .badge-tl { top: 8%;   left: -8%;  animation: badgeFloat1 6s ease-in-out infinite; }
        .badge-tr { top: 15%;  right: -10%; animation: badgeFloat2 7s ease-in-out infinite .8s; }
        .badge-bl { bottom: 18%; left: -12%; animation: badgeFloat1 5.5s ease-in-out infinite 1.2s; }
        .badge-br { bottom: 10%; right: -8%; animation: badgeFloat2 6.5s ease-in-out infinite .4s; }

        @keyframes badgeFloat1 {
            0%,100% { transform: translateY(0)    translateX(0); }
            40%     { transform: translateY(-8px)  translateX(3px); }
            70%     { transform: translateY(-4px)  translateX(-2px); }
        }
        @keyframes badgeFloat2 {
            0%,100% { transform: translateY(0)    translateX(0); }
            35%     { transform: translateY(-6px)  translateX(-3px); }
            65%     { transform: translateY(-10px) translateX(2px); }
        }

        .visual-bottom { position: relative; z-index: 2; }
        .visual-tagline {
            color: rgba(255,255,255,.35);
            font-size: .78rem; text-align: center;
        }
        .visual-tagline strong { color: rgba(255,255,255,.6); }

        /* ══════════════════════════════════════════════════
           PANNEAU DROIT — Formulaire
        ══════════════════════════════════════════════════ */
        .auth-form-panel {
            display: flex; align-items: center; justify-content: center;
            padding: 2rem;
            background: #f8faff;
            overflow-y: auto;
        }
        .auth-form-wrap { width: 100%; max-width: 440px; }

        .form-header { margin-bottom: 1.75rem; }
        .form-header-eyebrow {
            font-size: .72rem; font-weight: 600;
            text-transform: uppercase; letter-spacing: .1em;
            color: #3b82f6; margin-bottom: .5rem;
        }
        .form-header h1 {
            font-size: 1.65rem; font-weight: 800;
            color: #0f172a; letter-spacing: -.03em;
            line-height: 1.2; margin-bottom: .45rem;
        }
        .form-header p { color: #64748b; font-size: .87rem; line-height: 1.6; }

        /* Champs */
        .field-wrap { margin-bottom: .95rem; }
        .field-label {
            display: block; font-size: .8rem; font-weight: 600;
            color: #374151; margin-bottom: .4rem;
        }
        .field-input-wrap { position: relative; }
        .field-icon {
            position: absolute; left: .9rem; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8; font-size: .95rem;
            pointer-events: none; transition: color .15s;
        }
        .field-input {
            width: 100%;
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: .72rem 1rem .72rem 2.6rem;
            font-size: .88rem; font-family: inherit;
            color: #1e293b; outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .field-input::placeholder { color: #c4cdd8; }
        .field-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59,130,246,.1);
        }
        .field-input:focus + .field-icon,
        .field-input-wrap:focus-within .field-icon { color: #3b82f6; }
        .field-eye {
            position: absolute; right: .85rem; top: 50%;
            transform: translateY(-50%);
            color: #94a3b8; cursor: pointer;
            background: none; border: none; padding: 4px; font-size: .9rem;
            transition: color .15s;
        }
        .field-eye:hover { color: #475569; }

        /* Match password */
        .pwd-match { font-size: .78rem; margin-top: .35rem; min-height: 1.1rem; }

        /* Règles MDP */
        .pwd-rules {
            display: flex; gap: .5rem; flex-wrap: wrap;
            margin-top: .4rem;
        }
        .pwd-rule {
            font-size: .72rem; padding: .2rem .55rem;
            border-radius: 6px;
            background: #f1f5f9; color: #94a3b8;
            border: 1px solid #e2e8f0;
            transition: all .2s;
            display: flex; align-items: center; gap: .3rem;
        }
        .pwd-rule.ok { background: #ecfdf5; color: #065f46; border-color: #a7f3d0; }

        /* Info box */
        .info-box {
            background: #eff6ff; border: 1px solid #bfdbfe;
            border-radius: 12px; padding: .7rem .9rem;
            font-size: .8rem; color: #1e40af;
            display: flex; align-items: flex-start; gap: .5rem;
            margin-bottom: 1rem;
        }
        .info-box i { flex-shrink: 0; margin-top: 2px; }

        /* Bouton */
        .btn-primary-auth {
            width: 100%;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border: none; color: #fff;
            padding: .82rem 1rem;
            border-radius: 12px;
            font-size: .92rem; font-weight: 600; font-family: inherit;
            cursor: pointer;
            transition: transform .15s, box-shadow .15s, opacity .15s;
            box-shadow: 0 4px 16px rgba(37,99,235,.35);
            display: flex; align-items: center; justify-content: center; gap: .5rem;
        }
        .btn-primary-auth:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(37,99,235,.4);
            opacity: .96;
        }

        /* Lien login */
        .auth-link-row { text-align: center; font-size: .86rem; color: #64748b; margin-top: 1rem; }
        .auth-link {
            color: #2563eb; font-weight: 600; text-decoration: none;
            transition: color .15s;
        }
        .auth-link:hover { color: #1d4ed8; text-decoration: underline; }

        /* Flash */
        .flash-box {
            border-radius: 12px; padding: .75rem 1rem;
            font-size: .84rem; margin-bottom: 1.1rem;
            display: flex; align-items: flex-start; gap: .6rem;
            border: 1px solid;
        }
        .flash-box.success { background:#f0fdf4;border-color:#bbf7d0;color:#166534; }
        .flash-box.danger  { background:#fef2f2;border-color:#fecaca;color:#991b1b; }
        .flash-box.warning { background:#fffbeb;border-color:#fde68a;color:#92400e; }
        .flash-box.info    { background:#eff6ff;border-color:#bfdbfe;color:#1e40af; }

        /* Responsive */
        @media (max-width: 900px) {
            body { grid-template-columns: 1fr; }
            .auth-visual { display: none; }
        }
    </style>
</head>
<body>

<!-- ── Panneau gauche : image animée ─────────────────── -->
<div class="auth-visual">
    <div class="dots-grid"></div>

    
    <div class="visual-middle">
        <div class="img-scene">

            <div class="img-halo"></div>
            <div class="img-ring"></div>
            <div class="img-ring-inner"></div>

            <!-- Particules -->
            <div class="particles">
                <div class="particle" style="width:6px;height:6px;background:#3b82f6;left:20%;top:70%;--dur:7s;--delay:0s;--tx:15px;--ty:-90px;"></div>
                <div class="particle" style="width:4px;height:4px;background:#10b981;left:75%;top:60%;--dur:5.5s;--delay:.8s;--tx:-20px;--ty:-70px;"></div>
                <div class="particle" style="width:5px;height:5px;background:#f59e0b;left:35%;top:80%;--dur:6.5s;--delay:1.5s;--tx:25px;--ty:-85px;"></div>
                <div class="particle" style="width:3px;height:3px;background:#8b5cf6;left:65%;top:75%;--dur:8s;--delay:.3s;--tx:-10px;--ty:-100px;"></div>
                <div class="particle" style="width:5px;height:5px;background:#60a5fa;left:50%;top:85%;--dur:6s;--delay:2s;--tx:-30px;--ty:-80px;"></div>
                <div class="particle" style="width:4px;height:4px;background:#34d399;left:25%;top:55%;--dur:7.5s;--delay:1s;--tx:20px;--ty:-95px;"></div>
            </div>

            <!-- IMAGE PRINCIPALE — remplacer la src par votre fichier -->
            <img
                src="/BudgetSync/frontend/assets/img.png"
                alt="Budget Sync illustration"
                class="img-main"
                onerror="this.style.display='none'; document.getElementById('img-placeholder').style.display='flex';"
            >

            <!-- Placeholder si image absente -->
            <div id="img-placeholder"
                 style="display:none;width:200px;height:200px;border-radius:24px;
                        background:rgba(255,255,255,.06);border:2px dashed rgba(255,255,255,.15);
                        flex-direction:column;align-items:center;justify-content:center;gap:.5rem;
                        color:rgba(255,255,255,.35);font-size:.78rem;text-align:center;
                        animation:levitate 5s ease-in-out infinite;">
                <i class="bi bi-image" style="font-size:2.5rem;opacity:.4;"></i>
                <span>Votre image ici</span>
            </div>

            <!-- Badges -->
            <div class="img-badge badge-tl">
                <i class="bi bi-person-check-fill" style="color:#34d399;"></i>
                Compte sécurisé
            </div>
            <div class="img-badge badge-tr">
                <i class="bi bi-lock-fill" style="color:#60a5fa;"></i>
                Données chiffrées
            </div>
            <div class="img-badge badge-bl">
                <i class="bi bi-people-fill" style="color:#f59e0b;"></i>
                Collaboratif
            </div>
            <div class="img-badge badge-br">
                <i class="bi bi-graph-up-arrow" style="color:#34d399;"></i>
                Suivi en temps réel
            </div>

        </div>
    </div>

    <div class="visual-bottom">
        <p class="visual-tagline">
            <strong>Créez votre espace financier</strong> en quelques secondes.<br>
            Suivi budgétaire, catégories, alertes et rapports inclus.
        </p>
    </div>
</div>

<!-- ── Panneau droit : formulaire ────────────────────── -->
<div class="auth-form-panel">
    <div class="auth-form-wrap">

        <div class="form-header">
            <div class="form-header-eyebrow"><i class="bi bi-person-plus me-1"></i>Inscription gratuite</div>
            <h1>Créez votre<br>compte ✨</h1>
            <p>Rejoignez Budget Sync et prenez le contrôle de vos finances personnelles.</p>
        </div>

        <?php if ($flash): ?>
        <div class="flash-box <?= $flash['type'] ?>">
            <i class="bi bi-<?= match($flash['type']) {
                'success'=>'check-circle-fill','danger'=>'exclamation-circle-fill',
                'warning'=>'exclamation-triangle-fill', default=>'info-circle-fill'
            } ?> flex-shrink-0 mt-1" style="font-size:.9rem;"></i>
            <span><?= htmlspecialchars($flash['message'], ENT_QUOTES) ?></span>
        </div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=register&action=submit" novalidate>
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

            <!-- Prénom + Nom -->
            <div class="row g-3 mb-0">
                <div class="col-6">
                    <div class="field-wrap">
                        <label class="field-label" for="prenom">Prénom <span style="color:#ef4444;">*</span></label>
                        <div class="field-input-wrap">
                            <i class="bi bi-person field-icon"></i>
                            <input type="text" id="prenom" name="prenom" class="field-input"
                                   placeholder="Jean"
                                   value="<?= htmlspecialchars($_POST['prenom'] ?? '', ENT_QUOTES) ?>"
                                   autocomplete="given-name" required>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="field-wrap">
                        <label class="field-label" for="nom">Nom <span style="color:#ef4444;">*</span></label>
                        <div class="field-input-wrap">
                            <i class="bi bi-person field-icon"></i>
                            <input type="text" id="nom" name="nom" class="field-input"
                                   placeholder="Dupont"
                                   value="<?= htmlspecialchars($_POST['nom'] ?? '', ENT_QUOTES) ?>"
                                   autocomplete="family-name" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email -->
            <div class="field-wrap">
                <label class="field-label" for="email">Adresse email <span style="color:#ef4444;">*</span></label>
                <div class="field-input-wrap">
                    <i class="bi bi-envelope field-icon"></i>
                    <input type="email" id="email" name="email" class="field-input"
                           placeholder="vous@exemple.com"
                           value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES) ?>"
                           autocomplete="email" required autofocus>
                </div>
            </div>

            <!-- Mot de passe -->
            <div class="field-wrap">
                <label class="field-label" for="mdp1">Mot de passe <span style="color:#ef4444;">*</span></label>
                <div class="field-input-wrap">
                    <i class="bi bi-lock field-icon"></i>
                    <input type="password" id="mdp1" name="mot_de_passe" class="field-input"
                           placeholder="••••••••" autocomplete="new-password"
                           oninput="checkPwd(this.value)" required>
                    <button type="button" class="field-eye" onclick="toggleEye('mdp1','eye1')" tabindex="-1">
                        <i class="bi bi-eye" id="eye1"></i>
                    </button>
                </div>
                <div class="pwd-rules">
                    <span class="pwd-rule" id="rule-len"><i class="bi bi-check2"></i>8 car. min.</span>
                
                </div>
            </div>

            <!-- Confirmer -->
            <div class="field-wrap">
                <label class="field-label" for="mdp2">Confirmer le mot de passe <span style="color:#ef4444;">*</span></label>
                <div class="field-input-wrap">
                    <i class="bi bi-lock-fill field-icon"></i>
                    <input type="password" id="mdp2" name="mot_de_passe2" class="field-input"
                           placeholder="••••••••" autocomplete="new-password"
                           oninput="checkMatch()" required>
                    <button type="button" class="field-eye" onclick="toggleEye('mdp2','eye2')" tabindex="-1">
                        <i class="bi bi-eye" id="eye2"></i>
                    </button>
                </div>
                <div class="pwd-match" id="matchMsg"></div>
            </div>

            <!-- Info validation -->
            <div class="info-box">
                <i class="bi bi-hourglass-split"></i>
                <span>
                    Votre compte sera créé avec le statut <strong>en attente</strong>.
                    Un administrateur validera votre inscription avant votre première connexion.
                </span>
            </div>

            <button type="submit" class="btn-primary-auth">
                <i class="bi bi-person-check-fill"></i>
                Créer mon compte
            </button>
        </form>

        <div class="auth-link-row">
            Déjà inscrit ?
            <a href="index.php?page=login" class="auth-link">
                <i class="bi bi-box-arrow-in-right me-1"></i>Se connecter
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle visibilité mot de passe
function toggleEye(inputId, iconId) {
    const f = document.getElementById(inputId);
    const i = document.getElementById(iconId);
    f.type  = f.type === 'password' ? 'text' : 'password';
    i.className = f.type === 'text' ? 'bi bi-eye-slash' : 'bi bi-eye';
}

// Validation force mot de passe
function checkPwd(val) {
    toggle('rule-len',   val.length >= 8);
    toggle('rule-upper', /[A-Z]/.test(val));
    toggle('rule-num',   /[0-9]/.test(val));
    checkMatch();
}

function toggle(id, ok) {
    document.getElementById(id).classList.toggle('ok', ok);
}

// Correspondance des mots de passe
function checkMatch() {
    const v1  = document.getElementById('mdp1').value;
    const v2  = document.getElementById('mdp2').value;
    const msg = document.getElementById('matchMsg');
    if (!v2) { msg.innerHTML = ''; return; }
    if (v1 === v2) {
        msg.innerHTML = '<span style="color:#16a34a;"><i class="bi bi-check-circle-fill me-1"></i>Mots de passe identiques</span>';
    } else {
        msg.innerHTML = '<span style="color:#dc2626;"><i class="bi bi-x-circle-fill me-1"></i>Mots de passe différents</span>';
    }
}
</script>
</body>
</html>
