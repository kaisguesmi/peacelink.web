<?php
// controllers/EventController.php

require_once __DIR__ . '/../models/EventModel.php';

class EventController
{
    // GET ?action=list
    public static function list()
    {
        $events = EventModel::getAllEvents();
        echo json_encode($events);
    }

    // POST ?action=create
    public static function create()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input) {
            echo json_encode(['success' => false, 'error' => 'Données invalides']);
            return;
        }

        $ok = EventModel::createEvent($input);

        echo json_encode(['success' => $ok]);
    }

    // POST ?action=updateStatus
    public static function updateStatus()
    {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input || !isset($input['eventId'], $input['status'])) {
            echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
            return;
        }

        $ok = EventModel::updateStatus($input['eventId'], $input['status']);

        echo json_encode(['success' => $ok]);
    }
}
