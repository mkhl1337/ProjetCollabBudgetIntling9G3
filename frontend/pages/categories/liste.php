<?php
// frontend/pages/categories/liste.php
require_once __DIR__ . '/../partials/header.php';

$globales = array_filter($categories, fn($c) => $c['user_id'] === null);
$perso    = array_filter($categories, fn($c) => $c['user_id'] !== null);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Catégories</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalAjouter">
        <i class="bi bi-plus-lg me-1"></i>Nouvelle catégorie
    </button>
</div>

<div class="row g-4">
    <!-- Catégories par défaut -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <h6 class="fw-semibold mb-3 text-muted"><i class="bi bi-shield-check me-2"></i>Catégories par défaut</h6>
                <div class="row g-2">
                <?php foreach($globales as $c): ?>
                <div class="col-6">
                    <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:<?= nettoyer($c['couleur']) ?>15">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:36px;height:36px;background:<?= nettoyer($c['couleur']) ?>22">
                            <i class="<?= nettoyer($c['icone']) ?>" style="color:<?= nettoyer($c['couleur']) ?>"></i>
                        </div>
                        <span class="small fw-medium"><?= nettoyer($c['nom']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Catégories personnalisées -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <h6 class="fw-semibold mb-3 text-muted"><i class="bi bi-person-gear me-2"></i>Mes catégories</h6>
                <?php if(empty($perso)): ?>
                <p class="text-muted small text-center py-3">Aucune catégorie personnalisée.<br>Créez-en une !</p>
                <?php else: ?>
                <div class="row g-2">
                <?php foreach($perso as $c): ?>
                <div class="col-12">
                    <div class="d-flex align-items-center gap-2 p-2 rounded border">
                        <div class="rounded-circle d-flex align-items-center justify-content-center"
                             style="width:36px;height:36px;background:<?= nettoyer($c['couleur']) ?>22">
                            <i class="<?= nettoyer($c['icone']) ?>" style="color:<?= nettoyer($c['couleur']) ?>"></i>
                        </div>
                        <span class="small fw-medium flex-grow-1"><?= nettoyer($c['nom']) ?></span>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-primary p-1" title="Modifier"
                                    data-bs-toggle="modal" data-bs-target="#modalModifier"
                                    data-id="<?= $c['id'] ?>" data-nom="<?= nettoyer($c['nom']) ?>"
                                    data-icone="<?= nettoyer($c['icone']) ?>" data-couleur="<?= nettoyer($c['couleur']) ?>">
                                <i class="bi bi-pencil" style="font-size:.75rem"></i>
                            </button>
                            <a href="index.php?page=categories&action=supprimer&id=<?= $c['id'] ?>"
                               class="btn btn-sm btn-outline-danger p-1" title="Supprimer"
                               onclick="return confirm('Supprimer cette catégorie ?')">
                                <i class="bi bi-trash" style="font-size:.75rem"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajouter -->
<div class="modal fade" id="modalAjouter" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0"><h5 class="modal-title fw-semibold">Nouvelle catégorie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="index.php?page=categories&action=ajouter" id="formAjouter">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <div class="mb-3">
                        <label class="form-label fw-medium small">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" class="form-control" required placeholder="Ex: Voyages">
                    </div>
                    <div class="row g-3 mb-3">
                        <!-- <div class="col-6">
                            <label class="form-label fw-medium small">Icône Bootstrap</label>
                            <input type="text" name="icone" class="form-control" value="bi-tag" placeholder="bi-tag">
                            <div class="form-text"><a href="https://icons.getbootstrap.com" target="_blank">Voir les icônes</a></div>
                        </div> -->
                        <div class="">
                            <label class="form-label fw-medium small">Couleur</label>
                            <input type="color" name="couleur" class="form-control form-control-color w-100" value="#6366f1">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="formAjouter" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i>Créer</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Modifier -->
<div class="modal fade" id="modalModifier" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0"><h5 class="modal-title fw-semibold">Modifier la catégorie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="index.php?page=categories&action=modifier" id="formModifier">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="id" id="editId">
                    <div class="mb-3">
                        <label class="form-label fw-medium small">Nom <span class="text-danger">*</span></label>
                        <input type="text" name="nom" id="editNom" class="form-control" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium small">Icône</label>
                            <input type="text" name="icone" id="editIcone" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium small">Couleur</label>
                            <input type="color" name="couleur" id="editCouleur" class="form-control form-control-color w-100">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                <button type="submit" form="formModifier" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('modalModifier').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    document.getElementById('editId').value     = btn.dataset.id;
    document.getElementById('editNom').value    = btn.dataset.nom;
    document.getElementById('editIcone').value  = btn.dataset.icone;
    document.getElementById('editCouleur').value= btn.dataset.couleur;
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
