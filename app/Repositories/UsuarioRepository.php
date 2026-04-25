<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Usuario;
use PDO;

class UsuarioRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance();
    }

    /**
     * Buscar usuário por email (LOGIN)
     */
    public function findByEmail(string $email): ?Usuario
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new Usuario($data) : null;
    }

    /**
     * Criar usuário
     */
    public function create(Usuario $usuario): int
    {
        $sql = "INSERT INTO usuarios (
                    nome, email, telefone, senha, perfil, created_at, foto
                ) VALUES (
                    :nome, :email, :telefone, :senha, :perfil, :created_at, :foto
                )";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'nome'       => $usuario->nome,
            'email'      => $usuario->email,
            'telefone'   => $usuario->telefone,
            'senha'      => $usuario->senha,
            'perfil'     => $usuario->perfil,
            'created_at' => $usuario->created_at,
            'foto'       => $usuario->foto
        ]);

        return (int)$this->conn->lastInsertId();
    }

    /**
     * Verificar duplicidade (email)
     */
    public function existsByEmail(string $email): bool
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);

        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Buscar todos os usuários (admins)
     */
    public function getAll(): array
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE perfil IN ('Administrador', 'Gerente') ORDER BY created_at DESC");
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $usuarios = [];

        foreach ($data as $row) {
            $usuarios[] = new Usuario($row);
        }

        return $usuarios;
    }

    /**
     * Buscar usuário por ID
     */
    public function findById(int $id): ?Usuario
    {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new Usuario($data) : null;
    }

    /**
     * Atualizar usuário
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE usuarios SET
                    nome = :nome,
                    email = :email,
                    telefone = :telefone,
                    perfil = :perfil,
                    senha = :senha,
                    foto = :foto
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'id'       => $id,
            'nome'     => $data['nome'],
            'email'    => $data['email'],
            'telefone' => $data['telefone'],
            'perfil'   => $data['perfil'],
            'senha'    => $data['senha'],
            'foto'     => $data['foto']
        ]);
    }

    /**
     * Buscar todos os professores com detalhes
     */
    public function getProfessores(): array
    {
        $sql = "SELECT 
                    p.id,
                    u.nome_completo AS nome_completo,
                    u.email,
                    u.telefone,
                    p.grau_academico,
                    p.area_formacao,
                    p.instituicao_formacao,
                    p.ano_conclusao,
                    p.id_usuario,
                    GROUP_CONCAT(CONCAT(ptd.id_turma, ':', ptd.id_disciplina) SEPARATOR ',') AS turmas_disciplinas
                FROM professor p
                INNER JOIN usuarios u ON u.id = p.id_usuario
                LEFT JOIN professor_turma_disciplina ptd ON ptd.id_professor = u.id
                GROUP BY p.id, u.nome_completo, u.email, u.telefone, p.grau_academico, p.area_formacao, p.instituicao_formacao, p.ano_conclusao, p.id_usuario
                ORDER BY p.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Criar professor
     */
    public function createProfessor(int $usuarioId, array $data): int
    {
        $sql = "INSERT INTO professor (
                    id_usuario, 
                    grau_academico, 
                    area_formacao, 
                    instituicao_formacao, 
                    ano_conclusao,
                    created_at
                ) VALUES (
                    :id_usuario,
                    :grau_academico,
                    :area_formacao,
                    :instituicao_formacao,
                    :ano_conclusao,
                    NOW()
                )";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'id_usuario' => $usuarioId,
            'grau_academico' => $data['grau_academico'],
            'area_formacao' => $data['area_formacao'],
            'instituicao_formacao' => $data['instituicao_formacao'],
            'ano_conclusao' => $data['ano_conclusao']
        ]);

        return (int)$this->conn->lastInsertId();
    }

    /**
     * Atualizar professor
     */
    public function updateProfessor(int $usuarioId, array $userData, string $senha = ''): bool
    {
        // Atualizar dados do usuário
        if (!empty($senha)) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        }

        $sqlUsuario = "UPDATE usuarios SET
                    nome = :nome,
                    email = :email,
                    telefone = :telefone
                    " . (!empty($senha) ? ", senha = :senha" : "") . "
                WHERE id = :id";

        $params = [
            'id' => $usuarioId,
            'nome' => $userData['nome'],
            'email' => $userData['email'],
            'telefone' => $userData['telefone']
        ];

        if (!empty($senha)) {
            $params['senha'] = $senhaHash;
        }

        $stmt = $this->conn->prepare($sqlUsuario);
        if (!$stmt->execute($params)) {
            return false;
        }

        // Atualizar dados do professor
        $sqlProfessor = "UPDATE professor SET
                    grau_academico = :grau_academico,
                    area_formacao = :area_formacao,
                    instituicao_formacao = :instituicao_formacao,
                    ano_conclusao = :ano_conclusao,
                    updated_at = NOW()
                WHERE id_usuario = :id_usuario";

        $stmt = $this->conn->prepare($sqlProfessor);
        return $stmt->execute([
            'id_usuario' => $usuarioId,
            'grau_academico' => $userData['grau_academico'],
            'area_formacao' => $userData['area_formacao'],
            'instituicao_formacao' => $userData['instituicao_formacao'],
            'ano_conclusao' => $userData['ano_conclusao']
        ]);
    }

    /**
     * Buscar todas as turmas com detalhes da classe
     */
    public function getTurmas(): array
    {
        $sql = "SELECT 
                    t.id,
                    t.codigo,
                    c.id AS id_classe,
                    c.nome AS classe,
                    t.sala,
                    t.turno,
                    t.ano_lectivo,
                    t.quantidade_alunos
                FROM turma t
                JOIN classe c ON c.id = t.id_classe
                ORDER BY c.id, t.codigo";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar disciplinas agrupadas por classes
     */
    public function getDisciplinasGrouped(): array
    {
        $sql = "SELECT 
                    d.id,
                    d.nome AS disciplina,
                    d.descricao,
                    GROUP_CONCAT(DISTINCT c.id ORDER BY c.id SEPARATOR ', ') AS classe_ids,
                    GROUP_CONCAT(DISTINCT c.nome ORDER BY c.id SEPARATOR ', ') AS classes
                FROM disciplina d
                INNER JOIN classe_disciplina cd ON cd.disciplina_id = d.id
                INNER JOIN classe c ON c.id = cd.classe_id
                GROUP BY d.id, d.nome, d.descricao
                ORDER BY d.id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Atribuir professor a turma e disciplina
     */
    public function assignProfessorToTurmaDisciplina(int $professorId, int $turmaId, int $disciplinaId): bool
    {
        $sql = "INSERT INTO professor_turma_disciplina (
                    id_professor, id_turma, id_disciplina, created_at
                ) VALUES (
                    :id_professor, :id_turma, :id_disciplina, NOW()
                ) ON DUPLICATE KEY UPDATE updated_at = NOW()";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'id_professor' => $professorId,
            'id_turma' => $turmaId,
            'id_disciplina' => $disciplinaId
        ]);
    }

    /**
     * Buscar professor por ID
     */
    public function findProfessorById(int $professorId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM professor WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $professorId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Buscar atribuições de um professor (turma e disciplina)
     */
    public function getProfessorAssignments(int $usuarioId): array
    {
        $sql = "SELECT id_turma, id_disciplina FROM professor_turma_disciplina WHERE id_professor = :id_professor";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id_professor' => $usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}