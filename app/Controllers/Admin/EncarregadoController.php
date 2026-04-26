<?php

namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Helpers\Helpers;
use App\Models\Encarregado;
use App\Models\Usuario;
use App\Repositories\EncarregadoRepository;
use App\Repositories\UsuarioRepository;

class EncarregadoController extends Controller {

    private $encarregadoRepository;
    private $usuarioRepository;

    public function __construct() {
        $this->encarregadoRepository = new EncarregadoRepository();
        $this->usuarioRepository = new UsuarioRepository();
    }

    public function index() {
        try {
            $encarregados = $this->encarregadoRepository->findAll();

            echo $this->view('admin/encarregados', [
                'userName' => $_SESSION['user_nome'] ?? 'Admin',
                'title' => "Encarregados | Escola Conectada",
                'flashMessages' => Helpers::getFlashMessages(),
                'encarregados' => $encarregados
            ]);
        } catch (\Exception $e) {
            error_log("Erro ao listar encarregados: " . $e->getMessage());
            Helpers::flashMessage('Erro ao carregar a lista de encarregados.', 'danger');
            echo $this->view('admin/encarregados', [
                'userName' => $_SESSION['user_nome'] ?? 'Admin',
                'title' => "Encarregados | Escola Conectada",
                'flashMessages' => Helpers::getFlashMessages(),
                'encarregados' => []
            ]);
        }
    }

    public function store() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Método não permitido');
            }

            // Validar dados obrigatórios
            $requiredFields = ['nome_completo', 'email', 'telefone', 'data_nascimento', 'n_identidade', 'relacao_educando', 'senha'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new \Exception("Campo obrigatório: " . str_replace('_', ' ', $field));
                }
            }

            // Verificar se email já existe
            if ($this->usuarioRepository->existsByEmail($_POST['email'])) {
                throw new \Exception('Este email já está registado no sistema.');
            }

            // Verificar se número de identidade já existe
            if ($this->encarregadoRepository->existsByNIdentidade($_POST['n_identidade'])) {
                throw new \Exception('Este número de identidade já está registado.');
            }

            // Validar relação com educando (enum)
            $relacaoValida = ['pai', 'mae', 'tio', 'avo', 'responsavel', 'outro'];
            if (!in_array($_POST['relacao_educando'], $relacaoValida)) {
                throw new \Exception('Relação com educando inválida.');
            }

            // Criar usuário primeiro
            $usuarioData = [
                'nome_completo' => trim($_POST['nome_completo']),
                'email' => trim($_POST['email']),
                'senha' => password_hash($_POST['senha'], PASSWORD_DEFAULT),
                'telefone' => trim($_POST['telefone']),
                'telefone_alternativo' => trim($_POST['telefone_alternativo'] ?? ''),
                'perfil' => 'encarregado',
                'endereco' => trim($_POST['endereco'] ?? ''),
                'foto' => null
            ];

            $usuarioId = $this->usuarioRepository->create($usuarioData);

            if (!$usuarioId) {
                throw new \Exception('Erro ao criar usuário.');
            }

            // Criar encarregado
            $encarregadoData = [
                'id_usuario' => $usuarioId,
                'data_nascimento' => $_POST['data_nascimento'],
                'n_identidade' => trim($_POST['n_identidade']),
                'profissao' => trim($_POST['profissao'] ?? ''),
                'relacao_educando' => trim($_POST['relacao_educando'] ?? '')
            ];

            $encarregadoId = $this->encarregadoRepository->create($encarregadoData);

            if (!$encarregadoId) {
                // Se falhar, remover o usuário criado
                $this->usuarioRepository->delete($usuarioId);
                throw new \Exception('Erro ao criar encarregado.');
            }

            // Log da atividade
            Helpers::logActivity('encarregado_criado', "Encarregado '{$_POST['nome_completo']}' criado com ID: {$encarregadoId}");

            Helpers::flashMessage('Encarregado criado com sucesso!', 'success');
            header('Location: /admin/encarregados');
            exit;

        } catch (\Exception $e) {
            error_log("Erro ao criar encarregado: " . $e->getMessage());
            Helpers::flashMessage($e->getMessage(), 'danger');
            header('Location: /admin/encarregados');
            exit;
        }
    }

    public function update($id) {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Método não permitido');
            }

            // Buscar encarregado atual
            $encarregadoAtual = $this->encarregadoRepository->findById($id);
            if (!$encarregadoAtual) {
                throw new \Exception('Encarregado não encontrado.');
            }

            // Validar dados obrigatórios
            $requiredFields = ['nome_completo', 'email', 'telefone', 'data_nascimento', 'n_identidade', 'relacao_educando'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    throw new \Exception("Campo obrigatório: " . str_replace('_', ' ', $field));
                }
            }

            // Verificar se email já existe (exceto para o usuário atual)
            if ($this->usuarioRepository->existsByEmail($_POST['email'], $encarregadoAtual['id_usuario'])) {
                throw new \Exception('Este email já está registado no sistema.');
            }

            // Verificar se número de identidade já existe (exceto para o encarregado atual)
            if ($this->encarregadoRepository->existsByNIdentidade($_POST['n_identidade'], $id)) {
                throw new \Exception('Este número de identidade já está registado.');
            }

            // Validar relação com educando (enum)
            $relacaoValida = ['pai', 'mae', 'tio', 'avo', 'responsavel', 'outro'];
            if (!in_array($_POST['relacao_educando'], $relacaoValida)) {
                throw new \Exception('Relação com educando inválida.');
            }

            // Atualizar usuário
            $usuarioData = [
                'nome_completo' => trim($_POST['nome_completo']),
                'email' => trim($_POST['email']),
                'telefone' => trim($_POST['telefone']),
                'telefone_alternativo' => trim($_POST['telefone_alternativo'] ?? ''),
                'endereco' => trim($_POST['endereco'] ?? '')
            ];

            // Se senha foi fornecida, atualizar
            if (!empty($_POST['senha'])) {
                $usuarioData['senha'] = password_hash($_POST['senha'], PASSWORD_DEFAULT);
            }

            $this->usuarioRepository->update($encarregadoAtual['id_usuario'], $usuarioData);

            // Atualizar encarregado
            $encarregadoData = [
                'data_nascimento' => $_POST['data_nascimento'],
                'n_identidade' => trim($_POST['n_identidade']),
                'profissao' => trim($_POST['profissao'] ?? ''),
                'relacao_educando' => trim($_POST['relacao_educando'] ?? '')
            ];

            $this->encarregadoRepository->update($id, $encarregadoData);

            // Log da atividade
            Helpers::logActivity('encarregado_atualizado', "Encarregado '{$_POST['nome_completo']}' atualizado (ID: {$id})");

            Helpers::flashMessage('Encarregado atualizado com sucesso!', 'success');
            header('Location: /admin/encarregados');
            exit;

        } catch (\Exception $e) {
            error_log("Erro ao atualizar encarregado: " . $e->getMessage());
            Helpers::flashMessage($e->getMessage(), 'danger');
            header('Location: /admin/encarregados');
            exit;
        }
    }

    public function destroy($id) {
        try {
            // Buscar encarregado
            $encarregado = $this->encarregadoRepository->findById($id);
            if (!$encarregado) {
                throw new \Exception('Encarregado não encontrado.');
            }

            // Deletar encarregado primeiro (por causa da foreign key)
            $this->encarregadoRepository->delete($id);

            // Deletar usuário
            $this->usuarioRepository->delete($encarregado['id_usuario']);

            // Log da atividade
            Helpers::logActivity('encarregado_deletado', "Encarregado '{$encarregado['nome_completo']}' deletado (ID: {$id})");

            Helpers::flashMessage('Encarregado deletado com sucesso!', 'success');

        } catch (\Exception $e) {
            error_log("Erro ao deletar encarregado: " . $e->getMessage());
            Helpers::flashMessage('Erro ao deletar encarregado.', 'danger');
        }

        header('Location: /admin/encarregados');
        exit;
    }

    public function show($id) {
        try {
            $encarregado = $this->encarregadoRepository->findById($id);
            if (!$encarregado) {
                Helpers::flashMessage('Encarregado não encontrado.', 'danger');
                header('Location: /admin/encarregados');
                exit;
            }

            echo $this->view('admin/encarregado-detalhes', [
                'userName' => $_SESSION['user_nome'] ?? 'Admin',
                'title' => "Encarregado | Escola Conectada",
                'flashMessages' => Helpers::getFlashMessages(),
                'encarregado' => $encarregado
            ]);
        } catch (\Exception $e) {
            error_log("Erro ao mostrar encarregado: " . $e->getMessage());
            Helpers::flashMessage('Erro ao carregar detalhes do encarregado.', 'danger');
            header('Location: /admin/encarregados');
            exit;
        }
    }
}