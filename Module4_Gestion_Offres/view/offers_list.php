<?php include 'templates/header.php'; ?>

<!-- En-tête de la page -->
<div class="page-header">
    <div>
        <h1>Offres de Mission Disponibles</h1>
        <p class="page-subtitle">Consultez les dernières opportunités.</p>
    </div>
    
    <!-- Bouton Admin pour créer une offre -->
    <?php if ($user_role === 'admin'): ?>
        <a href="index.php?action=create&role=admin" class="btn btn-primary">
            <i class="fas fa-plus"></i> Publier une offre
        </a>
    <?php endif; ?>
</div>

<!-- Grille des offres -->
<div class="stories-grid">
    
    <?php if (empty($offers)): ?>
        <p>Aucune offre disponible pour le moment.</p>
    <?php else: ?>
        
        <?php foreach ($offers as $offer): ?>
            
            <?php 
                // --- LOGIQUE DE CALCUL ---
                $max = isset($offer['max_applications']) && $offer['max_applications'] > 0 ? $offer['max_applications'] : 10;
                $current = isset($offer['current_count']) ? $offer['current_count'] : 0;
                $remaining = $max - $current;
                $is_full = ($remaining <= 0);
                
                // Pourcentage pour la barre
                $percent = ($max > 0) ? ($current / $max) * 100 : 0;
                if ($percent > 100) $percent = 100;
            ?>

            <!-- Carte de l'offre -->
            <div class="story-card" style="<?= $is_full ? 'opacity: 0.8; background-color: #f9f9f9;' : '' ?>">
                
                <div class="story-header">
                    <span><i class="fas fa-calendar-alt"></i> Publiée le <?= date('d/m/Y', strtotime($offer['created_at'])) ?></span>
                    
                    <?php if ($is_full): ?>
                        <span class="status-badge" style="background: #ffdede; color: #e74c3c; border: 1px solid #e74c3c;">Fermée</span>
                    <?php else: ?>
                        <span class="status-badge active">En cours</span>
                    <?php endif; ?>
                </div>
                
                <div class="story-content">
                    <h3 style="<?= $is_full ? 'color: #888;' : 'color: var(--bleu-nuit);' ?>">
                        <?= htmlspecialchars($offer['title']) ?>
                    </h3>
                    
                    <p><?= nl2br(htmlspecialchars($offer['description'])) ?></p>
                    
                    <!-- ======================================================= -->
                    <!-- |   ZONE INFO PLACES (VISIBLE UNIQUEMENT PAR CLIENT)  | -->
                    <!-- ======================================================= -->
                    <?php if ($user_role !== 'admin'): ?>
                        
                        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #eee;">
                            
                            <?php if ($is_full): ?>
                                <!-- COMPLET -->
                                <div style="color: #e74c3c; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                                    <i class="fas fa-lock"></i>
                                    <span>Offre complète (<?= $max ?>/<?= $max ?>)</span>
                                </div>
                            <?php else: ?>
                                <!-- DISPONIBLE -->
                                <div style="display: flex; flex-direction: column; gap: 5px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px; font-weight: 600;">
                                        <span style="color: var(--orange-chaud);">
                                            <i class="fas fa-fire"></i> Vite ! Il reste <?= $remaining ?> place(s)
                                        </span>
                                        <span style="color: #aaa; font-size: 12px;">
                                            <?= $current ?> / <?= $max ?> inscrits
                                        </span>
                                    </div>
                                    <!-- Barre de progression -->
                                    <div style="width: 100%; height: 6px; background-color: #eee; border-radius: 3px; overflow: hidden;">
                                        <div style="width: <?= $percent ?>%; height: 100%; background-color: var(--bleu-pastel);"></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                        </div>

                    <?php endif; ?>
                    <!-- ======================================================= -->

                </div>
                
                <div class="story-actions" style="margin-top: 20px;">
                    <?php if ($user_role === 'admin'): ?>
                        
                        <!-- L'ADMIN VOIT ÇA : -->
                        <a href="index.php?action=list_applications&offer_id=<?= $offer['id'] ?>&role=admin" 
                           class="btn" 
                           style="background-color: var(--bleu-nuit); color: white; margin-right: auto; font-size: 13px;">
                            <i class="fas fa-users"></i> <?= $current ?>/<?= $max ?> Candidats
                        </a>

                        <a href="index.php?action=edit&id=<?= $offer['id'] ?>&role=admin" class="btn btn-secondary" title="Modifier"><i class="fas fa-edit"></i></a>
                        <a href="index.php?action=delete&id=<?= $offer['id'] ?>&role=admin" class="btn btn-danger" title="Supprimer" onclick="return confirm('Êtes-vous sûr ?');"><i class="fas fa-trash"></i></a>
                    
                    <?php else: ?>
                        
                        <!-- LE CLIENT VOIT ÇA : -->
                        <?php if ($is_full): ?>
                            <button class="btn btn-secondary" disabled style="width: 100%; cursor: not-allowed;">
                                <i class="fas fa-ban"></i> Candidatures fermées
                            </button>
                        <?php else: ?>
                            <a href="index.php?action=apply&id=<?= $offer['id'] ?>" class="btn btn-success" style="width: 100%; justify-content: center;">
                                <i class="fas fa-paper-plane"></i> Postuler maintenant
                            </a>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>