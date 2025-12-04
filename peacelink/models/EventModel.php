<?php
// models/EventModel.php

require_once __DIR__ . '/../config.php';   // doit définir $pdo (PDO)

class EventModel
{
    //Récupérer tous les événements
    public static function getAllEvents()
    {
        global $pdo;

        $sql = "SELECT * FROM events ORDER BY date ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer UN événement par son id
    public static function getEventById($id)
    {
        global $pdo;

        $sql = "SELECT * FROM events WHERE id = :id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Créer un nouvel événement (status = en_attente)
    public static function createEvent($data)
    {
        global $pdo;

        $sql = "INSERT INTO events (
                    title, category, location, date, capacity, description,
                    status, created_by, org_id, created_at
                )
                VALUES (
                    :title, :category, :location, :date, :capacity, :description,
                    'en_attente', :created_by, :org_id, NOW()
                )";

        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ':title'       => $data['title'],
            ':category'    => $data['category'],
            ':location'    => $data['location'],
            ':date'        => $data['date'],
            ':capacity'    => $data['capacity'],
            ':description' => $data['description'],
            ':created_by'  => $data['created_by'],   // ex: "Organisation"
            ':org_id'      => $data['org_id']        // ex: "555kkk555"
        ]);
    }

    // Mettre à jour le statut (validé / refusé / en_attente)
    public static function updateStatus($id, $status)
    {
        global $pdo;

        $sql = "UPDATE events SET status = :status WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ':status' => $status,
            ':id'     => $id
        ]);
    }

    // Supprimer un événement
    public static function deleteEvent($id)
    {
        global $pdo;

        $sql = "DELETE FROM events WHERE id = :id";
        $stmt = $pdo->prepare($sql);

        return $stmt->execute([':id' => $id]);
    }

    // Modifier un événement complet (on ne touche pas à org_id / created_by / status ici)
    public static function updateEvent($data)
    {
        global $pdo;

        $sql = "UPDATE events SET 
                    title       = :title,
                    category    = :category,
                    location    = :location,
                    date        = :date,
                    capacity    = :capacity,
                    description = :description
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ':title'       => $data['title'],
            ':category'    => $data['category'],
            ':location'    => $data['location'],
            ':date'        => $data['date'],
            ':capacity'    => $data['capacity'],
            ':description' => $data['description'],
            ':id'          => $data['id']
        ]);
    }
}
