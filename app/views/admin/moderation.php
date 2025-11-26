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
