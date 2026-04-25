<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\Helpers;
use App\Models\Aviso;
use App\Repositories\AvisoRepository;


class AvisosController extends Controller{

    private AvisoRepository $avisoRepo;

    public function __construct()
    {
        $this->avisoRepo = new AvisoRepository();
    }

    public function index()
    {
        // Obter filtros da query string
        $search = $_GET['search'] ?? null;
        $destinatario = $_GET['destinatario'] ?? null;
        $prioridade = $_GET['prioridade'] ?? null;

        // Buscar avisos com filtros aplicados
        if ($search || $destinatario || $prioridade) {
            $avisos = $this->avisoRepo->findFiltered($search, $destinatario, $prioridade);
        } else {
            $avisos = $this->avisoRepo->findAll();
        }

        echo $this->view('admin/avisos', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Avisos | Escola Conectada",
            'flashMessages' => Helpers::getFlashMessages(),
            'avisos' => $avisos,
            'search' => $search,
            'destinatario' => $destinatario,
            'prioridade' => $prioridade,
            'perfil' => $_SESSION['user_perfil'] ?? 'admin',
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titulo = trim($_POST['titulo'] ?? '');
            $destinatario = $_POST['destinatario'] ?? '';
            $prioridade = $_POST['prioridade'] ?? 'normal';
            $conteudo = trim($_POST['conteudo'] ?? '');
            $user_id = $_SESSION['user_id'] ?? 1; // Assumir 1 se não logado

            // Validação básica
            if (empty($titulo) || empty($destinatario) || empty($conteudo)) {
                Helpers::flashMessage('Todos os campos obrigatórios devem ser preenchidos.', 'error');
                Helpers::redirect('/admin/avisos');
                return;
            }

            $aviso = new Aviso([
                'titulo' => $titulo,
                'conteudo' => $conteudo,
                'destinatario' => $destinatario,
                'prioridade' => $prioridade,
                'user_id' => $user_id,
            ]);

            try {
                $this->avisoRepo->create($aviso);
                Helpers::flashMessage('Aviso "' . $titulo . '" publicado com sucesso!', 'success');
            } catch (\Exception $e) {
                Helpers::flashMessage('Erro ao publicar aviso: ' . $e->getMessage(), 'error');
            }

            Helpers::redirect('/admin/avisos');
        }
    }

    public function edit(int $id)
    {
        $aviso = $this->avisoRepo->findById($id);

        if (!$aviso) {
            Helpers::flashMessage('Aviso não encontrado.', 'error');
            Helpers::redirect('/admin/avisos');
            return;
        }

        echo $this->view('admin/avisos', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Editar Aviso | Escola Conectada",
            'flashMessages' => Helpers::getFlashMessages(),
            'avisos' => $this->avisoRepo->findAll(),
            'editAvisoModal' => true,
            'avisoEdit' => $aviso,
        ]);
    }

    public function update(int $id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $aviso = $this->avisoRepo->findById($id);

            if (!$aviso) {
                Helpers::flashMessage('Aviso não encontrado.', 'error');
                Helpers::redirect('/admin/avisos');
                return;
            }

            $titulo = trim($_POST['titulo'] ?? '');
            $destinatario = $_POST['destinatario'] ?? '';
            $prioridade = $_POST['prioridade'] ?? 'normal';
            $conteudo = trim($_POST['conteudo'] ?? '');

            // Validação básica
            if (empty($titulo) || empty($destinatario) || empty($conteudo)) {
                Helpers::flashMessage('Todos os campos obrigatórios devem ser preenchidos.', 'error');
                Helpers::redirect('/admin/avisos');
                return;
            }

            $aviso->titulo = $titulo;
            $aviso->conteudo = $conteudo;
            $aviso->destinatario = $destinatario;
            $aviso->prioridade = $prioridade;

            try {
                $this->avisoRepo->update($aviso);
                Helpers::flashMessage('Aviso "' . $titulo . '" atualizado com sucesso!', 'success');
            } catch (\Exception $e) {
                Helpers::flashMessage('Erro ao atualizar aviso: ' . $e->getMessage(), 'error');
            }

            Helpers::redirect('/admin/avisos');
        }
    }

    public function destroy(int $id)
    {
        $aviso = $this->avisoRepo->findById($id);

        if (!$aviso) {
            Helpers::flashMessage('Aviso não encontrado.', 'error');
            Helpers::redirect('/admin/avisos');
            return;
        }

        try {
            $this->avisoRepo->delete($id);
            Helpers::flashMessage('Aviso "' . $aviso->titulo . '" deletado com sucesso!', 'success');
        } catch (\Exception $e) {
            Helpers::flashMessage('Erro ao deletar aviso: ' . $e->getMessage(), 'error');
        }

        Helpers::redirect('/admin/avisos');
    }

}