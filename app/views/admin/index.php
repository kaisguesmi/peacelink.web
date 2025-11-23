<section class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p class="page-subtitle">Surveillez les contributions PeaceLink.</p>
    </div>
    <a class="btn-primary" href="?controller=admin&action=create"><i class="fa-solid fa-plus"></i> Nouvelle offre</a>
</section>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-book-open"></i></div>
        <div>
            <p class="stat-label">Stories</p>
            <h2 class="stat-value"><?= count($stories) ?></h2>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-hand-holding-heart"></i></div>
        <div>
            <p class="stat-label">Initiatives</p>
            <h2 class="stat-value"><?= count($initiatives) ?></h2>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-file-signature"></i></div>
        <div>
            <p class="stat-label">Candidatures</p>
            <h2 class="stat-value"><?= count($candidatures) ?></h2>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-flag"></i></div>
        <div>
            <p class="stat-label">Réclamations</p>
            <h2 class="stat-value"><?= count($reclamations) ?></h2>
        </div>
    </div>
</div>

<div class="table-card">
    <h3>Pending Stories</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Titre</th>
                <th>Auteur</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($stories)): ?>
                <tr>
                    <td colspan="4" style="text-align:center; padding:20px;">No pending stories.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($stories as $story): ?>
                    <tr>
                        <td><?= htmlspecialchars($story['titre']) ?></td>
                        <td><?= htmlspecialchars($story['nom_complet']) ?></td>
                        <td><?= htmlspecialchars($story['status'] ?? 'pending') ?></td>
                        <td style="display:flex; gap:8px;">
                            <a href="?controller=histoire&action=show&id=<?= $story['id_histoire'] ?>" class="action-btn" title="View">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            <a href="?controller=admin&action=approveStory&id=<?= $story['id_histoire'] ?>" class="action-btn" title="Approve">
                                <i class="fa-solid fa-check"></i>
                            </a>
                            <a href="?controller=admin&action=rejectStory&id=<?= $story['id_histoire'] ?>" class="action-btn" title="Reject">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                            <a href="?controller=admin&action=deleteStory&id=<?= $story['id_histoire'] ?>" class="action-btn danger" title="Delete" onclick="return confirm('Delete this story and all its comments?');">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="table-card">
    <h3>Réclamations</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Auteur</th>
                <th>Objet</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($reclamations as $reclamation): ?>
                <tr>
                    <td><?= htmlspecialchars($reclamation['auteur_email']) ?></td>
                    <td><?= htmlspecialchars($reclamation['histoire_titre'] ?? $reclamation['commentaire_contenu'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($reclamation['statut']) ?></td>
                    <td>
                        <a class="btn-hero-secondary" href="?controller=reclamation&action=edit&id=<?= $reclamation['id_reclamation'] ?>">Traiter</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

