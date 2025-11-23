<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
?>

<div class="page-header">
    <div>
        <h1>Modifier mon profil</h1>
        <p class="page-subtitle">Mettez à jour vos informations</p>
    </div>
    <a href="<?= $base ?>/?controller=auth&action=show" class="btn-secondary">
        <i class="fa-solid fa-arrow-left"></i> Retour
    </a>
</div>

<div class="profile-card">
    <form method="post" action="<?= $base ?>/?controller=auth&action=update" class="profile-fields">
        <div class="form-group">
            <label for="nom_complet">Nom complet *</label>
            <input type="text" 
                   id="nom_complet" 
                   name="nom_complet" 
                   value="<?= htmlspecialchars($user['nom_complet'] ?? '') ?>" 
                   required
                   class="form-control">
        </div>
        
        <div class="form-group">
            <label for="bio">Bio</label>
            <textarea id="bio" 
                      name="bio" 
                      rows="4" 
                      class="form-control"
                      placeholder="Parlez-nous de vous..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-save"></i> Enregistrer les modifications
            </button>
            <a href="<?= $base ?>/?controller=auth&action=show" class="btn-secondary">
                Annuler
            </a>
        </div>
    </form>
</div>
