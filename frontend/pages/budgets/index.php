<?php
// frontend/pages/utilisateur/budgets/index.php
require_once __DIR__ . '/../partials/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p class="text-muted mb-0 small">
        <?= count($budgets) ?> budget(s) trouvé(s)
    </p>
    <a href="index.php?page=budgets&action=nouveau" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i>Nouveau budget
    </a>
</div>

<?php if (empty($budgets)): ?>
<div class="card border-0 shadow-sm text-center p-5">
    <i class="bi bi-pie-chart fs-1 text-muted mb-3"></i>
    <h5 class="text-muted">Aucun budget pour l'instant</h5>
    <p class="text-muted small mb-4">Créez votre premier budget pour commencer à suivre vos dépenses.</p>
    <div>
        <a href="index.php?page=budgets&action=nouveau" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i>Créer un budget
        </a>
    </div>
</div>
<?php else: ?>
<div class="row g-3">
<?php foreach ($budgets as $b):
    $statusLabel = match($b['statut'] ?? 'actif') {
        'depasse'       => ['Dépassé',       'danger'],
        'proche_limite' => ['Proche limite',  'warning'],
        'expire'        => ['Expiré',         'secondary'],
        default         => ['Actif',          'success'],
    };
    $barClass = match($b['statut'] ?? 'actif') {
        'depasse'       => 'bg-danger',
        'proche_limite' => 'bg-warning',
        'expire'        => 'bg-secondary',
        default         => 'bg-primary',
    };
    $isOwner = ($b['proprietaire_id'] == utilisateurConnecte()['id']);
?>
<div class="col-md-6 col-lg-4">
    <div class="card border-0 shadow-sm h-100">
        <div class="card-body p-3">
            <!-- En-tête -->
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="flex-grow-1 me-2">
                    <h6 class="fw-bold mb-0 text-truncate">
                        <?= htmlspecialchars($b['nom'], ENT_QUOTES) ?>
                    </h6>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="badge bg-<?= $b['type'] === 'partage' ? 'info text-dark' : 'light text-muted' ?> small">
                            <i class="bi <?= $b['type'] === 'partage' ? 'bi-people' : 'bi-person' ?> me-1"></i>
                            <?= $b['type'] === 'partage' ? 'Partagé' : 'Individuel' ?>
                        </span>
                        <span class="badge text-bg-<?= $statusLabel[1] ?> small">
                            <?= $statusLabel[0] ?>
                        </span>
                        <?php if ($isOwner): ?>
                        <span class="badge bg-light text-primary border small" title="Propriétaire">
                            <i class="bi bi-star-fill"></i>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary border-0 p-1"
                            data-bs-toggle="dropdown">
                        <i class="bi bi-three-dots-vertical"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li>
                            <a class="dropdown-item" href="index.php?page=budgets&action=detail&id=<?= $b['id'] ?>">
                                <i class="bi bi-eye me-2 text-primary"></i>Voir le détail
                            </a>
                        </li>
                        <?php if ($isOwner): ?>
                        <li>
                            <a class="dropdown-item" href="index.php?page=budgets&action=edit&id=<?= $b['id'] ?>">
                                <i class="bi bi-pencil me-2 text-warning"></i>Modifier
                            </a>
                        </li>
                        <?php if ($b['type'] === 'partage'): ?>
                        <li>
                            <a class="dropdown-item" href="index.php?page=invitations">
                                <i class="bi bi-person-plus me-2 text-info"></i>Inviter
                            </a>
                        </li>
                        <?php endif; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger"
                               href="index.php?page=budgets&action=supprimer&id=<?= $b['id'] ?>"
                               onclick="return confirm('Supprimer ce budget ? Cette action est irréversible.')">
                                <i class="bi bi-trash me-2"></i>Supprimer
                            </a>
                        </li>
                        <?php else: ?>
                        <?php if ($b['type'] === 'partage'): ?>
                        <li>
                            <a class="dropdown-item text-warning"
                               href="index.php?page=budgets&action=quitter&id=<?= $b['id'] ?>"
                               onclick="return confirm('Quitter ce budget partagé ?')">
                                <i class="bi bi-box-arrow-right me-2"></i>Quitter
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Période -->
            <div class="text-muted small mb-3">
                <i class="bi bi-calendar2-range me-1"></i>
                <?= date('d/m/Y', strtotime($b['date_debut'])) ?> →
                <?= date('d/m/Y', strtotime($b['date_fin']))   ?>
                <span class="ms-2 badge bg-light text-muted border"><?= ucfirst($b['periode']) ?></span>
            </div>

            <!-- Barre de progression -->
            <?php if ($b['plafond_global']): ?>
            <div class="mb-2">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="text-muted">Consommation</span>
                    <span class="fw-semibold"><?= $b['taux'] ?>%</span>
                </div>
                <div class="progress" style="height:8px;border-radius:999px;">
                    <div class="progress-bar <?= $barClass ?>"
                         style="width:<?= min(100, $b['taux']) ?>%;border-radius:999px;transition:width .5s;"></div>
                </div>
                <div class="d-flex justify-content-between mt-1" style="font-size:.75rem;color:#64748b;">
                    <span><?= number_format($b['depenses'], 0, ',', ' ') ?> TND</span>
                    <span><?= number_format($b['plafond_global'], 0, ',', ' ') ?> TND</span>
                </div>
            </div>
            <?php else: ?>
            <p class="text-muted small mb-2">
                <i class="bi bi-infinity me-1"></i>Pas de plafond global défini
            </p>
            <?php endif; ?>

            <!-- Prop -->
            <?php if ($b['type'] === 'partage'): ?>
            <div class="text-muted small">
                <i class="bi bi-person me-1"></i>
                Propriétaire : <?= htmlspecialchars($b['proprio_prenom'] . ' ' . $b['proprio_nom'], ENT_QUOTES) ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0 pb-3 px-3">
            <a href="index.php?page=budgets&action=detail&id=<?= $b['id'] ?>"
               class="btn btn-outline-primary btn-sm w-100">
                <i class="bi bi-eye me-1"></i>Voir le détail
            </a>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
