<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Offres</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-header"><a href="index.php" class="logo"><img src="assets/images/logo1-removebg-preview.png" alt="Logo"></a></div>
    <nav class="sidebar-nav">
        <?php 
            $current_action = $_GET['action'] ?? 'list';
            $is_admin = isset($_GET['role']) && $_GET['role'] === 'admin';
        ?>
        <?php if ($is_admin): ?>
            <a href="index.php?role=admin" class="nav-item <?= ($current_action === 'list') ? 'active' : '' ?>"><i class="fas fa-briefcase"></i><span>Gestion Offres</span></a>
            <a href="index.php?action=list_applications&role=admin" class="nav-item <?= ($current_action === 'list_applications') ? 'active' : '' ?>"><i class="fas fa-inbox"></i><span>Candidatures</span></a>
            <a href="#" class="nav-item"><i class="fas fa-users"></i><span>Utilisateurs</span></a>
        <?php else: ?>
            <a href="index.php" class="nav-item active"><i class="fas fa-briefcase"></i><span>Offres de Mission</span></a>
            <a href="#" class="nav-item"><i class="fas fa-user-circle"></i><span>Mon Profil</span></a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
        <div class="user-profile">
            <span class="user-avatar"><i class="fas fa-user-circle"></i></span>
            <div class="user-info">
                <span class="user-name"><?= $is_admin ? 'Admin User' : 'Client' ?></span>
                <span class="user-role"><?= $is_admin ? 'Administrateur' : 'Client' ?></span>
            </div>
        </div>
        <button class="logout-btn" title="Déconnexion"><i class="fas fa-sign-out-alt"></i></button>
    </div>
</aside>

<div class="main-content">
    <div class="topbar">
        <div class="topbar-left"><button class="menu-toggle"><i class="fas fa-bars"></i></button><h2>Module 4 : Gestion des Offres</h2></div>
        <div class="topbar-right"><button class="icon-btn"><i class="fas fa-bell"></i><span class="notification-dot"></span></button></div>
    </div>
    <div class="content-wrapper">