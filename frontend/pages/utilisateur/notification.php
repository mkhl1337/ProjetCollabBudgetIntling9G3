<?php
// frontend/pages/utilisateur/notifications.php
// Variables disponibles : $notifications, $pageTitle (injectées par NotificationController)

require_once __DIR__ . '/../partials/header.php';

$nonLues = array_filter($notifications, fn($n) => !$n['lue']);
$lues    = array_filter($notifications, fn($n) =>  $n['lue']);
?>

<!-- En-tête page -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h5 class="fw-bold mb-1">Mes notifications</h5>
        <span class="text-muted small">
            <?= count($nonLues) ?> non lue(s) · <?= count($notifications) ?> au total
        </span>
    </div>
    <div class="d-flex gap-2">
        <?php if (!empty($nonLues)): ?>
        <a href="index.php?page=notifications&action=tout_lire"
           class="btn btn-sm btn-outline-primary">
            <i class="bi bi-check-all me-1"></i>Tout marquer lu
        </a>
        <?php endif; ?>
        <?php if (!empty($lues)): ?>
        <a href="index.php?page=notifications&action=supprimer_lues"
           class="btn btn-sm btn-outline-secondary"
           onclick="return confirm('Supprimer toutes les notifications lues ?')">
            <i class="bi bi-trash me-1"></i>Vider les lues
        </a>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($notifications)): ?>
<div class="card border-0 shadow-sm text-center py-5">
    <i class="bi bi-bell-slash text-muted" style="font-size:3rem"></i>
    <h6 class="text-muted mt-3 mb-1">Aucune notification</h6>
    <p class="text-muted small mb-0">Tout est calme pour l'instant.</p>
</div>

<?php else: ?>
<!-- Onglets -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-toutes">
            Toutes <span class="badge bg-secondary ms-1"><?= count($notifications) ?></span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-non-lues">
            Non lues
            <?php if (count($nonLues) > 0): ?>
            <span class="badge bg-danger ms-1"><?= count($nonLues) ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-lues">
            Lues <span class="badge bg-light text-muted ms-1"><?= count($lues) ?></span>
        </button>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="tab-toutes">
        <?php afficherListe($notifications); ?>
    </div>
    <div class="tab-pane fade" id="tab-non-lues">
        <?php if (empty($nonLues)): ?>
        <p class="text-center text-muted py-4 small">
            <i class="bi bi-check-circle text-success d-block fs-3 mb-2"></i>
            Aucune notification non lue.
        </p>
        <?php else: ?>
        <?php afficherListe($nonLues); ?>
        <?php endif; ?>
    </div>
    <div class="tab-pane fade" id="tab-lues">
        <?php if (empty($lues)): ?>
        <p class="text-center text-muted py-4 small">Aucune notification lue.</p>
        <?php else: ?>
        <?php afficherListe($lues); ?>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php
/**
 * Affiche une liste de notifications sous forme de cartes.
 */
function afficherListe(array $liste): void
{
    $icones = [
        'success' => 'check-circle-fill text-success',
        'warning' => 'exclamation-triangle-fill text-warning',
        'danger'  => 'x-circle-fill text-danger',
        'info'    => 'info-circle-fill text-primary',
    ];

    echo '<div class="d-flex flex-column gap-2">';
    foreach ($liste as $n):
        $icon   = $icones['info'];
        $nonLue = !$n['lue'];
        $border = $nonLue ? 'border-start border-primary border-3' : '';
?>
    <div class="card border-0 shadow-sm <?= $border ?>">
        <div class="card-body py-3 px-4 d-flex align-items-start gap-3">
            <i class="bi bi-<?= $icon ?> mt-1 flex-shrink-0 fs-5"></i>
            <div class="flex-grow-1">
                <p class="mb-1 small <?= $nonLue ? 'fw-semibold' : 'text-muted' ?>">
                    <?= nettoyer($n['message']) ?>
                </p>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted" style="font-size:.75rem">
                        <i class="bi bi-clock me-1"></i>
                        <?= date('d/m/Y à H:i', strtotime($n['date_notif'])) ?>
                    </span>
                    <?php if ($nonLue): ?>
                    <span class="badge bg-primary rounded-pill" style="font-size:.65rem">Nouveau</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex gap-1 flex-shrink-0">
                <?php if ($nonLue): ?>
                <a href="index.php?page=notifications&action=lire&id=<?= $n['id'] ?>"
                   class="btn btn-sm btn-light" title="Marquer comme lue">
                    <i class="bi bi-check text-success"></i>
                </a>
                <?php endif; ?>
                <a href="index.php?page=notifications&action=supprimer&id=<?= $n['id'] ?>"
                   class="btn btn-sm btn-light" title="Supprimer"
                   onclick="return confirm('Supprimer cette notification ?')">
                    <i class="bi bi-trash text-danger"></i>
                </a>
            </div>
        </div>
    </div>
<?php endforeach;
    echo '</div>';
}
?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
