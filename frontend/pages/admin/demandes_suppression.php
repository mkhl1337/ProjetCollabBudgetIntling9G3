<?php
// frontend/pages/admin/demandes_suppression.php
require_once __DIR__ . '/../partials/header.php';

$filtreStatut = $_GET['statut'] ?? 'all';

$filtres = [
    'all' => [
        'label' => 'Toutes les demandes',
        'cls'   => 'info',
        'icon'  => 'bi-list-ul'
    ],
    'en_attente' => [
        'label' => 'En attente',
        'cls'   => 'warning',
        'icon'  => 'bi-hourglass-split'
    ],
    'validee' => [
        'label' => 'Validées',
        'cls'   => 'success',
        'icon'  => 'bi-check-circle'
    ],
    'refusee' => [
        'label' => 'Refusées',
        'cls'   => 'danger',
        'icon'  => 'bi-x-circle'
    ],
];
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h5 class="fw-bold mb-1 text-dark">Demandes de suppression</h5>
        <p class="text-muted small mb-0"><?= count($demandes) ?> demande(s) affichée(s)</p>
    </div>
</div>

<!-- Filtres statut -->
<div class="d-flex gap-2 mb-4 flex-wrap">
    <?php foreach ($filtres as $val => $cfg): ?>
    <a href="index.php?page=admin_demandes&statut=<?= $val ?>"
       class="btn btn-sm <?= $filtreStatut === $val ? 'btn-'.$cfg['cls'] : 'btn-outline-secondary' ?>"
       style="border-radius:10px;padding:.45rem 1rem;font-size:.83rem;">
        <i class="bi <?= $cfg['icon'] ?> me-1"></i><?= $cfg['label'] ?>
        <?php if ($filtreStatut === $val): ?>
        <span class="badge bg-white <?= 'text-'.$cfg['cls'] ?> ms-1"
              style="font-size:.65rem;"><?= count($demandes) ?></span>
        <?php endif; ?>
    </a>
    <?php endforeach; ?>
</div>

<?php if (empty($demandes)): ?>
<div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body text-center py-5">
        <div class="mb-3" style="font-size:3rem;opacity:.2;"><i class="bi bi-inbox"></i></div>
        <h6 class="fw-semibold text-muted">Aucune demande <?= $filtres[$filtreStatut]['label'] ?></h6>
        <p class="text-muted small mb-0">Il n'y a aucune demande dans cette catégorie.</p>
    </div>
</div>

<?php else: ?>
<div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
    <div class="table-responsive">
        <table class="table align-middle mb-0" style="font-size:.875rem;">
            <thead style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                <tr>
                    <th class="ps-4 py-3 fw-semibold text-muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;">Utilisateur</th>
                    <th class="py-3 fw-semibold text-muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;">Motif</th>
                    <th class="py-3 fw-semibold text-muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;">Date demande</th>
                    <th class="py-3 fw-semibold text-muted" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;">Statut</th>
                    <?php if ($filtreStatut === 'en_attente'): ?>
                    <th class="py-3 pe-4 fw-semibold text-muted text-center" style="font-size:.78rem;text-transform:uppercase;letter-spacing:.04em;">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($demandes as $d):
                $statutCfg = match($d['statut']) {
                    'en_attente' => ['bg'=>'#fffbeb','color'=>'#92400e','border'=>'#fde68a','label'=>'En attente','icon'=>'bi-hourglass-split'],
                    'validee'    => ['bg'=>'#ecfdf5','color'=>'#065f46','border'=>'#a7f3d0','label'=>'Validée',   'icon'=>'bi-check-circle-fill'],
                    'refusee'    => ['bg'=>'#fef2f2','color'=>'#991b1b','border'=>'#fecaca','label'=>'Refusée',   'icon'=>'bi-x-circle-fill'],
                    default      => ['bg'=>'#f1f5f9','color'=>'#475569','border'=>'#e2e8f0','label'=>ucfirst($d['statut']),'icon'=>'bi-circle'],
                };
            ?>
            <tr style="border-bottom:1px solid #f8fafc;">
                <!-- Utilisateur -->
                <td class="ps-4 py-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                             style="width:40px;height:40px;font-size:.78rem;background:linear-gradient(135deg,#ef4444,#b91c1c);">
                            <?= strtoupper(substr($d['prenom'],0,1) . substr($d['nom'],0,1)) ?>
                        </div>
                        <div>
                            <div class="fw-semibold" style="color:#1e293b;">
                                <?= htmlspecialchars($d['prenom'].' '.$d['nom'], ENT_QUOTES) ?>
                            </div>
                            <div class="text-muted" style="font-size:.78rem;">
                                <i class="bi bi-envelope me-1"></i>
                                <?= htmlspecialchars($d['email'], ENT_QUOTES) ?>
                            </div>
                        </div>
                    </div>
                </td>
                <!-- Motif -->
                <td style="max-width:280px;">
                    <?php if ($d['motif']): ?>
                    <div class="text-muted small" style="white-space:normal;line-height:1.5;">
                        <?= nl2br(htmlspecialchars($d['motif'], ENT_QUOTES)) ?>
                    </div>
                    <?php else: ?>
                    <span class="text-muted fst-italic small">Aucun motif fourni</span>
                    <?php endif; ?>
                </td>
                <!-- Date -->
                <td class="text-muted small">
                    <i class="bi bi-calendar3 me-1"></i>
                    <?= date('d/m/Y', strtotime($d['date_demande'])) ?>
                    <div style="font-size:.72rem;"><?= date('H:i', strtotime($d['date_demande'])) ?></div>
                </td>
                <!-- Statut -->
                <td>
                    <span class="badge d-inline-flex align-items-center gap-1"
                          style="background:<?= $statutCfg['bg'] ?>;color:<?= $statutCfg['color'] ?>;
                                 border:1px solid <?= $statutCfg['border'] ?>;font-size:.75rem;
                                 border-radius:8px;padding:.3em .65em;">
                        <i class="bi <?= $statutCfg['icon'] ?>"></i>
                        <?= $statutCfg['label'] ?>
                    </span>
                </td>
                <!-- Actions (seulement si en_attente) -->
                <?php if ($filtreStatut === 'en_attente'): ?>
                <td class="text-center pe-4">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button"
                                class="btn btn-sm btn-danger d-flex align-items-center gap-1"
                                style="border-radius:9px;font-size:.8rem;"
                                data-bs-toggle="modal" data-bs-target="#modalValider"
                                data-id="<?= $d['id'] ?>"
                                data-nom="<?= htmlspecialchars($d['prenom'].' '.$d['nom'], ENT_QUOTES) ?>">
                            <i class="bi bi-trash"></i> Valider
                        </button>
                        <button type="button"
                                class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                                style="border-radius:9px;font-size:.8rem;"
                                data-bs-toggle="modal" data-bs-target="#modalRefuser"
                                data-id="<?= $d['id'] ?>"
                                data-nom="<?= htmlspecialchars($d['prenom'].' '.$d['nom'], ENT_QUOTES) ?>">
                            <i class="bi bi-x-lg"></i> Refuser
                        </button>
                    </div>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Modal : Valider suppression -->
<div class="modal fade" id="modalValider" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmer la suppression
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-0">
                <div class="alert alert-danger py-2 mb-3" style="border-radius:10px;font-size:.82rem;">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    Action <strong>irréversible</strong>. Le compte et toutes ses données seront supprimés.
                </div>
                <p class="mb-0">
                    Valider la demande de suppression du compte de <strong id="valNom"></strong> ?
                </p>
            </div>
            <div class="modal-footer border-0 px-4 py-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius:10px;">Annuler</button>
                <a href="#" id="valLien" class="btn btn-danger" style="border-radius:10px;">
                    <i class="bi bi-trash me-1"></i>Oui, supprimer le compte
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modal : Refuser demande -->
<div class="modal fade" id="modalRefuser" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:460px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-x-circle me-2 text-secondary"></i>Refuser la demande
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-0">
                <div class="alert alert-info py-2 mb-3" style="border-radius:10px;font-size:.82rem;">
                    <i class="bi bi-info-circle me-1"></i>
                    La demande sera annulée. L'utilisateur recevra une notification.
                </div>
                <p class="mb-0">
                    Refuser la demande de suppression de <strong id="refNom"></strong> ?
                    Son compte sera conservé.
                </p>
            </div>
            <div class="modal-footer border-0 px-4 py-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius:10px;">Annuler</button>
                <a href="#" id="refLien" class="btn btn-secondary" style="border-radius:10px;">
                    <i class="bi bi-x-circle me-1"></i>Refuser la demande
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('modalValider').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('valNom').textContent = btn.dataset.nom;
    document.getElementById('valLien').href =
        'index.php?page=admin_demandes&action=valider_suppression&id=' + btn.dataset.id;
});
document.getElementById('modalRefuser').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('refNom').textContent = btn.dataset.nom;
    document.getElementById('refLien').href =
        'index.php?page=admin_demandes&action=refuser_suppression&id=' + btn.dataset.id;
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>