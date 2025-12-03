<?php include 'templates/header.php'; ?>

<!-- En-tête de page -->
<div class="page-header">
    <div>
        <h1>Candidatures Reçues</h1>
        <p class="page-subtitle">Gérez et filtrez les candidatures par offre.</p>
    </div>
</div>

<!-- BARRE DE FILTRE -->
<div style="background: white; padding: 20px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px;">
    <i class="fas fa-filter" style="color: var(--bleu-pastel); font-size: 20px;"></i>
    <span style="font-weight: 600; color: #555;">Filtrer par offre :</span>
    
    <!-- Formulaire qui s'envoie automatiquement quand on change l'option -->
    <form action="index.php" method="GET" style="flex-grow: 1;">
        <input type="hidden" name="action" value="list_applications">
        <input type="hidden" name="role" value="admin">
        
        <select name="offer_id" onchange="this.form.submit()" style="width: 100%; max-width: 400px; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; cursor: pointer;">
            <option value="">-- Voir toutes les candidatures --</option>
            
            <?php foreach ($offers_list as $offer_item): ?>
                <?php 
                    // On vérifie si cette option est celle actuellement sélectionnée
                    $isSelected = (isset($_GET['offer_id']) && $_GET['offer_id'] == $offer_item['id']) ? 'selected' : '';
                    
                    // On affiche le titre + le nombre de candidats entre parenthèses
                    $countInfo = isset($offer_item['candidate_count']) ? " (" . $offer_item['candidate_count'] . ")" : "";
                ?>
                <option value="<?= $offer_item['id'] ?>" <?= $isSelected ?>>
                    <?= htmlspecialchars($offer_item['title']) . $countInfo ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<!-- Tableau des candidatures -->
<div class="table-card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Candidat</th>
                    <th>Email</th>
                    <th>Offre Concernée</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($applications)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: #888;">
                            <i class="fas fa-folder-open" style="font-size: 40px; margin-bottom: 10px; display: block; opacity: 0.5;"></i>
                            <?php if (!empty($selected_offer_id)): ?>
                                Aucun candidat n'a postulé à cette offre pour le moment.
                            <?php else: ?>
                                Aucune candidature enregistrée dans le système.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <div class="user-cell-avatar"><?= strtoupper(mb_substr(htmlspecialchars($app['candidate_name']), 0, 1)) ?></div>
                                    <?= htmlspecialchars($app['candidate_name']) ?>
                                </div>
                            </td>
                            <td><a href="mailto:<?= htmlspecialchars($app['candidate_email']) ?>"><?= htmlspecialchars($app['candidate_email']) ?></a></td>
                            <td><?= htmlspecialchars($app['offer_title']) ?></td>
                            <td><?= date('d/m/Y', strtotime($app['submitted_at'])) ?></td>
                            <td>
                                <?php 
                                    $status_map = ['en attente' => 'user', 'acceptée' => 'expert', 'refusée' => 'admin'];
                                    $cls = $status_map[$app['status']] ?? 'user';
                                ?>
                                <span class="role-badge <?= $cls ?>"><?= htmlspecialchars($app['status']) ?></span>
                            </td>
                            <td class="actions-cell" style="text-align: right;">
                                <?php if ($app['status'] === 'en attente'): ?>
                                    
                                    <!-- Si un filtre est actif, on l'ajoute dans l'URL pour y revenir après action -->
                                    <?php $filterParam = isset($_GET['offer_id']) ? '&offer_id=' . $_GET['offer_id'] : ''; ?>

                                    <a href="index.php?action=update_status&id=<?= $app['id'] ?>&status=acceptée&role=admin<?= $filterParam ?>" 
                                       class="action-btn success" title="Valider" onclick="return confirm('Valider ?');"><i class="fas fa-check"></i></a>
                                    
                                    <a href="index.php?action=update_status&id=<?= $app['id'] ?>&status=refusée&role=admin<?= $filterParam ?>" 
                                       class="action-btn danger" title="Refuser" onclick="return confirm('Refuser ?');"><i class="fas fa-times"></i></a>
                                <?php endif; ?>
                                
                                <a href="index.php?action=view_application&id=<?= $app['id'] ?>&role=admin" class="action-btn" title="Voir détails"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'templates/footer.php'; ?>