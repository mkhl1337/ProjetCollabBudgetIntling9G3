<?php
// views/admin/dashboard.php
require_once __DIR__ . '/../partials/header.php';
$labels    = array_column($evolution, 'mois');
$depData   = array_map('floatval', array_column($evolution, 'depenses'));
$revData   = array_map('floatval', array_column($evolution, 'revenus'));
$catLabels = array_column($repartition, 'nom');
$catData   = array_map('floatval', array_column($repartition, 'total'));
$catColors = array_column($repartition, 'couleur');
?>

<!-- Bienvenue -->
<div class="card border-0 mb-4 p-4"
     style="background:linear-gradient(135deg,#0f172a,#2563eb);border-radius:18px;color:#fff;">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold mb-1">Bonjour, <?= htmlspecialchars($user['prenom'], ENT_QUOTES) ?> 👋</h4>
            <p class="mb-0" style="opacity:.8;font-size:.9rem;">
                Aujourd'hui est un bon jour pour prendre le contrôle de votre budget !
            </p>
        </div>
        <div class="text-end">
            <div style="font-size:.78rem;opacity:.7;"><?= date('l d F Y') ?></div>
            <div class="badge" style="background:rgba(255,255,255,.15);color:#fff;font-size:.8rem;margin-top:4px;">
                <i class="bi bi-shield-check me-1"></i>Administrateur
            </div>
        </div>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-4">
    <?php
    $kpis = [
        ['label'=>'Utilisateurs total',    'value'=>$nbUsers,    'icon'=>'bi-people',          'color'=>'#2563eb', 'bg'=>'#eff6ff', 'link'=>'admin_utilisateurs'],
        ['label'=>'Comptes en attente',    'value'=>$nbAttente,  'icon'=>'bi-hourglass-split',  'color'=>'#f59e0b', 'bg'=>'#fffbeb', 'link'=>'admin_validation'],
        ['label'=>'Comptes actifs',        'value'=>$nbActifs,   'icon'=>'bi-person-check',     'color'=>'#10b981', 'bg'=>'#ecfdf5', 'link'=>'admin_utilisateurs'],
        ['label'=>'Demandes suppression',  'value'=>$nbDemandes, 'icon'=>'bi-trash2',           'color'=>'#ef4444', 'bg'=>'#fef2f2', 'link'=>'admin_demandes'],
    ];
    foreach($kpis as $k): ?>
    <div class="col-md-3 col-6">
        <a href="index.php?page=<?= $k['link'] ?>" class="text-decoration-none">
            <div class="card border-0 shadow-sm h-100" style="border-radius:14px;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div style="width:48px;height:48px;background:<?= $k['bg'] ?>;border-radius:12px;
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="<?= $k['icon'] ?>" style="font-size:1.3rem;color:<?= $k['color'] ?>"></i>
                    </div>
                    <div>
                        <div class="fw-bold fs-4" style="color:#1e293b;line-height:1;"><?= $k['value'] ?></div>
                        <div class="small text-muted"><?= $k['label'] ?></div>
                    </div>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<!-- Stats financières + graphiques -->
<div class="row g-3 mb-4">
    <!-- Solde total -->
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center p-3" style="border-radius:14px;">
            <div class="text-success fw-bold fs-4"><?= number_format($statsGlobales['revenus'],2) ?> TND</div>
            <div class="small text-muted">Total revenus</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card border-0 shadow-sm text-center p-3" style="border-radius:14px;">
            <div class="text-danger fw-bold fs-4"><?= number_format($statsGlobales['depenses'],2) ?> TND</div>
            <div class="small text-muted">Total dépenses</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <?php $soldeGlobal = $statsGlobales['revenus'] - $statsGlobales['depenses']; ?>
        <div class="card border-0 shadow-sm text-center p-3" style="border-radius:14px;">
            <div class="fw-bold fs-4" style="color:<?= $soldeGlobal >= 0 ? '#2563eb' : '#ef4444' ?>">
                <?= number_format($soldeGlobal,2) ?> TND
            </div>
            <div class="small text-muted">Solde global</div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <?php $tauxGlobal = $statsGlobales['revenus'] > 0 ? round(($statsGlobales['depenses']/$statsGlobales['revenus'])*100,1) : 0; ?>
        <div class="card border-0 shadow-sm text-center p-3" style="border-radius:14px;">
            <div class="fw-bold fs-4 text-warning"><?= $tauxGlobal ?>%</div>
            <div class="small text-muted">Taux de dépenses</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Évolution -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4" style="border-radius:16px;">
            <h6 class="fw-semibold mb-3">
                <i class="bi bi-graph-up me-2 text-primary"></i>Revenus/Dépenses
            </h6>
            <canvas id="chartEvolution" height="90"></canvas>
        </div>
    </div>
    <!-- Répartition -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4" style="border-radius:16px;">
            <h6 class="fw-semibold mb-3">
                <i class="bi bi-pie-chart me-2 text-primary"></i>Dépenses par catégorie
            </h6>
            <?php if(empty($repartition)): ?>
            <div class="text-center text-muted py-4" style="font-size:.85rem;">
                <i class="bi bi-pie-chart d-block mb-2 fs-3"></i>Aucune dépense ce mois
            </div>
            <?php else: ?>
            <canvas id="chartCategories" height="180"></canvas>
            <div class="mt-3">
                <?php foreach($repartition as $r): ?>
                <div class="d-flex justify-content-between align-items-center small mb-1">
                    <span><i class="bi bi-circle-fill me-1" style="color:<?= htmlspecialchars($r['couleur'],ENT_QUOTES) ?>;font-size:.5rem;"></i>
                        <?= htmlspecialchars($r['nom'], ENT_QUOTES) ?></span>
                    <span class="fw-medium"><?= number_format($r['total'],2) ?> TND</span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Accès rapides -->
<div class="card border-0 shadow-sm p-4" style="border-radius:16px;">
    <h6 class="fw-semibold mb-3"><i class="bi bi-lightning me-2 text-warning"></i>Actions rapides</h6>
    <div class="row g-2">
        <div class="col-auto">
            <a href="index.php?page=admin_validation" class="btn btn-outline-warning btn-sm">
                <i class="bi bi-patch-check me-1"></i>Valider des comptes
            </a>
        </div>
        <div class="col-auto">
            <a href="index.php?page=admin_utilisateurs" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-people me-1"></i>Gérer les utilisateurs
            </a>
        </div>
        <div class="col-auto">
            <a href="index.php?page=admin_demandes" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash2 me-1"></i>Demandes de suppression
            </a>
        </div>
    </div>
</div>

<script>
const ctx1 = document.getElementById('chartEvolution')?.getContext('2d');
if(ctx1) {
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [
                {label:'Revenus', data:<?= json_encode($revData) ?>, borderColor:'#22c55e',
                 backgroundColor:'rgba(34,197,94,.08)', fill:true, tension:.4, borderWidth:2, pointRadius:4},
                {label:'Dépenses', data:<?= json_encode($depData) ?>, borderColor:'#ef4444',
                 backgroundColor:'rgba(239,68,68,.08)', fill:true, tension:.4, borderWidth:2, pointRadius:4}
            ]
        },
        options:{
            responsive:true,
            plugins:{legend:{position:'bottom',labels:{font:{size:12}}}},
            scales:{y:{beginAtZero:true,ticks:{callback:v=>v+' TND'}}}
        }
    });
}
const ctx2 = document.getElementById('chartCategories')?.getContext('2d');
if(ctx2) {
    new Chart(ctx2, {
        type:'doughnut',
        data:{
            labels:<?= json_encode($catLabels) ?>,
            datasets:[{data:<?= json_encode($catData) ?>, backgroundColor:<?= json_encode($catColors) ?>,
                       borderWidth:2, borderColor:'#fff'}]
        },
        options:{
            responsive:true, cutout:'65%',
            plugins:{legend:{display:false}}
        }
    });
}
</script>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>