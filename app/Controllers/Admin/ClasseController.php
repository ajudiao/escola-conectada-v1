<?php

namespace App\Controllers\Admin;
use App\Core\Controller;

class ClasseController extends Controller
{
    public function index()
    {
        echo $this->view('admin/classes', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Classe | Escola Conectada",
        ]);
    }
    public function show()
    {
        echo $this->view('admin/classe-detalhes', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Classe | Escola Conectada",
        ]);
    }
}