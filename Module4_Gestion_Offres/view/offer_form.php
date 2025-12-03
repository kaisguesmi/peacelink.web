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
            </div>
            
            <!-- CHAMPS SPECIFIQUES -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label for="max_applications">Max Candidats</label>
                    <input type="number" id="max_applications" name="max_applications" min="1" value="<?= isset($offer) ? htmlspecialchars($offer['max_applications']) : '10' ?>">
                </div>
                
                <div class="form-group">
                    <label for="keywords">Mots-clés Obligatoires</label>
                    <input type="text" id="keywords" name="keywords" placeholder="Séparez par des virgules" value="<?= isset($offer) && !empty($offer['keywords']) ? htmlspecialchars($offer['keywords']) : '' ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="description">Description complète</label>
                <textarea id="description" name="description" rows="8"><?= isset($offer) ? htmlspecialchars($offer['description']) : '' ?></textarea>
            </div>
            
            <div class="form-actions">
                <a href="index.php?role=admin" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            </div>
        </div>
    </form>
</div>
<?php include 'templates/footer.php'; ?>