<?php

namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Helpers\Helpers;

class TurmaController extends Controller {

  public function index()
    {
        echo $this->view('admin/turmas', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Turmas | Escola Conectada",
            'flashMessages' => Helpers::getFlashMessages(),
        ]);
    }
    public function show()
    {
        echo $this->view('admin/turma-detalhes', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Classe | Escola Conectada",
        ]);
    }

}