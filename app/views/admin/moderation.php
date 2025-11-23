<?php
// Ensure variables exist to avoid notices
$pendingPosts    = $pendingPosts ?? [];
$pendingComments = $pendingComments ?? [];
$approvedPosts   = $approvedPosts ?? [];
$rejectedPosts   = $rejectedPosts ?? [];
?>

<section class="page-header">
    <div>
        <h1>Modération des contenus</h1>
        <p class="page-subtitle">Gérez les publications et commentaires de la communauté.</p>
    </div>
</section>

<?php include __DIR__ . '/../includes/flash_messages.php'; ?>

<div class="table-card">
    <h3>Publications en attente</h3>
    <?php if (empty($pendingPosts)): ?>
        <p style="padding: 16px;">Aucune publication en attente de modération.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingPosts as $post): ?>
                    <tr>
                        <td>
                            <a href="?controller=post&action=show&id=<?= $post['id_post'] ?>">
                                <?= htmlspecialchars($post['title'] ?? 'Sans titre') ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($post['nom_complet'] ?? 'Utilisateur inconnu') ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></td>
                        <td style="display:flex; gap:8px; flex-wrap:wrap;">
                            <form method="post" action="?controller=admin&action=approvePost" class="d-inline">
                                <input type="hidden" name="post_id" value="<?= $post['id_post'] ?>">
                                <input type="text" name="moderation_notes" class="form-control form-control-sm" placeholder="Notes (optionnel)">
                                <button type="submit" class="btn btn-success btn-sm">Approuver</button>
                            </form>

                            <form method="post" action="?controller=admin&action=rejectPost" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir rejeter cette publication ?');">
                                <input type="hidden" name="post_id" value="<?= $post['id_post'] ?>">
                                <input type="text" name="rejection_reason" class="form-control form-control-sm" placeholder="Raison du rejet" required>
                                <button type="submit" class="btn btn-danger btn-sm">Rejeter</button>
                            </form>

                            <form method="post" action="?controller=admin&action=deletePost" class="d-inline" onsubmit="return confirm('Supprimer définitivement cette publication ?');">
                                <input type="hidden" name="post_id" value="<?= $post['id_post'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<!-- Comment moderation removed: comments are published immediately without review. -->

<div class="table-card">
    <h3>Publications approuvées</h3>
    <?php if (empty($approvedPosts)): ?>
        <p style="padding: 16px;">Aucune publication approuvée pour le moment.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($approvedPosts as $post): ?>
                    <tr>
                        <td><?= htmlspecialchars($post['title'] ?? 'Sans titre') ?></td>
                        <td><?= htmlspecialchars($post['nom_complet'] ?? $post['user_id']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<div class="table-card">
    <h3>Publications rejetées</h3>
    <?php if (empty($rejectedPosts)): ?>
        <p style="padding: 16px;">Aucune publication rejetée.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Auteur</th>
                    <th>Date</th>
                    <th>Raison</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rejectedPosts as $post): ?>
                    <tr>
                        <td><?= htmlspecialchars($post['title'] ?? 'Sans titre') ?></td>
                        <td><?= htmlspecialchars($post['nom_complet'] ?? $post['user_id']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($post['created_at'])) ?></td>
                        <td><?= htmlspecialchars($post['moderation_notes'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
