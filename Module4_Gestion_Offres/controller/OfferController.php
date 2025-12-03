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
    
    // =============================================================
    // 1. GESTION DES OFFRES (ADMIN - CRUD)
    // =============================================================
    
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
            $this->offerModel->title = $_POST['title'];
            $this->offerModel->description = $_POST['description'];
            // Gestion du nombre max (par défaut 10)
            $this->offerModel->max_applications = !empty($_POST['max_applications']) ? $_POST['max_applications'] : 10;
            // Gestion des mots-clés ATS
            $this->offerModel->keywords = $_POST['keywords'];
            
            if ($this->offerModel->create()) {
                header("Location: index.php?role=admin&status=created");
                exit();
            }
        }
    }

    public function editOffer() {
        $id = $_GET['id'] ?? die('ID manquant');
        if ($this->offerModel->getById($id)) {
            $offer = [
                'id' => $this->offerModel->id, 
                'title' => $this->offerModel->title, 
                'description' => $this->offerModel->description,
                'max_applications' => $this->offerModel->max_applications,
                'keywords' => $this->offerModel->keywords
            ];
            require 'view/offer_form.php';
        }
    }

    public function updateOffer() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->offerModel->id = $_GET['id'];
            $this->offerModel->title = $_POST['title'];
            $this->offerModel->description = $_POST['description'];
            $this->offerModel->max_applications = $_POST['max_applications'];
            $this->offerModel->keywords = $_POST['keywords'];
            
            if ($this->offerModel->update()) {
                header("Location: index.php?role=admin&status=updated");
                exit();
            }
        }
    }

    public function deleteOffer() {
        $this->offerModel->id = $_GET['id'];
        if ($this->offerModel->delete()) {
            header("Location: index.php?role=admin&status=deleted");
            exit();
        }
    }

    // =============================================================
    // 2. GESTION DES CANDIDATURES (CLIENT)
    // =============================================================

    public function showApplicationForm() {
        $id = $_GET['id'] ?? die('ID manquant');
        
        // A. VÉRIFICATION QUOTA : Est-ce que l'offre est complète ?
        $this->offerModel->getById($id);
        $current = $this->offerModel->countCandidates($id);
        $max = $this->offerModel->max_applications;

        if ($current >= $max) {
            die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>
                    <h2 style='color:#e74c3c;'>Désolé, cette offre est complète.</h2>
                    <p>Le nombre maximum de candidats a été atteint.</p>
                    <a href='index.php' style='color:#5DADE2;'>Retour aux offres</a>
                 </div>");
        }

        if ($this->offerModel->getById($id)) {
            $offer = ['id' => $this->offerModel->id, 'title' => $this->offerModel->title];
            require 'view/application_form.php';
        }
    }

    public function submitApplication() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $offer_id = $_POST['offer_id'];
            $motivation = $_POST['motivation'];

            // B. DOUBLE VÉRIFICATION QUOTA
            $this->offerModel->getById($offer_id);
            $current = $this->offerModel->countCandidates($offer_id);
            
            if ($current >= $this->offerModel->max_applications) {
                die("Erreur : L'offre vient d'atteindre son quota maximum.");
            }

            // C. VÉRIFICATION ATS (Mots-clés obligatoires)
            $required_keywords = $this->offerModel->keywords;
            
            if (!empty($required_keywords)) {
                $keywords_array = explode(',', $required_keywords);
                $missing_words = [];

                foreach ($keywords_array as $word) {
                    $word = trim($word);
                    if (!empty($word)) {
                        // Recherche insensible à la casse
                        if (stripos($motivation, $word) === false) {
                            $missing_words[] = $word;
                        }
                    }
                }

                // Si des mots manquent, on bloque
                if (!empty($missing_words)) {
                    die("<div style='text-align:center; margin-top:50px; font-family:sans-serif; background:#fff0f0; padding:20px; border:1px solid red; border-radius:10px; max-width:600px; margin:50px auto;'>
                            <h2 style='color:#e74c3c;'>Candidature refusée automatiquement</h2>
                            <p>Votre lettre de motivation ne contient pas les compétences techniques requises pour ce poste.</p>
                            <p>Mots-clés manquants : <strong>" . implode(', ', $missing_words) . "</strong></p>
                            <br>
                            <a href='javascript:history.back()' style='color:#333; text-decoration:underline;'>Modifier ma motivation</a>
                         </div>");
                }
            }

            // D. ENREGISTREMENT
            $this->applicationModel->offer_id = $offer_id;
            $this->applicationModel->candidate_name = $_POST['candidate_name'];
            $this->applicationModel->candidate_email = $_POST['candidate_email'];
            $this->applicationModel->motivation = $motivation;
            
            if ($this->applicationModel->create()) {
                header("Location: index.php?status=applied");
                exit();
            }
        }
    }

    // =============================================================
    // 3. GESTION DES CANDIDATURES (ADMIN)
    // =============================================================

    public function listApplications() {
        if (!isset($_GET['role']) || $_GET['role'] !== 'admin') { die("Accès refusé."); }
        
        // Récupérer toutes les offres pour le menu déroulant (filtre)
        $offers_list = $this->offerModel->getAll()->fetchAll(PDO::FETCH_ASSOC);

        // Vérifier si un filtre est actif
        $selected_offer_id = $_GET['offer_id'] ?? '';

        if ($selected_offer_id) {
            // Filtrer par offre
            $applications = $this->applicationModel->getAllByOfferId($selected_offer_id)->fetchAll(PDO::FETCH_ASSOC);
        } else {
            // Tout afficher
            $applications = $this->applicationModel->getAllWithOfferDetails()->fetchAll(PDO::FETCH_ASSOC);
        }

        require 'view/admin_applications_list.php';
    }

    // Voir les détails complets (Nouvelle page)
    public function viewApplicationDetails() {
        if (!isset($_GET['role']) || $_GET['role'] !== 'admin') { die("Accès refusé."); }
        
        $id = $_GET['id'] ?? die('ID manquant');
        $app = $this->applicationModel->getApplicationDetails($id);
        
        if ($app) {
            require 'view/admin_application_details.php'; 
        } else {
            die("Candidature introuvable.");
        }
    }

    // Validation + Envoi Mail Automatique (HTML Pro)
    public function updateApplicationStatus() {
        if (!isset($_GET['role']) || $_GET['role'] !== 'admin') { die("Accès refusé."); }
        
        $id = $_GET['id'] ?? null;
        $status = $_GET['status'] ?? null;

        if ($id && ($status === 'acceptée' || $status === 'refusée')) {
            
            // 1. ENVOI MAIL PRO (Si Acceptée)
            if ($status === 'acceptée') {
                $details = $this->applicationModel->getApplicationDetails($id);
                
                if ($details) {
                    $to = $details['candidate_email'];
                    
                    // --- MODIFICATION ICI : AJOUT DU TITRE DANS LE SUJET ---
                    $subject = "Candidature retenue pour : " . $details['offer_title'];
                    
                    // --- DESIGN DE L'EMAIL (HTML + CSS INLINE) ---
                    $message = '
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <style>
                            body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; background-color: #f4f6f9; margin: 0; padding: 0; }
                            .container { max-width: 600px; margin: 40px auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
                            .header { background-color: #5DADE2; padding: 30px; text-align: center; color: white; }
                            .header h1 { margin: 0; font-size: 24px; letter-spacing: 1px; }
                            .content { padding: 40px 30px; color: #333333; line-height: 1.6; }
                            .job-card { background-color: #f9f9f9; border-left: 4px solid #5DADE2; padding: 15px; margin: 20px 0; border-radius: 4px; }
                            .footer { background-color: #eeeeee; padding: 20px; text-align: center; font-size: 12px; color: #888888; }
                            .btn { display: inline-block; background-color: #5DADE2; color: #ffffff; text-decoration: none; padding: 12px 25px; border-radius: 25px; font-weight: bold; margin-top: 20px; }
                        </style>
                    </head>
                    <body>
                        <div class="container">
                            <div class="header">
                                <h1>PeaceLink</h1>
                            </div>
                            <div class="content">
                                <p>Bonjour <strong>' . htmlspecialchars($details['candidate_name']) . '</strong>,</p>
                                <p>Nous avons le plaisir de vous annoncer une excellente nouvelle ! 🎉</p>
                                <p>Après étude de votre dossier, votre candidature a été <strong>validée</strong> par notre équipe.</p>
                                <div class="job-card">
                                    <strong>Poste :</strong> ' . htmlspecialchars($details['offer_title']) . '
                                </div>
                                <p>C\'est une étape importante et nous avons été impressionnés par votre profil.</p>
                                <p><strong>Quelle est la suite ?</strong><br>
                                L\'administrateur prendra contact avec vous très prochainement.</p>
                                <center>
                                    <a href="#" class="btn">Accéder à mon espace</a>
                                </center>
                            </div>
                            <div class="footer">
                                <p>Ceci est un message automatique, merci de ne pas y répondre.</p>
                                <p>&copy; ' . date('Y') . ' Peacelink.</p>
                            </div>
                        </div>
                    </body>
                    </html>
                    ';

                    // En-têtes obligatoires pour le HTML
                    $headers = "MIME-Version: 1.0" . "\r\n";
                    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                    $headers .= "From: PeaceLink Organisation <organisation@PeaceLink.com>" . "\r\n";

                    // Envoi silencieux
                    @mail($to, $subject, $message, $headers);
                }
            }

            // 2. MISE À JOUR STATUS
            if ($this->applicationModel->updateStatus($id, $status)) {
                // Redirection intelligente (Garde le filtre actif)
                $redirect_url = "index.php?action=list_applications&role=admin&status=app_updated";
                if (isset($_GET['offer_id'])) {
                    $redirect_url .= "&offer_id=" . $_GET['offer_id'];
                }
                header("Location: " . $redirect_url);
                exit();
            }
        }
        
        header("Location: index.php?action=list_applications&role=admin&status=error");
        exit();
    }
}
?>