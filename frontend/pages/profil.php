<?php
// utilisateur/profil.php
require_once __DIR__ . '/partials/header.php';

$role = $profil['role'] ?? 'utilisateur';

function roleLabel(string $role): string
{
    return match ($role) {
        'admin' => 'Administrateur',

        default => 'Utilisateur',
    };
}

function roleColor(string $role): array
{
    return match ($role) {
        'admin' => ['bg' => '#fef2f2', 'color' => '#ef4444', 'dot' => '#ef4444'],
        'directeur' => ['bg' => '#f8fafc', 'color' => '#475569', 'dot' => '#475569'],
        'chef_dept' => ['bg' => '#f0fdf4', 'color' => '#16a34a', 'dot' => '#16a34a'],
        'enseignant' => ['bg' => '#eff6ff', 'color' => '#3b82f6', 'dot' => '#3b82f6'],
        default => ['bg' => '#f5f3ff', 'color' => '#7c3aed', 'dot' => '#7c3aed'],
    };
}

$rc = roleColor($role);
$initiales = strtoupper(
    substr($profil['prenom'] ?? '', 0, 1) .
    substr($profil['nom'] ?? '', 0, 1)
);
?>

<style>
    /* ===== PROFIL STYLES ===== */
    @import url('https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&display=swap');

    .profil-wrap {
        max-width: 980px;
        margin: 0 auto;
        font-family: 'Sora', sans-serif;
    }

    /* PAGE TITLE */
    .profil-page-title {
        font-size: 20px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.03em;
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .profil-page-title span {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #6366f1;
        display: inline-block;
    }

    /* LEFT PANEL */
    .profil-card {
        background: white;
        border: 1px solid #eaecf5;
        border-radius: 20px;
        overflow: hidden;
    }

    /* AVATAR HEADER */
    .avatar-header {
        background: linear-gradient(135deg, #0f0f2d, #1e1b4b);
        padding: 36px 24px 48px;
        text-align: center;
        position: relative;
    }

    .avatar-header::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 24px;
        background: white;
        border-radius: 24px 24px 0 0;
    }

    .avatar-ring {
        width: 88px;
        height: 88px;
        border-radius: 50%;
        border: 3px solid rgba(99, 102, 241, 0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 4px;
        position: relative;
        z-index: 1;
    }

    .avatar-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        font-weight: 700;
        color: white;
        letter-spacing: -0.02em;
        font-family: 'Sora', sans-serif;
    }

    .online-dot {
        position: absolute;
        bottom: 4px;
        right: 4px;
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background: #22c55e;
        border: 2px solid #1e1b4b;
    }

    /* LEFT BODY */
    .avatar-body {
        padding: 8px 24px 28px;
        text-align: center;
    }

    .profile-name {
        font-size: 17px;
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -0.02em;
        margin-bottom: 4px;
    }

    .profile-email {
        font-size: 12px;
        color: #94a3b8;
        font-weight: 300;
        margin-bottom: 14px;
        word-break: break-all;
    }

    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 500;
        padding: 5px 14px;
        border-radius: 20px;
    }

    .role-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    /* DIVIDER */
    .left-divider {
        height: 1px;
        background: #f1f5f9;
        margin: 20px 0;
    }

    /* INFO ROWS */
    .info-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 0;
        font-size: 13px;
        color: #475569;
    }

    .info-row .info-icon {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: #f8fafc;
        border: 1px solid #eaecf5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        color: #6366f1;
        flex-shrink: 0;
    }

    .info-row .info-val {
        font-size: 12px;
        font-weight: 500;
        color: #1e293b;
    }

    .info-row .info-lbl {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 300;
        display: block;
    }

    /* ===== FORM CARDS ===== */
    .form-card {
        background: white;
        border: 1px solid #eaecf5;
        border-radius: 20px;
        overflow: hidden;
        margin-bottom: 16px;
    }

    .form-card-header {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 20px 24px 16px;
        border-bottom: 1px solid #f1f5f9;
    }

    .form-card-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }

    .fc-indigo {
        background: #eef2ff;
        color: #6366f1;
    }

    .fc-amber {
        background: #fffbeb;
        color: #f59e0b;
    }

    .form-card-title {
        font-size: 14px;
        font-weight: 600;
        color: #0f172a;
        letter-spacing: -0.01em;
    }

    .form-card-sub {
        font-size: 12px;
        color: #94a3b8;
        font-weight: 300;
    }

    .form-card-body {
        padding: 22px 24px;
    }

    /* FIELD */
    .field-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }

    .field-group {
        margin-bottom: 16px;
    }

    .field-label {
        display: block;
        font-size: 11px;
        font-weight: 500;
        color: #94a3b8;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        margin-bottom: 7px;
    }

    .field-input {
        width: 100%;
        background: #f8fafc;
        border: 1px solid #eaecf5;
        border-radius: 10px;
        padding: 10px 14px;
        font-family: 'Sora', sans-serif;
        font-size: 13px;
        color: #1e293b;
        outline: none;
        transition: all 0.2s;
        -webkit-appearance: none;
    }

    .field-input::placeholder {
        color: #cbd5e1;
    }

    .field-input:focus {
        border-color: rgba(99, 102, 241, 0.5);
        background: #fafbff;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.08);
    }

    /* PASSWORD TOGGLE */
    .pwd-wrap {
        position: relative;
    }

    .pwd-wrap .field-input {
        padding-right: 42px;
    }

    .pwd-eye {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #94a3b8;
        padding: 2px;
        font-size: 15px;
        line-height: 1;
        transition: color 0.15s;
    }

    .pwd-eye:hover {
        color: #6366f1;
    }

    /* STRENGTH */
    .strength-track {
        height: 3px;
        border-radius: 2px;
        background: #eaecf5;
        margin-top: 8px;
        overflow: hidden;
    }

    .strength-fill {
        height: 100%;
        border-radius: 2px;
        width: 0%;
        transition: all 0.3s;
        background: #ef4444;
    }

    /* SUBMIT BTN */
    .btn-save {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 20px;
        border: none;
        border-radius: 10px;
        font-family: 'Sora', sans-serif;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.18s;
        letter-spacing: 0.01em;
    }

    .btn-save-primary {
        background: #6366f1;
        color: white;
    }

    .btn-save-primary:hover {
        background: #5558e8;
        transform: translateY(-1px);
    }

    .btn-save-primary:active {
        transform: scale(0.97);
    }

    .btn-save-amber {
        background: #f59e0b;
        color: white;
    }

    .btn-save-amber:hover {
        background: #e08e08;
        transform: translateY(-1px);
    }

    .btn-save-amber:active {
        transform: scale(0.97);
    }

    /* ===== BUTTON DANGER ===== */
    .btn-save-danger {
        background: #ef4444;
        color: white;
    }

    .btn-save-danger:hover {
        background: #dc2626;
        transform: translateY(-1px);
    }

    .btn-save-danger:active {
        transform: scale(0.97);
    }


    /* MATCH INDICATOR */
    .match-indicator {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        margin-top: 6px;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .match-indicator.visible {
        opacity: 1;
    }

    .match-indicator.ok {
        color: #22c55e;
    }

    .match-indicator.bad {
        color: #ef4444;
    }

    @media (max-width: 600px) {
        .field-row {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="profil-wrap">

    <div class="profil-page-title">
        <span></span> Mon profil
    </div>

    <div class="row g-4">

        <!-- ===== LEFT : AVATAR CARD ===== -->
        <div class="col-lg-4">
            <div class="profil-card">

                <div class="avatar-header">
                    <div class="avatar-ring">
                        <div class="avatar-circle"><?= $initiales ?></div>
                        <div class="online-dot"></div>
                    </div>
                </div>

                <div class="avatar-body">
                    <div class="profile-name">
                        <?= nettoyer(($profil['prenom'] ?? '') . ' ' . ($profil['nom'] ?? '')) ?>
                    </div>
                    <div class="profile-email">
                        <?= nettoyer($profil['email'] ?? '') ?>
                    </div>

                    <div class="role-badge" style="background:<?= $rc['bg'] ?>; color:<?= $rc['color'] ?>">
                        <div class="role-dot" style="background:<?= $rc['dot'] ?>"></div>
                        <?= roleLabel($role) ?>
                    </div>

                    <div class="left-divider"></div>

                    <div class="info-row">
                        <div class="info-icon"><i class="bi bi-calendar3"></i></div>
                        <div>
                            <div class="info-lbl">Inscrit le</div>
                            <div class="info-val">
                                <?= date('d/m/Y', strtotime($profil['date_inscription'] ?? 'now')) ?>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($profil['last_login'])): ?>
                        <div class="info-row">
                            <div class="info-icon"><i class="bi bi-clock"></i></div>
                            <div>
                                <div class="info-lbl">Dernière connexion</div>
                                <div class="info-val">
                                    <?= date('d/m/Y H:i', strtotime($profil['last_login'])) ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- <div class="info-row">
                        <div class="info-icon"><i class="bi bi-shield-check"></i></div>
                        <div>
                            <div class="info-lbl">Statut du compte</div>
                            <div class="info-val" style="color:#22c55e">Actif</div>
                        </div>
                    </div> -->

                </div>
            </div>
        </div>

        <!-- ===== RIGHT : FORMS ===== -->
        <div class="col-lg-8">

            <!-- MODIFIER PROFIL -->
            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-icon fc-indigo">
                        <i class="bi bi-person-gear"></i>
                    </div>
                    <div>
                        <div class="form-card-title">Informations personnelles</div>
                        <div class="form-card-sub">Mettez à jour vos données de profil</div>
                    </div>
                </div>

                <div class="form-card-body">
                    <form method="POST" action="index.php?page=profil&action=modifier">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Prénom</label>
                                <input type="text" name="prenom" class="field-input"
                                    value="<?= nettoyer($profil['prenom'] ?? '') ?>" placeholder="Votre prénom"
                                    required>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Nom</label>
                                <input type="text" name="nom" class="field-input"
                                    value="<?= nettoyer($profil['nom'] ?? '') ?>" placeholder="Votre nom" required>
                            </div>
                        </div>

                        <div class="field-group">
                            <label class="field-label">Adresse email</label>
                            <input type="email" name="email" class="field-input"
                                value="<?= nettoyer($profil['email'] ?? '') ?>" placeholder="vous@exemple.com" required>
                        </div>

                        <button type="submit" class="btn-save btn-save-primary">
                            <i class="bi bi-check-lg"></i>
                            Enregistrer les modifications
                        </button>
                    </form>
                </div>
            </div>

            <!-- CHANGER MOT DE PASSE -->
            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-icon fc-amber">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <div>
                        <div class="form-card-title">Sécurité du compte</div>
                        <div class="form-card-sub">Modifiez votre mot de passe</div>
                    </div>
                </div>

                <div class="form-card-body">
                    <form method="POST" action="index.php?page=profil&action=changer_mdp">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="field-group">
                            <label class="field-label">Mot de passe actuel</label>
                            <div class="pwd-wrap">
                                <input type="password" name="ancien_mdp" id="oldPwd" class="field-input"
                                    placeholder="••••••••" required>
                                <button type="button" class="pwd-eye" onclick="togglePwd('oldPwd',this)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Nouveau mot de passe</label>
                                <div class="pwd-wrap">
                                    <input type="password" name="nouveau_mdp" id="newPwd" class="field-input"
                                        placeholder="••••••••" required oninput="checkStr(this.value); checkMatch()">
                                    <button type="button" class="pwd-eye" onclick="togglePwd('newPwd',this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <!-- <div class="strength-track">
                                    <div class="strength-fill" id="strFill"></div>
                                </div> -->
                            </div>

                            <div class="field-group">
                                <label class="field-label">Confirmer</label>
                                <div class="pwd-wrap">
                                    <input type="password" name="confirm_mdp" id="confPwd" class="field-input"
                                        placeholder="••••••••" required oninput="checkMatch()">
                                    <button type="button" class="pwd-eye" onclick="togglePwd('confPwd',this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                <div class="match-indicator" id="matchInd">
                                    <i class="bi bi-circle-fill" style="font-size:6px"></i>
                                    <span id="matchTxt"></span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-save btn-save-amber">
                            <i class="bi bi-shield-lock"></i>
                            Mettre à jour le mot de passe
                        </button>
                    </form>
                </div>
            </div>

            <?php if (!($profil['role'] == 'admin')): ?>
                <div class="card border-0 shadow-sm border-danger">
                    <div class="card-body p-4">
                        <h6 class="fw-semibold mb-3 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Zone
                            dangereuse</h6>
                        <p class="small text-muted mb-3">Vous pouvez demander la suppression de votre compte.
                            L'administrateur traitera votre demande.</p>
                        <button class="btn-save btn-save-danger" data-bs-toggle="collapse"
                            data-bs-target="#formSuppression">
                            <i class="bi bi-trash"></i>
                            Demander la suppression du compte
                        </button>
                        <div class="collapse mt-3" id="formSuppression">
                            <form method="POST" action="index.php?page=profil&action=demande_suppression">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                                <div class="field-group">
                                    <label class="field-label">Motif (optionnel)</label>
                                    <textarea name="motif" class="field-input" rows="2"
                                        placeholder="Pourquoi souhaitez-vous supprimer votre compte ?"></textarea>
                                </div>

                                <div class="field-group">
                                    <label class="field-label">Mot de passe pour confirmer</label>
                                    <input type="password" name="mot_de_passe_confirm" class="field-input"
                                        placeholder="**********">
                                </div>

                                <button type="submit" class="btn-save btn-save-danger">
                                    <i class="bi bi-trash"></i>
                                    Envoyer la demande
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>


        </div>
    </div>
</div>

<script>
    function togglePwd(id, btn) {
        const input = document.getElementById(id);
        const icon = btn.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }

    function checkStr(val) {
        let s = 0;
        if (val.length >= 8) s++;
        if (/[A-Z]/.test(val)) s++;
        if (/[0-9]/.test(val)) s++;
        if (/[^A-Za-z0-9]/.test(val)) s++;
        const fill = document.getElementById('strFill');
        fill.style.width = (s / 4 * 100) + '%';
        fill.style.background = ['#ef4444', '#f97316', '#eab308', '#22c55e'][s - 1] || '#ef4444';
    }

    function checkMatch() {
        const a = document.getElementById('newPwd').value;
        const b = document.getElementById('confPwd').value;
        const ind = document.getElementById('matchInd');
        const txt = document.getElementById('matchTxt');
        if (!b) { ind.classList.remove('visible'); return; }
        ind.classList.add('visible');
        if (a === b) {
            ind.className = 'match-indicator visible ok';
            txt.textContent = 'Les mots de passe correspondent';
        } else {
            ind.className = 'match-indicator visible bad';
            txt.textContent = 'Les mots de passe ne correspondent pas';
        }
    }
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>