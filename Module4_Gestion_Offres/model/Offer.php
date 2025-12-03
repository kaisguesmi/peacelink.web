<?php
// model/Offer.php
require_once 'Database.php';

class Offer {
    private $conn;
    private $table_name = "offers";

    public $id;
    public $title;
    public $description;
    public $status;
    public $max_applications;
    public $keywords; // NOUVEAU CHAMP

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function getAll() {
        $query = "SELECT offers.*, COUNT(applications.id) as current_count 
                  FROM " . $this->table_name . " 
                  LEFT JOIN applications ON offers.id = applications.offer_id 
                  GROUP BY offers.id 
                  ORDER BY offers.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        // On récupère aussi les keywords
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->id = $row['id'];
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->status = $row['status'];
            $this->max_applications = $row['max_applications'];
            $this->keywords = $row['keywords']; // NOUVEAU
            return true;
        }
        return false;
    }

    public function countCandidates($offer_id) {
        $query = "SELECT COUNT(*) as total FROM applications WHERE offer_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $offer_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'];
    }

    public function create() {
        // Ajout de keywords
        $query = "INSERT INTO " . $this->table_name . " SET title=:title, description=:description, status=:status, max_applications=:max_applications, keywords=:keywords";
        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->status = 'publiée'; 
        $this->max_applications = htmlspecialchars(strip_tags($this->max_applications));
        $this->keywords = htmlspecialchars(strip_tags($this->keywords));

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":max_applications", $this->max_applications);
        $stmt->bindParam(":keywords", $this->keywords);

        if ($stmt->execute()) { return true; }
        return false;
    }

    public function update() {
        // Ajout de keywords
        $query = "UPDATE " . $this->table_name . " SET title = :title, description = :description, max_applications = :max_applications, keywords = :keywords WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->description = htmlspecialchars(strip_tags($this->description));
        $this->max_applications = htmlspecialchars(strip_tags($this->max_applications));
        $this->keywords = htmlspecialchars(strip_tags($this->keywords));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':max_applications', $this->max_applications);
        $stmt->bindParam(':keywords', $this->keywords);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) { return true; }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        if ($stmt->execute()) { return true; }
        return false;
    }
}
?>