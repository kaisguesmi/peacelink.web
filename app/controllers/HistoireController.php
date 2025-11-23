<?php

/**
 * Contrôleur des histoires: CRUD + réactions.
 */
class HistoireController extends Controller
{
    private Histoire $histoireModel;
    private Commentaire $commentaireModel;
    private Post $postModel;
    private Reaction $reactionModel;
    private PostComment $commentModel;
    private Notifications $notificationModel;

    public function __construct()
    {
        $this->histoireModel = new Histoire();
        $this->commentaireModel = new Commentaire();
        $this->postModel = new Post();
        $this->reactionModel = new Reaction();
        $this->commentModel = new PostComment();
        $this->notificationModel = new Notifications();
    }

    public function index()
    {
        $user = $this->requireLogin();
        
        // Show only current user's posts (including pending)
        $posts = $this->postModel->getByUserId((int) $user['id_utilisateur'], true);
        
        // Get user reactions for each post
        foreach ($posts as &$post) {
            $post['user_reaction'] = $this->reactionModel->getUserReaction(
                (int) $post['id_post'],
                (int) $user['id_utilisateur']
            );
            $post['reactions'] = $this->reactionModel->getByPost((int) $post['id_post']);
            $post['comments'] = $this->commentModel->getByPost((int) $post['id_post']);
        }

        $toastNotification = null;
        $unread = $this->notificationModel->getUnreadByUser((int) $user['id_utilisateur']);
        foreach ($unread as $notification) {
            if ($notification['title'] === 'Post approuvé' || $notification['title'] === 'Post rejeté') {
                $toastNotification = [
                    'type' => $notification['title'] === 'Post rejeté' ? 'error' : 'success',
                    'message' => $notification['message'],
                ];
                break;
            }
        }

        if ($toastNotification) {
            $this->notificationModel->markAllRead((int) $user['id_utilisateur']);
        }

        $this->view('histoire/index', [
            'posts' => $posts,
            'user' => $user,
            'toastNotification' => $toastNotification,
        ], 'back');
    }

    /**
     * Show all stories (public)
     */
    public function all()
    {
        $stories = $this->postModel->getAllPublicStories();

        $this->view('histoire/all', [
            'stories' => $stories
        ], 'front');
    }
    
    public function show()
    {
        $user = $this->requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $story = $this->histoireModel->getWithComments($id);
        if (!$story) {
            http_response_code(404);
            exit('Histoire introuvable');
        }

        // Only owner or admin can view non-approved stories
        if (($story['status'] ?? 'approved') !== 'approved'
            && $story['id_client'] !== $user['id_utilisateur']
            && empty($user['is_admin'])) {
            http_response_code(404);
            exit('Histoire introuvable');
        }

        $this->view('histoire/show', ['story' => $story, 'user' => $user], 'back');
    }

    public function create()
    {
        $this->requireLogin();
        $this->view('histoire/create', [], 'back');
    }

    public function store()
    {
        $user = $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=histoire&action=create');
        }

        $titre = trim($_POST['titre'] ?? '');
        $contenu = trim($_POST['contenu'] ?? '');

        if (!$titre || !$contenu) {
            $_SESSION['flash'] = 'Titre et contenu obligatoires.';
            $this->redirect('?controller=histoire&action=create');
        }

        $success = $this->histoireModel->create([
            'titre' => $titre,
            'contenu' => $contenu,
            'statut' => 'pending',
            'id_client' => $user['id_utilisateur']
        ]);

        if ($success) {
            // histoire/index.php expects a simple string flash message
            $_SESSION['flash'] = 'Votre publication a été soumise et est en attente de modération.';
            $this->redirect('?controller=histoire&action=index');
        } else {
            $_SESSION['flash'] = 'Échec de la soumission de votre histoire. Veuillez réessayer.';
            $this->redirect('?controller=histoire&action=create');
        }
    }

    public function edit()
    {
        $user = $this->requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $story = $this->histoireModel->findById($id);
        if (!$story || ($story['id_client'] !== $user['id_utilisateur'] && empty($user['is_admin']))) {
            http_response_code(403);
            exit('Accès refusé');
        }
        $this->view('histoire/edit', ['story' => $story], 'back');
    }

    public function update()
    {
        $user = $this->requireLogin();
        $id = (int) ($_POST['id'] ?? 0);
        $story = $this->histoireModel->findById($id);
        if (!$story || ($story['id_client'] !== $user['id_utilisateur'] && empty($user['is_admin']))) {
            http_response_code(403);
            exit('Accès refusé');
        }

        $data = [
            'titre' => trim($_POST['titre'] ?? $story['titre']),
            'contenu' => trim($_POST['contenu'] ?? $story['contenu']),
        ];

        $this->histoireModel->update($id, $data);
        $_SESSION['flash'] = 'Histoire mise à jour.';
        $this->redirect('?controller=histoire&action=show&id=' . $id);
    }

    public function delete()
    {
        $user = $this->requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $story = $this->histoireModel->findById($id);
        if ($story && ($story['id_client'] === $user['id_utilisateur'] || !empty($user['is_admin']))) {
            $this->histoireModel->delete($id);
            $_SESSION['flash'] = 'Histoire supprimée.';
        }
        $this->redirect('?controller=histoire&action=index');
    }

    public function pollNotifications()
    {
        $user = $this->requireLogin();

        header('Content-Type: application/json');

        $toastNotification = null;
        $unread = $this->notificationModel->getUnreadByUser((int) $user['id_utilisateur']);
        foreach ($unread as $notification) {
            if ($notification['title'] === 'Post approuvé' || $notification['title'] === 'Post rejeté') {
                $toastNotification = [
                    'type' => $notification['title'] === 'Post rejeté' ? 'error' : 'success',
                    'message' => $notification['message'],
                ];
                break;
            }
        }

        if ($toastNotification) {
            $this->notificationModel->markAllRead((int) $user['id_utilisateur']);
        }

        echo json_encode([
            'success' => true,
            'toast' => $toastNotification,
        ]);
        exit;
    }

    public function react()
    {
        $user = $this->requireLogin();
        $storyId = (int) ($_POST['id_histoire'] ?? 0);
        $emoji = $_POST['emoji'] ?? '❤️';
        $this->histoireModel->react($storyId, $user['id_utilisateur'], $emoji);
        $this->redirect('?controller=histoire&action=show&id=' . $storyId);
    }

    public function moderate()
    {
        $this->requireAdmin();
        $storyId = (int) ($_POST['id_histoire'] ?? 0);
        $status = $_POST['statut'] ?? 'published';
        $this->histoireModel->moderate($storyId, $status);
        $this->redirect('?controller=admin&action=index');
    }
}

