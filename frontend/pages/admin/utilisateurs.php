<?php
// frontend/pages/admin/utilisateurs.php
require_once __DIR__ . '/../partials/header.php';

$filtreStatut = $_GET['statut'] ?? '';
$recherche    = $_GET['q']      ?? '';
$statutsDisponibles = [
    ''           => 'Tous',
    'actif'      => 'Actif',
    'en_attente' => 'En attente',
    'suspendu'   => 'Suspendu',
    'bloque'     => 'Bloqué',
];
?>

<!-- En-tête page -->
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h5 class="fw-bold mb-1 text-dark">Gestion des utilisateurs</h5>
        <p class="text-muted small mb-0"><?= count($utilisateurs) ?> utilisateur(s) affiché(s)</p>
    </div>
    <button class="btn btn-primary btn-sm d-flex align-items-center gap-2"
            data-bs-toggle="modal" data-bs-target="#modalAjouter"
            style="border-radius:10px;padding:.5rem 1rem;">
        <i class="bi bi-person-plus-fill"></i> Ajouter un utilisateur
    </button>
</div>

<!-- Filtres & recherche -->
<div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
    <div class="card-body p-3">
        <form method="GET" action="index.php" class="d-flex flex-wrap gap-2 align-items-center">
            <input type="hidden" name="page" value="admin_utilisateurs">
            <!-- Recherche -->
            <div class="input-group" style="max-width:280px;">
                <span class="input-group-text bg-white border-end-0" style="border-radius:10px 0 0 10px;">
                    <i class="bi bi-search text-muted" style="font-size:.85rem;"></i>
                </span>
                <input type="text" name="q" value="<?= htmlspecialchars($recherche, ENT_QUOTES) ?>"
                       class="form-control border-start-0 ps-0"
                       placeholder="Nom, prénom ou email…"
                       style="border-radius:0 10px 10px 0;">
            </div>
            <!-- Filtre statut -->
            <div class="d-flex gap-1 flex-wrap">
                <?php foreach ($statutsDisponibles as $val => $label): ?>
                <a href="index.php?page=admin_utilisateurs&statut=<?= $val ?>&q=<?= urlencode($recherche) ?>"
                   class="btn btn-sm <?= $filtreStatut === $val ? 'btn-primary' : 'btn-outline-secondary' ?>"
                   style="border-radius:8px;font-size:.8rem;">
                    <?= $label ?>
                </a>
                <?php endforeach; ?>
            </div>
            <button type="submit" class="btn btn-sm btn-outline-primary" style="border-radius:8px;">
                <i class="bi bi-funnel"></i> Filtrer
            </button>
            <?php if ($recherche || $filtreStatut): ?>
            <a href="index.php?page=admin_utilisateurs" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                <i class="bi bi-x-circle me-1"></i>Réinitialiser
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Tableau -->
<div class="card border-0 shadow-sm" style="border-radius:16px; overflow:hidden;">
    <div class="table-responsive">
        <table class="table align-middle mb-0" style="font-size:.875rem;">
            <thead style="background:#f8fafc; border-bottom:2px solid #e2e8f0;">
                <tr>
                    <th class="ps-4 py-3 fw-semibold text-muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;">Utilisateur</th>
                    <th class="py-3 fw-semibold text-muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;">Email</th>
                    <th class="py-3 fw-semibold text-muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;">Rôle</th>
                    <th class="py-3 fw-semibold text-muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;">Statut</th>
                    <th class="py-3 fw-semibold text-muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;">Inscription</th>
                    <th class="py-3 fw-semibold text-muted text-center" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;">Actions statut</th>
                    <th class="py-3 pe-4 fw-semibold text-muted text-center" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if (empty($utilisateurs)): ?>
            <tr>
                <td colspan="7" class="text-center text-muted py-5">
                    <i class="bi bi-people d-block mb-2" style="font-size:2rem;opacity:.3;"></i>
                    Aucun utilisateur trouvé
                </td>
            </tr>
            <?php else: ?>
            <?php foreach ($utilisateurs as $u):
                $isSelf  = $u['id'] == $_SESSION['user_id'];
                $isAdmin = $u['role'] === 'admin';

                $statutCfg = match($u['statut']) {
                    'actif'      => ['cls'=>'text-success','bg'=>'#ecfdf5','border'=>'#a7f3d0','label'=>'Actif',       'icon'=>'bi-check-circle-fill'],
                    'en_attente' => ['cls'=>'text-warning','bg'=>'#fffbeb','border'=>'#fde68a','label'=>'En attente',  'icon'=>'bi-hourglass-split'],
                    'suspendu'   => ['cls'=>'text-warning','bg'=>'#fff7ed','border'=>'#fed7aa','label'=>'Suspendu',    'icon'=>'bi-pause-circle-fill'],
                    'bloque'     => ['cls'=>'text-danger', 'bg'=>'#fef2f2','border'=>'#fecaca','label'=>'Bloqué',      'icon'=>'bi-slash-circle-fill'],
                    default      => ['cls'=>'text-muted',  'bg'=>'#f1f5f9','border'=>'#e2e8f0','label'=>ucfirst($u['statut']),'icon'=>'bi-circle'],
                };
            ?>
            <tr style="border-bottom:1px solid #f8fafc;">
                <!-- Avatar + Nom -->
                <td class="ps-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                             style="width:38px;height:38px;font-size:.75rem;
                                    background:<?= $isAdmin ? '#dc2626' : '#2563eb' ?>;">
                            <?= strtoupper(substr($u['prenom'],0,1) . substr($u['nom'],0,1)) ?>
                        </div>
                        <div>
                            <div class="fw-semibold" style="color:#1e293b;">
                                <?= htmlspecialchars($u['prenom'].' '.$u['nom'], ENT_QUOTES) ?>
                                <?php if ($isSelf): ?>
                                <span class="badge bg-secondary ms-1" style="font-size:.6rem;border-radius:6px;">Vous</span>
                                <?php endif; ?>
                            </div>
                            <div class="text-muted" style="font-size:.75rem;">
                                Dernière connexion : <?= $u['derniere_connexion'] ? date('d/m/Y H:i', strtotime($u['derniere_connexion'])) : 'Jamais' ?>
                            </div>
                        </div>
                    </div>
                </td>
                <!-- Email -->
                <td class="text-muted" style="font-size:.82rem;">
                    <?= htmlspecialchars($u['email'], ENT_QUOTES) ?>
                </td>
                <!-- Rôle -->
                <td>
                    <?php if ($isAdmin): ?>
                    <span class="badge d-inline-flex align-items-center gap-1"
                          style="background:#fef2f2;color:#dc2626;border:1px solid #fecaca;font-size:.75rem;border-radius:8px;padding:.3em .65em;">
                        <i class="bi bi-shield-fill-check"></i> Admin
                    </span>
                    <?php else: ?>
                    <span class="badge d-inline-flex align-items-center gap-1"
                          style="background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;font-size:.75rem;border-radius:8px;padding:.3em .65em;">
                        <i class="bi bi-person-fill"></i> Utilisateur
                    </span>
                    <?php endif; ?>
                </td>
                <!-- Statut -->
                <td>
                    <span class="badge d-inline-flex align-items-center gap-1"
                          style="background:<?= $statutCfg['bg'] ?>;color:<?= $statutCfg['cls'] === 'text-success' ? '#065f46' : ($statutCfg['cls'] === 'text-danger' ? '#991b1b' : '#92400e') ?>;
                                 border:1px solid <?= $statutCfg['border'] ?>;font-size:.75rem;border-radius:8px;padding:.3em .65em;">
                        <i class="bi <?= $statutCfg['icon'] ?>"></i>
                        <?= $statutCfg['label'] ?>
                    </span>
                </td>
                <!-- Date inscription -->
                <td class="text-muted" style="font-size:.82rem;">
                    <?= date('d/m/Y', strtotime($u['date_inscription'])) ?>
                </td>
                <!-- Actions statut : bloquer/suspendre seulement si actif et non admin/self -->
                <td class="text-center">
                    <?php if (!$isSelf && !$isAdmin): ?>
                    <div class="d-flex justify-content-center gap-1">
                        <?php if ($u['statut'] !== 'actif'): ?>
                        <a href="index.php?page=admin_utilisateurs&action=activer&id=<?= $u['id'] ?>"
                           class="btn btn-sm btn-outline-success d-flex align-items-center gap-1"
                           title="Activer le compte" style="border-radius:8px;font-size:.78rem;"
                           onclick="return confirm('Activer ce compte ?')">
                            <i class="bi bi-check-circle"></i>
                        </a>
                        <?php endif; ?>
                        <?php if ($u['statut'] === 'actif'): ?>
                        <a href="index.php?page=admin_utilisateurs&action=bloquer&id=<?= $u['id'] ?>"
                           class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1"
                           title="Bloquer le compte" style="border-radius:8px;font-size:.78rem;"
                           onclick="return confirm('Bloquer ce compte ? L\'utilisateur ne pourra plus se connecter.')">
                            <i class="bi bi-slash-circle"></i>
                        </a>
                        <!-- <a href="index.php?page=admin_utilisateurs&action=suspendre&id=<?= $u['id'] ?>"
                           class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1"
                           title="Suspendre le compte" style="border-radius:8px;font-size:.78rem;"
                           onclick="return confirm('Suspendre ce compte temporairement ?')">
                            <i class="bi bi-pause-circle"></i>
                        </a> -->
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <span class="text-muted small">—</span>
                    <?php endif; ?>
                </td>
                <!-- Actions modifier/supprimer -->
                <td class="text-center pe-4">
                    <?php if (!$isSelf && !$isAdmin): ?>
                    <div class="d-flex justify-content-center gap-1">
                        <button type="button"
                                class="btn btn-sm btn-outline-primary"
                                style="border-radius:8px;" title="Modifier"
                                onclick="ouvrirModifier(<?= htmlspecialchars(json_encode($u), ENT_QUOTES) ?>)">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-outline-danger"
                                style="border-radius:8px;" title="Supprimer"
                                data-bs-toggle="modal" data-bs-target="#modalSupprimer"
                                data-id="<?= $u['id'] ?>"
                                data-nom="<?= htmlspecialchars($u['prenom'].' '.$u['nom'], ENT_QUOTES) ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <?php else: ?>
                    <span class="text-muted small">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ══ MODAL : Ajouter ═══════════════════════════════════════ -->
<div class="modal fade" id="modalAjouter" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="bi bi-person-plus-fill text-primary me-2"></i>Ajouter un utilisateur
                    </h5>
                    <p class="text-muted small mb-0 mt-1">Créer un nouveau compte utilisateur</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <form method="POST" action="index.php?page=admin_utilisateurs&action=ajouter">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="prenom" class="form-control" placeholder="Jean" required
                                   style="border-radius:10px;">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" class="form-control" placeholder="Dupont" required
                                   style="border-radius:10px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="jean@exemple.com" required
                               style="border-radius:10px;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Mot de passe temporaire <span class="text-danger">*</span></label>
                        <input type="password" name="mot_de_passe" class="form-control" value="ChangeMe123!"
                               style="border-radius:10px;">
                        <div class="form-text">L'utilisateur devra le changer à sa première connexion.</div>
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Rôle</label>
                            <select name="role" class="form-select" style="border-radius:10px;">
                                <option value="utilisateur">Utilisateur</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Statut</label>
                            <select name="statut" class="form-select" style="border-radius:10px;">
                                <option value="actif">Actif</option>
                                <option value="en_attente">En attente</option>
                                <!-- <option value="suspendu">Suspendu</option> -->
                                <option value="bloque">Bloqué</option>
                            </select>
                        </div>
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                style="border-radius:10px;">Annuler</button>
                        <button type="submit" class="btn btn-primary"
                                style="border-radius:10px;">
                            <i class="bi bi-person-plus me-1"></i>Créer le compte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL : Modifier ══════════════════════════════════════ -->
<div class="modal fade" id="modalModifier" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="bi bi-pencil-square text-warning me-2"></i>Modifier l'utilisateur
                    </h5>
                    <p class="text-muted small mb-0 mt-1">Modifier les informations du compte</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <form method="POST" action="index.php?page=admin_utilisateurs&action=modifier" id="formModifier">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="id" id="mod_id">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Prénom <span class="text-danger">*</span></label>
                            <input type="text" name="prenom" id="mod_prenom" class="form-control" required
                                   style="border-radius:10px;">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Nom <span class="text-danger">*</span></label>
                            <input type="text" name="nom" id="mod_nom" class="form-control" required
                                   style="border-radius:10px;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" id="mod_email" class="form-control" required
                               style="border-radius:10px;">
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Rôle</label>
                            <select name="role" id="mod_role" class="form-select" style="border-radius:10px;">
                                <option value="utilisateur">Utilisateur</option>
                                <option value="admin">Administrateur</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Statut</label>
                            <select name="statut" id="mod_statut" class="form-select" style="border-radius:10px;">
                                <option value="actif">Actif</option>
                                <option value="en_attente">En attente</option>
                                <option value="suspendu">Suspendu</option>
                                <option value="bloque">Bloqué</option>
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-info py-2 mb-3" style="border-radius:10px;font-size:.82rem;">
                        <i class="bi bi-info-circle me-1"></i>
                        Une notification sera envoyée à l'utilisateur pour toute modification.
                    </div>
                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                style="border-radius:10px;">Annuler</button>
                        <button type="submit" class="btn btn-warning"
                                style="border-radius:10px;">
                            <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ══ MODAL : Supprimer ═════════════════════════════════════ -->
<div class="modal fade" id="modalSupprimer" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmer la suppression
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-4">
                <div class="alert alert-danger py-2 mb-3" style="border-radius:10px;font-size:.82rem;">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    Cette action est <strong>irréversible</strong>. Toutes les données associées seront supprimées.
                </div>
                <p class="mb-0">Voulez-vous supprimer définitivement le compte de <strong id="supNom"></strong> ?</p>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius:10px;">Annuler</button>
                <a href="#" id="supLien" class="btn btn-danger" style="border-radius:10px;">
                    <i class="bi bi-trash me-1"></i>Supprimer définitivement
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function ouvrirModifier(u) {
    document.getElementById('mod_id').value     = u.id;
    document.getElementById('mod_prenom').value = u.prenom;
    document.getElementById('mod_nom').value    = u.nom;
    document.getElementById('mod_email').value  = u.email;
    document.getElementById('mod_role').value   = u.role;
    document.getElementById('mod_statut').value = u.statut;
    new bootstrap.Modal(document.getElementById('modalModifier')).show();
}

document.getElementById('modalSupprimer').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('supNom').textContent = btn.dataset.nom;
    document.getElementById('supLien').href =
        'index.php?page=admin_utilisateurs&action=supprimer&id=' + btn.dataset.id;
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>