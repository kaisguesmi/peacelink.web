<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<div class="page-header">
    <div>
        <h1>Stories</h1>
        <p class="page-subtitle">Partagez vos expériences et découvrez celles des autres</p>
    </div>
    <button class="btn-primary" id="toggle-post-form-btn" onclick="togglePostForm()">
        <i class="fa-solid fa-plus"></i> Post
    </button>
</div>

<?php if ($flash): ?>
    <div class="flash-message" style="background-color: var(--vert-doux); color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
        <?= htmlspecialchars($flash) ?>
    </div>
<?php endif; ?>

<!-- Create Post Form (Hidden by default) -->
<div class="post-create-card" id="post-create-form" style="display: none; margin-bottom: 30px;">
    <h3 style="margin-bottom: 20px; font-family: var(--font-titre); color: var(--bleu-nuit);">Créer un nouveau post</h3>
    <form action="<?= $base ?>/?controller=post&action=store" method="POST" enctype="multipart/form-data" class="post-form">
        <div class="form-group">
            <label for="title">Titre (optionnel)</label>
            <input type="text" id="title" name="title" class="form-control" placeholder="Ajoutez un titre...">
        </div>
        <div class="form-group">
            <label for="content">Contenu *</label>
            <textarea id="content" name="content" class="form-control" rows="4" placeholder="Quoi de neuf ?" required></textarea>
        </div>
        <div class="form-group">
            <label for="image">Image (optionnel)</label>
            <input type="file" id="image" name="image" accept="image/*" class="form-control">
        </div>
        <div style="display: flex; gap: 10px;">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-paper-plane"></i> Publier
            </button>
            <button type="button" class="btn-secondary" onclick="togglePostForm()">
                Annuler
            </button>
        </div>
    </form>
</div>

<!-- Posts Feed -->
<div class="posts-feed">
    <h2 style="font-family: var(--font-titre); color: var(--bleu-nuit); margin-bottom: 20px;">Fil d'actualité</h2>
    
    <?php if (empty($posts)): ?>
        <div class="empty-state" style="text-align: center; padding: 40px; background: var(--blanc-pur); border-radius: var(--border-radius); box-shadow: var(--card-shadow);">
            <i class="fa-solid fa-inbox" style="font-size: 48px; color: var(--gris-moyen); margin-bottom: 15px;"></i>
            <p style="color: #888;">Aucun post pour le moment. Cliquez sur "Post" pour créer le premier !</p>
        </div>
    <?php else: ?>
        <div class="posts-list">
            <?php foreach ($posts as $post): ?>
                <?php include __DIR__ . '/../partials/post-card.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function togglePostForm() {
    const form = document.getElementById('post-create-form');
    const btn = document.getElementById('toggle-post-form-btn');
    
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        btn.innerHTML = '<i class="fa-solid fa-times"></i> Annuler';
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-secondary');
    } else {
        form.style.display = 'none';
        btn.innerHTML = '<i class="fa-solid fa-plus"></i> Post';
        btn.classList.remove('btn-secondary');
        btn.classList.add('btn-primary');
        // Clear form
        document.querySelector('#post-create-form form').reset();
    }
}
</script>
