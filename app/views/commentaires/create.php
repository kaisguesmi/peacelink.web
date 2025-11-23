<?php $config = require __DIR__ . '/../../../config/config.php'; $base = rtrim($config['app']['base_url'], '/'); ?>
<section class="mission-section">
    <div class="mission-container">
        <h2>Ajouter un commentaire</h2>
        <form method="post" action="<?= $base ?>/?controller=commentaires&action=store" class="form-card">
            <input type="hidden" name="id_histoire" value="<?= $storyId ?? '' ?>">
            <textarea name="contenu" rows="4" placeholder="Votre message" required></textarea>
            <button class="btn-hero-primary">Envoyer</button>
        </form>
    </div>
</section>

