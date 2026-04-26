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
        $stmt = $this->db->prepare("SELECT * FROM turma WHERE id = ?");
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
}
