<?php

namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Helpers\Helpers;
use App\Repositories\TurmaRepository;
use App\Repositories\ClasseRepository;

class TurmaController extends Controller {
    private $turmaRepository;
    private $classeRepository;

    public function __construct()
    {
        $this->turmaRepository = new TurmaRepository();
        $this->classeRepository = new ClasseRepository();
    }

    public function index()
    {
        $turmas = $this->turmaRepository->findAll();
        $classes = $this->classeRepository->findAll();

        echo $this->view('admin/turmas', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Turmas | Escola Conectada",
            'flashMessages' => Helpers::getFlashMessages(),
            'turmas' => $turmas,
            'classes' => $classes,
        ]);
    }

    public function store()
    {
        $codigo = trim($_POST['codigo'] ?? '');
        $id_classe = (int)($_POST['id_classe'] ?? 0);
        $sala = trim($_POST['sala'] ?? '');
        $turno = trim($_POST['turno'] ?? '');
        $ano_lectivo = trim($_POST['ano_lectivo'] ?? '');
        $quantidade_alunos = (int)($_POST['quantidade_alunos'] ?? 0);

        if (empty($codigo) || $id_classe <= 0 || empty($sala) || empty($turno) || empty($ano_lectivo)) {
            Helpers::flashMessage('Por favor preencha todos os campos obrigatórios da turma.', 'error');
            Helpers::redirect('/admin/turmas');
            return;
        }

        if ($this->turmaRepository->existsByCodigo($codigo, $id_classe)) {
            Helpers::flashMessage('Já existe uma turma com este código nesta classe.', 'error');
            Helpers::logActivity('Turma Duplicada', "Tentativa de criar turma duplicada '$codigo' na classe ID $id_classe.");
            Helpers::redirect('/admin/turmas');
            return;
        }

        try {
            $this->turmaRepository->create([
                'codigo' => $codigo,
                'id_classe' => $id_classe,
                'sala' => $sala,
                'turno' => $turno,
                'ano_lectivo' => $ano_lectivo,
                'quantidade_alunos' => $quantidade_alunos,
            ]);
            Helpers::flashMessage('Turma "' . $codigo . '" criada com sucesso!', 'success');
            Helpers::logActivity('Turma Criada', "Turma '$codigo' criada.");
        } catch (\Exception $e) {
            Helpers::flashMessage('Erro ao criar turma: ' . $e->getMessage(), 'error');
        }

        Helpers::redirect('/admin/turmas');
    }

    public function update($id)
    {
        $codigo = trim($_POST['codigo'] ?? '');
        $id_classe = (int)($_POST['id_classe'] ?? 0);
        $sala = trim($_POST['sala'] ?? '');
        $turno = trim($_POST['turno'] ?? '');
        $ano_lectivo = trim($_POST['ano_lectivo'] ?? '');
        $quantidade_alunos = (int)($_POST['quantidade_alunos'] ?? 0);

        if (empty($codigo) || $id_classe <= 0 || empty($sala) || empty($turno) || empty($ano_lectivo)) {
            Helpers::flashMessage('Por favor preencha todos os campos obrigatórios da turma.', 'error');
            Helpers::redirect('/admin/turmas');
            return;
        }

        if ($this->turmaRepository->existsByCodigo($codigo, $id_classe, (int)$id)) {
            Helpers::flashMessage('Já existe outra turma com este código nesta classe.', 'error');
            Helpers::logActivity('Turma Duplicada', "Tentativa de atualizar turma para código duplicado '$codigo' na classe ID $id_classe.");
            Helpers::redirect('/admin/turmas');
            return;
        }

        try {
            $this->turmaRepository->update($id, [
                'codigo' => $codigo,
                'id_classe' => $id_classe,
                'sala' => $sala,
                'turno' => $turno,
                'ano_lectivo' => $ano_lectivo,
                'quantidade_alunos' => $quantidade_alunos,
            ]);
            Helpers::flashMessage('Turma "' . $codigo . '" atualizada com sucesso!', 'success');
            Helpers::logActivity('Turma Atualizada', "Turma '$codigo' atualizada.");
        } catch (\Exception $e) {
            Helpers::flashMessage('Erro ao atualizar turma: ' . $e->getMessage(), 'error');
        }

        Helpers::redirect('/admin/turmas');
    }

    public function destroy($id)
    {
        try {
            $turma = $this->turmaRepository->findById($id);
            if (!$turma) {
                throw new \Exception('Turma não encontrada.');
            }

            $this->turmaRepository->delete($id);
            Helpers::flashMessage('Turma "' . $turma['codigo'] . '" eliminada com sucesso!', 'success');
            Helpers::logActivity('Turma Eliminada', "Turma '{$turma['codigo']}' eliminada.");
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                Helpers::flashMessage('Não é possível eliminar esta turma porque ela está associada a alunos.', 'error');
                Helpers::logActivity('Tentativa de Eliminação', "Tentativa falhada de eliminar turma '{$turma['codigo']}' - associada a alunos.");
            } else {
                Helpers::flashMessage('Erro ao eliminar turma: ' . $e->getMessage(), 'error');
            }
        } catch (\Exception $e) {
            Helpers::flashMessage('Erro ao eliminar turma: ' . $e->getMessage(), 'error');
        }

        Helpers::redirect('/admin/turmas');
    }

    public function show($id)
    {
        $turma = $this->turmaRepository->findById($id);

        if (!$turma) {
            Helpers::flashMessage('Turma não encontrada.', 'error');
            Helpers::redirect('/admin/turmas');
            return;
        }

        // Buscar alunos da turma
        $alunos = $this->turmaRepository->getAlunosByTurma($id);

        // Buscar professores/professores da turma
        $professores = $this->turmaRepository->getProfessoresByTurma($id);

        // Buscar todas as classes para o modal
        $classes = $this->classeRepository->findAll();

        echo $this->view('admin/turma_show', [
            'turma' => $turma,
            'alunos' => $alunos,
            'professores' => $professores,
            'classes' => $classes,
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Detalhes da Turma | Escola Conectada",
            'flashMessages' => Helpers::getFlashMessages(),
        ]);
    }

}