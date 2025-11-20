<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');
$user = $_SESSION['user'] ?? null;
$currentController = $_GET['controller'] ?? 'dashboard';
$currentAction = $_GET['action'] ?? 'index';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Back Office - PeaceLink</title>
    <link rel="stylesheet" href="<?= $base ?>/assets/css/backoffice.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="<?= $base ?>/?controller=dashboard&action=index" class="logo">
                <img src="<?= $base ?>/assets/images/mon-logo-back.png" alt="Logo PeaceLink">
                <span>PeaceLink</span>
            </a>
        </div>
        <nav class="sidebar-nav">
            <a href="<?= $base ?>/?controller=dashboard&action=index" 
               class="nav-item <?= $currentController === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-house"></i>
                <span>Dashboard</span>
            </a>
            <a href="<?= $base ?>/?controller=histoire&action=index" 
               class="nav-item <?= $currentController === 'histoire' ? 'active' : '' ?>">
                <i class="fa-solid fa-book-open"></i>
                <span>Stories</span>
            </a>
            <a href="<?= $base ?>/?controller=initiative&action=index" 
               class="nav-item <?= $currentController === 'initiative' ? 'active' : '' ?>">
                <i class="fa-solid fa-hand-holding-heart"></i>
                <span>Initiatives</span>
            </a>
            <a href="<?= $base ?>/?controller=user&action=index" 
               class="nav-item <?= $currentController === 'user' ? 'active' : '' ?>">
                <i class="fa-solid fa-users"></i>
                <span>Users</span>
            </a>
            <a href="<?= $base ?>/?controller=auth&action=show" 
               class="nav-item <?= ($currentController === 'auth' && $currentAction === 'show') ? 'active' : '' ?>">
                <i class="fa-solid fa-gear"></i>
                <span>Settings</span>
            </a>
        </nav>
        <div class="sidebar-footer">
            <a href="<?= $base ?>/?controller=auth&action=show" class="user-profile">
                <i class="fa-solid fa-user-circle user-avatar"></i>
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($user['nom_complet'] ?? ($user['email'] ?? 'User')) ?></span>
                    <span class="user-role"><?= !empty($user['is_admin']) ? 'Administrator' : 'User' ?></span>
                </div>
            </a>
            <form method="post" action="<?= $base ?>/?controller=auth&action=delete">
                <button type="submit" class="logout-btn" aria-label="Logout">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menu-toggle" aria-label="Toggle Menu">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>
            <div class="topbar-right">
                <button class="icon-btn">
                    <i class="fa-solid fa-bell"></i>
                    <span class="notification-dot"></span>
                </button>
                <button class="icon-btn">
                    <i class="fa-solid fa-envelope"></i>
                </button>
            </div>
        </header>
        <main class="content-wrapper">
            <?= $content ?>
        </main>
    </div>

    <script>
        // Menu toggle
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('collapsed');
            });
        }

        // Post interactions
        function toggleReaction(postId, type) {
            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('type', type);

            fetch('<?= $base ?>/?controller=post&action=react', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Simple reload for now
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function toggleComments(postId) {
            const commentsSection = document.getElementById('comments-' + postId);
            if (commentsSection) {
                commentsSection.style.display = commentsSection.style.display === 'none' ? 'block' : 'none';
            }
        }

        function submitComment(event, postId) {
            event.preventDefault();
            const form = event.target;
            const formData = new FormData(form);
            formData.append('post_id', postId);

            fetch('<?= $base ?>/?controller=comment&action=store', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Erreur lors de l\'ajout du commentaire');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Erreur lors de l\'ajout du commentaire');
            });
        }

        function deletePost(postId) {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce post ?')) {
                window.location.href = '<?= $base ?>/?controller=post&action=delete&id=' + postId;
            }
        }
    </script>
</body>
</html>

