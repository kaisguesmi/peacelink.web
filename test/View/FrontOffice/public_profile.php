<?php
session_start();
$data = $_SESSION['public_profile_data'] ?? null;

if (!$data) {
    header("Location: index.php");
    exit();
}

// Détermine le nom et la description selon le type
$nom = $data['nom_complet'] ?? $data['nom_organisation'] ?? 'Inconnu';
$desc = $data['bio'] ?? $data['adresse'] ?? '';
$roleDisplay = $data['role_display'] ?? 'Membre';

// Initiales
$initials = strtoupper(substr($nom, 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Profil de <?php echo htmlspecialchars($nom); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Réutilisation du style de profile.php */
        .page-container { max-width: 800px; margin: 120px auto; }
        .profile-header { text-align: center; background: white; padding: 40px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .avatar-large { width: 100px; height: 100px; background: var(--vert-doux); color: white; font-size: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%; margin: 0 auto 20px auto; }
        .role-tag { background: var(--bleu-pastel); color: white; padding: 5px 10px; border-radius: 20px; font-size: 14px; }
        .desc-box { margin-top: 20px; font-size: 18px; color: #555; }
        .join-date { margin-top: 30px; color: #999; font-size: 14px; }
    </style>
</head>
<body>
    <?php include 'partials/header.php'; ?>

    <div class="page-container">
        <div class="profile-header">
            <div class="avatar-large"><?php echo $initials; ?></div>
            <h1><?php echo htmlspecialchars($nom); ?></h1>
            <span class="role-tag"><?php echo $roleDisplay; ?></span>

            <div class="desc-box">
                <?php if($roleDisplay == 'Organisation'): ?>
                    <p><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($desc); ?></p>
                <?php else: ?>
                    <p>"<?php echo htmlspecialchars($desc) ?: 'Aucune bio.'; ?>"</p>
                <?php endif; ?>
            </div>
            
            <div class="join-date">
                Membre PeaceLink depuis <?php echo date('F Y', strtotime($data['date_inscription'])); ?>
            </div>
        </div>
    </div>
</body>
</html>