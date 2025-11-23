<?php

/**
 * Base controller: loads models and renders views.
 */
abstract class Controller
{
    protected function model(string $model)
    {
        $modelClass = ucfirst($model);
        $path = __DIR__ . '/../models/' . $modelClass . '.php';

        if (!file_exists($path)) {
            throw new RuntimeException("Model {$modelClass} non trouvé.");
        }

        require_once $path;
        return new $modelClass();
    }

    protected function view(string $view, array $data = [], string $layout = 'front')
    {
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            throw new RuntimeException("Vue {$view} introuvable.");
        }

        extract($data);

        $layoutFile = __DIR__ . '/../views/layouts/' . $layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new RuntimeException("Layout {$layout} introuvable.");
        }

        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        include $layoutFile;
    }

    protected function currentUser(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    protected function requireLogin(): array
    {
        $user = $this->currentUser();
        if (!$user) {
            $this->redirect('?controller=auth&action=index');
        }
        return $user;
    }

    protected function requireAdmin(): array
    {
        $user = $this->requireLogin();
        if (empty($user['is_admin'])) {
            http_response_code(403);
            exit('Accès administrateur requis.');
        }
        return $user;
    }
    
    /**
     * Check if user is logged in, except for specified actions
     * 
     * @param array $exceptActions List of action names that don't require login
     */
    protected function checkLoginExcept(array $exceptActions = []): void
    {
        // Get the current action from the URL
        $currentAction = $_GET['action'] ?? 'index';
        
        // If current action is not in the except list, require login
        if (!in_array($currentAction, $exceptActions)) {
            $this->requireLogin();
        }
    }

    protected function redirect(string $path)
    {
        $config = require __DIR__ . '/../../config/config.php';
        $base = rtrim($config['app']['base_url'], '/');
        header('Location: ' . $base . '/' . ltrim($path, '/'));
        exit;
    }
}

