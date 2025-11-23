<?php

/**
 * Contrôleur Comment : gestion des commentaires sur les posts.
 */
class CommentController extends Controller
{
    private PostComment $commentModel;
    private Post $postModel;
    private Notifications $notificationModel;

    public function __construct()
    {
        $this->commentModel = new PostComment();
        $this->postModel = new Post();
        $this->notificationModel = new Notifications();
    }

    public function create()
    {
        $this->requireLogin();
        $postId = (int) ($_GET['post_id'] ?? 0);
        $post = $postId ? $this->postModel->findById($postId) : null;
        if (!$post) {
            http_response_code(404);
            exit('Post introuvable');
        }

        $this->view('comment/create', [
            'post' => $post,
        ], 'back');
    }

    public function store()
    {
        $user = $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(400);
            exit('Invalid request');
        }

        $postId = (int) ($_POST['post_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        if (!$content || !$postId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Contenu requis']);
            exit;
        }

        // Verify post exists
        $post = $this->postModel->findById($postId);
        if (!$post) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Post introuvable']);
            exit;
        }

        $commentId = $this->commentModel->create([
            'post_id' => $postId,
            'user_id' => $user['id_utilisateur'],
            'content' => $content,
            // Comments do not go through moderation: mark as approved immediately
            'status' => 'approved',
        ]);

        // Get the created comment with user info
        $comment = $this->commentModel->findById($commentId);
        $clientModel = new Client();
        $client = $clientModel->getProfile($user['id_utilisateur']);
        
        $comment['nom_complet'] = $client['nom_complet'] ?? $user['email'];
        $comment['avatar'] = $client['avatar'] ?? null;
        $comment['email'] = $user['email'];

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'comment' => $comment
        ]);
        exit;
    }

    public function edit()
    {
        $user = $this->requireLogin();
        $commentId = (int) ($_GET['id'] ?? 0);

        $comment = $this->commentModel->findById($commentId);
        if (!$comment) {
            http_response_code(404);
            exit('Commentaire introuvable');
        }

        if ($comment['user_id'] !== $user['id_utilisateur'] && empty($user['is_admin'])) {
            http_response_code(403);
            exit('Accès refusé');
        }

        $post = $this->postModel->findById((int) $comment['post_id']);

        $this->view('comment/edit', [
            'comment' => $comment,
            'post' => $post,
        ], 'back');
    }

    public function update()
    {
        $user = $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=dashboard&action=index');
        }

        $commentId = (int) ($_POST['id'] ?? 0);
        $comment = $this->commentModel->findById($commentId);
        if (!$comment) {
            http_response_code(404);
            exit('Commentaire introuvable');
        }

        if ($comment['user_id'] !== $user['id_utilisateur'] && empty($user['is_admin'])) {
            http_response_code(403);
            exit('Accès refusé');
        }

        $content = trim($_POST['content'] ?? '');
        if (!$content) {
            $_SESSION['flash'] = 'Le contenu est obligatoire.';
            $this->redirect('?controller=comment&action=edit&id=' . $commentId);
        }

        $this->commentModel->update($commentId, [
            'content' => $content,
        ]);

        $_SESSION['flash'] = 'Commentaire mis à jour.';
        $this->redirect('?controller=dashboard&action=index');
    }

    public function delete()
    {
        $user = $this->requireLogin();
        $commentId = (int) ($_GET['id'] ?? 0);
        
        $comment = $this->commentModel->findById($commentId);
        if (!$comment) {
            http_response_code(404);
            exit('Commentaire introuvable');
        }

        // Check if user owns the comment or is admin
        if ($comment['user_id'] !== $user['id_utilisateur'] && empty($user['is_admin'])) {
            http_response_code(403);
            exit('Accès refusé');
        }

        // If admin deletes someone else's comment, notify the owner
        if (!empty($user['is_admin']) && $comment['user_id'] !== $user['id_utilisateur']) {
            $message = 'One of your comments was deleted by an administrator.';
            $this->notificationModel->create([
                'user_id' => $comment['user_id'],
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s'),
                'read' => 0,
            ]);
        }

        $this->commentModel->delete($commentId);
        $_SESSION['flash'] = 'Commentaire supprimé.';
        $this->redirect('?controller=dashboard&action=index');
    }
}

