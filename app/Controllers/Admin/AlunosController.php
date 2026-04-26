<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Helpers\Helpers;
use App\Repositories\AlunoRepository;
use App\Repositories\TurmaRepository;
use App\Repositories\EncarregadoRepository;

class AlunosController extends Controller
{
    private $alunoRepository;
    private $turmaRepository;
    private $encarregadoRepository;

    public function __construct()
    {
        $this->alunoRepository = new AlunoRepository();
        $this->turmaRepository = new TurmaRepository();
        $this->encarregadoRepository = new EncarregadoRepository();
    }

    public function index()
    {
        $alunos = $this->alunoRepository->findAll();
        $turmas = $this->turmaRepository->findAll();
        $encarregados = $this->encarregadoRepository->findAll();

        echo $this->view('admin/alunos', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Alunos | Escola Conectada",
            'flashMessages' => Helpers::getFlashMessages(),
            'alunos' => $alunos,
            'turmas' => $turmas,
            'encarregados' => $encarregados,
        ]);
    }

    public function store()
    {
        $nome_completo = trim($_POST['nome_completo'] ?? '');
        $n_processo = trim($_POST['n_processo'] ?? '');
        $data_nascimento = trim($_POST['data_nascimento'] ?? '');
        $sexo = trim($_POST['sexo'] ?? '');
        $nacionalidade = trim($_POST['nacionalidade'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $id_turma = (int)($_POST['id_turma'] ?? 0);
        $id_encarregado = (int)($_POST['id_encarregado'] ?? 0);

        if (empty($nome_completo) || empty($n_processo) || empty($data_nascimento) || empty($sexo) || empty($telefone) || $id_turma <= 0 || $id_encarregado <= 0) {
            Helpers::flashMessage('Por favor preencha todos os campos obrigatórios do aluno.', 'error');
            Helpers::redirect('/admin/alunos');
            return;
        }

        if ($this->alunoRepository->existsByNProcesso($n_processo)) {
            Helpers::flashMessage('Já existe um aluno com este número de processo.', 'error');
            Helpers::logActivity('Aluno Duplicado', "Tentativa de criar aluno duplicado '$n_processo'.");
            Helpers::redirect('/admin/alunos');
            return;
        }

        // Verificar se o encarregado existe
        $encarregado = $this->encarregadoRepository->findById($id_encarregado);
        if (!$encarregado) {
            Helpers::flashMessage('Encarregado não encontrado. Selecione um encarregado válido.', 'error');
            Helpers::redirect('/admin/alunos');
            return;
        }

        try {
            $this->alunoRepository->create([
                'nome_completo' => $nome_completo,
                'n_processo' => $n_processo,
                'data_nascimento' => $data_nascimento,
                'sexo' => $sexo,
                'nacionalidade' => $nacionalidade,
                'email' => $email,
                'telefone' => $telefone,
                'id_turma' => $id_turma,
                'id_encarregado' => $id_encarregado,
            ]);
            Helpers::flashMessage('Aluno "' . $nome_completo . '" adicionado com sucesso!', 'success');
            Helpers::logActivity('Aluno Cadastrado', "Aluno '$nome_completo' ($n_processo) cadastrado.");
        } catch (\Exception $e) {
            Helpers::flashMessage('Erro ao adicionar aluno: ' . $e->getMessage(), 'error');
        }

        Helpers::redirect('/admin/alunos');
    }

    public function update($id)
    {
        $nome_completo = trim($_POST['nome_completo'] ?? '');
        $n_processo = trim($_POST['n_processo'] ?? '');
        $data_nascimento = trim($_POST['data_nascimento'] ?? '');
        $sexo = trim($_POST['sexo'] ?? '');
        $nacionalidade = trim($_POST['nacionalidade'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telefone = trim($_POST['telefone'] ?? '');
        $id_turma = (int)($_POST['id_turma'] ?? 0);
        $id_encarregado = (int)($_POST['id_encarregado'] ?? 0);

        if (empty($nome_completo) || empty($n_processo) || empty($data_nascimento) || empty($sexo) || empty($telefone) || $id_turma <= 0 || $id_encarregado <= 0) {
            Helpers::flashMessage('Por favor preencha todos os campos obrigatórios do aluno.', 'error');
            Helpers::redirect('/admin/alunos');
            return;
        }

        if ($this->alunoRepository->existsByNProcesso($n_processo, (int)$id)) {
            Helpers::flashMessage('Já existe outro aluno com este número de processo.', 'error');
            Helpers::logActivity('Aluno Duplicado', "Tentativa de atualizar aluno para número de processo duplicado '$n_processo'.");
            Helpers::redirect('/admin/alunos');
            return;
        }

        // Verificar se o encarregado existe
        $encarregado = $this->encarregadoRepository->findById($id_encarregado);
        if (!$encarregado) {
            Helpers::flashMessage('Encarregado não encontrado. Selecione um encarregado válido.', 'error');
            Helpers::redirect('/admin/alunos');
            return;
        }

        try {
            $this->alunoRepository->update($id, [
                'nome_completo' => $nome_completo,
                'n_processo' => $n_processo,
                'data_nascimento' => $data_nascimento,
                'sexo' => $sexo,
                'nacionalidade' => $nacionalidade,
                'email' => $email,
                'telefone' => $telefone,
                'id_turma' => $id_turma,
                'id_encarregado' => $id_encarregado,
            ]);
            Helpers::flashMessage('Aluno "' . $nome_completo . '" atualizado com sucesso!', 'success');
            Helpers::logActivity('Aluno Atualizado', "Aluno '$nome_completo' ($n_processo) atualizado.");
        } catch (\Exception $e) {
            Helpers::flashMessage('Erro ao atualizar aluno: ' . $e->getMessage(), 'error');
        }

        Helpers::redirect('/admin/alunos');
    }

    public function destroy($id)
    {
        try {
            $aluno = $this->alunoRepository->findById($id);
            if (!$aluno) {
                throw new \Exception('Aluno não encontrado.');
            }

            $this->alunoRepository->delete($id);
            Helpers::flashMessage('Aluno "' . $aluno['nome_completo'] . '" eliminado com sucesso!', 'success');
            Helpers::logActivity('Aluno Eliminado', "Aluno '{$aluno['nome_completo']}' ({$aluno['n_processo']}) eliminado.");
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                Helpers::flashMessage('Não é possível eliminar este aluno porque ele possui registros associados.', 'error');
                Helpers::logActivity('Tentativa de Eliminação', "Tentativa falhada de eliminar aluno '{$aluno['nome_completo']}' - possui registros associados.");
            } else {
                Helpers::flashMessage('Erro ao eliminar aluno: ' . $e->getMessage(), 'error');
            }
        } catch (\Exception $e) {
            Helpers::flashMessage('Erro ao eliminar aluno: ' . $e->getMessage(), 'error');
        }

        Helpers::redirect('/admin/alunos');
    }

    public function show()
    {
        echo $this->view('admin/aluno-detalhes', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Classe | Escola Conectada",
        ]);
    }
}

