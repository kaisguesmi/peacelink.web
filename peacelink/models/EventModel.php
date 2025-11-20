<?php
// models/EventModel.php

require_once __DIR__ . '/../config.php';   // charge $pdo

class EventModel
{
    // 1) Récupérer tous les événements
    public static function getAllEvents()
    {
        global $pdo;

        $sql = "SELECT * FROM events ORDER BY date ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2) Créer un nouvel événement (status = en_attente)
    public static function createEvent($data)
    {
        global $pdo;

        $sql = "INSERT INTO events (title, category, location, date, capacity, description, status, created_by)
                VALUES (:title, :category, :location, :date, :capacity, :description, 'en_attente', :created_by)";

        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            ':title'       => $data['title'],
            ':category'    => $data['category'],
            ':location'    => $data['location'],
            ':date'        => $data['date'],
            ':capacity'    => $data['capacity'],
            ':description' => $data['description'],
            ':created_by'  => $data['created_by'] ?? 'Organisation'
        ]);
    }

    // 3) Mettre à jour le statut (validé / refusé / en_attente)
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
}
