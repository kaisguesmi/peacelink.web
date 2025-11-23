<?php

/**
 * Contrôleur Home : page d'accueil publique (avant connexion).
 */
class HomeController extends Controller
{
    public function index()
    {
        // If user is logged in, redirect to dashboard
        if ($this->currentUser()) {
            $this->redirect('?controller=dashboard&action=index');
            return;
        }
        
        $postModel = new Post();
        $stories = $postModel->getLatestPublicStories(6);
        
        $this->view('home/index', [
            'stories' => $stories
        ], 'front');
    }
}

