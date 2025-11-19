<?php
// index.php - Front Controller

require_once 'controller/OfferController.php';

$controller = new OfferController();
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

switch ($action) {
    // --- Actions CLIENT ---
    case 'apply': $controller->showApplicationForm(); break;
    case 'submit_application': $controller->submitApplication(); break;

    // --- Actions ADMIN ---
    case 'create': $controller->createOffer(); break;
    case 'store': $controller->storeOffer(); break;
    case 'edit': $controller->editOffer(); break;
    case 'update': $controller->updateOffer(); break;
    case 'delete': $controller->deleteOffer(); break;
    case 'list_applications': $controller->listApplications(); break;
    case 'update_status': $controller->updateApplicationStatus(); break;
    
    // --- Action par défaut (afficher la liste des offres) ---
    case 'list':
    default: $controller->listOffers(); break;
}
?>