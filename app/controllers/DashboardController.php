<?php

/**
 * Contrôleur Dashboard : page principale après connexion.
 */
class DashboardController extends Controller
{
    private Post $postModel;
    private Reaction $reactionModel;
    private PostComment $commentModel;
    private Client $clientModel;

    public function __construct()
    {
        $this->postModel = new Post();
        $this->reactionModel = new Reaction();
        $this->commentModel = new PostComment();
        $this->clientModel = new Client();
    }

    public function index()
    {
        $user = $this->requireLogin();
        
        // Get all posts or user's posts based on role
        if (!empty($user['is_admin'])) {
            $posts = $this->postModel->getAllWithUsers();
        } else {
            $posts = $this->postModel->getAllWithUsers(); // Show all posts for now
        }

        // Get user reactions for each post
        foreach ($posts as &$post) {
            $post['user_reaction'] = $this->reactionModel->getUserReaction(
                (int) $post['id_post'],
                (int) $user['id_utilisateur']
            );
            $post['reactions'] = $this->reactionModel->getByPost((int) $post['id_post']);
            $post['comments'] = $this->commentModel->getByPost((int) $post['id_post']);
        }

        $this->view('dashboard/index', [
            'posts' => $posts,
            'user' => $user
        ], 'back');
    }
}

