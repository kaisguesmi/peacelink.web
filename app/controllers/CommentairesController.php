<?php

/**
 * Gère les discussions associées aux histoires.
 */
class CommentairesController extends Controller
{
    private Commentaire $commentaireModel;

    public function __construct()
    {
        $this->commentaireModel = new Commentaire();
    }

    public function index()
    {
        $storyId = (int) ($_GET['id_histoire'] ?? 0);
        $comments = $storyId ? $this->commentaireModel->getByStory($storyId) : $this->commentaireModel->getAllWithUsers();
        $user = $this->currentUser();
        $this->view('commentaires/index', [
            'comments' => $comments,
            'storyId' => $storyId,
        ], !empty($user['is_admin']) ? 'back' : 'front');
    }

    public function create()
    {
        $this->requireLogin();
        $storyId = (int) ($_GET['id_histoire'] ?? 0);
        $this->view('commentaires/create', ['storyId' => $storyId]);
    }

    public function store()
    {
        $user = $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=commentaires&action=create');
        }

        $contenu = trim($_POST['contenu'] ?? '');
        $storyId = (int) ($_POST['id_histoire'] ?? 0);
        if (!$contenu || !$storyId) {
            $_SESSION['flash'] = 'Commentaire invalide.';
            $this->redirect('?controller=commentaires&action=create&id_histoire=' . $storyId);
        }

        $this->commentaireModel->create([
            'contenu' => $contenu,
            'id_utilisateur' => $user['id_utilisateur'],
            'id_histoire' => $storyId,
        ]);

        $this->redirect('?controller=histoire&action=show&id=' . $storyId);
    }

    public function edit()
    {
        $user = $this->requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $comment = $this->commentaireModel->findById($id);
        if (!$comment || ($comment['id_utilisateur'] !== $user['id_utilisateur'] && empty($user['is_admin']))) {
            http_response_code(403);
            exit('Accès refusé');
        }

        $this->view('commentaires/edit', ['comment' => $comment]);
    }

    public function update()
    {
        $user = $this->requireLogin();
        $id = (int) ($_POST['id'] ?? 0);
        $comment = $this->commentaireModel->findById($id);
        if (!$comment || ($comment['id_utilisateur'] !== $user['id_utilisateur'] && empty($user['is_admin']))) {
            http_response_code(403);
            exit('Accès refusé');
        }
        $contenu = trim($_POST['contenu'] ?? $comment['contenu']);
        $this->commentaireModel->update($id, ['contenu' => $contenu]);
        $this->redirect('?controller=histoire&action=show&id=' . $comment['id_histoire']);
    }

    public function delete()
    {
        $user = $this->requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $comment = $this->commentaireModel->findById($id);
        if ($comment && ($comment['id_utilisateur'] === $user['id_utilisateur'] || !empty($user['is_admin']))) {
            $this->commentaireModel->delete($id);
        }
        $this->redirect('?controller=histoire&action=show&id=' . ($comment['id_histoire'] ?? 0));
    }

    public function show()
    {
        $this->edit(); // reuse form
    }
}

