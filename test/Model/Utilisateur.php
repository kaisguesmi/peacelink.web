<?php
class Utilisateur {
    private $conn;

    // Le constructeur ne change pas
    public function __construct($db) {
        $this->conn = $db;
    }

    // Méthode générique pour créer n'importe quel type d'utilisateur
    // Elle prend un tableau de données en paramètre
    public function create($data) {
        
        $this->conn->beginTransaction();

        try {
            // --- Étape 1: Insertion commune dans la table Utilisateur ---
            $query_user = "INSERT INTO Utilisateur (email, mot_de_passe_hash) VALUES (:email, :mot_de_passe_hash)";
            
            $stmt_user = $this->conn->prepare($query_user);

            // "Nettoyer" et lier les données communes
            $email = htmlspecialchars(strip_tags($data['email']));
            $password_hash = password_hash($data['mot_de_passe'], PASSWORD_BCRYPT);
            
            $stmt_user->bindParam(':email', $email);
            $stmt_user->bindParam(':mot_de_passe_hash', $password_hash);
            
            $stmt_user->execute();
            
            $last_id = $this->conn->lastInsertId();

            // --- Étape 2: Insertion spécifique basée sur le rôle ---
            // On utilise un switch pour choisir la bonne requête
            switch ($data['role']) {
                case 'client':
                    $query_role = "INSERT INTO Client (id_utilisateur, nom_complet, bio) VALUES (:id_utilisateur, :nom_complet, :bio)";
                    $stmt_role = $this->conn->prepare($query_role);

                    $nom_complet = htmlspecialchars(strip_tags($data['nom_complet']));
                    $bio = htmlspecialchars(strip_tags($data['bio']));

                    $stmt_role->bindParam(':id_utilisateur', $last_id);
                    $stmt_role->bindParam(':nom_complet', $nom_complet);
                    $stmt_role->bindParam(':bio', $bio);
                    break;

                case 'organisation':
                    $query_role = "INSERT INTO Organisation (id_utilisateur, nom_organisation, adresse) VALUES (:id_utilisateur, :nom_organisation, :adresse)";
                    $stmt_role = $this->conn->prepare($query_role);

                    $nom_organisation = htmlspecialchars(strip_tags($data['nom_organisation']));
                    $adresse = htmlspecialchars(strip_tags($data['adresse']));
                    
                    $stmt_role->bindParam(':id_utilisateur', $last_id);
                    $stmt_role->bindParam(':nom_organisation', $nom_organisation);
                    $stmt_role->bindParam(':adresse', $adresse);
                    // Le statut de vérification a une valeur par défaut dans la BDD
                    break;
                
                case 'admin':
                    // ATTENTION: En production, la création d'admin ne devrait jamais se faire depuis un formulaire public.
                    // Ceci est à but démonstratif.
                    $query_role = "INSERT INTO Admin (id_utilisateur, niveau_permission) VALUES (:id_utilisateur, :niveau_permission)";
                    $stmt_role = $this->conn->prepare($query_role);

                    $niveau_permission = filter_var($data['niveau_permission'], FILTER_VALIDATE_INT) ? $data['niveau_permission'] : 1;

                    $stmt_role->bindParam(':id_utilisateur', $last_id);
                    $stmt_role->bindParam(':niveau_permission', $niveau_permission);
                    break;
                
                default:
                    // Si le rôle n'est pas reconnu, on annule tout.
                    throw new Exception("Rôle utilisateur non valide.");
            }

            // Exécuter la requête spécifique au rôle
            $stmt_role->execute();
            
            // Si tout s'est bien passé, on valide la transaction
            $this->conn->commit();
            return $last_id;

        } catch (Exception $e) {
            // En cas d'erreur, on annule tout
            $this->conn->rollBack();
            // error_log($e->getMessage()); // Bonne pratique: logger l'erreur
            return false;
        }
    }
    public function findByEmail($email) {
        $query = "SELECT id_utilisateur, email, mot_de_passe_hash FROM Utilisateur WHERE email = :email LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC); // Renvoie l'utilisateur ou false s'il n'est pas trouvé
    }

    // NOUVELLE MÉTHODE: Récupérer toutes les infos d'un Client pour son profil
    public function findClientById($id) {
        // On ajoute u.date_inscription à la requête SELECT
        $query = "SELECT u.id_utilisateur, u.email, u.date_inscription, c.nom_complet, c.bio 
                  FROM Utilisateur u
                  JOIN Client c ON u.id_utilisateur = c.id_utilisateur
                  WHERE u.id_utilisateur = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    // NOUVELLE MÉTHODE: Mettre à jour les informations d'un Client
    public function updateClient($id, $nom_complet, $bio) {
        $query = "UPDATE Client SET nom_complet = :nom_complet, bio = :bio WHERE id_utilisateur = :id";
        
        $stmt = $this->conn->prepare($query);

        // Nettoyer les données
        $nom_complet = htmlspecialchars(strip_tags($nom_complet));
        $bio = htmlspecialchars(strip_tags($bio));
        $id = htmlspecialchars(strip_tags($id));
        
        // Lier les paramètres
        $stmt->bindParam(':nom_complet', $nom_complet);
        $stmt->bindParam(':bio', $bio);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        // Exécuter
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    // Récupérer le rôle d'un utilisateur connecté
    public function getUserRole($id_utilisateur) {
        // On vérifie dans chaque table spécifique
        // Est-ce un Admin ?
        $query = "SELECT id_utilisateur FROM Admin WHERE id_utilisateur = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id_utilisateur);
        $stmt->execute();
        if($stmt->rowCount() > 0) return 'admin';

        // Est-ce une Organisation ?
        $query = "SELECT id_utilisateur FROM Organisation WHERE id_utilisateur = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id_utilisateur);
        $stmt->execute();
        if($stmt->rowCount() > 0) return 'organisation';

        // Est-ce un Client ?
        $query = "SELECT id_utilisateur FROM Client WHERE id_utilisateur = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id_utilisateur);
        $stmt->execute();
        if($stmt->rowCount() > 0) return 'client';

        return 'inconnu';
    }

    // Récupérer toutes les infos d'une Organisation (pour le profil ou session)
    public function findOrganisationById($id) {
        $query = "SELECT u.id_utilisateur, u.email, u.date_inscription, 
                         o.nom_organisation, o.adresse, o.statut_verification 
                  FROM Utilisateur u
                  JOIN Organisation o ON u.id_utilisateur = o.id_utilisateur
                  WHERE u.id_utilisateur = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    // Mise à jour pour l'Organisation
    public function updateOrganisation($id, $nom_organisation, $adresse) {
        $query = "UPDATE Organisation SET nom_organisation = :nom, adresse = :adresse WHERE id_utilisateur = :id";
        
        $stmt = $this->conn->prepare($query);

        $nom = htmlspecialchars(strip_tags($nom_organisation));
        $adresse = htmlspecialchars(strip_tags($adresse));
        $id = htmlspecialchars(strip_tags($id));
        
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':adresse', $adresse);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
    // --- PARTIE ADMIN : STATISTIQUES ET GESTION ---

    // 1. Récupérer les statistiques globales
    // Dans Model/Utilisateur.php

    public function getDashboardStats() {
        // Compter les clients
        $stmt = $this->conn->query("SELECT COUNT(*) as total_clients FROM Client");
        $clients = $stmt->fetch(PDO::FETCH_ASSOC)['total_clients'];

        // Compter les organisations
        $stmt = $this->conn->query("SELECT COUNT(*) as total_orgs FROM Organisation");
        $orgs = $stmt->fetch(PDO::FETCH_ASSOC)['total_orgs'];

        // --- CORRECTION ICI ---
        // Au lieu de chercher "En attente", on compte tout ce qui N'EST PAS "Verifié"
        // Cela inclut NULL, vide, "En attente", "non verifié", etc.
        $queryPending = "SELECT COUNT(*) as pending_orgs 
                         FROM Organisation 
                         WHERE statut_verification != 'Verifié' 
                         OR statut_verification IS NULL";
                         
        $stmt = $this->conn->query($queryPending);
        $pending = $stmt->fetch(PDO::FETCH_ASSOC)['pending_orgs'];

        return [
            'clients' => $clients,
            'organisations' => $orgs,
            'pending_validations' => $pending
        ];
    }

    // 2. Récupérer la liste de tous les clients
    public function getAllClients() {
        $query = "SELECT u.id_utilisateur, u.email, u.date_inscription, c.nom_complet 
                  FROM Utilisateur u
                  JOIN Client c ON u.id_utilisateur = c.id_utilisateur
                  ORDER BY u.date_inscription DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Récupérer la liste de toutes les organisations
    public function getAllOrganisations() {
        $query = "SELECT u.id_utilisateur, u.email, o.nom_organisation, o.adresse, o.statut_verification 
                  FROM Utilisateur u
                  JOIN Organisation o ON u.id_utilisateur = o.id_utilisateur
                  ORDER BY o.statut_verification ASC, u.date_inscription DESC"; 
                  // On trie pour voir les "non vérifiés" en premier
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. Valider une organisation
    public function validateOrganisation($id_admin, $id_organisation) {
        // Note: Dans ton diagramme, l'admin "valide" l'organisation. 
        // On pourrait stocker l'ID de l'admin qui a validé si tu as une colonne pour ça, 
        // sinon on change juste le statut.
        $query = "UPDATE Organisation SET statut_verification = 'Verifié' WHERE id_utilisateur = :id_org";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id_org', $id_organisation);
        return $stmt->execute();
    }

    // 5. Supprimer un utilisateur (Client ou Organisation)
    public function deleteUser($id) {
        // Grâce aux contraintes de clé étrangère (ON DELETE CASCADE) dans la BDD,
        // supprimer la ligne dans 'Utilisateur' devrait supprimer aussi la ligne dans 'Client'/'Organisation'.
        $query = "DELETE FROM Utilisateur WHERE id_utilisateur = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    // Rechercher des utilisateurs (Clients ou Organisations)
    public function searchGlobal($keyword) {
        $keyword = "%" . htmlspecialchars(strip_tags($keyword)) . "%";

        // Cette requête cherche dans Client ET Organisation et combine les résultats
        // On ajoute une colonne 'role_type' pour savoir qui est qui dans l'affichage
        $query = "
            SELECT u.id_utilisateur, c.nom_complet as nom, 'Client' as type_compte, c.bio as description
            FROM Utilisateur u
            JOIN Client c ON u.id_utilisateur = c.id_utilisateur
            WHERE c.nom_complet LIKE :keyword1
            
            UNION
            
            SELECT u.id_utilisateur, o.nom_organisation as nom, 'Organisation' as type_compte, o.adresse as description
            FROM Utilisateur u
            JOIN Organisation o ON u.id_utilisateur = o.id_utilisateur
            WHERE o.nom_organisation LIKE :keyword2 AND o.statut_verification = 'Verifié'
        ";
        
        // Note : J'ai ajouté "AND o.statut_verification = 'Verifié'" pour ne pas montrer les fausses organisations

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':keyword1', $keyword);
        $stmt->bindParam(':keyword2', $keyword);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    
}
?>