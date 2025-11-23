<?php

class InitiativeController extends Controller
{
    private Initiative $initiativeModel;
    private Participation $participationModel;

    public function __construct()
    {
        $this->initiativeModel = new Initiative();
        $this->participationModel = new Participation();
    }

    public function index()
    {
        $this->requireLogin();
        $initiatives = $this->initiativeModel->getWithCreator();
        $this->view('initiative/index', [
            'initiatives' => $initiatives,
            'user' => $this->currentUser(),
        ], 'back');
    }

    public function show()
    {
        $id = (int) ($_GET['id'] ?? 0);
        $initiative = $this->initiativeModel->findById($id);
        if (!$initiative) {
            http_response_code(404);
            exit('Initiative introuvable');
        }
        $participants = $this->participationModel->getParticipants($id);
        $this->view('initiative/show', ['initiative' => $initiative, 'participants' => $participants], 'back');
    }

    public function create()
    {
        $this->requireLogin();
        $this->view('initiative/create', [], 'back');
    }

    public function store()
    {
        $user = $this->requireLogin();
        $data = [
            'nom' => trim($_POST['nom'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'statut' => 'en_attente',
            'date_evenement' => $_POST['date_evenement'] ?? date('Y-m-d H:i:s'),
            'id_createur' => $user['id_utilisateur'],
        ];

        if (!$data['nom'] || !$data['description']) {
            $_SESSION['flash'] = 'Tous les champs sont requis.';
            $this->redirect('?controller=initiative&action=create');
        }

        $this->initiativeModel->create($data);
        $this->redirect('?controller=initiative&action=index');
    }

    public function edit()
    {
        $user = $this->requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $initiative = $this->initiativeModel->findById($id);
        if (!$initiative || ($initiative['id_createur'] !== $user['id_utilisateur'] && empty($user['is_admin']))) {
            http_response_code(403);
            exit('Accès refusé');
        }
        $this->view('initiative/edit', ['initiative' => $initiative], 'back');
    }

    public function update()
    {
        $user = $this->requireLogin();
        $id = (int) ($_POST['id'] ?? 0);
        $initiative = $this->initiativeModel->findById($id);
        if (!$initiative || ($initiative['id_createur'] !== $user['id_utilisateur'] && empty($user['is_admin']))) {
            http_response_code(403);
            exit('Accès refusé');
        }
        $data = [
            'nom' => trim($_POST['nom'] ?? $initiative['nom']),
            'description' => trim($_POST['description'] ?? $initiative['description']),
            'date_evenement' => $_POST['date_evenement'] ?? $initiative['date_evenement'],
        ];
        $this->initiativeModel->update($id, $data);
        $this->redirect('?controller=initiative&action=show&id=' . $id);
    }

    public function delete()
    {
        $user = $this->requireLogin();
        $id = (int) ($_GET['id'] ?? 0);
        $initiative = $this->initiativeModel->findById($id);
        if ($initiative && ($initiative['id_createur'] === $user['id_utilisateur'] || !empty($user['is_admin']))) {
            $this->initiativeModel->delete($id);
        }
        $this->redirect('?controller=initiative&action=index');
    }

    public function moderate()
    {
        $this->requireAdmin();
        $id = (int) ($_POST['id_initiative'] ?? 0);
        $status = $_POST['statut'] ?? 'approuvee';
        $this->initiativeModel->moderate($id, $status);
        $this->redirect('?controller=admin&action=index');
    }
}

