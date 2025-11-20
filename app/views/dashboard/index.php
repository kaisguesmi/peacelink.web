<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<div class="page-header">
    <div>
        <h1>Dashboard</h1>
        <p class="page-subtitle">Bienvenue, <?= htmlspecialchars($user['nom_complet'] ?? $user['email']) ?>!</p>
    </div>
</div>

<?php if ($flash): ?>
    <div class="flash-message" style="background-color: var(--vert-doux); color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <?= htmlspecialchars($flash) ?>
    </div>
<?php endif; ?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="fa-solid fa-book-open"></i></div>
        <div>
            <p class="stat-label">Total Posts</p>
            <h2 class="stat-value"><?= count($posts) ?></h2>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="fa-solid fa-hand-holding-heart"></i></div>
        <div>
            <p class="stat-label">Active Initiatives</p>
            <h2 class="stat-value">-</h2>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="fa-solid fa-users"></i></div>
        <div>
            <p class="stat-label">Community</p>
            <h2 class="stat-value">-</h2>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="fa-solid fa-flag"></i></div>
        <div>
            <p class="stat-label">Reports</p>
            <h2 class="stat-value">-</h2>
        </div>
    </div>
</div>

<!-- Recent Posts Preview -->
<div class="table-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Posts récents</h3>
        <a href="<?= $base ?>/?controller=histoire&action=index" class="btn-primary">
            <i class="fa-solid fa-arrow-right"></i> Voir tous les posts
        </a>
    </div>
    
    <?php if (empty($posts)): ?>
        <div class="empty-state" style="text-align: center; padding: 40px;">
            <i class="fa-solid fa-inbox" style="font-size: 48px; color: var(--gris-moyen); margin-bottom: 15px;"></i>
            <p style="color: #888;">Aucun post pour le moment.</p>
            <a href="<?= $base ?>/?controller=histoire&action=index" class="btn-primary" style="margin-top: 15px;">
                <i class="fa-solid fa-plus"></i> Créer un post
            </a>
        </div>
    <?php else: ?>
        <div class="posts-list" style="max-height: 600px; overflow-y: auto;">
            <?php foreach (array_slice($posts, 0, 5) as $post): ?>
                <?php include __DIR__ . '/../partials/post-card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
