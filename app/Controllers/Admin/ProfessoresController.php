<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\Helpers;
use App\Models\Usuario;
use App\Repositories\UsuarioRepository;
use App\Repositories\ProfessorRepository;


class ProfessoresController extends Controller{

    private UsuarioRepository $usuarioRepo;
    private ProfessorRepository $professorRepo;

    public function __construct()
    {
        $this->usuarioRepo = new UsuarioRepository();
        $this->professorRepo = new ProfessorRepository();
    }

    public function index()
    {
        // Obter filtros da query string
        $search = $_GET['search'] ?? null;
        $disciplinaId = !empty($_GET['disciplina']) ? (int)$_GET['disciplina'] : null;
        $turmaId = !empty($_GET['turma']) ? (int)$_GET['turma'] : null;

        // Buscar professores com filtros aplicados
        if ($search || $disciplinaId || $turmaId) {
            $professores = $this->professorRepo->findFiltered($search, $disciplinaId, $turmaId);
        } else {
            $professores = $this->professorRepo->getAll();
        }

        $turmas = $this->professorRepo->getAllTurmas();
        $disciplinas = $this->professorRepo->getAllDisciplinasGrouped();

        // TESTE: Descomente as linhas abaixo para testar mensagens
        // Helpers::flashMessage('Sistema funcionando! Esta é uma mensagem de sucesso.', 'success');
        // Helpers::flashMessage('Atenção: Esta é uma mensagem de aviso.', 'warning');
        // Helpers::flashMessage('Erro: Esta é uma mensagem de erro.', 'error');

        echo $this->view('admin/professores', [
            'professores' => $professores,
            'turmas' => $turmas,
            'disciplinas' => $disciplinas,
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Professores | Escola Conectada",
            'flashMessages' => Helpers::getFlashMessages(),
            'search' => $search,
            'disciplina_id' => $disciplinaId,
            'turma_id' => $turmaId,
        ]);
    }

    public function store()
    {
       

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            error_log('=== STORE PROFESSOR START ===');
            error_log('POST data: ' . json_encode($_POST));

            // ===== Dados do usuário =====
            $id = !empty($_POST['id']) ? (int)$_POST['id'] : null;
            $nome_completo = trim($_POST['nome_completo'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $telefone = trim($_POST['telefone'] ?? '');
            $telefone_alternativo = trim($_POST['telefone_alterativo'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $perfil = "professor";
            $endereco = trim($_POST['endereco'] ?? '');

            // ===== Dados do professor =====
            $grau_academico = trim($_POST['grau_academico'] ?? '');
            $area_formacao = trim($_POST['area_formacao'] ?? '');
            $instituicao_formacao = trim($_POST['instituicao_formacao'] ?? '');
            $ano_conclusao = !empty($_POST['ano_conclusao']) ? (int)$_POST['ano_conclusao'] : null;
            $numero_certificado = trim($_POST['numero_certificado'] ?? '');

            // ===== Upload do certificado PDF =====
            $certificado_pdf = null;
            if (isset($_FILES['arquivo_diploma']) && $_FILES['arquivo_diploma']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['arquivo_diploma'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['pdf', 'jpg', 'jpeg', 'png'];

                if (!in_array($ext, $allowed, true)) {
                    Helpers::flashMessage('O certificado deve ser um arquivo PDF, JPG ou PNG.', 'error');
                    Helpers::redirect('/admin/professores');
                    return;
                }

                if ($file['size'] > 5 * 1024 * 1024) {
                    Helpers::flashMessage('O certificado não pode ter mais de 5 MB.', 'error');
                    Helpers::redirect('/admin/professores');
                    return;
                }

                $uploadDir = UPLOADS_PATH . '/certificados';
                if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
                    Helpers::flashMessage('Não foi possível criar a pasta de uploads.', 'error');
                    Helpers::redirect('/admin/professores');
                    return;
                }

                $certificado_pdf = uniqid('cert_', true) . '.' . $ext;
                $targetPath = $uploadDir . '/' . $certificado_pdf;

                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    Helpers::flashMessage('Erro ao salvar o certificado.', 'error');
                    Helpers::redirect('/admin/professores');
                    return;
                }
            }

            // ===== Turmas e disciplinas (arrays) =====
            $turmas = $_POST['turmas'] ?? [];
            $turmas_disciplinas = [];

            foreach ($_POST as $key => $value) {
                if (strpos($key, 'turma_disciplina_') === 0) {
                    $id_turma = (int) str_replace('turma_disciplina_', '', $key);

                    if (is_array($value)) {
                        foreach ($value as $id_disciplina) {
                            if (!empty($id_disciplina)) {
                                $turmas_disciplinas[] = [
                                    'id_turma' => $id_turma,
                                    'id_disciplina' => (int)$id_disciplina
                                ];
                            }
                        }
                    }
                }
            }

            error_log("Turmas e disciplinas recebidas: " . json_encode($turmas_disciplinas));
            if (!is_array($turmas)) $turmas = [];
            if (!is_array($turmas_disciplinas)) $turmas_disciplinas = [];

            // ===== Validações =====
            if (empty($nome_completo)) {
                Helpers::flashMessage('Nome completo é obrigatório.', 'error');
                Helpers::redirect('/admin/professores');
                return;
            }

            if (empty($email)) {
                Helpers::flashMessage('Email é obrigatório.', 'error');
                Helpers::redirect('/admin/professores');
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                Helpers::flashMessage('Email inválido.', 'error');
                Helpers::redirect('/admin/professores');
                return;
            }

            if (!$id && empty($senha)) {
                Helpers::flashMessage('Senha é obrigatória para novo professor.', 'error');
                Helpers::redirect('/admin/professores');
                return;
            }

            if (strlen($senha) < 6 && !empty($senha)) {
                Helpers::flashMessage('Senha deve ter pelo menos 6 caracteres.', 'error');
                Helpers::redirect('/admin/professores');
                return;
            }

            if (empty($grau_academico)) {
                Helpers::flashMessage('Grau acadêmico é obrigatório.', 'error');
                Helpers::redirect('/admin/professores');
                return;
            }

            if (empty($area_formacao)) {
                Helpers::flashMessage('Área de formação é obrigatória.', 'error');
                Helpers::redirect('/admin/professores');
                return;
            }

            if (empty($instituicao_formacao)) {
                Helpers::flashMessage('Instituição de formação é obrigatória.', 'error');
                Helpers::redirect('/admin/professores');
                return;
            }

            if (!$ano_conclusao || $ano_conclusao < 1950 || $ano_conclusao > date('Y')) {
                Helpers::flashMessage('Ano de conclusão inválido.', 'error');
                Helpers::redirect('/admin/professores');
                return;
            }

            if (empty($turmas)) {
                Helpers::flashMessage('Selecione pelo menos uma turma.', 'error');
                Helpers::redirect('/admin/professores');
                return;
            }

            $conn = Database::getInstance();
            $conn->beginTransaction();
            error_log('Transaction started');

            try {
                $usuarioId = null;
                $professorId = null;

                if ($id) {
                    error_log("Atualizando professor com ID: $id");
                    $professorData = $this->professorRepo->findById($id);
                    if (!$professorData) {
                        throw new \Exception('Professor não encontrado.');
                    }

                    $usuarioId = (int)$professorData['id_usuario'];
                    
                    // Atualizar dados do usuário
                    $userUpdateData = [
                        'nome_completo' => $nome_completo,
                        'email' => $email,
                        'telefone' => $telefone,
                        'telefone_alternativo' => $telefone_alternativo
                    ];
                    if (!empty($senha)) {
                        $userUpdateData['senha'] = $senha;
                    }
                    $this->usuarioRepo->update($usuarioId, $userUpdateData);
                    
                    // Atualizar dados do professor
                    $professorDataExisting = $this->professorRepo->findById($id);
                    $certificadoAtual = $professorDataExisting['certificado_pdf'] ?? null;

                    if ($certificado_pdf && $certificadoAtual) {
                        $existingFile = UPLOADS_PATH . '/certificados/' . $certificadoAtual;
                        if (is_file($existingFile)) {
                            @unlink($existingFile);
                        }
                    }

                    $this->professorRepo->update($usuarioId, [
                        'grau_academico' => $grau_academico,
                        'area_formacao' => $area_formacao,
                        'instituicao_formacao' => $instituicao_formacao,
                        'ano_conclusao' => $ano_conclusao,
                        'numero_certificado' => $numero_certificado,
                        'certificado_pdf' => $certificado_pdf ?? $certificadoAtual
                    ]);

                    $professorId = $id;
                    Helpers::flashMessage('Professor "' . $nome_completo . '" atualizado com sucesso!', 'success');
                    Helpers::logActivity("Professor Atualizado", "Professor '$nome_completo' atualizado.");
                } else {
                    error_log("Criando novo professor: $nome_completo ($email)");

                    $existingUser = $this->usuarioRepo->findByEmail($email);
              
                    if ($existingUser) {
                        throw new \Exception('Este email já está cadastrado.');
                    }
                    $usuario = new Usuario([
                        'nome_completo'        => $nome_completo,
                        'email'                => $email,
                        'senha'                => password_hash($senha, PASSWORD_DEFAULT),
                        'telefone'             => $telefone,
                        'telefone_alternativo' => $telefone_alternativo,
                        'perfil'               => $perfil,
                        'foto'                 => null,
                        'endereco'             => $endereco,
                        'created_at'           => date('Y-m-d H:i:s'),
                        'updated_at'           => date('Y-m-d H:i:s'),
                    ]);
                    

                    $usuarioId = $this->usuarioRepo->create($usuario);
                    error_log("Usuário criado com ID: $usuarioId");

                    if ($usuarioId <= 0) {
                        throw new \Exception('Falha ao criar usuário - ID inválido retornado');
                    }
                   
                    $professorId = $this->professorRepo->create($usuarioId, [
                        'grau_academico' => $grau_academico,
                        'area_formacao' => $area_formacao,
                        'instituicao_formacao' => $instituicao_formacao,
                        'ano_conclusao' => $ano_conclusao,
                        'numero_certificado' => $numero_certificado,
                        'certificado_pdf' => $certificado_pdf
                    ]);
                    error_log("Professor criado com ID: $professorId");

                    Helpers::flashMessage('Professor "' . $nome_completo . '" adicionado com sucesso!', 'success');
                    Helpers::logActivity("Professor Cadastrado", "Professor '$nome_completo' cadastrado.");
                }

                if ($professorId) {
                    error_log("Deletando atribuições antigas para professorId: $professorId");
                    $this->professorRepo->deleteAssignments($professorId);

                    error_log("Turmas a processar: " . json_encode($turmas));
                    
                    foreach ($turmas as $turmaId) {
                        $turmaId = (int)$turmaId;
                        $disciplinaKey = 'turma_disciplina_' . $turmaId;
                        $disciplinaIds = $_POST[$disciplinaKey] ?? [];

                        if (!is_array($disciplinaIds)) {
                            $disciplinaIds = [$disciplinaIds];
                        }

                        error_log("Processando turma $turmaId com disciplinas: " . json_encode($disciplinaIds));
                        
                        foreach ($disciplinaIds as $disciplinaId) {
                            $disciplinaId = (int)$disciplinaId;
                            if ($disciplinaId > 0) {
                                error_log("Atribuindo professor $professorId a turma $turmaId disciplina $disciplinaId");
                                $this->professorRepo->assignToTurmaDisciplina($professorId, $turmaId, $disciplinaId);
                            }
                        }
                    }
                }

                $conn->commit();
                error_log('Transaction committed successfully');
            } catch (\Exception $e) {
                error_log('Exception caught: ' . $e->getMessage());
                error_log('Exception trace: ' . $e->getTraceAsString());
                $conn->rollBack();
                Helpers::flashMessage('Erro ao salvar professor: ' . $e->getMessage(), 'error');
            }

            error_log('Redirecting to /admin/professores');
            Helpers::redirect('/admin/professores');
        }
    }

    public function show(int $id)
    {
        $professor = $this->professorRepo->findById($id);

        if (!$professor) {
            Helpers::flashMessage('Professor não encontrado.', 'error');
            Helpers::redirect('/admin/professores');
            return;
        }

        echo $this->view('admin/professor_show', [
            'professor' => $professor,
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Detalhes do Professor | Escola Conectada",
            'flashMessages' => Helpers::getFlashMessages(),
        ]);
    }

}