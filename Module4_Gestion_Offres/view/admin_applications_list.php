<?php include 'templates/header.php'; ?>

<!-- =============================================== -->
<!-- |          EN-TÊTE DE LA PAGE                 | -->
<!-- =============================================== -->
<div class="page-header">
    <div>
        <h1>Candidatures Reçues</h1>
        <p class="page-subtitle">Gérez les candidatures soumises par les utilisateurs pour chaque mission.</p>
    </div>
</div>

<!-- =============================================== -->
<!-- |        CONTENEUR PRINCIPAL DU TABLEAU       | -->
<!-- =============================================== -->
<div class="table-card">
    <div class="table-wrapper">
        
        <!-- Le tableau HTML qui affiche les données -->
        <table class="data-table">
            
            <!-- En-têtes des colonnes du tableau -->
            <thead>
                <tr>
                    <th>Candidat</th>
                    <th>Email</th>
                    <th>Offre Concernée</th>
                    <th>Date de soumission</th>
                    <th>Statut</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            
            <!-- Corps du tableau, rempli dynamiquement par PHP -->
            <tbody>
                
                <?php if (empty($applications)): ?>
                    <!-- Cas où il n'y a aucune candidature -->
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px;">
                            Il n'y a aucune candidature pour le moment.
                        </td>
                    </tr>
                <?php else: ?>
                    <!-- Boucle sur chaque candidature pour créer une ligne de tableau -->
                    <?php foreach ($applications as $app): ?>
                        <tr>
                            <!-- Colonne 1 : Nom du Candidat -->
                            <td>
                                <div class="user-cell">
                                    <div class="user-cell-avatar">
                                        <?= strtoupper(mb_substr(htmlspecialchars($app['candidate_name']), 0, 1)) ?>
                                    </div>
                                    <?= htmlspecialchars($app['candidate_name']) ?>
                                </div>
                            </td>
                            
                            <!-- Colonne 2 : Email (cliquable) -->
                            <td>
                                <a href="mailto:<?= htmlspecialchars($app['candidate_email']) ?>"><?= htmlspecialchars($app['candidate_email']) ?></a>
                            </td>
                            
                            <!-- Colonne 3 : Titre de l'offre -->
                            <td>
                                <?= htmlspecialchars($app['offer_title']) ?>
                            </td>
                            
                            <!-- Colonne 4 : Date de la candidature -->
                            <td>
                                <?= date('d/m/Y à H:i', strtotime($app['submitted_at'])) ?>
                            </td>
                            
                            <!-- Colonne 5 : Statut (avec badge de couleur) -->
                            <td>
                                <?php 
                                    // Associe un statut à une classe CSS pour la couleur du badge
                                    $status_class_map = [
                                        'en attente' => 'user',     // Gris
                                        'acceptée'   => 'expert',   // Bleu
                                        'refusée'    => 'admin',    // Violet
                                    ];
                                    $status_class = $status_class_map[$app['status']] ?? 'user';
                                ?>
                                <span class="role-badge <?= $status_class ?>"><?= htmlspecialchars($app['status']) ?></span>
                            </td>
                            
                            <!-- Colonne 6 : Boutons d'action (Accepter / Refuser) -->
                            <td class="actions-cell" style="text-align: right;">
                                <?php if ($app['status'] === 'en attente'): ?>
                                    <a href="index.php?action=update_status&id=<?= $app['id'] ?>&status=acceptée&role=admin" class="action-btn success" title="Accepter" onclick="return confirm('Voulez-vous vraiment accepter cette candidature ?');">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a href="index.php?action=update_status&id=<?= $app['id'] ?>&status=refusée&role=admin" class="action-btn danger" title="Refuser" onclick="return confirm('Voulez-vous vraiment refuser cette candidature ?');">
                                        <i class="fas fa-times"></i>
                                    </a>
                                <?php endif; ?>
                                <button class="action-btn" title="Voir les détails de la motivation (Fonctionnalité à venir)">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                
            </tbody>
        </table>
        
    </div>
</div>

<?php include 'templates/footer.php'; ?>