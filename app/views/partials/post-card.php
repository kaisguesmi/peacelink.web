<?php
$config = require __DIR__ . '/../../../config/config.php';
$base = rtrim($config['app']['base_url'], '/');

// Format time helper function
if (!function_exists('getTimeAgo')) {
    function getTimeAgo(DateTime $date): string {
        $now = new DateTime();
        $diff = $now->diff($date);
        
        if ($diff->y > 0) return $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
        if ($diff->m > 0) return $diff->m . ' mois';
        if ($diff->d > 0) return $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
        if ($diff->h > 0) return $diff->h . ' heure' . ($diff->h > 1 ? 's' : '');
        if ($diff->i > 0) return $diff->i . ' min';
        return 'À l\'instant';
    }
}

// Format time
$createdAt = new DateTime($post['created_at']);
$timeAgo = getTimeAgo($createdAt);

// Get user display name
$displayName = $post['nom_complet'] ?? $post['email'] ?? 'Utilisateur';
$avatar = $post['avatar'] ?? null;
$avatarInitials = strtoupper(substr($displayName, 0, 2));

// Get reactions summary
$reactions = $post['reactions'] ?? [];
$reactionCounts = [];
foreach ($reactions as $r) {
    $reactionCounts[$r['type']] = (int) $r['count'];
}
$totalReactions = array_sum($reactionCounts);

// Get comments
$comments = $post['comments'] ?? [];
$commentCount = count($comments);

// User's current reaction
$userReaction = $post['user_reaction'] ?? null;
?>

<div class="post-card" data-post-id="<?= $post['id_post'] ?>">
    <!-- Post Header -->
    <div class="post-header">
        <div class="post-author">
            <?php if ($avatar): ?>
                <img src="<?= $base ?>/assets/images/<?= htmlspecialchars($avatar) ?>" alt="<?= htmlspecialchars($displayName) ?>" class="post-avatar">
            <?php else: ?>
                <div class="post-avatar post-avatar-placeholder"><?= htmlspecialchars($avatarInitials) ?></div>
            <?php endif; ?>
            <div class="post-author-info">
                <span class="post-author-name"><?= htmlspecialchars($displayName) ?></span>
                <span class="post-time"><?= $timeAgo ?></span>
            </div>
        </div>
        <?php if (($user['id_utilisateur'] ?? null) == $post['user_id'] || !empty($user['is_admin'])): ?>
            <div class="post-actions-menu">
                <a href="<?= $base ?>/?controller=post&action=edit&id=<?= $post['id_post'] ?>" class="action-btn" title="Modifier le post">
                    <i class="fa-solid fa-pen"></i>
                </a>
                <button class="action-btn" onclick="deletePost(<?= $post['id_post'] ?>)" title="Supprimer le post">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Post Content -->
    <div class="post-content">
        <?php if (!empty($post['title'])): ?>
            <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
        <?php endif; ?>
        <p class="post-text"><?= nl2br(htmlspecialchars($post['content'])) ?></p>
        <?php if (!empty($post['image'])): ?>
            <div class="post-image-wrapper">
                <img src="<?= $base ?>/assets/images/<?= htmlspecialchars($post['image']) ?>" alt="Post image" class="post-image">
            </div>
        <?php endif; ?>
    </div>

    <!-- Post Actions (Reactions & Comments) -->
    <div class="post-actions">
        <div class="post-reactions">
            <?php if ($totalReactions > 0): ?>
                <div class="reactions-summary">
                    <?php
                    $emojiMap = ['like' => '👍', 'love' => '❤️', 'laugh' => '😂', 'angry' => '😡'];
                    $displayed = [];
                    foreach ($reactionCounts as $type => $count) {
                        if ($count > 0) {
                            $displayed[] = $emojiMap[$type] . ' ' . $count;
                        }
                    }
                    echo implode(' ', $displayed);
                    ?>
                </div>
            <?php endif; ?>
            
            <div class="reaction-buttons">
                <button class="reaction-btn <?= $userReaction === 'like' ? 'active' : '' ?>" 
                        data-type="like" 
                        onclick="toggleReaction(<?= $post['id_post'] ?>, 'like')">
                    👍
                </button>
                <button class="reaction-btn <?= $userReaction === 'love' ? 'active' : '' ?>" 
                        data-type="love" 
                        onclick="toggleReaction(<?= $post['id_post'] ?>, 'love')">
                    ❤️
                </button>
                <button class="reaction-btn <?= $userReaction === 'laugh' ? 'active' : '' ?>" 
                        data-type="laugh" 
                        onclick="toggleReaction(<?= $post['id_post'] ?>, 'laugh')">
                    😂
                </button>
                <button class="reaction-btn <?= $userReaction === 'angry' ? 'active' : '' ?>" 
                        data-type="angry" 
                        onclick="toggleReaction(<?= $post['id_post'] ?>, 'angry')">
                    😡
                </button>
            </div>
        </div>
        
        <button class="comment-toggle-btn" onclick="toggleComments(<?= $post['id_post'] ?>)">
            <i class="fa-solid fa-comment"></i> Commenter
            <?php if ($commentCount > 0): ?>
                <span class="comment-count"><?= $commentCount ?></span>
            <?php endif; ?>
        </button>
    </div>

    <!-- Comments Section -->
    <div class="post-comments" id="comments-<?= $post['id_post'] ?>" style="display: none;">
        <div class="comments-list">
            <?php foreach ($comments as $comment): ?>
                <div class="comment-item">
                    <?php
                    $commentAvatar = $comment['avatar'] ?? null;
                    $commentName = $comment['nom_complet'] ?? $comment['email'] ?? 'Utilisateur';
                    $commentInitials = strtoupper(substr($commentName, 0, 2));
                    $commentTime = getTimeAgo(new DateTime($comment['created_at'] ?? 'now'));
                    ?>
                    <div class="comment-avatar">
                        <?php if ($commentAvatar): ?>
                            <img src="<?= $base ?>/assets/images/<?= htmlspecialchars($commentAvatar) ?>" alt="<?= htmlspecialchars($commentName) ?>">
                        <?php else: ?>
                            <div class="comment-avatar-placeholder"><?= htmlspecialchars($commentInitials) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="comment-content">
                        <div class="comment-header">
                            <span class="comment-author"><?= htmlspecialchars($commentName) ?></span>
                            <span class="comment-time"><?= $commentTime ?></span>
                        </div>
                        <p class="comment-text"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                        <?php if (($user['id_utilisateur'] ?? null) == ($comment['user_id'] ?? null) || !empty($user['is_admin'])): ?>
                            <div class="comment-actions" style="margin-top: 6px; display: flex; gap: 8px;">
                                <a href="<?= $base ?>/?controller=comment&action=edit&id=<?= $comment['id_comment'] ?>" class="action-btn" style="font-size: 12px;">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <a href="<?= $base ?>/?controller=comment&action=delete&id=<?= $comment['id_comment'] ?>" class="action-btn danger" style="font-size: 12px;" onclick="return confirm('Supprimer ce commentaire ?');">
                                    <i class="fa-solid fa-trash"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Comment Form -->
        <div class="comment-form-wrapper">
            <form class="comment-form" method="post" action="<?= $base ?>/?controller=comment&action=store" onsubmit="submitComment(event, <?= $post['id_post'] ?>)">
                <div class="comment-input-wrapper">
                    <?php
                    $userAvatar = $user['avatar'] ?? null;
                    $userName = $user['nom_complet'] ?? $user['email'] ?? 'Utilisateur';
                    $userInitials = strtoupper(substr($userName, 0, 2));
                    ?>
                    <div class="comment-form-avatar">
                        <?php if ($userAvatar): ?>
                            <img src="<?= $base ?>/assets/images/<?= htmlspecialchars($userAvatar) ?>" alt="<?= htmlspecialchars($userName) ?>">
                        <?php else: ?>
                            <div class="comment-avatar-placeholder"><?= htmlspecialchars($userInitials) ?></div>
                        <?php endif; ?>
                    </div>
                    <input type="text" 
                           class="comment-input" 
                           placeholder="Écrivez un commentaire..." 
                           name="content">
                    <button type="submit" class="comment-submit-btn">
                        <i class="fa-solid fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

