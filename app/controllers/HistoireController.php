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

    public function __construct()
    {
        $this->histoireModel = new Histoire();
        $this->commentaireModel = new Commentaire();
        $this->postModel = new Post();
        $this->reactionModel = new Reaction();
        $this->commentModel = new PostComment();
    }

    public function index()
    {
        $this->requireLogin();
        
        // Get posts instead of old stories
        $posts = $this->postModel->getAllWithUsers();
        $user = $this->currentUser();
        
        // Get user reactions for each post
        foreach ($posts as &$post) {
            $post['user_reaction'] = $this->reactionModel->getUserReaction(
                (int) $post['id_post'],
                (int) $user['id_utilisateur']
            );
            $post['reactions'] = $this->reactionModel->getByPost((int) $post['id_post']);
            $post['comments'] = $this->commentModel->getByPost((int) $post['id_post']);
        }
        
        $this->view('histoire/index', [
            'posts' => $posts,
            'user' => $user,
        ], 'back');
    }

    public function show()
    {
        $this->requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $story = $this->histoireModel->getWithComments($id);
        if (!$story) {
            http_response_code(404);
            exit('Histoire introuvable');
        }
        $this->view('histoire/show', ['story' => $story, 'user' => $this->currentUser()], 'back');
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

        $this->histoireModel->create([
            'titre' => $titre,
            'contenu' => $contenu,
            'statut' => 'submitted',
            'id_client' => $user['id_utilisateur'],
        ]);

        $_SESSION['flash'] = 'Histoire soumise pour validation.';
        $this->redirect('?controller=histoire&action=index');
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

