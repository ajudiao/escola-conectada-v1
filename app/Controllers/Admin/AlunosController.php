<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\Helpers;

class AlunosController extends Controller
{
    public function index()
    {
        echo $this->view('admin/alunos', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Alunos | Escola Conectada",
            'flashMessages' => Helpers::getFlashMessages(),
        ]);
    }
    public function show()
    {
        echo $this->view('admin/aluno-detalhes', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Classe | Escola Conectada",
        ]);
    }
}

