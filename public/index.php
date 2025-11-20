<?php
/**
 * PeaceLink front controller.
 * Example route: /peaceforum/public/index.php?controller=histoire&action=create
 */

declare(strict_types=1);

session_start();

define('ROOT_PATH', dirname(__DIR__));

require ROOT_PATH . '/app/core/Database.php';
require ROOT_PATH . '/app/core/Model.php';
require ROOT_PATH . '/app/core/Controller.php';

spl_autoload_register(function ($class) {
    $paths = [
        ROOT_PATH . '/app/models/' . $class . '.php',
        ROOT_PATH . '/app/controllers/' . $class . '.php',
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Default to dashboard if logged in, otherwise home (landing page)
$controllerParam = $_GET['controller'] ?? (isset($_SESSION['user']) ? 'dashboard' : 'home');
$action = $_GET['action'] ?? 'index';

$controllerName = ucfirst($controllerParam) . 'Controller';
$controllerFile = ROOT_PATH . '/app/controllers/' . $controllerName . '.php';

if (!file_exists($controllerFile)) {
    http_response_code(404);
    exit("Contrôleur {$controllerParam} introuvable.");
}

require_once $controllerFile;
$controller = new $controllerName();

if (!method_exists($controller, $action)) {
    http_response_code(404);
    exit("Action {$action} non définie dans {$controllerName}.");
}

// Support HTTP method override from forms (?method=DELETE or hidden field)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_method'])) {
    $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_method']);
}

$controller->{$action}();

