<?php
// model/Application.php
require_once 'Database.php';

class Application {
    private $conn;
    private $table_name = "applications";
    
    public $offer_id;
    public $candidate_name;
    public $candidate_email;
    public $motivation;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    // 1. Créer une candidature
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET offer_id=:offer_id, candidate_name=:candidate_name, candidate_email=:candidate_email, motivation=:motivation";
        $stmt = $this->conn->prepare($query);
        
        $this->offer_id = htmlspecialchars(strip_tags($this->offer_id));
        $this->candidate_name = htmlspecialchars(strip_tags($this->candidate_name));
        $this->candidate_email = htmlspecialchars(strip_tags($this->candidate_email));
        $this->motivation = htmlspecialchars(strip_tags($this->motivation));
        
        $stmt->bindParam(":offer_id", $this->offer_id);
        $stmt->bindParam(":candidate_name", $this->candidate_name);
        $stmt->bindParam(":candidate_email", $this->candidate_email);
        $stmt->bindParam(":motivation", $this->motivation);
        
        return $stmt->execute();
    }

    // 2. Récupérer TOUTES les candidatures (Sans filtre)
    public function getAllWithOfferDetails() {
        $query = "SELECT app.id, app.candidate_name, app.candidate_email, app.status, app.submitted_at, off.title as offer_title 
                  FROM " . $this->table_name . " as app
                  LEFT JOIN offers as off ON app.offer_id = off.id
                  ORDER BY app.submitted_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // 3. (C'EST CELLE-CI QUI MANQUAIT) Récupérer les candidatures filtrées par OFFRE
    public function getAllByOfferId($offer_id) {
        $query = "SELECT app.id, app.candidate_name, app.candidate_email, app.status, app.submitted_at, off.title as offer_title 
                  FROM " . $this->table_name . " as app
                  JOIN offers as off ON app.offer_id = off.id
                  WHERE app.offer_id = :offer_id
                  ORDER BY app.submitted_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":offer_id", $offer_id);
        $stmt->execute();
        return $stmt;
    }

    // 4. Mettre à jour le statut (Accepter/Refuser)
    public function updateStatus($id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        $id = htmlspecialchars(strip_tags($id));
        $status = htmlspecialchars(strip_tags($status));
        
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }

    // 5. Récupérer TOUS les détails (Pour la page "Voir détails" et l'email)
    public function getApplicationDetails($id) {
        $query = "SELECT app.*, off.title as offer_title 
                  FROM " . $this->table_name . " as app
                  JOIN offers as off ON app.offer_id = off.id
                  WHERE app.id = :id LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>