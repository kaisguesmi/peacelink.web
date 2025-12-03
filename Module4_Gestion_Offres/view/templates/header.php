<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Offres</title>
    <!-- Lien vers le CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- FontAwesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<?php 
    // Détection de la page courante et du rôle
    $current_action = $_GET['action'] ?? 'list';
    $is_admin = isset($_GET['role']) && $_GET['role'] === 'admin';
?>

<!-- Si ce n'est pas un admin, on ajoute la classe 'client-mode' au body -->
<body class="<?= $is_admin ? '' : 'client-mode' ?>">

<?php if ($is_admin): ?>

    <!-- =============================================== -->
    <!-- |              MODE ADMIN : SIDEBAR           | -->
    <!-- =============================================== -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="index.php?role=admin" class="logo">
                <img src="assets/images/logo1-removebg-preview.png" alt="Logo Admin">
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <div class="nav-title">Organisation</div>
            
            <a href="index.php?role=admin" class="nav-item <?= ($current_action === 'list' || $current_action === 'create' || $current_action === 'edit') ? 'active' : '' ?>">
                <i class="fas fa-briefcase"></i><span>Gestion Offres</span>
            </a>
            
            <a href="index.php?action=list_applications&role=admin" class="nav-item <?= ($current_action === 'list_applications' || $current_action === 'view_application') ? 'active' : '' ?>">
                <i class="fas fa-inbox"></i><span>Candidatures</span>
            </a>
            
            <a href="#" class="nav-item">
                <i class="fas fa-users"></i><span>Utilisateurs</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-profile">
                <span class="user-avatar"><i class="fas fa-user-circle"></i></span>
                <div class="user-info">
                    <span class="user-name">Organisateur</span>
                    <span class="user-role">Super Organisateur</span>
                </div>
            </div>
            <a href="index.php" class="logout-btn" title="Quitter Admin"><i class="fas fa-sign-out-alt"></i></a>
        </div>
    </aside>

    <!-- Contenu Principal Admin (avec Topbar) -->
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle"><i class="fas fa-bars"></i></button>
                <h2>Organisation</h2>
            </div>
            <div class="topbar-right">
                <button class="icon-btn"><i class="fas fa-bell"></i></button>
            </div>
        </div>
        
        <div class="content-wrapper">

<?php else: ?>

    <!-- =============================================== -->
    <!-- |           MODE CLIENT : NAVBAR HORIZONTALE  | -->
    <!-- =============================================== -->
    <header class="client-header">
        <div class="nav-container">
            
            <!-- 1. Logo à gauche -->
            <a href="index.php" class="nav-logo">
                <img src="assets/images/logo1-removebg-preview.png" alt="Logo Client">
                <span>PeaceLink</span>
            </a>

            <!-- 2. Liens au centre -->
            <nav class="nav-links">
                <a href="index.php" class="<?= ($current_action === 'list' || $current_action === 'apply') ? 'active' : '' ?>">
                    Offres de Mission
                </a>
                <a href="#">Mon Profil</a>
                <a href="#">À propos</a>
            </nav>

            <!-- 3. Bouton à droite -->
            <div class="nav-actions">
                <a href="#" class="btn-login">
                    <i class="fas fa-user"></i> Mon Espace
                </a>
            </div>

        </div>
    </header>

    <!-- Contenu Principal Client (Sans Sidebar) -->
    <div class="main-content">
        <div class="content-wrapper container">

<?php endif; ?>