<?php
// index.php - Routeur Principal

require_once 'controller/OfferController.php';

$controller = new OfferController();
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

switch ($action) {
    // --- PARTIE CLIENT (Candidats) ---
    case 'apply': 
        $controller->showApplicationForm(); 
        break;
        
    case 'submit_application': 
        $controller->submitApplication(); 
        break;

    // --- PARTIE ADMIN (Gestion des Offres) ---
    case 'create': $controller->createOffer(); break;
    case 'store': $controller->storeOffer(); break;
    case 'edit': $controller->editOffer(); break;
    case 'update': $controller->updateOffer(); break;
    case 'delete': $controller->deleteOffer(); break;
    
    // --- PARTIE ADMIN (Gestion des Candidatures) ---
    
    // 1. Voir la liste des candidatures
    case 'list_applications': 
        $controller->listApplications(); 
        break;

    // 2. Valider ou Refuser (et envoyer mail)
    case 'update_status': 
        $controller->updateApplicationStatus(); 
        break;
    
    // 3. Voir les détails complets (Nouvelle Page)
    case 'view_application': 
        $controller->viewApplicationDetails(); 
        break;

    // --- PAR DEFAUT ---
    case 'list':
    default: 
        $controller->listOffers(); 
        break;
}
?>