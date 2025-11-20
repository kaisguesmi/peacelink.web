<?php

class AdminController extends Controller
{
    private Histoire $histoireModel;
    private Initiative $initiativeModel;
    private Reclamation $reclamationModel;
    private Offre $offreModel;
    private Candidature $candidatureModel;

    public function __construct()
    {
        $this->histoireModel = new Histoire();
        $this->initiativeModel = new Initiative();
        $this->reclamationModel = new Reclamation();
        $this->offreModel = new Offre();
        $this->candidatureModel = new Candidature();
    }

    public function index()
    {
        $this->requireAdmin();
        $this->view('admin/index', [
            'stories' => $this->histoireModel->getWithClients(),
            'initiatives' => $this->initiativeModel->getWithCreator(),
            'reclamations' => $this->reclamationModel->getWithRelations(),
            'offres' => $this->offreModel->getWithAdmin(),
            'candidatures' => $this->candidatureModel->getWithRelations(),
        ], 'back');
    }

    public function create()
    {
        $this->requireAdmin();
        $this->view('admin/create', [], 'back');
    }

    public function store()
    {
        $this->requireAdmin();
        $data = [
            'titre' => trim($_POST['titre'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'statut' => $_POST['statut'] ?? 'draft',
            'id_admin' => $this->currentUser()['id_utilisateur'],
        ];
        if (!$data['titre']) {
            $_SESSION['flash'] = 'Le titre est obligatoire.';
            $this->redirect('?controller=admin&action=create');
        }
        $this->offreModel->create($data);
        $this->redirect('?controller=admin&action=index');
    }

    public function show()
    {
        $this->index();
    }

    public function edit()
    {
        $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $offre = $this->offreModel->findById($id);
        if (!$offre) {
            http_response_code(404);
            exit('Offre introuvable');
        }
        $this->view('admin/edit', ['offre' => $offre], 'back');
    }

    public function update()
    {
        $this->requireAdmin();
        $id = (int) ($_POST['id'] ?? 0);
        $offre = $this->offreModel->findById($id);
        if (!$offre) {
            http_response_code(404);
            exit('Offre introuvable');
        }
        $data = [
            'titre' => $_POST['titre'] ?? '',
            'description' => $_POST['description'] ?? '',
            'statut' => $_POST['statut'] ?? 'draft',
        ];
        $this->offreModel->update($id, $data);
        $this->redirect('?controller=admin&action=index');
    }

    public function delete()
    {
        $this->requireAdmin();
        $id = (int) ($_GET['id'] ?? 0);
        $this->offreModel->delete($id);
        $this->redirect('?controller=admin&action=index');
    }
}

