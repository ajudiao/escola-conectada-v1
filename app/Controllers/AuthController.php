<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Repositories\UsuarioRepository;

class AuthController extends Controller
{
    private UsuarioRepository $usuarioRepo;

    public function __construct()
    {
        $this->usuarioRepo = new UsuarioRepository();
    }

    public function loginForm()
    {
        // Se já estiver logado, redireciona para o painel baseado no perfil
        if (isset($_SESSION['user_perfil'])) {
            $this->redirectBasedOnPerfil($_SESSION['user_perfil']);
        }

        $this->view('login', [
            'message' => 'Olá Mundo com Twig'
        ]);
    }

    public function login()
    {
        session_start();

        $email = $_POST['email'] ?? '';
        $senha = $_POST['password'] ?? '';

        $user = $this->usuarioRepo->findByEmail($email);

        if (!$user || !password_verify($senha, $user->senha)) {
            $error = "Email ou senha inválidos.";
            $this->view('login', [
                'error' => $error
            ]);
            unset($error);
            return;
        }

        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_nome'] = $user->nome_completo;
        $_SESSION['email'] = $user->email;
        $_SESSION['senha'] = $user->senha;
        $_SESSION['user_perfil'] = $user->perfil;
        $_SESSION['user_foto'] = $user->foto;

        $this->redirectBasedOnPerfil($user->perfil);
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_destroy();

        header('Location: /login');
        exit;
    }

    private function redirectBasedOnPerfil($perfil)
    {
        switch ($perfil) {
            case 'admin':
            case 'Administrador':
                header('Location: /admin');
                break;
            case 'professor':
            case 'Professor':
                header('Location: /professor');
                break;
            case 'encarregado':
            case 'Encarregado':
                header('Location: /encarregado');
                break;
            default:
                header('Location: /login');
                break;
        }
        exit;
    }
}
