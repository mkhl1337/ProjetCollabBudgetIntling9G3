<?php
// frontend/pages/admin/validation_comptes.php
require_once __DIR__ . '/../partials/header.php';
$recherche = $_GET['q'] ?? '';
?>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
        <h5 class="fw-bold mb-1 text-dark">Validation des comptes</h5>
        <p class="text-muted small mb-0">
            <?= count($utilisateurs) ?> compte(s) en attente de validation
        </p>
    </div>
    <span class="badge bg-warning text-dark px-3 py-2" style="border-radius:10px;font-size:.82rem;">
        <i class="bi bi-hourglass-split me-1"></i><?= count($utilisateurs) ?> en attente
    </span>
</div>

<!-- Barre de recherche -->
<div class="card border-0 shadow-sm mb-4" style="border-radius:14px;">
    <div class="card-body p-3">
        <form method="GET" action="index.php" class="d-flex gap-2 align-items-center">
            <input type="hidden" name="page" value="admin_validation">
            <div class="input-group" style="max-width:320px;">
                <span class="input-group-text bg-white border-end-0" style="border-radius:10px 0 0 10px;">
                    <i class="bi bi-search text-muted" style="font-size:.85rem;"></i>
                </span>
                <input type="text" name="q" value="<?= htmlspecialchars($recherche, ENT_QUOTES) ?>"
                       class="form-control border-start-0 ps-0"
                       placeholder="Chercher par nom, prénom ou email…"
                       style="border-radius:0 10px 10px 0;">
            </div>
            <button type="submit" class="btn btn-sm btn-outline-primary" style="border-radius:8px;">
                <i class="bi bi-search me-1"></i>Rechercher
            </button>
            <?php if ($recherche): ?>
            <a href="index.php?page=admin_validation" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                <i class="bi bi-x-circle me-1"></i>Effacer
            </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if (empty($utilisateurs)): ?>
<!-- État vide -->
<div class="card border-0 shadow-sm" style="border-radius:16px;">
    <div class="card-body text-center py-5">
        <div class="mb-3" style="font-size:3rem;opacity:.25;">
            <i class="bi bi-patch-check"></i>
        </div>
        <h6 class="fw-semibold text-muted mb-1">
            <?= $recherche ? 'Aucun résultat pour cette recherche' : 'Aucun compte en attente' ?>
        </h6>
        <p class="text-muted small mb-0">
            <?= $recherche ? 'Essayez avec d\'autres termes.' : 'Tous les comptes ont été traités.' ?>
        </p>
    </div>
</div>

<?php else: ?>
<!-- Liste des comptes en attente -->
<div class="row g-3">
<?php foreach ($utilisateurs as $u): ?>
<div class="col-lg-6">
    <div class="card border-0 shadow-sm" style="border-radius:16px;border-left:4px solid #f59e0b !important;overflow:hidden;">
        <div class="card-body p-4">
            <div class="d-flex align-items-start gap-3">
                <!-- Avatar -->
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                     style="width:50px;height:50px;font-size:.9rem;background:linear-gradient(135deg,#f59e0b,#d97706);">
                    <?= strtoupper(substr($u['prenom'],0,1) . substr($u['nom'],0,1)) ?>
                </div>
                <!-- Info -->
                <div class="flex-grow-1 min-width-0">
                    <div class="fw-bold text-dark mb-1" style="font-size:.95rem;">
                        <?= htmlspecialchars($u['prenom'].' '.$u['nom'], ENT_QUOTES) ?>
                    </div>
                    <div class="text-muted small d-flex align-items-center gap-1 mb-1">
                        <i class="bi bi-envelope"></i>
                        <?= htmlspecialchars($u['email'], ENT_QUOTES) ?>
                    </div>
                    <div class="text-muted small d-flex align-items-center gap-1">
                        <i class="bi bi-calendar3"></i>
                        Inscrit le <?= date('d/m/Y à H:i', strtotime($u['date_inscription'])) ?>
                    </div>
                </div>
                <!-- Badge statut -->
                <span class="badge" style="background:#fffbeb;color:#92400e;border:1px solid #fde68a;border-radius:8px;padding:.35em .7em;font-size:.75rem;flex-shrink:0;">
                    <i class="bi bi-hourglass-split me-1"></i>En attente
                </span>
            </div>

            <!-- Actions -->
            <div class="d-flex gap-2 mt-4 pt-3" style="border-top:1px solid #f1f5f9;">
                <a href="index.php?page=admin_validation&action=valider&id=<?= $u['id'] ?>"
                   class="btn btn-success btn-sm flex-fill d-flex align-items-center justify-content-center gap-2"
                   style="border-radius:10px;"
                   onclick="return confirm('Valider et activer le compte de <?= htmlspecialchars($u['prenom'].' '.$u['nom'], ENT_QUOTES) ?> ? Un email lui sera envoyé.')">
                    <i class="bi bi-check-circle-fill"></i>
                    Valider le compte
                </a>
                <button type="button"
                        class="btn btn-outline-danger btn-sm flex-fill d-flex align-items-center justify-content-center gap-2"
                        style="border-radius:10px;"
                        data-bs-toggle="modal" data-bs-target="#modalRejeter"
                        data-id="<?= $u['id'] ?>"
                        data-nom="<?= htmlspecialchars($u['prenom'].' '.$u['nom'], ENT_QUOTES) ?>">
                    <i class="bi bi-x-circle"></i>
                    Refuser
                </button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Modal refus -->
<div class="modal fade" id="modalRejeter" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">
            <div class="modal-header border-0 px-4 pt-4 pb-0">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-x-circle-fill me-2"></i>Refuser ce compte
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-0">
                <div class="alert alert-warning py-2 mb-3" style="border-radius:10px;font-size:.82rem;">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Le refus supprimera définitivement ce compte.
                </div>
                <p class="mb-0">Refuser et supprimer le compte de <strong id="rejNom"></strong> ?</p>
            </div>
            <div class="modal-footer border-0 px-4 py-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius:10px;">Annuler</button>
                <a href="#" id="rejLien" class="btn btn-danger" style="border-radius:10px;">
                    <i class="bi bi-trash me-1"></i>Oui, refuser et supprimer
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('modalRejeter').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('rejNom').textContent = btn.dataset.nom;
    document.getElementById('rejLien').href =
        'index.php?page=admin_validation&action=rejeter&id=' + btn.dataset.id;
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>