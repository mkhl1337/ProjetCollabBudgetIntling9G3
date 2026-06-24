<?php
// frontend/pages/utilisateur/budgets/create.php
// Used for both create (budget=null) and edit (budget=array)
require_once __DIR__ . '/../partials/header.php';
$estEdition = ($budget !== null);
?>

<div class="row justify-content-center">
<div class="col-lg-8">

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
        <h5 class="fw-bold mb-0">
            <i class="bi <?= $estEdition ? 'bi-pencil-square' : 'bi-plus-circle' ?> me-2 text-primary"></i>
            <?= $estEdition ? 'Modifier le budget' : 'Créer un nouveau budget' ?>
        </h5>
        <p class="text-muted small mt-1 mb-0">
            <?= $estEdition
                ? 'Modifiez les paramètres et les plafonds de votre budget.'
                : 'Définissez les paramètres de votre budget. Vous pourrez inviter des membres après la création.' ?>
        </p>
    </div>

    <div class="card-body p-4">
        <form method="POST"
              action="index.php?page=budgets&action=<?= $estEdition ? 'modifier' : 'creer' ?>">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <?php if ($estEdition): ?>
            <input type="hidden" name="id" value="<?= $budget['id'] ?>">
            <?php endif; ?>

            <!-- ── Infos générales ─────────────────────────── -->
            <h6 class="text-muted text-uppercase fw-semibold mb-3" style="font-size:.72rem;letter-spacing:.08em;">
                Informations générales
            </h6>

            <div class="row g-3 mb-4">
                <div class="col-12">
                    <label class="form-label fw-medium">Nom du budget <span class="text-danger">*</span></label>
                    <input type="text" name="nom" class="form-control"
                           placeholder="Ex : Budget familial juin, Colocation 2025…"
                           value="<?= htmlspecialchars($budget['nom'] ?? '', ENT_QUOTES) ?>"
                           required maxlength="150">
                </div>

                <div class="col-12">
                    <label class="form-label fw-medium">Description</label>
                    <textarea name="description" class="form-control" rows="2"
                              placeholder="Objectif, notes…"
                    ><?= htmlspecialchars($budget['description'] ?? '', ENT_QUOTES) ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-medium">Type</label>
                    <select name="type" class="form-select" id="selectType">
                        <option value="individuel" <?= ($budget['type'] ?? 'individuel') === 'individuel' ? 'selected' : '' ?>>
                            👤 Individuel
                        </option>
                        <option value="partage" <?= ($budget['type'] ?? '') === 'partage' ? 'selected' : '' ?>>
                            👥 Partagé (collaboratif)
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-medium">Période</label>
                    <select name="periode" class="form-select">
                        <option value="mensuel"      <?= ($budget['periode'] ?? 'mensuel') === 'mensuel'      ? 'selected' : '' ?>>Mensuel</option>
                        <option value="hebdomadaire" <?= ($budget['periode'] ?? '') === 'hebdomadaire' ? 'selected' : '' ?>>Hebdomadaire</option>
                        <option value="personnalise" <?= ($budget['periode'] ?? '') === 'personnalise' ? 'selected' : '' ?>>Personnalisé</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-medium">Date de début <span class="text-danger">*</span></label>
                    <input type="date" name="date_debut" class="form-control"
                           value="<?= htmlspecialchars($budget['date_debut'] ?? date('Y-m-01'), ENT_QUOTES) ?>"
                           required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-medium">Date de fin <span class="text-danger">*</span></label>
                    <input type="date" name="date_fin" class="form-control"
                           value="<?= htmlspecialchars($budget['date_fin'] ?? date('Y-m-t'), ENT_QUOTES) ?>"
                           required>
                </div>
            </div>

            <!-- ── Plafonds ────────────────────────────────── -->
            <h6 class="text-muted text-uppercase fw-semibold mb-3" style="font-size:.72rem;letter-spacing:.08em;">
                Plafonds budgétaires
            </h6>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-medium">Plafond global (TND)</label>
                    <div class="input-group">
                        <input type="number" name="plafond_global" class="form-control"
                               step="0.01" min="0" placeholder="Illimité si vide"
                               value="<?= $budget['plafond_global'] ?? '' ?>">
                        <span class="input-group-text">TND</span>
                    </div>
                    <div class="form-text">Laissez vide pour ne pas définir de plafond global.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-medium">Seuil d'alerte (%)</label>
                    <div class="input-group">
                        <input type="number" name="seuil_alerte" class="form-control"
                               min="10" max="100" value="<?= $budget['seuil_alerte'] ?? 80 ?>">
                        <span class="input-group-text">%</span>
                    </div>
                    <div class="form-text">Alerte quand ce pourcentage du plafond est atteint.</div>
                </div>
            </div>

            <!-- ── Plafonds par catégorie ──────────────────── -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-muted text-uppercase fw-semibold mb-0" style="font-size:.72rem;letter-spacing:.08em;">
                    Plafonds par catégorie
                    <span class="badge bg-light text-muted border ms-1" id="nbrPlafonds">
                        <?= count($plafonds) ?>
                    </span>
                </h6>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="ajouterPlafond()">
                    <i class="bi bi-plus me-1"></i>Ajouter
                </button>
            </div>

            <div id="plafonds-container">
                <?php if (!empty($plafonds)): ?>
                <?php foreach ($plafonds as $i => $p): ?>
                <div class="row g-2 align-items-center mb-2 plafond-row">
                    <div class="col-6">
                        <select name="plafond_cat[]" class="form-select form-select-sm">
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"
                                <?= $cat['id'] == $p['categorie_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nom'], ENT_QUOTES) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-5">
                        <div class="input-group input-group-sm">
                            <input type="number" name="plafond_montant[]" class="form-control"
                                   step="0.01" min="0.01" value="<?= $p['plafond'] ?>" required>
                            <span class="input-group-text">TND</span>
                        </div>
                    </div>
                    <div class="col-1">
                        <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                onclick="this.closest('.plafond-row').remove(); updateCount()">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Template caché pour JS -->
            <template id="plafond-tpl">
                <div class="row g-2 align-items-center mb-2 plafond-row">
                    <div class="col-6">
                        <select name="plafond_cat[]" class="form-select form-select-sm">
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>">
                                <?= htmlspecialchars($cat['nom'], ENT_QUOTES) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-5">
                        <div class="input-group input-group-sm">
                            <input type="number" name="plafond_montant[]" class="form-control"
                                   step="0.01" min="0.01" placeholder="Montant" required>
                            <span class="input-group-text">TND</span>
                        </div>
                    </div>
                    <div class="col-1">
                        <button type="button" class="btn btn-sm btn-outline-danger border-0"
                                onclick="this.closest('.plafond-row').remove(); updateCount()">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>
            </template>

            <!-- Info budget partagé -->
            <div id="info-partage" class="alert alert-info d-flex gap-2 mt-3 mb-0"
                 style="display:<?= ($budget['type'] ?? '') === 'partage' ? 'flex' : 'none' ?>!important;">
                <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                <div class="small">
                    Après la création, vous pourrez inviter des membres via la page
                    <strong>Invitations</strong>.
                </div>
            </div>

            <!-- Boutons -->
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                <a href="index.php?page=budgets" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi <?= $estEdition ? 'bi-check-lg' : 'bi-plus-lg' ?> me-1"></i>
                    <?= $estEdition ? 'Enregistrer les modifications' : 'Créer le budget' ?>
                </button>
            </div>
        </form>
    </div>
</div>

</div>
</div>

<script>
function ajouterPlafond() {
    const tpl = document.getElementById('plafond-tpl');
    const clone = tpl.content.cloneNode(true);
    document.getElementById('plafonds-container').appendChild(clone);
    updateCount();
}
function updateCount() {
    const n = document.querySelectorAll('.plafond-row').length;
    document.getElementById('nbrPlafonds').textContent = n;
}
document.getElementById('selectType')?.addEventListener('change', function () {
    document.getElementById('info-partage').style.display =
        this.value === 'partage' ? 'flex' : 'none';
});
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
