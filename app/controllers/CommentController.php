<?php

/**
 * Contrôleur Comment : gestion des commentaires sur les posts.
 */
class CommentController extends Controller
{
    private PostComment $commentModel;
    private Post $postModel;

    public function __construct()
    {
        $this->commentModel = new PostComment();
        $this->postModel = new Post();
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
            'content' => $content
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

        $this->commentModel->delete($commentId);
        $_SESSION['flash'] = 'Commentaire supprimé.';
        $this->redirect('?controller=dashboard&action=index');
    }
}

