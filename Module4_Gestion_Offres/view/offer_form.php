<?php include 'templates/header.php'; ?>
<div class="page-header">
    <h1><?= isset($offer) ? 'Modifier l\'offre' : 'Créer une nouvelle offre' ?></h1>
</div>
<div class="profile-card">
    <form id="offer-form" action="index.php?action=<?= isset($offer) ? 'update&id='.$offer['id'] : 'store' ?>" method="POST" novalidate>
        <div class="profile-fields">
            <div class="form-group">
                <label for="title">Titre de la mission</label>
                <input type="text" id="title" name="title" value="<?= isset($offer) ? htmlspecialchars($offer['title']) : '' ?>">
                <div class="error-message"></div>
            </div>
            <div class="form-group">
                <label for="description">Description complète du besoin</label>
                <textarea id="description" name="description" rows="8"><?= isset($offer) ? htmlspecialchars($offer['description']) : '' ?></textarea>
                <div class="error-message"></div>
            </div>
            <div class="form-actions">
                <a href="index.php?role=admin" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </div>
        </div>
    </form>
</div>
<?php include 'templates/footer.php'; ?>