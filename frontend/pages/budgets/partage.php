<?php
// frontend/pages/utilisateur/budgets/partage.php — Détail + membres
require_once __DIR__ . '/../partials/header.php';

$uid = utilisateurConnecte()['id'];
$isOwner = ($budget['proprietaire_id'] == $uid);

// Préparer données graphiques
$catLabels = array_column($depCat, 'nom');
$catData = array_map('floatval', array_column($depCat, 'total'));
$catColors = array_column($depCat, 'couleur');

// Plafonds par cat en tableau indexé par categorie_id
$plafondsByCat = [];
foreach ($plafonds as $p) {
    $plafondsByCat[$p['categorie_id']] = (float) $p['plafond'];
}

// Statut
$statusLabel = match ($budget['statut'] ?? 'actif') {
    'depasse' => ['Dépassé', 'danger'],
    'proche_limite' => ['Proche limite', 'warning'],
    'expire' => ['Expiré', 'secondary'],
    default => ['Actif', 'success'],
};
?>

<!-- ── En-tête budget ─────────────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4 p-4"
    style="background:linear-gradient(135deg,#0f172a,#1e40af);color:#fff;border-radius:16px;">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="fw-bold mb-0"><?= htmlspecialchars($budget['nom'], ENT_QUOTES) ?></h4>
                <span class="badge text-bg-<?= $statusLabel[1] ?>"><?= $statusLabel[0] ?></span>
                <span class="badge" style="background:rgba(255,255,255,.15);color:#fff;">
                    <i class="bi <?= $budget['type'] === 'partage' ? 'bi-people' : 'bi-person' ?> me-1"></i>
                    <?= $budget['type'] === 'partage' ? 'Partagé' : 'Individuel' ?>
                </span>
            </div>
            <?php if ($budget['description']): ?>
                <p style="opacity:.8;margin:0;font-size:.88rem;"><?= htmlspecialchars($budget['description'], ENT_QUOTES) ?>
                </p>
            <?php endif; ?>
            <p style="opacity:.7;font-size:.82rem;margin:4px 0 0;">
                <i class="bi bi-calendar2-range me-1"></i>
                <?= date('d/m/Y', strtotime($budget['date_debut'])) ?> →
                <?= date('d/m/Y', strtotime($budget['date_fin'])) ?>
                · <?= ucfirst($budget['periode']) ?>
            </p>
        </div>
        <?php if ($isOwner): ?>
            <div class="d-flex gap-2">
                <a href="index.php?page=budgets&action=edit&id=<?= $budget['id'] ?>" class="btn btn-sm"
                    style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);">
                    <i class="bi bi-pencil me-1"></i>Modifier
                </a>
                <?php if ($budget['type'] === 'partage'): ?>
                    <a href="index.php?page=invitations" class="btn btn-sm"
                        style="background:rgba(96,165,250,.25);color:#93c5fd;border:1px solid rgba(96,165,250,.4);">
                        <i class="bi bi-person-plus me-1"></i>Inviter
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── KPIs ─────────────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <?php
    $kpis = [
        ['label' => 'Total revenus', 'value' => number_format($revenus, 2, ',', ' ') . ' TND', 'icon' => 'bi-arrow-up-circle-fill', 'color' => '#22c55e', 'bg' => '#f0fdf4'],
        ['label' => 'Total dépenses', 'value' => number_format($depenses, 2, ',', ' ') . ' TND', 'icon' => 'bi-arrow-down-circle-fill', 'color' => '#ef4444', 'bg' => '#fef2f2'],
        [
            'label' => 'Solde',
            'value' => number_format($revenus - $depenses, 2, ',', ' ') . ' TND',
            'icon' => 'bi-wallet2',
            'color' => ($revenus - $depenses) >= 0 ? '#2563eb' : '#f59e0b',
            'bg' => '#eff6ff'
        ],
        ['label' => 'Consommation', 'value' => $taux . '%', 'icon' => 'bi-speedometer2', 'color' => $taux >= 100 ? '#ef4444' : ($taux >= 80 ? '#f59e0b' : '#8b5cf6'), 'bg' => '#f5f3ff'],
    ];
    foreach ($kpis as $k): ?>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3 h-100">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width:42px;height:42px;background:<?= $k['bg'] ?>">
                        <i class="<?= $k['icon'] ?> fs-5" style="color:<?= $k['color'] ?>"></i>
                    </div>
                    <div>
                        <div class="fw-bold"><?= $k['value'] ?></div>
                        <div class="text-muted small"><?= $k['label'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- ── Barre de consommation globale ────────────────────────────── -->
<?php if ($budget['plafond_global']): ?>
    <div class="card border-0 shadow-sm mb-4 p-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold small">Plafond global</span>
            <span class="fw-bold"><?= $taux ?>% consommé</span>
        </div>
        <div class="progress mb-1" style="height:12px;border-radius:999px;">
            <div class="progress-bar <?= $taux >= 100 ? 'bg-danger' : ($taux >= ($budget['seuil_alerte'] ?? 80) ? 'bg-warning' : 'bg-primary') ?>"
                style="width:<?= min(100, $taux) ?>%;border-radius:999px;transition:width .6s;"></div>
        </div>
        <div class="d-flex justify-content-between text-muted small mt-1">
            <span><?= number_format($depenses, 2, ',', ' ') ?> TND dépensés</span>
            <span>sur <?= number_format($budget['plafond_global'], 2, ',', ' ') ?> TND</span>
        </div>
    </div>
<?php endif; ?>

<!-- ── Graphique + Plafonds par catégorie ───────────────────────── -->
<div class="row g-3 mb-4">
    <!-- Camembert dépenses -->
    <?php if (!empty($depCat)): ?>
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-3 h-100">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-pie-chart me-2 text-primary"></i>Répartition des dépenses
                </h6>
                <canvas id="chartDepCat" height="200"></canvas>
            </div>
        </div>
    <?php endif; ?>

    <!-- Plafonds par catégorie -->
    <?php if (!empty($plafonds)): ?>
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-3 h-100">
                <h6 class="fw-semibold mb-3">
                    <i class="bi bi-bar-chart-line me-2 text-primary"></i>Plafonds par catégorie
                </h6>
                <?php foreach ($plafonds as $p):
                    $depCatMontant = 0;
                    foreach ($depCat as $d) {
                        if ($d['categorie_id'] == $p['categorie_id']) {
                            $depCatMontant = (float) $d['total'];
                            break;
                        }
                    }
                    $pctCat = $p['plafond'] > 0 ? min(100, round(($depCatMontant / $p['plafond']) * 100, 1)) : 0;
                    ?>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <div class="d-flex align-items-center gap-2 small">
                                <span class="rounded-circle d-inline-block"
                                    style="width:10px;height:10px;background:<?= htmlspecialchars($p['couleur'], ENT_QUOTES) ?>;flex-shrink:0;"></span>
                                <i class="<?= htmlspecialchars($p['icone'] ?? 'bi-tag', ENT_QUOTES) ?>"
                                    style="color:<?= htmlspecialchars($p['couleur'], ENT_QUOTES) ?>"></i>
                                <span class="fw-medium"><?= htmlspecialchars($p['categorie_nom'], ENT_QUOTES) ?></span>
                            </div>
                            <span class="small fw-bold"><?= $pctCat ?>%</span>
                        </div>
                        <div class="progress" style="height:7px;border-radius:999px;">
                            <div class="progress-bar"
                                style="width:<?= $pctCat ?>%;border-radius:999px;background:<?= htmlspecialchars($p['couleur'], ENT_QUOTES) ?>80;">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between text-muted" style="font-size:.73rem;margin-top:2px;">
                            <span><?= number_format($depCatMontant, 0, ',', ' ') ?> TND</span>
                            <span><?= number_format($p['plafond'], 0, ',', ' ') ?> TND</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ── Membres ───────────────────────────────────────────────────── -->
<?php if (!empty($membres)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold mb-0">
                    <i class="bi bi-people me-2 text-primary"></i>
                    Membres (<?= count($membres) ?>)
                </h6>
                <?php if (($isOwner) && ($budget['type'] === 'partage')): ?>
                    <a href="index.php?page=invitations" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-person-plus me-1"></i>Inviter
                    </a>
                <?php endif; ?>
            </div>
            <div class="row g-2">
                <?php foreach ($membres as $m): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="d-flex align-items-center gap-2 p-2 rounded border bg-light">
                            <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                                style="width:36px;height:36px;font-size:.85rem;
                            background:<?= $m['role'] === 'proprietaire' ? '#2563eb' : '#6366f1' ?>;">
                                <?= strtoupper(mb_substr($m['prenom'], 0, 1)) . strtoupper(mb_substr($m['nom'], 0, 1)) ?>
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="small fw-medium text-truncate">
                                    <?= htmlspecialchars($m['prenom'] . ' ' . $m['nom'], ENT_QUOTES) ?>
                                </div>
                                <div class="text-muted" style="font-size:.72rem;">
                                    <?= $m['role'] === 'proprietaire' ? '⭐ Propriétaire' : 'Membre' ?>
                                </div>
                            </div>
                            <?php if ($isOwner && $m['id'] != $uid): ?>
                                <form method="POST" action="index.php?page=budgets&action=retirer_membre"
                                    onsubmit="return confirm('Retirer ce membre ?')">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="budget_id" value="<?= $budget['id'] ?>">
                                    <input type="hidden" name="membre_id" value="<?= $m['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-1" title="Retirer">
                                        <i class="bi bi-person-x"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php
            // Le propriétaire peut supprimer le groupe s'il n'y a aucun autre membre
            $autresMembres = array_filter($membres, fn($m) => $m['id'] != $uid);
            if ($isOwner && empty($autresMembres)): ?>
                <div class="mt-3 pt-3 border-top">
                    <a href="index.php?page=budgets&action=supprimer&id=<?= $budget['id'] ?>"
                        class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('Supprimer ce groupe ? Il ne contient aucun autre membre.')">
                        <i class="bi bi-trash me-1"></i>Supprimer le groupe
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- ── Transactions du budget ────────────────────────────────────── -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-semibold mb-0">
                <i class="bi bi-arrow-left-right me-2 text-primary"></i>
                Transactions (<?= count($transactions) ?>)
            </h6>
            <a href="index.php?page=transactions&action=nouveau&budget_id=<?= $budget['id'] ?>"
                class="btn btn-sm btn-outline-success">
                <i class="bi bi-plus me-1"></i>Ajouter
            </a>
        </div>

        <?php if (empty($transactions)): ?>
            <p class="text-muted small text-center py-4">
                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                Aucune transaction pour ce budget
            </p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Catégorie</th>
                            <?php if ($budget['type'] === 'partage'): ?>
                                <th>Auteur</th>
                            <?php endif; ?>
                            <th class="text-end">Montant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $tx): ?>
                            <tr>
                                <td class="text-muted small"><?= date('d/m/Y', strtotime($tx['date_transaction'])) ?></td>
                                <td class="small"><?= htmlspecialchars($tx['description'] ?: '—', ENT_QUOTES) ?></td>
                                <td>
                                    <?php if ($tx['categorie_nom']): ?>
                                        <span class="badge"
                                            style="background:<?= htmlspecialchars($tx['couleur'] ?? '#6366f1', ENT_QUOTES) ?>22;color:<?= htmlspecialchars($tx['couleur'] ?? '#6366f1', ENT_QUOTES) ?>;">
                                            <?= htmlspecialchars($tx['categorie_nom'], ENT_QUOTES) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($budget['type'] === 'partage'): ?>
                                    <td class="small text-muted">
                                        <?= htmlspecialchars($tx['prenom'] . ' ' . $tx['user_nom'], ENT_QUOTES) ?>
                                    </td>
                                <?php endif; ?>
                                <td
                                    class="text-end fw-semibold <?= $tx['type'] === 'revenu' ? 'text-success' : 'text-danger' ?>">
                                    <?= $tx['type'] === 'revenu' ? '+' : '-' ?>
                                    <?= number_format($tx['montant'], 2, ',', ' ') ?>
                                    TND
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Script graphique ──────────────────────────────────────────── -->
<?php if (!empty($depCat)): ?>
    <script>
        const ctx = document.getElementById('chartDepCat')?.getContext('2d');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($catLabels) ?>,
                    datasets: [{ data: <?= json_encode($catData) ?>, backgroundColor: <?= json_encode($catColors) ?> }]
                },
                options: {
                    responsive: true,
                    cutout: '62%',
                    plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
                }
            });
        }
    </script>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>