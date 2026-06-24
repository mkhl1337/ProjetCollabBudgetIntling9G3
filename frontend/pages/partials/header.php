<?php
// includes/header.php — Layout principal Budget Sync

require_once __DIR__ . '/../../../backend/middlewares/auth.php';
require_once __DIR__ . '/../../../backend/config/database.php';

$user  = utilisateurConnecte();
$flash = getFlash();
$page  = $_GET['page'] ?? '';
$uid   = $user['id'];

// Nombre de notifications non lues (pour l'icône bell)
$nbNotifs = 0;
$notifRecentes = [];
if ($uid) {
    require_once __DIR__ . '/../../../backend/models/NotificationModel.php';
    $nm = new NotificationModel();
    $nbNotifs      = $nm->compterNonLues($uid);
    $notifRecentes = $nm->nonLuesParUser($uid);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES) ?></title>
    <!-- <link rel="icon" type="image/x-icon" href="../../assets/favicon.ico"> -->
    <link rel="icon" type="image/png" sizes="16x16" href="/BudgetSync/frontend/assets/favicon-16x16.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --sidebar-bg: #0f172a;
            --sidebar-w: 260px;
            --accent: #3b82f6;
            --accent-light: rgba(59,130,246,.15);
            --topbar-h: 60px;
        }
        * { box-sizing: border-box; }
        body { background: #f1f5f9; font-family: 'Segoe UI', system-ui, sans-serif; margin: 0; }

        /* ── Sidebar ──────────────────────────── */
        #sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--sidebar-bg);
            position: fixed;
            top: 0; left: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform .3s cubic-bezier(.4,0,.2,1);
            overflow-y: auto;
            overflow-x: hidden;
        }
        #sidebar .brand {
            padding: 1.1rem 1.2rem;
            display: flex;
            align-items: center;
            gap: .65rem;
            border-bottom: 1px solid rgba(255,255,255,.08);
            text-decoration: none;
            flex-shrink: 0;
        }
        #sidebar .brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; color: #fff;
            flex-shrink: 0;
        }
        #sidebar .brand-text { color: #fff; font-size: 1rem; font-weight: 700; line-height: 1.1; }
        #sidebar .brand-text span { color: #60a5fa; }

        #sidebar .nav-section {
            padding: .6rem .8rem .2rem;
            font-size: .68rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #475569;
        }
        #sidebar .nav-item-wrap { padding: 0 .5rem; }
        #sidebar .nav-link {
            color: #94a3b8;
            padding: .48rem .75rem;
            border-radius: 8px;
            margin: 1px 0;
            font-size: .865rem;
            display: flex;
            align-items: center;
            gap: .55rem;
            text-decoration: none;
            transition: background .15s, color .15s;
            white-space: nowrap;
        }
        #sidebar .nav-link i { font-size: 1rem; width: 20px; flex-shrink: 0; }
        #sidebar .nav-link:hover { background: var(--accent-light); color: #60a5fa; }
        #sidebar .nav-link.active { background: var(--accent-light); color: #60a5fa; font-weight: 600; }
        #sidebar .nav-link.danger { color: #f87171 !important; }
        #sidebar .nav-link.danger:hover { background: rgba(239,68,68,.12); }

        /* Collapse parent arrow */
        #sidebar .nav-link[data-bs-toggle="collapse"] .arrow {
            margin-left: auto;
            transition: transform .2s;
            font-size: .75rem;
        }
        #sidebar .nav-link[data-bs-toggle="collapse"][aria-expanded="true"] .arrow {
            transform: rotate(180deg);
        }
        #sidebar .sub-menu { padding-left: 2rem; }
        #sidebar .sub-menu .nav-link { font-size: .82rem; padding: .38rem .75rem; }

        /* ── Main content ─────────────────────── */
        #main { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }

        /* ── Topbar ───────────────────────────── */
        .topbar {
            height: var(--topbar-h);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 900;
        }
        .topbar-title { font-size: .95rem; font-weight: 600; color: #1e293b; }

        /* Bell icon */
        .bell-wrapper { position: relative; }
        .bell-badge {
            position: absolute;
            top: -5px; right: -5px;
            background: #ef4444;
            color: #fff;
            border-radius: 50%;
            font-size: .65rem;
            font-weight: 700;
            min-width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
        }

        /* ── Page body ───────────────────────── */
        .page-body { flex: 1; padding: 1.75rem; }

        /* ── Overlay (mobile) ────────────────── */
        #overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 1030; }

        /* ── Notification dropdown ───────────── */
        .notif-dropdown {
            width: 340px;
            max-height: 420px;
            overflow-y: auto;
            border: none;
            box-shadow: 0 8px 32px rgba(0,0,0,.12);
            border-radius: 14px;
            padding: 0;
        }
        .notif-header {
            padding: .9rem 1rem .7rem;
            border-bottom: 1px solid #f1f5f9;
            font-weight: 600;
            font-size: .9rem;
            color: #1e293b;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .notif-item {
            padding: .8rem 1rem;
            border-bottom: 1px solid #f8fafc;
            font-size: .82rem;
            color: #374151;
            display: flex;
            gap: .75rem;
            align-items: flex-start;
            text-decoration: none;
            transition: background .12s;
        }
        .notif-item:hover { background: #f8fafc; color: #374151; }
        .notif-item.unread { background: #eff6ff; }
        .notif-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #3b82f6; margin-top: 4px; flex-shrink: 0;
        }
        .notif-time { font-size: .72rem; color: #94a3b8; margin-top: 2px; }
        .notif-footer {
            padding: .7rem 1rem;
            text-align: center;
            font-size: .82rem;
            border-top: 1px solid #f1f5f9;
        }

        /* User avatar */
        .user-avatar {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 50%;
            color: #fff;
            font-size: .78rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); }
            #main { margin-left: 0; }
            #overlay.show { display: block; }
        }
    </style>
</head>
<body>

<!-- ══ SIDEBAR ══════════════════════════════════════ -->
<nav id="sidebar">
    <!-- Brand -->
    <a href="index.php?page=dashboard" class="brand">
        <div class="brand-icon"><i class="bi bi-wallet2"></i></div>
        <div class="brand-text">Budget<span>Sync</span></div>
    </a>

    <div style="flex:1;padding-bottom:1rem;">

    <?php if (estAdmin()): ?>
    <!-- ══ MENU ADMINISTRATEUR ══ -->
    <div class="nav-section">Tableau de bord</div>
    <div class="nav-item-wrap">
        <a href="index.php?page=dashboard" class="nav-link <?= $page==='dashboard'?'active':'' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
    </div>

    <div class="nav-section">Utilisateurs</div>
    <div class="nav-item-wrap">
        <a class="nav-link <?= in_array($page,['admin_utilisateurs','admin_validation','admin_demandes'])?'active':'' ?>"
           data-bs-toggle="collapse" href="#menuUsers" aria-expanded="<?= in_array($page,['admin_utilisateurs','admin_validation','admin_demandes'])?'true':'false' ?>">
            <i class="bi bi-people"></i> Utilisateurs
            <i class="bi bi-chevron-down arrow"></i>
        </a>
        <div class="collapse <?= in_array($page,['admin_utilisateurs','admin_validation','admin_demandes'])?'show':'' ?>" id="menuUsers">
            <div class="sub-menu">
                <a href="index.php?page=admin_utilisateurs" class="nav-link <?= $page==='admin_utilisateurs'?'active':'' ?>">
                    <i class="bi bi-list-ul"></i> Liste des utilisateurs
                </a>
                <a href="index.php?page=admin_validation" class="nav-link <?= $page==='admin_validation'?'active':'' ?>">
                    <i class="bi bi-patch-check"></i> Validation des comptes
                </a>
                <a href="index.php?page=admin_demandes" class="nav-link <?= $page==='admin_demandes'?'active':'' ?>">
                    <i class="bi bi-trash2"></i> Demandes de suppression
                </a>
            </div>
        </div>
    </div>

    <div class="nav-section">Finances</div>
    <div class="nav-item-wrap">
        <!-- <a class="nav-link <?= in_array($page,['categories','historique_categories'])?'active':'' ?>"
           data-bs-toggle="collapse" href="#menuCatAdmin" aria-expanded="<?= in_array($page,['categories','historique_categories'])?'true':'false' ?>">
            <i class="bi bi-tags"></i> Catégories
            <i class="bi bi-chevron-down arrow"></i>
        </a>
        <div class="collapse <?= in_array($page,['categories','historique_categories'])?'show':'' ?>" id="menuCatAdmin">
            <div class="sub-menu">
                <a href="index.php?page=categories" class="nav-link <?= $page==='categories'?'active':'' ?>">
                    <i class="bi bi-tag"></i> Catégories
                </a>
                <a href="index.php?page=historique_categories" class="nav-link <?= $page==='historique_categories'?'active':'' ?>">
                    <i class="bi bi-clock-history"></i> Historique
                </a>
            </div>
        </div> -->
        <a href="index.php?page=categories"
           class="nav-link <?= $page === 'categories' ? 'active' : '' ?>">
            <i class="bi bi-tags"></i> Catégories
        </a>
        <!-- <a class="nav-link <?= in_array($page,['transactions','historique_transactions'])?'active':'' ?>"
           data-bs-toggle="collapse" href="#menuTxAdmin" aria-expanded="<?= in_array($page,['transactions','historique_transactions'])?'true':'false' ?>">
            <i class="bi bi-arrow-left-right"></i> Transactions
            <i class="bi bi-chevron-down arrow"></i>
        </a>
        <div class="collapse <?= in_array($page,['transactions','historique_transactions'])?'show':'' ?>" id="menuTxAdmin">
            <div class="sub-menu">
                <a href="index.php?page=transactions" class="nav-link <?= $page==='transactions'?'active':'' ?>">
                    <i class="bi bi-list-ul"></i> Transactions
                </a>
                <a href="index.php?page=historique_transactions" class="nav-link <?= $page==='historique_transactions'?'active':'' ?>">
                    <i class="bi bi-clock-history"></i> Historique
                </a>
            </div>
        </div> -->

        <a href="index.php?page=transactions"
           class="nav-link <?= $page === 'transactions' ? 'active' : '' ?>">
            <i class="bi bi-arrow-left-right"></i> Transactions
        </a>

        <!-- <a class="nav-link <?= in_array($page,['budgets','historique_budgets'])?'active':'' ?>"
           data-bs-toggle="collapse" href="#menuBudAdmin" aria-expanded="<?= in_array($page,['budgets','historique_budgets'])?'true':'false' ?>">
            <i class="bi bi-pie-chart"></i> Budgets
            <i class="bi bi-chevron-down arrow"></i>
        </a>
        <div class="collapse <?= in_array($page,['budgets','historique_budgets'])?'show':'' ?>" id="menuBudAdmin">
            <div class="sub-menu">
                <a href="index.php?page=budgets" class="nav-link <?= $page==='budgets'?'active':'' ?>">
                    <i class="bi bi-wallet2"></i> Mes Budgets
                </a>
                <a href="index.php?page=historique_budgets" class="nav-link <?= $page==='historique_budgets'?'active':'' ?>">
                    <i class="bi bi-clock-history"></i> Historique
                </a>
            </div>
        </div> -->
        <a href="index.php?page=budgets"
           class="nav-link <?= $page === 'budgets' ? 'active' : '' ?>">
            <i class="bi bi-pie-chart"></i> Mes Budgets
        </a>

        <a href="index.php?page=alertes" class="nav-link <?= $page==='alertes'?'active':'' ?>">
            <i class="bi bi-exclamation-triangle"></i> Mes alertes
        </a>
    </div>

    <div class="nav-section">Compte</div>
    <div class="nav-item-wrap">
        <!-- <a class="nav-link <?= $page==='profil'?'active':'' ?>"
           data-bs-toggle="collapse" href="#menuProfilAdmin" aria-expanded="<?= $page==='profil'?'true':'false' ?>">
            <i class="bi bi-person-gear"></i> Paramètres
            <i class="bi bi-chevron-down arrow"></i>
        </a>
        <div class="collapse <?= $page==='profil'?'show':'' ?>" id="menuProfilAdmin">
            <div class="sub-menu">
                <a href="index.php?page=profil" class="nav-link <?= $page==='profil'?'active':'' ?>">
                    <i class="bi bi-person"></i> Gérer mon profil
                </a>
            </div>
        </div> -->
        <a href="index.php?page=profil"
           class="nav-link <?= $page === 'profil' ? 'active' : '' ?>">
            <i class="bi bi-person-circle"></i> Mon profil
        </a>
        <a href="index.php?page=logout" class="nav-link danger">
            <i class="bi bi-box-arrow-right"></i> Déconnexion
        </a>
    </div>

    <?php else: ?>
    <!-- ══ MENU UTILISATEUR ══ -->
    <div class="nav-section">Tableau de bord</div>
    <div class="nav-item-wrap">
        <a href="index.php?page=dashboard" class="nav-link <?= $page==='dashboard'?'active':'' ?>">
            <i class="bi bi-grid"></i> Dashboard
        </a>
    </div>

    <div class="nav-section">Mes finances</div>
    <div class="nav-item-wrap">
        <!-- <a class="nav-link <?= in_array($page,['transactions','historique_transactions'])?'active':'' ?>"
           data-bs-toggle="collapse" href="#menuRev" aria-expanded="<?= in_array($page,['transactions','historique_transactions'])?'true':'false' ?>">
            <i class="bi bi-currency-exchange"></i> Gérer mes revenus & dépenses
            <i class="bi bi-chevron-down arrow"></i>
        </a>
        <div class="collapse <?= in_array($page,['transactions','historique_transactions'])?'show':'' ?>" id="menuRev">
            <div class="sub-menu">
                <a href="index.php?page=transactions" class="nav-link <?= $page==='transactions'?'active':'' ?>">
                    <i class="bi bi-list-ul"></i> Transactions
                </a>
                <a href="index.php?page=historique_transactions" class="nav-link <?= $page==='historique_transactions'?'active':'' ?>">
                    <i class="bi bi-clock-history"></i> Historique des transactions
                </a>
            </div>
        </div> -->
        <a href="index.php?page=transactions"
           class="nav-link <?= $page === 'transactions' ? 'active' : '' ?>">
            <i class="bi bi-arrow-left-right"></i> Transactions
        </a>

        <!-- <a class="nav-link <?= in_array($page,['budgets','budgets_partages','historique_budgets'])?'active':'' ?>"
           data-bs-toggle="collapse" href="#menuBud" aria-expanded="<?= in_array($page,['budgets','budgets_partages','historique_budgets'])?'true':'false' ?>">
            <i class="bi bi-pie-chart"></i> Budgets
            <i class="bi bi-chevron-down arrow"></i>
        </a>
        <div class="collapse <?= in_array($page,['budgets','budgets_partages','historique_budgets'])?'show':'' ?>" id="menuBud">
            <div class="sub-menu">
                <a href="index.php?page=budgets" class="nav-link <?= $page==='budgets'?'active':'' ?>">
                    <i class="bi bi-wallet2"></i> Mes budgets individuels
                </a>
                <a href="index.php?page=budgets_partages" class="nav-link <?= $page==='budgets_partages'?'active':'' ?>">
                    <i class="bi bi-people"></i> Mes budgets partagés
                </a>
                <a href="index.php?page=historique_budgets" class="nav-link <?= $page==='historique_budgets'?'active':'' ?>">
                    <i class="bi bi-clock-history"></i> Historique des budgets
                </a>
            </div>
        </div> -->
        <a href="index.php?page=budgets"
           class="nav-link <?= $page === 'budgets' ? 'active' : '' ?>">
            <i class="bi bi-pie-chart"></i> Mes Budgets
        </a>

        <!-- <a class="nav-link <?= in_array($page,['categories','historique_categories'])?'active':'' ?>"
           data-bs-toggle="collapse" href="#menuCat" aria-expanded="<?= in_array($page,['categories','historique_categories'])?'true':'false' ?>">
            <i class="bi bi-tags"></i> Catégories
            <i class="bi bi-chevron-down arrow"></i>
        </a>
        <div class="collapse <?= in_array($page,['categories','historique_categories'])?'show':'' ?>" id="menuCat">
            <div class="sub-menu">
                <a href="index.php?page=categories" class="nav-link <?= $page==='categories'?'active':'' ?>">
                    <i class="bi bi-tag"></i> Catégories
                </a>
                <a href="index.php?page=historique_categories" class="nav-link <?= $page==='historique_categories'?'active':'' ?>">
                    <i class="bi bi-clock-history"></i> Historique des catégories
                </a>
            </div>
        </div> -->

        <a href="index.php?page=categories"
           class="nav-link <?= $page === 'categories' ? 'active' : '' ?>">
            <i class="bi bi-tags"></i> Catégories
        </a>
    </div>

    <div class="nav-section">Notifications</div>
    <div class="nav-item-wrap">
        <a href="index.php?page=notifications" class="nav-link <?= $page==='notifications'?'active':'' ?>">
            <i class="bi bi-bell"></i> Mes notifications
            <?php if($nbNotifs > 0): ?>
            <span class="badge bg-danger rounded-pill ms-auto" style="font-size:.65rem;"><?= $nbNotifs ?></span>
            <?php endif; ?>
        </a>
    </div>

    <div class="nav-section">Compte</div>
    <div class="nav-item-wrap">
        <a href="index.php?page=profil"
           class="nav-link <?= $page === 'profil' ? 'active' : '' ?>">
            <i class="bi bi-person-circle"></i> Mon profil
        </a>
        <a href="index.php?page=logout" class="nav-link danger">
            <i class="bi bi-box-arrow-right"></i> Déconnexion
        </a>
    </div>
    <?php endif; ?>

    </div><!-- /flex:1 -->
</nav>

<!-- ══ OVERLAY (mobile) ══════════════════════════════ -->
<div id="overlay"></div>

<!-- ══ MAIN ══════════════════════════════════════════ -->
<div id="main">

    <!-- Topbar -->
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-light d-md-none border-0" id="menuBtn">
                <i class="bi bi-list fs-5"></i>
            </button>
            <span class="topbar-title"><?= htmlspecialchars($pageTitle ?? APP_NAME, ENT_QUOTES) ?></span>
        </div>

        <div class="d-flex align-items-center gap-3">
            <!-- Bell icon avec dropdown -->
            <div class="dropdown bell-wrapper">
                <a href="#" class="btn btn-sm btn-light border-0 d-flex align-items-center"
                   data-bs-toggle="dropdown" aria-expanded="false"
                   style="width:36px;height:36px;border-radius:50%;justify-content:center;">
                    <i class="bi bi-bell" style="font-size:1.05rem;color:#64748b;"></i>
                    <?php if($nbNotifs > 0): ?>
                    <span class="bell-badge"><?= $nbNotifs ?></span>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end notif-dropdown">
                    <div class="notif-header">
                        <span><i class="bi bi-bell me-2 text-primary"></i>Notifications</span>
                        <?php if($nbNotifs > 0): ?>
                        <a href="index.php?page=notifications&action=tout_lire" class="text-muted small text-decoration-none">
                            Tout lire
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php if(empty($notifRecentes)): ?>
                    <li class="text-center text-muted small py-4">
                        <i class="bi bi-bell-slash d-block mb-2 fs-4"></i>
                        Aucune notification non lue
                    </li>
                    <?php else: ?>
                    <?php foreach(array_slice($notifRecentes, 0, 5) as $n): ?>
                    <a href="index.php?page=notifications&action=lire&id=<?= $n['id'] ?>&redirect=notifications"
                       class="notif-item unread">
                        <div class="notif-dot"></div>
                        <div>
                            <div><?= htmlspecialchars($n['message'], ENT_QUOTES) ?></div>
                            <div class="notif-time">
                                <i class="bi bi-clock me-1"></i>
                                <?= date('d/m/Y H:i', strtotime($n['date_notif'])) ?>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="notif-footer">
                        <a href="index.php?page=notifications" class="text-primary text-decoration-none small fw-medium">
                            Voir toutes les notifications
                        </a>
                    </div>
                </ul>
            </div>

            <!-- User dropdown -->
            <div class="dropdown">
                <button class="btn btn-sm btn-light border-0 d-flex align-items-center gap-2"
                        data-bs-toggle="dropdown" style="border-radius:8px;padding:.3rem .6rem;">
                    <div class="user-avatar">
                        <?= strtoupper(substr($user['prenom'],0,1) . substr($user['nom'],0,1)) ?>
                    </div>
                    <div class="text-start d-none d-md-block">
                        <div style="font-size:.82rem;font-weight:600;color:#1e293b;line-height:1.1;">
                            <?= htmlspecialchars($user['prenom'].' '.$user['nom'], ENT_QUOTES) ?>
                        </div>
                        <div style="font-size:.7rem;color:#94a3b8;">
                            <?= $user['role'] === 'admin' ? 'Administrateur' : 'Utilisateur' ?>
                        </div>
                    </div>
                    <i class="bi bi-chevron-down small text-muted"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="border-radius:12px;min-width:180px;">
                    <li class="px-3 py-2">
                        <div class="small fw-semibold text-dark"><?= htmlspecialchars($user['prenom'].' '.$user['nom'], ENT_QUOTES) ?></div>
                        <div class="text-muted" style="font-size:.72rem;"><?= htmlspecialchars($user['email'], ENT_QUOTES) ?></div>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item small d-flex align-items-center gap-2" href="index.php?page=profil">
                            <i class="bi bi-person text-muted"></i> Mon profil
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item small d-flex align-items-center gap-2" href="index.php?page=notifications">
                            <i class="bi bi-bell text-muted"></i> Notifications
                            <?php if($nbNotifs > 0): ?>
                            <span class="badge bg-danger ms-auto" style="font-size:.65rem;"><?= $nbNotifs ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <a class="dropdown-item small text-danger d-flex align-items-center gap-2" href="index.php?page=logout">
                            <i class="bi bi-box-arrow-right"></i> Déconnexion
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Flash message -->
    <div class="page-body">
    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show shadow-sm border-0 mb-4"
         style="border-radius:12px;" role="alert">
        <?= htmlspecialchars($flash['message'], ENT_QUOTES) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>