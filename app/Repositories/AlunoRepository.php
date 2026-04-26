<?php

namespace App\Repositories;

use App\Core\Database;

class AlunoRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            "SELECT a.id, a.nome_completo, a.n_processo, a.data_nascimento, a.sexo, a.nacionalidade, a.email, a.telefone, a.id_turma, a.id_encarregado, t.codigo AS turma_codigo, u.nome_completo AS encarregado_nome FROM aluno a LEFT JOIN turma t ON a.id_turma = t.id LEFT JOIN encarregado e ON a.id_encarregado = e.id LEFT JOIN usuarios u ON e.id_usuario = u.id ORDER BY a.nome_completo"
        );
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO aluno (nome_completo, n_processo, data_nascimento, sexo, nacionalidade, email, telefone, id_turma, id_encarregado, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
        );
        $stmt->execute([
            $data['nome_completo'],
            $data['n_processo'],
            $data['data_nascimento'],
            $data['sexo'],
            $data['nacionalidade'],
            $data['email'],
            $data['telefone'],
            $data['id_turma'],
            $data['id_encarregado'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM aluno WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE aluno SET nome_completo = ?, n_processo = ?, data_nascimento = ?, sexo = ?, nacionalidade = ?, email = ?, telefone = ?, id_turma = ?, id_encarregado = ?, updated_at = NOW() WHERE id = ?"
        );
        return $stmt->execute([
            $data['nome_completo'],
            $data['n_processo'],
            $data['data_nascimento'],
            $data['sexo'],
            $data['nacionalidade'],
            $data['email'],
            $data['telefone'],
            $data['id_turma'],
            $data['id_encarregado'],
            $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM aluno WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function existsByNProcesso(string $n_processo, int $excludeId = 0): bool
    {
        if ($excludeId > 0) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM aluno WHERE n_processo = ? AND id != ?");
            $stmt->execute([$n_processo, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM aluno WHERE n_processo = ?");
            $stmt->execute([$n_processo]);
        }
        return (int)$stmt->fetchColumn() > 0;
    }
}