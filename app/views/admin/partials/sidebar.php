<?php
$currentController = $_GET['controller'] ?? 'dashboard';
$isAdmin = isset($user) && ($user['is_admin'] ?? false);
?>

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?= $currentController === 'dashboard' ? 'active' : '' ?>" 
                   href="?controller=dashboard">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Tableau de bord
                </a>
            </li>
            
            <?php if ($isAdmin): ?>
            <li class="nav-item">
                <a class="nav-link <?= $currentController === 'admin' ? 'active' : '' ?>" 
                   href="?controller=admin">
                    <i class="fas fa-cog me-2"></i>
                    Administration
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentController === 'user' ? 'active' : '' ?>" 
                   href="?controller=user">
                    <i class="fas fa-users me-2"></i>
                    Utilisateurs
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentController === 'admin' && ($_GET['action'] ?? '') === 'moderation' ? 'active' : '' ?>" 
                   href="?controller=admin&action=moderation">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Modération
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link <?= $currentController === 'post' ? 'active' : '' ?>" 
                   href="?controller=post">
                    <i class="fas fa-newspaper me-2"></i>
                    Publications
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link" href="?controller=auth&action=logout">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Déconnexion
                </a>
            </li>
        </ul>
    </div>
</nav>
