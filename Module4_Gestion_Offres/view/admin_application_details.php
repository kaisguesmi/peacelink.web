<?php include 'templates/header.php'; ?>

<div class="page-header">
    <h1>Détails de la candidature</h1>
    <a href="index.php?action=list_applications&role=admin" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Retour à la liste
    </a>
</div>

<div class="profile-card">
    
    <!-- 1. BLOC IDENTITÉ -->
    <div style="display: flex; align-items: center; gap: 20px; border-bottom: 1px solid #eee; padding-bottom: 20px; margin-bottom: 20px;">
        
        <div style="width: 70px; height: 70px; background-color: var(--bleu-pastel); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: bold;">
            <?php echo strtoupper(mb_substr($app['candidate_name'], 0, 1)); ?>
        </div>

        <div style="flex-grow: 1;">
            <h2 style="margin: 0; color: var(--bleu-nuit); font-size: 22px;">
                <?php echo htmlspecialchars($app['candidate_name']); ?>
            </h2>
            <div style="color: #666; margin-top: 5px;">
                <i class="fas fa-envelope"></i> 
                <a href="mailto:<?php echo htmlspecialchars($app['candidate_email']); ?>" style="color: var(--bleu-pastel); font-weight: 600;">
                    <?php echo htmlspecialchars($app['candidate_email']); ?>
                </a>
            </div>
            <div style="color: #888; font-size: 13px; margin-top: 3px;">
                <i class="fas fa-calendar-alt"></i> 
                Reçu le <?php echo date('d/m/Y à H:i', strtotime($app['submitted_at'])); ?>
            </div>
        </div>

        <div>
            <?php 
                $status_map = ['en attente' => 'user', 'acceptée' => 'expert', 'refusée' => 'admin'];
                $cls = $status_map[$app['status']] ?? 'user';
            ?>
            <span class="role-badge <?php echo $cls; ?>" style="font-size: 14px; padding: 8px 15px;">
                <?php echo htmlspecialchars($app['status']); ?>
            </span>
        </div>
    </div>

    <!-- 2. BLOC TITRE DE L'OFFRE -->
    <div class="form-group">
        <label style="color: var(--bleu-nuit); text-transform: uppercase; font-size: 12px; font-weight: 700;">Poste visé</label>
        <div style="font-size: 18px; color: #333; font-weight: 600;">
            <?php echo htmlspecialchars($app['offer_title']); ?>
        </div>
    </div>

    <hr style="border: 0; border-top: 1px solid #f0f0f0; margin: 25px 0;">

    <!-- 3. BLOC MOTIVATION -->
    <div class="form-group">
        <label style="color: var(--bleu-nuit); text-transform: uppercase; font-size: 12px; font-weight: 700; display: block; margin-bottom: 10px;">
            <i class="fas fa-file-alt"></i> Lettre de Motivation
        </label>
        
        <div style="background-color: #f9f9f9; padding: 25px; border-radius: 8px; border: 1px solid #e0e0e0; color: #444; line-height: 1.8; font-size: 15px; white-space: pre-wrap;">
            <?php echo htmlspecialchars($app['motivation']); ?>
        </div>
    </div>

    <!-- 4. BOUTONS D'ACTIONS -->
    <?php 
    // J'utilise ici la syntaxe standard avec des accolades { } pour éviter les erreurs
    if ($app['status'] === 'en attente') { 
    ?>
        <div class="form-actions" style="margin-top: 40px; border-top: 1px solid #eee; padding-top: 25px; display: flex; justify-content: flex-end; gap: 15px;">
            
            <a href="index.php?action=update_status&id=<?php echo $app['id']; ?>&status=refusée&role=admin" 
               class="btn btn-danger" 
               onclick="return confirm('Êtes-vous sûr de vouloir refuser cette candidature ?');">
               <i class="fas fa-times"></i> Refuser
            </a>

            <a href="index.php?action=update_status&id=<?php echo $app['id']; ?>&status=acceptée&role=admin" 
               class="btn btn-success" 
               onclick="return confirm('Accepter la candidature et envoyer l\'email de confirmation ?');">
               <i class="fas fa-check"></i> Accepter la candidature
            </a>

        </div>
    <?php 
    } // Fin du if 
    ?>

</div>

<?php include 'templates/footer.php'; ?>