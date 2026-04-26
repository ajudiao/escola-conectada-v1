<?php

namespace App\Repositories;

use App\Core\Database;

class ClasseRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findAll()
    {
        $stmt = $this->db->query("SELECT c.id, c.nome, c.descricao, COUNT(DISTINCT cd.disciplina_id) as num_disciplinas, COUNT(DISTINCT t.id) as num_turmas FROM classe c LEFT JOIN classe_disciplina cd ON c.id = cd.classe_id LEFT JOIN turma t ON t.id_classe = c.id GROUP BY c.id ORDER BY c.nome");
        return $stmt->fetchAll();
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM classe WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findAllDisciplinas()
    {
        $stmt = $this->db->query("SELECT id, nome FROM disciplina ORDER BY nome");
        return $stmt->fetchAll();
    }

    public function existsByNome($nome, $excludeId = null)
    {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM classe WHERE nome = ? AND id != ?");
            $stmt->execute([$nome, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM classe WHERE nome = ?");
            $stmt->execute([$nome]);
        }
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("INSERT INTO classe (nome, descricao, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        $stmt->execute([$data['nome'], $data['descricao']]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("UPDATE classe SET nome = ?, descricao = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$data['nome'], $data['descricao'], $id]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM classe WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function getDisciplinasByClasse($classeId)
    {
        $stmt = $this->db->prepare("SELECT d.id, d.nome FROM disciplina d INNER JOIN classe_disciplina cd ON d.id = cd.disciplina_id WHERE cd.classe_id = ? ORDER BY d.nome");
        $stmt->execute([$classeId]);
        return $stmt->fetchAll();
    }

    public function getTurmasByClasse($classeId)
    {
        $stmt = $this->db->prepare("SELECT id, codigo, sala, turno, ano_lectivo, quantidade_alunos FROM turma WHERE id_classe = ? ORDER BY codigo");
        $stmt->execute([$classeId]);
        return $stmt->fetchAll();
    }

    public function associateDisciplinas($classeId, $disciplinaIds)
    {
        // Delete existing
        $stmt = $this->db->prepare("DELETE FROM classe_disciplina WHERE classe_id = ?");
        $stmt->execute([$classeId]);

        // Insert new
        $stmt = $this->db->prepare("INSERT INTO classe_disciplina (classe_id, disciplina_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        foreach ($disciplinaIds as $discId) {
            $stmt->execute([$classeId, $discId]);
        }
    }
}