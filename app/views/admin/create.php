<section class="table-card">
    <h3>Nouvelle offre</h3>
    <form method="post" action="?controller=admin&action=store" class="form-card">
        <label>Titre
            <input type="text" name="titre" required>
        </label>
        <label>Description
            <textarea name="description" rows="4" required></textarea>
        </label>
        <label>Statut
            <select name="statut">
                <option value="draft">Brouillon</option>
                <option value="published">Publiée</option>
            </select>
        </label>
        <button class="btn-primary">Publier</button>
    </form>
</section>

