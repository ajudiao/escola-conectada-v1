<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\Helpers;
use App\Models\Usuario;
use App\Repositories\UsuarioRepository;


class ProfessoresController extends Controller{

    private UsuarioRepository $usuarioRepo;

    public function __construct()
    {
        $this->usuarioRepo = new UsuarioRepository();
    }

    public function index()
    {
        $professores = $this->usuarioRepo->getProfessores();
        $turmas = $this->usuarioRepo->getTurmas();
        $disciplinas = $this->usuarioRepo->getDisciplinasGrouped();

        echo $this->view('admin/professores', [
            'professores' => $professores,
            'turmas' => $turmas,
            'disciplinas' => $disciplinas,
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Professores | Escola Conectada",
            'flashMessages' => Helpers::getFlashMessages(),
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Debug: verificar se a requisição está chegando
            error_log('ProfessoresController::store() - Requisição POST recebida');
            error_log('POST data: ' . print_r($_POST, true));

            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nome = trim($_POST['nome'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $grau_academico = trim($_POST['grau_academico'] ?? '');
            $area_formacao = trim($_POST['area_formacao'] ?? '');
            $instituicao_formacao = trim($_POST['instituicao_formacao'] ?? '');
            $ano_conclusao = !empty($_POST['ano_conclusao']) ? (int)$_POST['ano_conclusao'] : null;

            // Validação básica
            if (empty($nome) || empty($email)) {
                Helpers::flashMessage('Nome e email são obrigatórios.', 'error');
                Helpers::redirect('/admin/professores');
                return;
            }

            // Se for novo professor, validar senha
            if (!$id && empty($senha)) {
                Helpers::flashMessage('Senha é obrigatória para novo professor.', 'error');
                Helpers::redirect('/admin/professores');
                return;
            }

            try {
                $usuarioId = null;
                if ($id) {
                    // Atualizar professor existente - encontrar usuarioId pelo professor id
                    $professorData = $this->usuarioRepo->findProfessorById($id);
                    if (!$professorData) {
                        Helpers::flashMessage('Professor não encontrado.', 'error');
                        Helpers::redirect('/admin/professores');
                        return;
                    }
                    $usuarioId = $professorData['id_usuario'];
                    $this->usuarioRepo->updateProfessor($id, [
                        'nome' => $nome,
                        'email' => $email,
                        'telefone' => $telefone,
                        'grau_academico' => $grau_academico,
                        'area_formacao' => $area_formacao,
                        'instituicao_formacao' => $instituicao_formacao,
                        'ano_conclusao' => $ano_conclusao,
                    ], $senha);
                    Helpers::flashMessage('Professor "' . $nome . '" atualizado com sucesso!', 'success');
                } else {
                    // Verificar se email já existe
                    $existingUser = $this->usuarioRepo->findByEmail($email);
                    if ($existingUser) {
                        Helpers::flashMessage('Este email já está cadastrado.', 'error');
                        Helpers::redirect('/admin/professores');
                        return;
                    }

                    // Criar novo professor
                    $usuario = new Usuario([
                        'nome' => $nome,
                        'email' => $email,
                        'telefone' => $telefone,
                        'senha' => password_hash($senha, PASSWORD_DEFAULT),
                        'perfil' => 'Professor',
                    ]);

                    $usuarioId = $this->usuarioRepo->create($usuario);

                    // Inserir dados do professor
                    $this->usuarioRepo->createProfessor($usuarioId, [
                        'grau_academico' => $grau_academico,
                        'area_formacao' => $area_formacao,
                        'instituicao_formacao' => $instituicao_formacao,
                        'ano_conclusao' => $ano_conclusao,
                    ]);

                    Helpers::flashMessage('Professor "' . $nome . '" adicionado com sucesso!', 'success');
                }

                // Handle assignments
                if ($usuarioId) {
                    // Remove old assignments
                    $this->usuarioRepo->removeAllAssignments($usuarioId);

                    // Add new assignments
                    $turmas = $_POST['turmas'] ?? [];
                    foreach ($turmas as $turmaId) {
                        $disciplinaKey = 'turma_disciplina_' . $turmaId;
                        $disciplinaId = !empty($_POST[$disciplinaKey]) ? (int)$_POST[$disciplinaKey] : null;
                        if ($disciplinaId) {
                            $this->usuarioRepo->assignProfessorToTurmaDisciplina($usuarioId, (int)$turmaId, $disciplinaId);
                        }
                    }
                }
            } catch (\Exception $e) {
                Helpers::flashMessage('Erro: ' . $e->getMessage(), 'error');
            }

            Helpers::redirect('/admin/professores');
        }
    }

}