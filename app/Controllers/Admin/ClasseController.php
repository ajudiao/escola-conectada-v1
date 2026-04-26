<?php

namespace App\Controllers\Admin;
use App\Core\Controller;
use App\Helpers\Helpers;
use App\Repositories\ClasseRepository;
use App\Core\Database;

class ClasseController extends Controller
{
    private $classeRepository;

    public function __construct()
    {
        $this->classeRepository = new ClasseRepository();
    }

    public function index()
    {
        $disciplinas = $this->classeRepository->findAllDisciplinas();
        $classes = $this->classeRepository->findAll();

        echo $this->view('admin/classes', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Classe | Escola Conectada",
            'flashMessages' => Helpers::getFlashMessages(),
            'disciplinas' => $disciplinas,
            'classes' => $classes
        ]);
    }

    public function store()
    {
        $numero = $_POST['numero'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $disciplinas = $_POST['disciplinas'] ?? [];
        $nome = trim($numero) !== '' ? $numero . 'ª Classe' : '';

        if (empty($numero) || empty($nome)) {
            Helpers::flashMessage('Selecione a classe antes de gravar.', 'error');
            Helpers::redirect('/admin/classes');
        }

        if ($this->classeRepository->existsByNome($nome)) {
            Helpers::flashMessage('Já existe outra classe com esse nome', 'error');
            Helpers::redirect('/admin/classes');
        }

        $classeId = $this->classeRepository->create(['nome' => $nome, 'descricao' => $descricao]);
        $this->classeRepository->associateDisciplinas($classeId, $disciplinas);
        Helpers::flashMessage('Classe criada com sucesso', 'success');
        Helpers::logActivity('Classe Criada', "Classe '$nome' foi criada com sucesso.");
        Helpers::redirect('/admin/classes');
    }

    public function edit($id)
    {
        $classe = $this->classeRepository->findById($id);
        if (!$classe) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Classe não encontrada']);
            return;
        }

        $disciplinas = $this->classeRepository->getDisciplinasByClasse($id);

        echo json_encode([
            'success' => true,
            'classe' => $classe,
            'disciplinas' => $disciplinas
        ]);
    }

    public function update($id)
    {
        $numero = $_POST['numero'] ?? '';
        $descricao = $_POST['descricao'] ?? '';
        $disciplinas = $_POST['disciplinas'] ?? [];
        $nome = trim($numero) !== '' ? $numero . 'ª Classe' : '';

        if (empty($numero) || empty($nome)) {
            Helpers::flashMessage('Selecione a classe antes de gravar.', 'error');
            Helpers::redirect('/admin/classes');
        }

        if ($this->classeRepository->existsByNome($nome, $id)) {
            Helpers::flashMessage('Já existe outra classe com esse nome', 'error');
            Helpers::redirect('/admin/classes');
        }

        $this->classeRepository->update($id, ['nome' => $nome, 'descricao' => $descricao]);
        $this->classeRepository->associateDisciplinas($id, $disciplinas);
        Helpers::flashMessage('Classe atualizada com sucesso', 'success');
        Helpers::logActivity('Classe Atualizada', "Classe '$nome' foi atualizada.");
        Helpers::redirect('/admin/classes');
    }

    public function destroy($id)
    {
        try {
            $this->classeRepository->delete($id);
            Helpers::flashMessage('Classe eliminada com sucesso', 'success');
            Helpers::logActivity('Classe Eliminada', "Classe ID $id foi eliminada.");
        } catch (\PDOException $e) {
            if ($e->getCode() == 23000) {
                Helpers::flashMessage('Não é possível eliminar esta classe porque ela está associada a uma ou mais turmas.', 'error');
                Helpers::logActivity('Tentativa de Eliminação', "Tentativa falhada de eliminar classe ID $id - associada a turmas.");
            } else {
                Helpers::flashMessage('Erro ao eliminar classe: ' . $e->getMessage(), 'error');
            }
        }
        Helpers::redirect('/admin/classes');
    }

    public function show($id)
    {
        $classe = $this->classeRepository->findById($id);
        if (!$classe) {
            http_response_code(404);
            echo $this->view('errors/404', [
                'userName' => $_SESSION['user_nome'] ?? 'Admin',
                'title' => 'Classe não encontrada'
            ]);
            return;
        }

        $disciplinas = $this->classeRepository->getDisciplinasByClasse($id);
        $selectedDisciplinaIds = array_column($disciplinas, 'id');
        $turmas = $this->classeRepository->getTurmasByClasse($id);
        $allDisciplinas = $this->classeRepository->findAllDisciplinas();

        $totalAlunos = array_sum(array_column($turmas, 'quantidade_alunos'));
        $anoLectivo = $turmas[0]['ano_lectivo'] ?? '';

        echo $this->view('admin/classe_show', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Classe | Escola Conectada",
            'flashMessages' => Helpers::getFlashMessages(),
            'classe' => $classe,
            'disciplinas' => $disciplinas,
            'turmas' => $turmas,
            'totalAlunos' => $totalAlunos,
            'anoLectivo' => $anoLectivo,
            'allDisciplinas' => $allDisciplinas,
            'selectedDisciplinaIds' => $selectedDisciplinaIds
        ]);
    }
}