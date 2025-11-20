<?php
// api_events.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit; // Pour les requêtes CORS préflight
}

require_once __DIR__ . '/controllers/EventController.php';

// action=list / create / updateStatus
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        EventController::list();
        break;

    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            EventController::create();
        } else {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        }
        break;

    case 'updateStatus':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            EventController::updateStatus();
        } else {
            echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Action inconnue']);
}
