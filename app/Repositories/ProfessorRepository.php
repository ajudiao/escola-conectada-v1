<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class ProfessorRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance();
    }

    /**
     * Buscar todos os professores com detalhes
     */
    public function getAll(): array
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
     * Buscar professores com filtros aplicados
     */
    public function findFiltered(?string $search = null, ?int $disciplinaId = null, ?int $turmaId = null): array
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
                    GROUP_CONCAT(DISTINCT CONCAT(ptd.id_turma, ':', ptd.id_disciplina) SEPARATOR ',') AS turmas_disciplinas
                FROM professor p
                INNER JOIN usuarios u ON u.id = p.id_usuario
                LEFT JOIN professor_turma_disciplina ptd ON ptd.id_professor = p.id";

        $where = [];
        $params = [];

        if ($search) {
            $where[] = "(u.nome_completo LIKE :search OR u.email LIKE :search OR u.telefone LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        if ($disciplinaId) {
            $where[] = "ptd.id_disciplina = :disciplina_id";
            $params['disciplina_id'] = $disciplinaId;
        }

        if ($turmaId) {
            $where[] = "ptd.id_turma = :turma_id";
            $params['turma_id'] = $turmaId;
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " GROUP BY p.id, u.nome_completo, u.email, u.telefone, p.grau_academico, p.area_formacao, p.instituicao_formacao, p.ano_conclusao, p.id_usuario
                  ORDER BY p.id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Criar professor
     */
    public function create(int $usuarioId, array $data): int
    {
        $sql = "INSERT INTO professor (
                    id_usuario, 
                    grau_academico, 
                    area_formacao, 
                    instituicao_formacao, 
                    ano_conclusao,
                    numero_certificado,
                    certificado_pdf,
                    updated_at,
                    created_at
                ) VALUES (
                    :id_usuario,
                    :grau_academico,
                    :area_formacao,
                    :instituicao_formacao,
                    :ano_conclusao,
                    :numero_certificado,
                    :certificado_pdf,
                    NOW(),  
                    NOW()
                )";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'id_usuario' => $usuarioId,
            'grau_academico' => $data['grau_academico'],
            'area_formacao' => $data['area_formacao'],
            'instituicao_formacao' => $data['instituicao_formacao'],
            'ano_conclusao' => $data['ano_conclusao'],
            'numero_certificado' => $data['numero_certificado'] ?? null,
            'certificado_pdf' => $data['certificado_pdf'] ?? null
        ]);

        return (int)$this->conn->lastInsertId();
    }

    /**
     * Atualizar professor
     */
    public function update(int $usuarioId, array $professorData): bool
    {
        $sqlProfessor = "UPDATE professor SET
                    grau_academico = :grau_academico,
                    area_formacao = :area_formacao,
                    instituicao_formacao = :instituicao_formacao,
                    ano_conclusao = :ano_conclusao,
                    numero_certificado = :numero_certificado,
                    certificado_pdf = :certificado_pdf,
                    updated_at = NOW()
                WHERE id_usuario = :id_usuario";

        $stmt = $this->conn->prepare($sqlProfessor);
        return $stmt->execute([
            'id_usuario' => $usuarioId,
            'grau_academico' => $professorData['grau_academico'],
            'area_formacao' => $professorData['area_formacao'],
            'instituicao_formacao' => $professorData['instituicao_formacao'],
            'ano_conclusao' => $professorData['ano_conclusao'],
            'numero_certificado' => $professorData['numero_certificado'] ?? null,
            'certificado_pdf' => $professorData['certificado_pdf'] ?? null
        ]);
    }

    /**
     * Buscar professor por ID com dados do usuário
     */
    public function findById(int $professorId): ?array
    {
        $sql = "SELECT
                    p.*,
                    u.nome_completo,
                    u.email,
                    u.telefone,
                    u.telefone_alternativo,
                    u.endereco,
                    u.created_at AS usuario_created_at,
                    u.updated_at AS usuario_updated_at
                FROM professor p
                INNER JOIN usuarios u ON u.id = p.id_usuario
                WHERE p.id = :id LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $professorId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Atribuir professor a turma e disciplina
     */
    public function assignToTurmaDisciplina(int $professorId, int $turmaId, int $disciplinaId): bool
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
     * Deletar professor e usuário associado
     */
    public function delete(int $professorId): bool
    {
        try {
            // Buscar dados do professor para obter o ID do usuário
            $professor = $this->findById($professorId);
            if (!$professor) {
                return false;
            }

            // Deletar atribuições primeiro
            $this->deleteAssignments($professorId);

            // Deletar professor
            $stmt = $this->conn->prepare("DELETE FROM professor WHERE id = :id");
            $stmt->execute(['id' => $professorId]);

            // Deletar usuário
            $usuarioRepo = new \App\Repositories\UsuarioRepository();
            return $usuarioRepo->delete($professor['id_usuario']);

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Buscar atribuições de um professor (turma e disciplina)
     */
    public function getAssignments(int $usuarioId): array
    {
        $sql = "SELECT id_turma, id_disciplina FROM professor_turma_disciplina WHERE id_professor = :id_professor";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id_professor' => $usuarioId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Buscar todas as turmas com detalhes da classe
     */
    public function getAllTurmas(): array
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
    public function getAllDisciplinasGrouped(): array
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
     * Buscar turmas e disciplinas de um professor específico
     */
    public function getTurmasDisciplinasByProfessor(int $professorId): array
    {
        $sql = "SELECT 
                    t.id AS turma_id,
                    t.codigo AS turma_codigo,
                    t.sala,
                    t.turno,
                    t.ano_lectivo,
                    c.id AS classe_id,
                    c.nome AS classe_nome,
                    d.id AS disciplina_id,
                    d.nome AS disciplina_nome,
                    d.descricao AS disciplina_descricao
                FROM professor_turma_disciplina ptd
                INNER JOIN turma t ON t.id = ptd.id_turma
                INNER JOIN classe c ON c.id = t.id_classe
                INNER JOIN disciplina d ON d.id = ptd.id_disciplina
                WHERE ptd.id_professor = :professor_id
                ORDER BY c.id, t.codigo, d.nome";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['professor_id' => $professorId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
