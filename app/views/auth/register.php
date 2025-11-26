<?php $config = require __DIR__ . '/../../../config/config.php'; $base = rtrim($config['app']['base_url'], '/'); ?>
<section class="mission-section">
    <div class="mission-container">
        <h2>Créer un compte</h2>
        <?php if (!empty($_SESSION['flash'])): ?>
            <p class="alert-error"><?= $_SESSION['flash']; unset($_SESSION['flash']); ?></p>
        <?php endif; ?>
        <form method="post" action="<?= $base ?>/?controller=auth&action=store" class="form-card" id="register-form">
            <label>Nom complet
                <input type="text" name="nom_complet">
            </label>
            <label>Email
                <input type="text" name="email">
            </label>
            <label>Mot de passe
                <input type="password" name="password">
            </label>
            <label>Confirmer le mot de passe
                <input type="password" name="password_confirm">
            </label>
            <label>Bio
                <textarea name="bio" rows="3"></textarea>
            </label>
            <button class="btn-hero-primary">S'inscrire</button>
        </form>
    </div>
</section>

