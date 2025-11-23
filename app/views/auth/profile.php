<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<div class="page-header">
    <div>
        <h1>Mon Profil</h1>
        <p class="page-subtitle">Gérez vos informations personnelles</p>
    </div>
    <a href="<?= $base ?>/?controller=auth&action=edit" class="btn-primary">
        <i class="fa-solid fa-pen"></i> Modifier
    </a>
</div>

<div class="profile-card">
    <div class="profile-grid">
        <div class="profile-avatar-wrapper">
            <?php
            $avatar = $user['avatar'] ?? null;
            $name = $user['nom_complet'] ?? $user['email'] ?? 'User';
            $initials = strtoupper(substr($name, 0, 2));
            ?>
            <div class="profile-avatar">
                <?php if ($avatar): ?>
                    <img src="<?= $base ?>/assets/images/<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($name) ?>">
                <?php else: ?>
                    <span><?= htmlspecialchars($initials) ?></span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="profile-fields">
            <div class="form-group">
                <label>Nom complet</label>
                <input type="text" value="<?= htmlspecialchars($user['nom_complet'] ?? '') ?>" readonly>
            </div>
            
            <div class="form-group">
                <label>Email</label>
                <input type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly>
            </div>
            
            <div class="form-group">
                <label>Bio</label>
                <textarea readonly rows="4"><?= htmlspecialchars($user['bio'] ?? 'Aucune bio') ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Date d'inscription</label>
                <input type="text" value="<?= date('d/m/Y', strtotime($user['date_inscription'] ?? 'now')) ?>" readonly>
            </div>
        </div>
    </div>
</div>
