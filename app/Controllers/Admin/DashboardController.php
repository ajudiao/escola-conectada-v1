<?php

namespace App\Controllers\Admin;

use App\Core\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        echo $this->view('admin/dashboard', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'perfil' => $_SESSION['user_perfil'] ?? 'admin',
            'title' => "Dashboard - Administração | Escola Conectada",
        ]);
    }

    public function perfil() {
        echo $this->view('admin/perfil', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'perfil' => $_SESSION['user_perfil'] ?? 'admin',
            'email' => $_SESSION['email'] ?? 'exemple@email.com',
            'senha' => $_SESSION['senha'] ?? '',
            'title' => "Perfil - Administração | Escola Conectada",
        ]);
    }
}

