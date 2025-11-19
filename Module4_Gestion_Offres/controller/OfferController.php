<?php
// controller/OfferController.php

require_once 'model/Offer.php';
require_once 'model/Application.php';

class OfferController {
    private $offerModel;
    private $applicationModel;
    
    public function __construct() {
        $this->offerModel = new Offer();
        $this->applicationModel = new Application();
    }
    
    public function listOffers() {
        $user_role = isset($_GET['role']) && $_GET['role'] === 'admin' ? 'admin' : 'client';
        $offers = $this->offerModel->getAll()->fetchAll(PDO::FETCH_ASSOC);
        require 'view/offers_list.php';
    }

    public function createOffer() {
        require 'view/offer_form.php';
    }

    public function storeOffer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $errors = [];
            if (empty($title) || strlen($title) < 5) { $errors[] = "Le titre est requis (5 caractères min)."; }
            if (empty($description) || strlen($description) < 20) { $errors[] = "La description est requise (20 caractères min)."; }
            if (empty($errors)) {
                $this->offerModel->title = $title;
                $this->offerModel->description = $description;
                if ($this->offerModel->create()) {
                    header("Location: index.php?role=admin&status=created");
                    exit();
                }
            } else {
                die("<h1>Erreurs de validation</h1><p>" . implode("</p><p>", $errors) . "</p><a href='javascript:history.back()'>Retour</a>");
            }
        }
    }

    public function editOffer() {
        $id = $_GET['id'] ?? die('ID manquant.');
        if ($this->offerModel->getById($id)) {
            $offer = [
                'id' => $this->offerModel->id,
                'title' => $this->offerModel->title,
                'description' => $this->offerModel->description
            ];
            require 'view/offer_form.php';
        }
    }

    public function updateOffer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $errors = [];
            if (empty($title) || strlen($title) < 5) { $errors[] = "Le titre est requis (5 caractères min)."; }
            if (empty($description) || strlen($description) < 20) { $errors[] = "La description est requise (20 caractères min)."; }
            if (empty($errors)) {
                $this->offerModel->id = $_GET['id'];
                $this->offerModel->title = $title;
                $this->offerModel->description = $description;
                if ($this->offerModel->update()) {
                    header("Location: index.php?role=admin&status=updated");
                    exit();
                }
            } else {
                die("<h1>Erreurs de validation</h1><p>" . implode("</p><p>", $errors) . "</p><a href='javascript:history.back()'>Retour</a>");
            }
        }
    }

    public function deleteOffer() {
        $this->offerModel->id = $_GET['id'] ?? die('ID manquant.');
        if ($this->offerModel->delete()) {
            header("Location: index.php?role=admin&status=deleted");
            exit();
        }
    }

    public function showApplicationForm() {
        $id = $_GET['id'] ?? die('ID manquant.');
        if ($this->offerModel->getById($id)) {
            $offer = ['id' => $this->offerModel->id, 'title' => $this->offerModel->title];
            require 'view/application_form.php';
        }
    }

    public function submitApplication() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['candidate_name']);
            $email = trim($_POST['candidate_email']);
            $motivation = trim($_POST['motivation']);
            $errors = [];
            if (empty($name) || !preg_match("/^[a-zA-ZÀ-ÿ\s\-']+$/", $name)) { $errors[] = "Le nom est invalide."; }
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = "L'email est invalide."; }
            if (empty($motivation) || strlen($motivation) < 20) { $errors[] = "La motivation est requise (20 caractères min)."; }
            if (empty($errors)) {
                $this->applicationModel->offer_id = $_POST['offer_id'];
                $this->applicationModel->candidate_name = $name;
                $this->applicationModel->candidate_email = $email;
                $this->applicationModel->motivation = $motivation;
                if ($this->applicationModel->create()) {
                    header("Location: index.php?status=applied");
                    exit();
                }
            } else {
                die("<h1>Erreurs de validation</h1><p>" . implode("</p><p>", $errors) . "</p><a href='javascript:history.back()'>Retour</a>");
            }
        }
    }

    public function listApplications() {
        if (!isset($_GET['role']) || $_GET['role'] !== 'admin') { die("Accès refusé."); }
        $applications = $this->applicationModel->getAllWithOfferDetails()->fetchAll(PDO::FETCH_ASSOC);
        require 'view/admin_applications_list.php';
    }

    public function updateApplicationStatus() {
        if (!isset($_GET['role']) || $_GET['role'] !== 'admin') { die("Accès refusé."); }
        $id = $_GET['id'] ?? null;
        $status = $_GET['status'] ?? null;
        if ($id && in_array($status, ['acceptée', 'refusée'])) {
            if ($this->applicationModel->updateStatus($id, $status)) {
                header("Location: index.php?action=list_applications&role=admin&status=app_updated");
                exit();
            }
        }
        header("Location: index.php?action=list_applications&role=admin&status=error");
        exit();
    }
}
?>