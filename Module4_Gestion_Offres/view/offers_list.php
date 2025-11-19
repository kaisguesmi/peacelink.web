<?php include 'templates/header.php'; ?>
<div class="page-header">
    <div><h1>Offres de Mission Disponibles</h1><p class="page-subtitle">Consultez les dernières opportunités.</p></div>
    <?php if ($user_role === 'admin'): ?>
        <a href="index.php?action=create&role=admin" class="btn btn-primary"><i class="fas fa-plus"></i> Publier une offre</a>
    <?php endif; ?>
</div>
<div class="stories-grid">
    <?php if (empty($offers)): ?>
        <p>Aucune offre disponible pour le moment.</p>
    <?php else: ?>
        <?php foreach ($offers as $offer): ?>
            <div class="story-card">
                <div class="story-header"><span><i class="fas fa-calendar-alt"></i> Publiée le <?= date('d/m/Y', strtotime($offer['created_at'])) ?></span><span class="status-badge active"><?= htmlspecialchars($offer['status']) ?></span></div>
                <div class="story-content"><h3><?= htmlspecialchars($offer['title']) ?></h3><p><?= nl2br(htmlspecialchars($offer['description'])) ?></p></div>
                <div class="story-actions">
                    <?php if ($user_role === 'admin'): ?>
                        <a href="index.php?action=edit&id=<?= $offer['id'] ?>&role=admin" class="btn btn-secondary" title="Modifier"><i class="fas fa-edit"></i></a>
                        <a href="index.php?action=delete&id=<?= $offer['id'] ?>&role=admin" class="btn btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr ?');"><i class="fas fa-trash"></i></a>
                    <?php else: ?>
                        <a href="index.php?action=apply&id=<?= $offer['id'] ?>" class="btn btn-success"><i class="fas fa-paper-plane"></i> Postuler</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php include 'templates/footer.php'; ?>