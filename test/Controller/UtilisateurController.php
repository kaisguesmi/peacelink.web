<?php
session_start();

include_once __DIR__ . '/../Model/Database.php';
include_once __DIR__ . '/../Model/Utilisateur.php';

// Initialisation
$database = new Database();
$db = $database->getConnection();
$utilisateur = new Utilisateur($db);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- ROUTEUR ---
switch ($action) {
    case 'register':
        handleRegister($utilisateur);
        break;

    case 'login':
        handleLogin($utilisateur);
        break;

    case 'logout':
        handleLogout();
        break;
        
    case 'updateProfile':
        handleUpdateProfile($utilisateur);
        break;
    case 'admin_validate_org':
        handleAdminValidateOrg($utilisateur);
        break;
    
    case 'admin_delete_user':
        handleAdminDeleteUser($utilisateur);
        break;
    case 'search':
        handleSearch($utilisateur);
        break;

    case 'show_public_profile':
        handleShowPublicProfile($utilisateur);
        break;

    default:
        header("Location: ../View/FrontOffice/index.php");
        exit();
}

// --- FONCTIONS ---

function handleRegister($utilisateur) {
    // 1. Récupération des données
    $role = $_POST['role'] ?? 'client';
    $email = $_POST['email'] ?? '';
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    $data = [
        'role' => $role,
        'email' => $email,
        'mot_de_passe' => $mot_de_passe
    ];

    // Gestion des champs spécifiques
    if ($role === 'client') {
        $data['nom_complet'] = $_POST['nom_complet'] ?? '';
        $data['bio'] = $_POST['bio'] ?? '';
        $nom_session = $data['nom_complet']; // Pour la session

    } elseif ($role === 'organisation') {
        $data['nom_organisation'] = $_POST['nom_organisation'] ?? '';
        $data['adresse'] = $_POST['adresse'] ?? '';
        $nom_session = $data['nom_organisation']; // Pour la session

    } elseif ($role === 'admin') {
        $data['niveau_permission'] = $_POST['niveau_permission'] ?? 1;
        $nom_session = "Administrateur"; // Pour la session
    }


    // 2. Appel au Modèle pour créer l'utilisateur
    $newUserId = $utilisateur->create($data);

    if ($newUserId) {
        // --- CONNEXION AUTOMATIQUE ---
        
        // On démarre la session si ce n'est pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // On remplit les variables de session
        $_SESSION['user_id'] = $newUserId;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = $role;
        $_SESSION['username'] = $nom_session;

        // Message de bienvenue
        $_SESSION['success_message'] = "Bienvenue ! Votre compte a été créé avec succès.";

        // --- REDIRECTION INTELLIGENTE ---
        if ($role === 'admin') {
            header("Location: ../View/BackOffice/backoffice.php");
        } else {
            // Client et Organisation vont sur l'accueil
            header("Location: ../View/FrontOffice/index.php");
        }
        exit();

    } else {
        // En cas d'échec (ex: email déjà pris)
        $_SESSION['errors'] = ["Une erreur est survenue (Email déjà utilisé ?)."];
        header("Location: ../View/FrontOffice/inscription.php");
        exit();
    }
}

function handleLogin($utilisateur) {
    $email = $_POST['email'] ?? '';
    $password = $_POST['mot_de_passe'] ?? '';

    if (empty($email) || empty($password)) {
        $_SESSION['error_login'] = "Veuillez remplir tous les champs.";
        header("Location: ../View/FrontOffice/login.php");
        exit();
    }

    // 1. Vérifier email et mot de passe (Table Utilisateur)
    $userFound = $utilisateur->findByEmail($email);

    if ($userFound && password_verify($password, $userFound['mot_de_passe_hash'])) {
        
        // 2. Connexion réussie : Mise en session de l'ID
        $_SESSION['user_id'] = $userFound['id_utilisateur'];
        $_SESSION['email'] = $userFound['email'];

        // 3. DÉTECTION DU RÔLE (C'est l'étape importante pour l'organisation)
        $role = $utilisateur->getUserRole($userFound['id_utilisateur']);
        $_SESSION['role'] = $role;

        // 4. Récupérer le nom spécifique pour l'afficher (UX)
        if ($role === 'organisation') {
            $orgaDetails = $utilisateur->findOrganisationById($userFound['id_utilisateur']);
            $_SESSION['username'] = $orgaDetails['nom_organisation'];
            // Redirection vers une page spécifique si besoin, sinon index
            header("Location: ../View/FrontOffice/index.php"); 
        } 
        elseif ($role === 'client') {
            $clientDetails = $utilisateur->findClientById($userFound['id_utilisateur']);
            $_SESSION['username'] = $clientDetails['nom_complet'];
            header("Location: ../View/FrontOffice/index.php");
        } 
        elseif ($role === 'admin') {
             $_SESSION['username'] = "Administrateur";
             // Redirection vers le backoffice pour les admins
             header("Location: ../View/BackOffice/backoffice.php"); 
        } 
        else {
             header("Location: ../View/FrontOffice/index.php");
        }
        exit();

    } else {
        $_SESSION['error_login'] = "Email ou mot de passe incorrect.";
        header("Location: ../View/FrontOffice/login.php");
        exit();
    }
}

function handleLogout() {
    session_unset();
    session_destroy();
    header("Location: ../View/FrontOffice/index.php");
    exit();
}

function handleUpdateProfile($utilisateur) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../View/FrontOffice/login.php");
        exit();
    }
    
    $id = $_SESSION['user_id'];
    // On récupère le rôle stocké en session lors du login
    $role = $_SESSION['role'] ?? 'client'; 

    if ($role === 'client') {
        $nom_complet = $_POST['nom_complet'] ?? '';
        $bio = $_POST['bio'] ?? '';

        if ($utilisateur->updateClient($id, $nom_complet, $bio)) {
            $_SESSION['success_update'] = "Profil client mis à jour !";
            $_SESSION['username'] = $nom_complet; // Mettre à jour le nom en session
        }
    } 
    elseif ($role === 'organisation') {
        $nom_orga = $_POST['nom_organisation'] ?? '';
        $adresse = $_POST['adresse'] ?? '';

        if ($utilisateur->updateOrganisation($id, $nom_orga, $adresse)) {
            $_SESSION['success_update'] = "Informations de l'organisation mises à jour !";
            $_SESSION['username'] = $nom_orga; // Mettre à jour le nom en session
        }
    }

    header("Location: ../View/FrontOffice/profile.php");
    exit();
}
// --- FONCTIONS ADMIN ---

function checkAdminAccess() {
    // Sécurité : On vérifie si l'utilisateur est connecté ET si c'est un admin
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        // Tentative d'accès non autorisé
        header("Location: ../View/FrontOffice/login.php");
        exit();
    }
}

function handleAdminValidateOrg($utilisateur) {
    checkAdminAccess(); // Sécurité d'abord

    $id_org = $_POST['id_organisation'] ?? null;
    $id_admin = $_SESSION['user_id'];

    if ($id_org && $utilisateur->validateOrganisation($id_admin, $id_org)) {
        $_SESSION['success_msg'] = "L'organisation a été validée avec succès.";
    } else {
        $_SESSION['error_msg'] = "Erreur lors de la validation.";
    }

    header("Location: ../View/BackOffice/backoffice.php"); // Note le .php
    exit();
}

function handleAdminDeleteUser($utilisateur) {
    checkAdminAccess();

    $id_user = $_POST['id_utilisateur'] ?? null;

    // On empêche l'admin de se supprimer lui-même par erreur
    if ($id_user == $_SESSION['user_id']) {
        $_SESSION['error_msg'] = "Vous ne pouvez pas supprimer votre propre compte ici.";
        header("Location: ../View/BackOffice/backoffice.php");
        exit();
    }

    if ($id_user && $utilisateur->deleteUser($id_user)) {
        $_SESSION['success_msg'] = "Utilisateur supprimé avec succès.";
    } else {
        $_SESSION['error_msg'] = "Erreur lors de la suppression.";
    }

    header("Location: ../View/BackOffice/backoffice.php");
    exit();
}
function handleSearch($utilisateur) {
    $keyword = $_GET['q'] ?? '';
    
    if (empty($keyword)) {
        header("Location: ../View/FrontOffice/index.php");
        exit();
    }

    // On récupère les résultats
    $results = $utilisateur->searchGlobal($keyword);
    
    // On stocke les résultats en session pour les afficher dans la vue
    $_SESSION['search_results'] = $results;
    $_SESSION['search_keyword'] = $keyword;

    header("Location: ../View/FrontOffice/search_results.php");
    exit();
}

function handleShowPublicProfile($utilisateur) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        header("Location: ../View/FrontOffice/index.php");
        exit();
    }

    // On doit deviner le rôle pour savoir quelle méthode appeler
    // On utilise ta méthode getUserRole()
    $role = $utilisateur->getUserRole($id);
    $profileData = null;

    if ($role === 'client') {
        $profileData = $utilisateur->findClientById($id);
        $profileData['role_display'] = 'Client';
    } elseif ($role === 'organisation') {
        $profileData = $utilisateur->findOrganisationById($id);
        $profileData['role_display'] = 'Organisation';
    }

    if ($profileData) {
        $_SESSION['public_profile_data'] = $profileData;
        header("Location: ../View/FrontOffice/public_profile.php");
    } else {
        // Profil introuvable
        header("Location: ../View/FrontOffice/index.php");
    }
    exit();
}
?>