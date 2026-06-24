<?php
// frontend/pages/transactions/liste.php
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="fw-bold mb-0">Mes transactions</h5>
    <div class="d-flex gap-2">
        <a class="btn btn-success btn-sm" href="index.php?page=transactions&action=export">
            <i class="bi bi-download me-1"></i>Exporter CSV
        </a>
        <button type="button" class="btn btn-primary btn-sm" id="btnNouvelle">
            <i class="bi bi-plus-lg me-1"></i>Nouvelle transaction
        </button>
    </div>
</div>

<!-- Filtres -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="index.php" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="transactions">
            <div class="col-md-3">
                <label class="form-label small fw-medium">Type</label>
                <select name="type" class="form-select form-select-sm">
                    <option value="">Tous</option>
                    <option value="revenu"  <?= ($_GET['type'] ?? '') === 'revenu'  ? 'selected' : '' ?>>Revenus</option>
                    <option value="depense" <?= ($_GET['type'] ?? '') === 'depense' ? 'selected' : '' ?>>Dépenses</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-medium">Catégorie</label>
                <select name="categorie_id" class="form-select form-select-sm">
                    <option value="">Toutes</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($_GET['categorie_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>>
                            <?= nettoyer($c['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-medium">Mois</label>
                <input type="month" name="mois" class="form-control form-control-sm"
                    value="<?= nettoyer($_GET['mois'] ?? '') ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="bi bi-funnel me-1"></i>Filtrer
                </button>
                <a href="index.php?page=transactions" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tableau -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($transactions)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                Aucune transaction trouvée.
                <button type="button" id="btnNouvelleBis" class="d-block mx-auto mt-2 btn btn-link">
                    Ajouter la première
                </button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Description</th>
                            <th>Catégorie</th>
                            <th>Budget</th>
                            <th>Type</th>
                            <th class="text-end">Montant</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td class="ps-4 small text-muted">
                                    <?= date('d/m/Y', strtotime($t['date_transaction'])) ?>
                                </td>
                                <td>
                                    <span class="fw-medium small">
                                        <?= nettoyer($t['description'] ?: 'Sans description') ?>
                                    </span>
                                    <?php if ($t['commentaire']): ?>
                                        <br><span class="text-muted" style="font-size:.73rem">
                                            <?= nettoyer($t['commentaire']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($t['categorie_nom']): ?>
                                        <span class="badge rounded-pill small"
                                            style="background:<?= nettoyer($t['categorie_couleur'] ?? '#6366f1') ?>22;color:<?= nettoyer($t['categorie_couleur'] ?? '#6366f1') ?>">
                                            <i class="<?= nettoyer($t['categorie_icone'] ?? 'bi-tag') ?> me-1"></i>
                                            <?= nettoyer($t['categorie_nom']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted"><?= nettoyer($t['budget_nom'] ?? '—') ?></td>
                                <td>
                                    <span class="badge <?= $t['type'] === 'revenu' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= $t['type'] === 'revenu' ? 'Revenu' : 'Dépense' ?>
                                    </span>
                                </td>
                                <td class="text-end fw-semibold <?= $t['type'] === 'revenu' ? 'text-success' : 'text-danger' ?>">
                                    <?= $t['type'] === 'revenu' ? '+' : '-' ?>
                                    <?= number_format($t['montant'], 2) ?> TND
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex gap-1 justify-content-end">
                                        <!-- Modifier → opens modal -->
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            title="Modifier"
                                            onclick='ouvrirModifier(<?= json_encode([
                                                'id'               => $t['id'],
                                                'type'             => $t['type'],
                                                'montant'          => $t['montant'],
                                                'date_transaction' => $t['date_transaction'],
                                                'description'      => $t['description'] ?? '',
                                                'categorie_id'     => $t['categorie_id'] ?? '',
                                                'budget_id'        => $t['budget_id'] ?? '',
                                                'commentaire'      => $t['commentaire'] ?? '',
                                            ], JSON_HEX_APOS | JSON_HEX_TAG) ?>)'>
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <!-- Supprimer → opens confirm modal -->
                                        <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            title="Supprimer"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalSupprimer"
                                            data-id="<?= $t['id'] ?>"
                                            data-desc="<?= htmlspecialchars($t['description'] ?: 'Sans description', ENT_QUOTES) ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Totaux -->
            <?php
            $totalRev = array_sum(array_map(fn($t) => $t['type'] === 'revenu'  ? (float)$t['montant'] : 0, $transactions));
            $totalDep = array_sum(array_map(fn($t) => $t['type'] === 'depense' ? (float)$t['montant'] : 0, $transactions));
            ?>
            <div class="d-flex justify-content-end gap-4 px-4 py-3 bg-light border-top small fw-semibold">
                <span class="text-success">Revenus : +<?= number_format($totalRev, 2) ?> TND</span>
                <span class="text-danger">Dépenses : -<?= number_format($totalDep, 2) ?> TND</span>
                <span class="<?= ($totalRev - $totalDep) >= 0 ? 'text-primary' : 'text-warning' ?>">
                    Solde : <?= number_format($totalRev - $totalDep, 2) ?> TND
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>


<!-- ══ MODAL : Ajouter / Modifier ══════════════════════════════ -->
<div class="modal fade" id="modalTransaction" tabindex="-1" aria-labelledby="modalTransactionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:560px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">

            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="modalTransactionLabel">
                        <i class="bi bi-plus-circle text-primary me-2" id="modalTxnIcon"></i>
                        <span id="modalTxnTitle">Nouvelle transaction</span>
                    </h5>
                    <p class="text-muted small mb-0 mt-1">Remplissez les informations ci-dessous</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body px-4 pb-4">
                <form method="POST" action="index.php?page=transactions&action=ajouter" id="formTransaction">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="id" id="txn_id">

                    <!-- Type -->
                    <div class="mb-3">
                        <label class="form-label fw-medium small">Type <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="txnTypeDepense" value="depense" checked>
                                <label class="form-check-label text-danger fw-medium" for="txnTypeDepense">
                                    <i class="bi bi-arrow-down-circle me-1"></i>Dépense
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="type" id="txnTypeRevenu" value="revenu">
                                <label class="form-check-label text-success fw-medium" for="txnTypeRevenu">
                                    <i class="bi bi-arrow-up-circle me-1"></i>Revenu
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Montant + Date -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium small">Montant (TND) <span class="text-danger">*</span></label>
                            <input type="number" name="montant" id="txn_montant" class="form-control"
                                   step="0.01" min="0.01" required placeholder="0.00"
                                   style="border-radius:10px;">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium small">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date_transaction" id="txn_date" class="form-control"
                                   required style="border-radius:10px;">
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label fw-medium small">Description</label>
                        <input type="text" name="description" id="txn_description" class="form-control"
                               maxlength="255" placeholder="Ex: Courses Carrefour, Loyer..."
                               style="border-radius:10px;">
                    </div>

                    <!-- Catégorie + Budget -->
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium small">Catégorie</label>
                            <select name="categorie_id" id="txn_categorie" class="form-select" style="border-radius:10px;">
                                <option value="">— Sans catégorie —</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= nettoyer($c['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium small">Budget associé</label>
                            <select name="budget_id" id="txn_budget" class="form-select" style="border-radius:10px;">
                                <option value="">— Sans budget —</option>
                                <?php foreach ($budgets as $b): ?>
                                    <option value="<?= $b['id'] ?>"><?= nettoyer($b['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Commentaire -->
                    <div class="mb-4">
                        <label class="form-label fw-medium small">Commentaire (optionnel)</label>
                        <textarea name="commentaire" id="txn_commentaire" class="form-control"
                                  rows="2" placeholder="Notes supplémentaires..."
                                  style="border-radius:10px;"></textarea>
                    </div>

                    <div class="d-flex gap-2 justify-content-end">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                                style="border-radius:10px;">Annuler</button>
                        <button type="submit" class="btn btn-primary" id="txnSubmitBtn"
                                style="border-radius:10px;">
                            <i class="bi bi-plus-lg me-1" id="txnSubmitIcon"></i>
                            <span id="txnSubmitText">Ajouter</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>


<!-- ══ MODAL : Confirmer suppression ════════════════════════════ -->
<div class="modal fade" id="modalSupprimer" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content border-0 shadow-lg" style="border-radius:18px;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold text-danger">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmer la suppression
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 pb-2">
                <div class="alert alert-danger py-2 mb-3" style="border-radius:10px;font-size:.82rem;">
                    <i class="bi bi-exclamation-circle me-1"></i>
                    Cette action est <strong>irréversible</strong>.
                </div>
                <p class="mb-0">Voulez-vous supprimer la transaction
                    <strong id="supDesc"></strong> ?
                </p>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        style="border-radius:10px;">Annuler</button>
                <a href="#" id="supLien" class="btn btn-danger" style="border-radius:10px;">
                    <i class="bi bi-trash me-1"></i>Supprimer
                </a>
            </div>
        </div>
    </div>
</div>


<script>
/* ── Helpers ──────────────────────────────────────────────── */
const today = new Date().toISOString().split('T')[0];

function resetForm() {
    document.getElementById('formTransaction').action = 'index.php?page=transactions&action=ajouter';
    document.getElementById('txn_id').value           = '';
    document.getElementById('txnTypeDepense').checked = true;
    document.getElementById('txn_montant').value      = '';
    document.getElementById('txn_date').value         = today;
    document.getElementById('txn_description').value  = '';
    document.getElementById('txn_categorie').value    = '';
    document.getElementById('txn_budget').value       = '';
    document.getElementById('txn_commentaire').value  = '';
}

function setModalMode(isEdit) {
    document.getElementById('modalTxnTitle').textContent   = isEdit ? 'Modifier la transaction' : 'Nouvelle transaction';
    document.getElementById('modalTxnIcon').className      = `bi bi-${isEdit ? 'pencil' : 'plus-circle'} text-primary me-2`;
    document.getElementById('txnSubmitIcon').className     = `bi bi-${isEdit ? 'check-lg' : 'plus-lg'} me-1`;
    document.getElementById('txnSubmitText').textContent   = isEdit ? 'Enregistrer' : 'Ajouter';
    document.getElementById('txnSubmitBtn').className      = `btn ${isEdit ? 'btn-warning' : 'btn-primary'}`;
}

/* ── Open modal for NEW transaction ───────────────────────── */
function ouvrirNouvelle() {
    resetForm();
    setModalMode(false);
    new bootstrap.Modal(document.getElementById('modalTransaction')).show();
}

/* ── Open modal for EDIT ──────────────────────────────────── */
function ouvrirModifier(t) {
    resetForm();
    setModalMode(true);

    document.getElementById('formTransaction').action     = 'index.php?page=transactions&action=modifier';
    document.getElementById('txn_id').value               = t.id;
    document.querySelector(`input[name="type"][value="${t.type}"]`).checked = true;
    document.getElementById('txn_montant').value          = t.montant;
    document.getElementById('txn_date').value             = t.date_transaction;
    document.getElementById('txn_description').value      = t.description  || '';
    document.getElementById('txn_categorie').value        = t.categorie_id || '';
    document.getElementById('txn_budget').value           = t.budget_id    || '';
    document.getElementById('txn_commentaire').value      = t.commentaire  || '';

    new bootstrap.Modal(document.getElementById('modalTransaction')).show();
}

/* ── Delete confirmation modal ────────────────────────────── */
document.getElementById('modalSupprimer').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('supDesc').textContent = btn.dataset.desc || 'cette transaction';
    document.getElementById('supLien').href =
        'index.php?page=transactions&action=supprimer&id=' + btn.dataset.id;
});

/* ── "Nouvelle transaction" buttons ───────────────────────── */
document.getElementById('btnNouvelle').addEventListener('click', ouvrirNouvelle);
document.getElementById('btnNouvelleBis')?.addEventListener('click', ouvrirNouvelle);
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>