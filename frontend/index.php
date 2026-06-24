<?php
// index.php — Routeur frontal

require_once __DIR__ . '/../backend/middlewares/auth.php';
require_once __DIR__ . '/../backend/config/database.php';

$page   = $_GET['page']   ?? 'login';
$action = $_GET['action'] ?? '';
$id     = (int)($_GET['id'] ?? 0);

if (estConnecte() && in_array($page, ['login', 'register'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

switch ($page) {

    case 'login':
        require_once __DIR__ . '/../backend/controllers/AuthController.php';
        $ctrl = new AuthController();
        ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit')
            ? $ctrl->handleLogin() : $ctrl->showLogin();
        break;

    case 'register':
        require_once __DIR__ . '/../backend/controllers/AuthController.php';
        $ctrl = new AuthController();
        ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'submit')
            ? $ctrl->handleRegister() : $ctrl->showRegister();
        break;

    case 'logout':
        require_once __DIR__ . '/../backend/controllers/AuthController.php';
        (new AuthController())->logout();
        break;
    
    // ── Profil ─────────────────────────────────────────
    case 'profil':
        require_once __DIR__ . '/../backend/controllers/ProfilController.php';
        $ctrl = new ProfilController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            match($action) {
                'modifier'            => $ctrl->handleModifier(),
                'changer_mdp'         => $ctrl->handleChangerMdp(),
                'demande_suppression' => $ctrl->handleDemandeSuppression(),
                default               => $ctrl->show()
            };
        } else {
            $ctrl->show();
        }
        break;

	// ── Invitations ────────────────────────────────────
    case 'invitations':
        require_once __DIR__ . '/../backend/controllers/InvitationController.php';
        $ctrl = new InvitationController();
        switch ($action) {
            case 'inviter':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') $ctrl->handleInviter();
                else header('Location: index.php?page=invitations');
                break;
            case 'accepter':
                $ctrl->accepter($id);
                break;
            case 'accepter_token':
                $ctrl->accepterParToken($_GET['token'] ?? '');
                break;
            case 'refuser':
                $ctrl->refuser($id);
                break;
            case 'annuler':
                $ctrl->annuler($id);
                break;
            default:
                $ctrl->liste();
        }
        break;

    // ── Notifications ──────────────────────────────────
    case 'notifications':
        require_once __DIR__ . '/../backend/controllers/NotificationController.php';
        $ctrl = new NotificationController();
        match($action) {
            'lire'      => $ctrl->marquerLue(),
            'tout_lire' => $ctrl->toutMarquerLu(),
            'supprimer'=> $ctrl->supprimer(),
            default     => $ctrl->liste()
        };
        break;

    // ── Administration — Dashboard ─────────────────────
    case 'admin':
        require_once __DIR__ . '/../backend/controllers/AdminController.php';
        (new AdminController())->dashboard();
        break;

    // ── Administration — Liste utilisateurs ───────────
    case 'admin_utilisateurs':
        require_once __DIR__ . '/../backend/controllers/AdminController.php';
        $ctrl = new AdminController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            match($action) {
                'ajouter'  => $ctrl->handleAjouter(),
                'modifier' => $ctrl->handleModifier(),
                default    => $ctrl->utilisateurs()
            };
        } else {
            match($action) {
                'activer'   => $ctrl->activer(),
                'suspendre' => $ctrl->suspendre(),
                'bloquer'   => $ctrl->bloquer(),
                'supprimer' => $ctrl->supprimer(),
                default     => $ctrl->utilisateurs()
            };
        }
        break;

    // ── Administration — Validation comptes ───────────
    case 'admin_validation':
        require_once __DIR__ . '/../backend/controllers/AdminController.php';
        $ctrl = new AdminController();
        match($action) {
            'valider' => $ctrl->valider(),
            'rejeter' => $ctrl->rejeter(),
            default   => $ctrl->validationComptes()
        };
        break;

    // ── Administration — Demandes suppression ─────────
    case 'admin_demandes':
        require_once __DIR__ . '/../backend/controllers/AdminController.php';
        $ctrl = new AdminController();
        match($action) {
            'valider_suppression' => $ctrl->validerSuppression(),
            'refuser_suppression' => $ctrl->refuserSuppression(),
            default               => $ctrl->demandesSuppression()
        };
        break;

    case 'dashboard':
        require_once __DIR__ . '/../backend/controllers/DashboardController.php';
        (new DashboardController())->show();
        break;

    case 'categories':
        require_once __DIR__ . '/../backend/controllers/CategorieController.php';
        $ctrl = new CategorieController();
        match($action) {
            'ajouter'  => $ctrl->handleAjouter(),
            'modifier' => $ctrl->handleModifier(),
            'supprimer' => $ctrl->supprimer(),
            default    => $ctrl->liste()
        };        
        break;

    case 'transactions':
        require_once __DIR__ . '/../backend/controllers/TransactionController.php';
        $ctrl = new TransactionController();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            match($action) {
                'ajouter'  => $ctrl->handleAjouter(),
                'modifier' => $ctrl->handleModifier(),
                default    => $ctrl->liste()
            };
        } else {
            match($action) {
                'supprimer' => $ctrl->supprimer($id),
                'export'    => $ctrl->export($id),
                default     => $ctrl->liste()
            };
        }
        break;

    case 'budgets':
        require_once __DIR__ . '/../backend/controllers/BudgetController.php';
        $ctrl = new BudgetController();
        switch ($action) {
            case 'nouveau':
                $ctrl->formulaireCreer();
                break;
            case 'creer':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') $ctrl->handleCreer();
                else header('Location: index.php?page=budgets');
                break;
            case 'edit':
                $ctrl->formulaireEditer($id);
                break;
            case 'modifier':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') $ctrl->handleModifier();
                else header('Location: index.php?page=budgets');
                break;
            case 'supprimer':
                $ctrl->supprimer($id);
                break;
            case 'detail':
                $ctrl->detail($id);
                break;
            case 'retirer_membre':
                if ($_SERVER['REQUEST_METHOD'] === 'POST') $ctrl->retirerMembre();
                else header('Location: index.php?page=budgets');
                break;
            case 'quitter':
                $ctrl->quitter($id);
                break;
            default:
                $ctrl->liste();
        }
        break;

    case 'alertes':
        require_once __DIR__ . '/../backend/controllers/AlerteController.php';
        $ctrl = new AlerteController();
        match($action) {
            'lire'    => $ctrl->marquerLue((int)($_GET['id']??0)),
            'tout_lire' => $ctrl->toutMarquerLu(),
            default   => $ctrl->liste()
        };
        break;

    default:
        header('Location: index.php?page=' . (estConnecte() ? 'dashboard' : 'login'));
        exit;
}
