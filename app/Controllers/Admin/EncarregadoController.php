<?php

namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Helpers\Helpers;

class EncarregadoController extends Controller {

  public function index()
    {
        echo $this->view('admin/encarregados', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Encarregados | Escola Conectada",
            'flashMessages' => Helpers::getFlashMessages(),
        ]);
    }
     public function show()
    {
        echo $this->view('admin/encarregado-detalhes', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Classe | Escola Conectada",
        ]);
    }
}