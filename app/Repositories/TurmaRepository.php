<?php

namespace App\Repositories;

use App\Core\Database;

class TurmaRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            "SELECT t.id, t.codigo, t.id_classe, c.nome AS classe, t.sala, t.turno, t.ano_lectivo, t.quantidade_alunos FROM turma t LEFT JOIN classe c ON t.id_classe = c.id ORDER BY t.codigo"
        );
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO turma (codigo, id_classe, sala, turno, ano_lectivo, quantidade_alunos, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())"
        );
        $stmt->execute([
            $data['codigo'],
            $data['id_classe'],
            $data['sala'],
            $data['turno'],
            $data['ano_lectivo'],
            $data['quantidade_alunos'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function existsByCodigo(string $codigo, int $id_classe, int $excludeId = 0): bool
    {
        if ($excludeId > 0) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM turma WHERE codigo = ? AND id_classe = ? AND id != ?");
            $stmt->execute([$codigo, $id_classe, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM turma WHERE codigo = ? AND id_classe = ?");
            $stmt->execute([$codigo, $id_classe]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("
            SELECT t.*, c.nome AS classe
            FROM turma t
            LEFT JOIN classe c ON t.id_classe = c.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE turma SET codigo = ?, id_classe = ?, sala = ?, turno = ?, ano_lectivo = ?, quantidade_alunos = ?, updated_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([
            $data['codigo'],
            $data['id_classe'],
            $data['sala'],
            $data['turno'],
            $data['ano_lectivo'],
            $data['quantidade_alunos'],
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM turma WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAlunosByTurma(int $turmaId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                nome_completo,
                email,
                telefone,
                n_processo,
                data_nascimento,
                sexo,
                nacionalidade,
                id_turma,
                id_encarregado,
                created_at,
                updated_at
            FROM aluno
            WHERE id_turma = ?
            ORDER BY nome_completo
        ");

        $stmt->execute([$turmaId]);
        return $stmt->fetchAll();
    }

    public function getProfessoresByTurma(int $turmaId): array
    {
        $stmt = $this->db->prepare("
            SELECT DISTINCT p.id, u.nome_completo, GROUP_CONCAT(d.nome SEPARATOR ', ') as disciplinas
            FROM professor_turma_disciplina ptd
            INNER JOIN professor p ON ptd.id_professor = p.id
            INNER JOIN usuarios u ON p.id_usuario = u.id
            INNER JOIN disciplina d ON ptd.id_disciplina = d.id
            WHERE ptd.id_turma = ?
            GROUP BY p.id, u.nome_completo
            ORDER BY u.nome_completo
        ");
        $stmt->execute([$turmaId]);
        return $stmt->fetchAll();
    }
}
