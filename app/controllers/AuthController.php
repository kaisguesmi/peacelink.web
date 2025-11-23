<?php

class AuthController extends Controller
{
    private Utilisateur $userModel;
    private Client $clientModel;
    private Admin $adminModel;

    public function __construct()
    {
        $this->userModel = new Utilisateur();
        $this->clientModel = new Client();
        $this->adminModel = new Admin();
    }

    public function index()
    {
        $this->view('auth/login');
    }

    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=auth&action=index');
        }

        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['mot_de_passe_hash'])) {
            $_SESSION['flash'] = 'Identifiants incorrects.';
            $this->redirect('?controller=auth&action=index');
        }

        $profile = $this->clientModel->getProfile((int) $user['id_utilisateur']) ?? $user;

        // Determine role: admin or user
        $isAdmin = ($email === 'admins@peacelink.com') || $this->adminModel->isAdmin((int) $user['id_utilisateur']);
        $profile['is_admin'] = $isAdmin;

        $_SESSION['user'] = $profile;
        $_SESSION['role'] = $isAdmin ? 'admin' : 'user';

        // Redirect based on role
        if ($isAdmin) {
            $this->redirect('?controller=admin&action=index');
        } else {
            $this->redirect('?controller=dashboard&action=index');
        }
    }

    public function create()
    {
        $this->view('auth/register');
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('?controller=auth&action=create');
        }

        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $nom = trim($_POST['nom_complet'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        if (!$email || !$password || !$nom) {
            $_SESSION['flash'] = 'Tous les champs sont requis.';
            $this->redirect('?controller=auth&action=create');
        }

        if ($this->userModel->findByEmail($email)) {
            $_SESSION['flash'] = 'Email déjà utilisé.';
            $this->redirect('?controller=auth&action=create');
        }

        $userId = $this->userModel->create([
            'email' => $email,
            'mot_de_passe_hash' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        $this->clientModel->create([
            'id_utilisateur' => $userId,
            'nom_complet' => $nom,
            'bio' => $bio,
        ]);

        $_SESSION['flash'] = 'Compte créé. Connectez-vous !';
        $this->redirect('?controller=auth&action=index');
    }

    public function show()
    {
        $user = $this->requireLogin();
        $this->view('auth/profile', ['user' => $user], 'back');
    }

    public function edit()
    {
        $user = $this->requireLogin();
        $this->view('auth/edit', ['user' => $user], 'back');
    }

    public function update()
    {
        $user = $this->requireLogin();
        $bio = trim($_POST['bio'] ?? ($user['bio'] ?? ''));
        $this->clientModel->update($user['id_utilisateur'], ['bio' => $bio, 'nom_complet' => $_POST['nom_complet'] ?? $user['nom_complet']]);
        $profile = $this->clientModel->getProfile($user['id_utilisateur']);
        if ($profile) {
            $profile['is_admin'] = $user['is_admin'] ?? false;
            $_SESSION['user'] = $profile;
        }
        $this->redirect('?controller=auth&action=show');
    }

    public function delete()
    {
        session_destroy();
        $this->redirect('?controller=histoire&action=index');
    }
}

