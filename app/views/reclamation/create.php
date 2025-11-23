<?php $config = require __DIR__ . '/../../../config/config.php'; $base = rtrim($config['app']['base_url'], '/'); ?>
<section class="mission-section">
    <div class="mission-container">
        <h2>Signaler un contenu</h2>
        <form method="post" action="<?= $base ?>/?controller=reclamation&action=store" class="form-card">
            <input type="hidden" name="id_histoire_cible" value="<?= htmlspecialchars($target['histoire'] ?? '') ?>">
            <input type="hidden" name="id_commentaire_cible" value="<?= htmlspecialchars($target['commentaire'] ?? '') ?>">
            <label>Causes
                <select name="causes[]" multiple>
                    <?php foreach ($causes as $cause): ?>
                        <option value="<?= $cause['id_cause'] ?>"><?= htmlspecialchars($cause['libelle']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Description
                <textarea name="description_personnalisee" rows="5" required></textarea>
            </label>
            <button class="btn-hero-primary">Envoyer</button>
        </form>
    </div>
</section>

