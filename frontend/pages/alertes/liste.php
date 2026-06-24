<?php
// frontend/pages/alertes/liste.php
require_once __DIR__ . '/../partials/header.php';
$nonLues = array_filter($alertes, fn($a) => !$a['lue']);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h5 class="fw-bold mb-1">Alertes</h5>
        <?php if(count($nonLues) > 0): ?>
        <span class="badge bg-danger"><?= count($nonLues) ?> non lue(s)</span>
        <?php endif; ?>
    </div>
    <?php if(!empty($alertes)): ?>
    <a href="index.php?page=alertes&action=tout_lire" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-check2-all me-1"></i>Tout marquer comme lu
    </a>
    <?php endif; ?>
</div>

<?php if(empty($alertes)): ?>
<div class="text-center py-5 text-muted">
    <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
    Aucune alerte pour l'instant. Vos budgets sont sous contrôle !
</div>
<?php else: ?>
<div class="d-flex flex-column gap-3">
<?php foreach($alertes as $a): ?>
<?php $isDepassement = $a['type'] === 'depassement'; ?>
<div class="card border-0 shadow-sm <?= !$a['lue']?'border-start border-4 border-'.($isDepassement?'danger':'warning'):'' ?>">
    <div class="card-body p-3">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                 style="width:44px;height:44px;background:<?= $isDepassement?'#ef444422':'#f59e0b22' ?>">
                <i class="bi bi-<?= $isDepassement?'exclamation-triangle':'exclamation-circle' ?> fs-5"
                   style="color:<?= $isDepassement?'#ef4444':'#f59e0b' ?>"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="fw-semibold small"><?= nettoyer($a['message']) ?></span>
                        <div class="text-muted" style="font-size:.78rem">
                            <i class="bi bi-pie-chart me-1"></i><?= nettoyer($a['budget_nom']) ?>
                            · <?= date('d/m/Y H:i', strtotime($a['date_alerte'])) ?>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-3">
                        <span class="badge bg-<?= $isDepassement?'danger':'warning text-dark' ?>">
                            <?= $isDepassement ? 'Dépassement' : 'Seuil '.$a['seuil_pourcentage'].'%' ?>
                        </span>
                        <?php if(!$a['lue']): ?>
                        <a href="index.php?page=alertes&action=lire&id=<?= $a['id'] ?>"
                           class="btn btn-sm btn-outline-secondary py-0 px-2" title="Marquer comme lue">
                            <i class="bi bi-check2" style="font-size:.8rem"></i>
                        </a>
                        <?php else: ?>
                        <span class="text-muted small"><i class="bi bi-check2-all"></i></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
