<?php
// frontend/pages/utilisateur/dashboard.php
require_once __DIR__ . '/../partials/header.php';
$labels   = array_column($evolution, 'mois');
$depData  = array_map('floatval', array_column($evolution, 'depenses'));
$revData  = array_map('floatval', array_column($evolution, 'revenus'));
$catLabels = array_column($repartition, 'nom');
$catData   = array_map('floatval', array_column($repartition, 'total'));
$catColors = array_column($repartition, 'couleur');
?>

<div class="row g-3 mb-4">
    <!-- Carte bienvenue -->
    <div class="col-12">
        <div class="card border-0 shadow-sm p-4" style="background:linear-gradient(135deg,#1e3a5f,#2563eb);color:#fff;border-radius:16px">
            <h4 class="fw-bold mb-1">Bonjour, <?=nettoyer($user['prenom'])?> 👋</h4>
            <p class="mb-0" style="opacity:.8;font-size:.9rem;">
                Aujourd'hui est un bon jour pour prendre le contrôle de votre budget !
            </p>
        </div>
    </div>

    <!-- KPIs -->
    <?php
    $kpis = [
        ['label'=>'Revenus (mois)', 'value'=>number_format($stats['revenus'],2).' TND', 'icon'=>'bi-arrow-up-circle', 'color'=>'#22c55e'],
        ['label'=>'Dépenses (mois)', 'value'=>number_format($stats['depenses'],2).' TND', 'icon'=>'bi-arrow-down-circle', 'color'=>'#ef4444'],
        ['label'=>'Solde', 'value'=>number_format($stats['solde'],2).' TND', 'icon'=>'bi-wallet2', 'color'=>$stats['solde']>=0?'#2563eb':'#f59e0b'],
        ['label'=>'Budgets actifs', 'value'=>count($budgets), 'icon'=>'bi-pie-chart', 'color'=>'#8b5cf6'],
    ];
    foreach($kpis as $k): ?>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm p-3 text-center h-100">
            <i class="<?=$k['icon']?> fs-3 mb-2" style="color:<?=$k['color']?>"></i>
            <div class="fw-bold fs-5"><?=$k['value']?></div>
            <div class="small text-muted"><?=$k['label']?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="row g-3 mb-4">
    <!-- Graphique évolution -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-3">
            <h6 class="fw-semibold mb-3"><i class="bi bi-graph-up me-2 text-primary"></i>Évolution sur 6 mois</h6>
            <canvas id="chartEvolution" height="100"></canvas>
        </div>
    </div>
    <!-- Camembert catégories -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-3">
            <h6 class="fw-semibold mb-3"><i class="bi bi-pie-chart me-2 text-primary"></i>Répartition dépenses</h6>
            <?php if(empty($repartition)): ?>
            <p class="text-muted small text-center mt-4">Aucune dépense ce mois-ci</p>
            <?php else: ?>
            <canvas id="chartCategories" height="180"></canvas>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Dernières transactions -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-arrow-left-right me-2 text-primary"></i>Dernières transactions</h6>
                    <a href="index.php?page=transactions" class="btn btn-sm btn-outline-primary">Tout voir</a>
                </div>
                <?php if(empty($dernieresTx)): ?>
                <p class="text-muted small text-center py-3">Aucune transaction pour l'instant</p>
                <?php else: ?>
                <?php foreach($dernieresTx as $tx): ?>
                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                         style="width:38px;height:38px;background:<?=nettoyer($tx['couleur']??'#6366f1')?>22">
                        <i class="<?=nettoyer($tx['icone']??'bi-tag')?>" style="color:<?=nettoyer($tx['couleur']??'#6366f1')?>"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="small fw-medium"><?=nettoyer($tx['description']?:'Sans description')?></div>
                        <div class="text-muted" style="font-size:.75rem"><?=nettoyer($tx['categorie_nom']??'Sans catégorie')?> · <?=date('d/m/Y',strtotime($tx['date_transaction']))?></div>
                    </div>
                    <div class="fw-semibold <?=$tx['type']==='revenu'?'text-success':'text-danger'?>">
                        <?=$tx['type']==='revenu'?'+':'-'?><?=number_format($tx['montant'],2)?> TND
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- Budgets actifs -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-semibold mb-0"><i class="bi bi-pie-chart me-2 text-primary"></i>Budgets actifs</h6>
                    <a href="index.php?page=budgets&action=nouveau" class="btn btn-sm btn-outline-success">+ Nouveau</a>
                </div>
                <?php if(empty($budgets)): ?>
                <p class="text-muted small text-center py-3">Aucun budget actif</p>
                <?php else: ?>
                <?php foreach($budgets as $b): ?>
                <?php
              /*  $dep = (new \TransactionModel ?? null); */
                // Simplifié : on affiche juste le nom et les dates
                ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <a href="index.php?page=budgets&action=detail&id=<?=$b['id']?>" class="fw-medium text-decoration-none">
                            <?=nettoyer($b['nom'])?>
                        </a>
                        <span class="text-muted"><?=date('d/m',strtotime($b['date_fin']))?></span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
// Graphique évolution
const ctx1 = document.getElementById('chartEvolution')?.getContext('2d');
if(ctx1) {
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?=json_encode($labels)?>,
            datasets: [
                {label:'Dépenses',data:<?=json_encode($depData)?>,borderColor:'#ef4444',backgroundColor:'rgba(239,68,68,.1)',fill:true,tension:.4},
                {label:'Revenus', data:<?=json_encode($revData)?>, borderColor:'#22c55e',backgroundColor:'rgba(34,197,94,.1)',fill:true,tension:.4}
            ]
        },
        options: { responsive:true, plugins:{legend:{position:'bottom'}}, scales:{y:{beginAtZero:true}} }
    });
}
// Camembert
const ctx2 = document.getElementById('chartCategories')?.getContext('2d');
if(ctx2 && <?=json_encode(!empty($repartition))?>) {
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: <?=json_encode($catLabels)?>,
            datasets: [{data:<?=json_encode($catData)?>,backgroundColor:<?=json_encode($catColors)?>}]
        },
        options: { responsive:true, plugins:{legend:{position:'bottom',labels:{font:{size:11}}}} }
    });
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>