<?php

/**
 * Contrôleur Post : gestion des posts (création, affichage, réactions).
 */
class PostController extends Controller
{
    private Post $postModel;
    private Reaction $reactionModel;
    private PostComment $commentModel;
    private Notifications $notificationModel;

    public function __construct()
    {
        $this->postModel = new Post();
        $this->reactionModel = new Reaction();
        $this->commentModel = new PostComment();
        $this->notificationModel = new Notifications();
        
        // Check if user is logged in for all actions except index and show
        $this->checkLoginExcept(['index', 'show']);
    }
    
    /**
     * List all approved posts
     */
    public function index()
    {
        $user = $this->currentUser();
        $posts = $this->postModel->getAllWithUsers($user ? ($user['is_admin'] ?? false) : false);
        
        $this->view('post/index', [
            'posts' => $posts,
            'user' => $user
        ]);
    }
    
    /**
     * Show a single post
     */
    public function show($id)
    {
        $post = $this->postModel->getByIdWithUser($id);
        if (!$post) {
            http_response_code(404);
            exit('Post introuvable');
        }
        
        // User may be null here (public view allowed for approved posts)
        $user = $this->currentUser();
        
        // Only show post if it's approved or the user is the owner or an admin
        if ($post['status'] !== 'approved' && 
            (!$user || ($post['user_id'] !== $user['id_utilisateur'] && empty($user['is_admin'])))) {
            http_response_code(403);
            exit('Ce post est en attente de modération.');
        }
        
        $comments = $this->commentModel->getForPost($id);
        
        $this->view('post/view', [
            'post' => $post,
            'comments' => $comments,
            'user' => $user
        ]);
    }

    public function create()
    {
        $user = $this->requireLogin();

        // Admins cannot create posts; they only moderate content
        if (!empty($user['is_admin'])) {
            http_response_code(403);
            exit('Les administrateurs ne peuvent pas créer de posts.');
        }

        $this->view('post/create', ['user' => $user], 'back');
    }

    public function store()
    {
        $user = $this->requireLogin();

        // Extra guard: admins are not allowed to create posts
        if (!empty($user['is_admin'])) {
            http_response_code(403);
            exit('Les administrateurs ne peuvent pas créer de posts.');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=post&action=create');
        }

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (!$content) {
            $_SESSION['flash'] = 'Le contenu est obligatoire.';
            $this->redirect('?controller=post&action=create');
        }

        $now = date('Y-m-d H:i:s');

        $postId = $this->postModel->create([
            'user_id' => $user['id_utilisateur'],
            'title' => $title ?: null,
            'content' => $content,
            'image' => null,
            'status' => 'pending', // New posts require admin approval
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Notify user that their post is under review
        $this->notificationModel->create([
            'user_id' => (int) $user['id_utilisateur'],
            'title'   => 'Post soumis',
            'message' => sprintf('Votre post "%s" a été soumis et est en attente de modération.', $title ?: 'sans titre'),
        ]);

        $_SESSION['flash'] = 'Votre publication a été soumise et est en attente de modération.';
        // Stay in the authenticated Stories workflow (back-office layout)
        $this->redirect('?controller=histoire&action=index');
    }

    public function edit()
    {
        $user = $this->requireLogin();
        $postId = (int) ($_GET['id'] ?? 0);

        $post = $this->postModel->findById($postId);
        if (!$post) {
            http_response_code(404);
            exit('Post introuvable');
        }

        // Only post owner can edit their own posts, and only if not an admin
        if ($post['user_id'] !== $user['id_utilisateur'] || !empty($user['is_admin'])) {
            http_response_code(403);
            exit('Accès refusé');
        }

        $this->view('post/edit', [
            'post' => $post,
            'user' => $user,
        ], 'back');
    }

    public function update()
    {
        $user = $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=post&action=index');
        }

        $postId = (int) ($_POST['id'] ?? 0);
        $post = $this->postModel->findById($postId);
        if (!$post) {
            http_response_code(404);
            exit('Post introuvable');
        }

        // Only post owner can update their own posts, and only if not an admin
        if ($post['user_id'] !== $user['id_utilisateur'] || !empty($user['is_admin'])) {
            http_response_code(403);
            exit('Accès refusé');
        }

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');

        if (!$content) {
            $_SESSION['flash'] = 'Le contenu est obligatoire.';
            $this->redirect('?controller=post&action=edit&id=' . $postId);
        }

        $this->postModel->update($postId, [
            'title' => $title ?: null,
            'content' => $content,
            'status' => 'pending', // Set back to pending for re-approval after edit
            'moderation_notes' => null // Clear previous moderation notes
        ]);

        $_SESSION['flash'] = 'Votre modification a été soumise et est en attente de modération.';
        // After editing, return to the Stories dashboard (back-office)
        $this->redirect('?controller=histoire&action=index');
    }

    public function react()
    {
        $user = $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            exit('Invalid request');
        }

        $postId = (int) ($_POST['post_id'] ?? 0);
        $type = $_POST['type'] ?? 'like';

        if (!in_array($type, ['like', 'love', 'laugh', 'angry'])) {
            $type = 'like';
        }

        $this->reactionModel->toggle($postId, $user['id_utilisateur'], $type);

        // Return JSON response for AJAX
        header('Content-Type: application/json');
        $reactions = $this->reactionModel->getByPost($postId);
        $userReaction = $this->reactionModel->getUserReaction($postId, $user['id_utilisateur']);
        
        echo json_encode([
            'success' => true,
            'reactions' => $reactions,
            'user_reaction' => $userReaction
        ]);
        exit;
    }

    public function delete()
    {
        $user = $this->requireLogin();
        $postId = (int) ($_GET['id'] ?? 0);
        
        $post = $this->postModel->findById($postId);
        if (!$post) {
            http_response_code(404);
            exit('Post introuvable');
        }

        // User can delete their own posts, or admin can delete any post
        if ($post['user_id'] !== $user['id_utilisateur'] && empty($user['is_admin'])) {
            http_response_code(403);
            exit('Accès refusé');
        }

        // Delete image if exists
        if ($post['image']) {
            $imagePath = __DIR__ . '/../../public/assets/images/' . $post['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $this->postModel->delete($postId);
        $_SESSION['flash'] = 'Post supprimé avec succès.';
        
        // Redirect back to appropriate page based on user role
        if (!empty($user['is_admin'])) {
            $this->redirect('?controller=admin&action=moderation');
        } else {
            // Normal users return to the Stories dashboard (back-office)
            $this->redirect('?controller=histoire&action=index');
        }
    }
}

