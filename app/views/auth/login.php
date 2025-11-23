<?php $config = require __DIR__ . '/../../../config/config.php'; $base = rtrim($config['app']['base_url'], '/'); ?>
<section class="mission-section">
    <div class="mission-container">
        <h2>Connexion</h2>
        <?php if (!empty($_SESSION['flash'])): ?>
            <p class="alert-error"><?= $_SESSION['flash']; unset($_SESSION['flash']); ?></p>
        <?php endif; ?>
        <form method="post" action="<?= $base ?>/?controller=auth&action=authenticate" class="form-card">
            <label>Email
                <input type="email" name="email" required>
            </label>
            <label>Mot de passe
                <input type="password" name="password" required>
            </label>
            <button class="btn-hero-primary">Se connecter</button>
        </form>
        <p>Pas encore de compte ? <a href="<?= $base ?>/?controller=auth&action=create">Créer un compte</a></p>
    </div>
</section>

