<?php

class AdminController extends Controller
{
    private Histoire $histoireModel;
    private Initiative $initiativeModel;
    private Reclamation $reclamationModel;
    private Offre $offreModel;
    private Candidature $candidatureModel;
    private Commentaire $commentaireModel;
    private Notifications $notificationModel;
    private Post $postModel;
    private PostComment $postCommentModel;

    public function __construct()
    {
        $this->histoireModel = new Histoire();
        $this->initiativeModel = new Initiative();
        $this->reclamationModel = new Reclamation();
        $this->offreModel = new Offre();
        $this->candidatureModel = new Candidature();
        $this->commentaireModel = new Commentaire();
        $this->notificationModel = new Notifications();
        $this->postModel = new Post();
        $this->postCommentModel = new PostComment();
    }

    public function index()
    {
        $this->requireAdmin();
        $this->view('admin/index', [
            'stories' => $this->histoireModel->getWithClients('pending'),
            'initiatives' => $this->initiativeModel->getWithCreator(),
            'reclamations' => $this->reclamationModel->getWithRelations(),
            'offres' => $this->offreModel->getWithAdmin(),
            'candidatures' => $this->candidatureModel->getWithRelations(),
            'pendingPosts' => $this->postModel->getPendingPosts(),
            'pendingComments' => $this->postCommentModel->getPendingComments()
        ], 'back');
    }
    
    /**
     * Show moderation dashboard
     */
    public function moderation()
    {
        $this->requireAdmin();
        
        $pendingPosts = $this->postModel->getPendingPosts();
        $pendingComments = $this->postCommentModel->getPendingComments();
        $approvedPosts = $this->postModel->findAll('status = ?', ['approved'], 'created_at DESC');
        $rejectedPosts = $this->postModel->findAll('status = ?', ['rejected'], 'created_at DESC');

        $this->view('admin/moderation', [
            'pendingPosts' => $pendingPosts,
            'pendingComments' => $pendingComments,
            'approvedPosts' => $approvedPosts,
            'rejectedPosts' => $rejectedPosts,
        ], 'back');
    }
    
    /**
     * Approve a post
     */
    public function approvePost()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=admin&action=moderation');
        }
        
        $postId = (int) ($_POST['post_id'] ?? 0);
        $notes = trim($_POST['moderation_notes'] ?? '');
        
        if ($this->postModel->approvePost($postId, $notes)) {
            $_SESSION['flash'] = 'Le post a été approuvé avec succès.';

            // Notify post owner that their post is now visible
            $post = $this->postModel->findById($postId);
            if ($post) {
                $title = $post['title'] ?? 'Votre publication';
                $this->notificationModel->create([
                    'user_id' => (int) $post['user_id'],
                    'title'   => 'Post approuvé',
                    'message' => sprintf('Votre post "%s" a été approuvé et est maintenant visible.', $title),
                ]);
            }
        } else {
            $_SESSION['flash_error'] = 'Une erreur est survenue lors de l\'approbation du post.';
        }
        
        $this->redirect('?controller=admin&action=moderation');
    }

    /**
     * Delete a post permanently
     */
    public function deletePost()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=admin&action=moderation');
        }

        $postId = (int) ($_POST['post_id'] ?? 0);

        if ($postId && $this->postModel->delete($postId)) {
            $_SESSION['flash'] = 'La publication a été supprimée avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Une erreur est survenue lors de la suppression de la publication.';
        }

        $this->redirect('?controller=admin&action=moderation');
    }
    
    /**
     * Reject a post
     */
    public function rejectPost()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=admin&action=moderation');
        }
        
        $postId = (int) ($_POST['post_id'] ?? 0);
        $reason = trim($_POST['rejection_reason'] ?? 'Contenu inapproprié');
        
        if ($this->postModel->rejectPost($postId, $reason)) {
            $_SESSION['flash'] = 'Le post a été rejeté avec succès.';

            // Notify post owner that their post was rejected with a reason
            $post = $this->postModel->findById($postId);
            if ($post) {
                $title = $post['title'] ?? 'Votre publication';
                $this->notificationModel->create([
                    'user_id' => (int) $post['user_id'],
                    'title'   => 'Post rejeté',
                    'message' => sprintf('Votre post "%s" a été rejeté : %s', $title, $reason),
                ]);
            }
        } else {
            $_SESSION['flash_error'] = 'Une erreur est survenue lors du rejet du post.';
        }
        
        $this->redirect('?controller=admin&action=moderation');
    }
    
    /**
     * Approve a comment
     */
    public function approveComment()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=admin&action=moderation');
        }
        
        $commentId = (int) ($_POST['comment_id'] ?? 0);
        
        if ($this->postCommentModel->approve($commentId)) {
            $_SESSION['flash'] = 'Le commentaire a été approuvé avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Une erreur est survenue lors de l\'approbation du commentaire.';
        }
        
        $this->redirect('?controller=admin&action=moderation');
    }
    
    /**
     * Reject a comment
     */
    public function rejectComment()
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=admin&action=moderation');
        }
        
        $commentId = (int) ($_POST['comment_id'] ?? 0);
        $reason = trim($_POST['rejection_reason'] ?? 'Contenu inapproprié');
        
        if ($this->postCommentModel->reject($commentId, $reason)) {
            $_SESSION['flash'] = 'Le commentaire a été rejeté avec succès.';
        } else {
            $_SESSION['flash_error'] = 'Une erreur est survenue lors du rejet du commentaire.';
        }
        
        $this->redirect('?controller=admin&action=moderation');
    }

    public function create()
    {
        $this->requireAdmin();
        $this->view('admin/create', [], 'back');
    }

    public function store()
    {
        $this->requireAdmin();
        $data = [
            'titre' => trim($_POST['titre'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'statut' => $_POST['statut'] ?? 'draft',
            'id_admin' => $this->currentUser()['id_utilisateur'],
        ];
        if (!$data['titre']) {
            $_SESSION['flash'] = 'Le titre est obligatoire.';
            $this->redirect('?controller=admin&action=create');
        }
        $this->offreModel->create($data);
        $this->redirect('?controller=admin&action=index');
    }

    public function show()
    {
        $this->index();
    }

    public function edit()
    {
        $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $offre = $this->offreModel->findById($id);
        if (!$offre) {
            http_response_code(404);
            exit('Offre introuvable');
        }
        $this->view('admin/edit', ['offre' => $offre], 'back');
    }

    public function update()
    {
        $this->requireAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $offre = $this->offreModel->findById($id);
        if (!$offre) {
            http_response_code(404);
            exit('Offre introuvable');
        }
        $data = [
            'titre' => $_POST['titre'] ?? '',
            'description' => $_POST['description'] ?? '',
            'statut' => $_POST['statut'] ?? 'draft',
        ];
        $this->offreModel->update($id, $data);
        $this->redirect('?controller=admin&action=index');
    }

    public function delete()
    {
        $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $this->offreModel->delete($id);
        $this->redirect('?controller=admin&action=index');
    }

    public function pendingStories()
    {
        $this->requireAdmin();
        // This dedicated pending stories page is no longer used.
        // Redirect admins to the main stories listing instead.
        $this->redirect('?controller=admin&action=stories');
    }

    public function stories()
    {
        $this->requireAdmin();
        
        // Get all approved stories with user information
        $stories = $this->histoireModel->getAllApprovedWithUsers();
        
        $this->view('admin/stories', [
            'stories' => $stories
        ], 'back');
    }

    public function approveStory()
    {
        $user = $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $story = $this->histoireModel->findById($id);
        if (!$story) {
            http_response_code(404);
            exit('Story not found');
        }

        $this->histoireModel->moderate($id, 'approved', null);

        // Notify story owner
        $message = sprintf('Your story "%s" has been approved.', $story['titre']);
        $this->notificationModel->create([
            'user_id' => $story['id_client'],
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
            'read' => 0,
        ]);

        $this->redirect('?controller=admin&action=stories');
    }

    public function rejectStory()
    {
        $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? ($_POST['id'] ?? 0));
        $story = $this->histoireModel->findById($id);
        if (!$story) {
            http_response_code(404);
            exit('Story not found');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view('admin/reject-story', [
                'story' => $story,
            ], 'back');
            return;
        }

        $reason = trim($_POST['rejection_reason'] ?? '');
        $this->histoireModel->moderate($id, 'rejected', $reason ?: null);

        $message = sprintf('Your story "%s" was rejected.%s',
            $story['titre'],
            $reason ? ' Reason: ' . $reason : ''
        );
        $this->notificationModel->create([
            'user_id' => $story['id_client'],
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
            'read' => 0,
        ]);

        $this->redirect('?controller=admin&action=stories');
    }

    public function deleteStory()
    {
        $user = $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $story = $this->histoireModel->findById($id);
        if (!$story) {
            http_response_code(404);
            exit('Story not found');
        }

        // Delete associated comments
        $this->commentaireModel->deleteByStory($id);
        $this->histoireModel->delete($id);

        // Notify story owner if different from admin
        if ($story['id_client'] !== $user['id_utilisateur']) {
            $message = sprintf('Your story "%s" was deleted by an administrator.', $story['titre']);
            $this->notificationModel->create([
                'user_id' => $story['id_client'],
                'message' => $message,
                'created_at' => date('Y-m-d H:i:s'),
                'read' => 0,
            ]);
        }

        $this->redirect('?controller=admin&action=stories');
    }
}

