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
        
        $this->view('home/index', [], 'front');
    }
}

