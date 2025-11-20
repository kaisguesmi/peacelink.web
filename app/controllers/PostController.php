<?php

/**
 * Contrôleur Post : gestion des posts (création, affichage, réactions).
 */
class PostController extends Controller
{
    private Post $postModel;
    private Reaction $reactionModel;
    private PostComment $commentModel;

    public function __construct()
    {
        $this->postModel = new Post();
        $this->reactionModel = new Reaction();
        $this->commentModel = new PostComment();
    }

    public function create()
    {
        $user = $this->requireLogin();
        $this->view('post/create', ['user' => $user], 'back');
    }

    public function store()
    {
        $user = $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=post&action=create');
        }

        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $image = null;

        if (!$content) {
            $_SESSION['flash'] = 'Le contenu est obligatoire.';
            $this->redirect('?controller=post&action=create');
        }

        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/assets/images/posts/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('post_', true) . '.' . $extension;
            $targetPath = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
                $image = 'posts/' . $filename;
            }
        }

        $this->postModel->create([
            'user_id' => $user['id_utilisateur'],
            'title' => $title ?: null,
            'content' => $content,
            'image' => $image
        ]);

        $_SESSION['flash'] = 'Post publié avec succès !';
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

        // Check if user owns the post or is admin
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
        $_SESSION['flash'] = 'Post supprimé.';
        $this->redirect('?controller=dashboard&action=index');
    }
}

