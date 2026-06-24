<?php
// frontend/pages/utilisateur/invitations/index.php
require_once __DIR__ . '/../partials/header.php';
$uid = utilisateurConnecte()['id'];

$nbRecues = count(array_filter($recues, fn($i) => $i['statut'] === 'en_attente'));
?>

<!-- ── Formulaire d'invitation ──────────────────────────────────── -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 pt-4 pb-2 px-4">
        <h6 class="fw-bold mb-0">
            <i class="bi bi-envelope-plus me-2 text-primary"></i>Envoyer une invitation
        </h6>
        <p class="text-muted small mt-1 mb-0">
            Invitez une personne à rejoindre l'un de vos budgets partagés.
        </p>
    </div>
    <div class="card-body px-4 pb-4">
        <?php if (empty($budgets)): ?>
        <div class="alert alert-info d-flex gap-2 mb-0">
            <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
            <div class="small">
                Vous n'êtes propriétaire d'aucun budget pour l'instant.
                <a href="index.php?page=budgets&action=nouveau">Créez un budget</a> d'abord, puis revenez inviter des membres.
            </div>
        </div>
        <?php else: ?>
        <form method="POST" action="index.php?page=invitations&action=inviter">
            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-medium small">Budget</label>
                    <select name="budget_id" class="form-select" required>
                        <?php foreach ($budgets as $b): ?>
                        <option value="<?= $b['id'] ?>">
                            <?= htmlspecialchars($b['nom'], ENT_QUOTES) ?>
                            (<?= $b['type'] === 'partage' ? 'Partagé' : 'Individuel' ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-medium small">Email de la personne à inviter</label>
                    <input type="email" name="email_invite" class="form-control"
                           placeholder="exemple@email.com" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-send me-1"></i>Envoyer
                    </button>
                </div>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<!-- ── Onglets ───────────────────────────────────────────────────── -->
<ul class="nav nav-tabs mb-3" id="invTabs" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-recues">
            <i class="bi bi-inbox me-1"></i>Reçues
            <?php if ($nbRecues > 0): ?>
            <span class="badge bg-danger ms-1"><?= $nbRecues ?></span>
            <?php endif; ?>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-envoyees">
            <i class="bi bi-send me-1"></i>Envoyées
            <span class="badge bg-secondary ms-1"><?= count($envoyees) ?></span>
        </button>
    </li>
</ul>

<div class="tab-content">

    <!-- ── Invitations reçues ──────────────────────────────────── -->
    <div class="tab-pane fade show active" id="tab-recues">
        <?php if (empty($recues)): ?>
        <div class="card border-0 shadow-sm text-center p-5">
            <i class="bi bi-envelope-open fs-2 text-muted mb-3"></i>
            <p class="text-muted mb-0">Aucune invitation reçue pour l'instant.</p>
        </div>
        <?php else: ?>
        <div class="row g-3">
        <?php foreach ($recues as $inv):
            $statusBadge = match($inv['statut']) {
                'accepte' => ['Acceptée', 'success'],
                'refuse'  => ['Refusée',  'secondary'],
                default   => ['En attente', 'warning'],
            };
        ?>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 <?= $inv['statut'] === 'en_attente' ? 'border-start border-warning border-3' : '' ?>">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="fw-bold mb-1">
                                <?= htmlspecialchars($inv['budget_nom'], ENT_QUOTES) ?>
                            </h6>
                            <div class="small text-muted">
                                <i class="bi bi-person me-1"></i>
                                Invité par
                                <strong><?= htmlspecialchars($inv['envoyeur_prenom'] . ' ' . $inv['envoyeur_nom'], ENT_QUOTES) ?></strong>
                            </div>
                        </div>
                        <span class="badge text-bg-<?= $statusBadge[1] ?>"><?= $statusBadge[0] ?></span>
                    </div>

                    <div class="text-muted small mb-3">
                        <i class="bi bi-clock me-1"></i>
                        <?= date('d/m/Y à H:i', strtotime($inv['date_invitation'])) ?>
                    </div>

                    <?php if ($inv['statut'] === 'en_attente'): ?>
                    <div class="d-flex gap-2">
                        <a href="index.php?page=invitations&action=accepter&id=<?= $inv['id'] ?>"
                           class="btn btn-sm btn-success flex-grow-1">
                            <i class="bi bi-check-lg me-1"></i>Accepter
                        </a>
                        <a href="index.php?page=invitations&action=refuser&id=<?= $inv['id'] ?>"
                           class="btn btn-sm btn-outline-secondary flex-grow-1"
                           onclick="return confirm('Refuser cette invitation ?')">
                            <i class="bi bi-x-lg me-1"></i>Refuser
                        </a>
                    </div>
                    <?php elseif ($inv['statut'] === 'accepte'): ?>
                    <a href="index.php?page=budgets&action=detail&id=<?= $inv['budget_id'] ?>"
                       class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-eye me-1"></i>Voir le budget
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ── Invitations envoyées ────────────────────────────────── -->
    <div class="tab-pane fade" id="tab-envoyees">
        <?php if (empty($envoyees)): ?>
        <div class="card border-0 shadow-sm text-center p-5">
            <i class="bi bi-send fs-2 text-muted mb-3"></i>
            <p class="text-muted mb-0">Vous n'avez envoyé aucune invitation.</p>
        </div>
        <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Budget</th>
                            <th>Email invité</th>
                            <th>Date</th>
                            <th>Statut</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($envoyees as $inv):
                        $statusBadge = match($inv['statut']) {
                            'accepte' => ['Acceptée', 'success'],
                            'refuse'  => ['Refusée',  'secondary'],
                            default   => ['En attente', 'warning'],
                        };
                    ?>
                    <tr>
                        <td class="fw-medium small">
                            <?= htmlspecialchars($inv['budget_nom'], ENT_QUOTES) ?>
                        </td>
                        <td class="small text-muted">
                            <?= htmlspecialchars($inv['email_invite'], ENT_QUOTES) ?>
                        </td>
                        <td class="small text-muted">
                            <?= date('d/m/Y H:i', strtotime($inv['date_invitation'])) ?>
                        </td>
                        <td>
                            <span class="badge text-bg-<?= $statusBadge[1] ?>"><?= $statusBadge[0] ?></span>
                        </td>
                        <td class="text-end">
                            <?php if ($inv['statut'] === 'en_attente'): ?>
                            <a href="index.php?page=invitations&action=annuler&id=<?= $inv['id'] ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Annuler cette invitation ?')">
                                <i class="bi bi-x-lg"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>
