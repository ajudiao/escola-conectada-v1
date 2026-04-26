<?php
namespace App\Repositories;

use App\Core\Database;

class EncarregadoRepository
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findAll()
    {
        $stmt = $this->db->prepare("
            SELECT e.*, u.nome_completo, u.email, u.telefone
            FROM encarregado e
            JOIN usuarios u ON e.id_usuario = u.id
            ORDER BY u.nome_completo
        ");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("
            SELECT e.*, u.nome_completo, u.email, u.telefone
            FROM encarregado e
            JOIN usuarios u ON e.id_usuario = u.id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function findByCodigo($codigo)
    {
        // Como o código não existe mais no modelo, vou buscar por nome ou criar um método alternativo
        // Por enquanto, vou assumir que o código é o id do encarregado
        return $this->findById($codigo);
    }

    public function findByUsuarioId($usuarioId)
    {
        $stmt = $this->db->prepare("
            SELECT e.*, u.nome as nome_completo, u.email, u.telefone
            FROM encarregado e
            JOIN usuarios u ON e.id_usuario = u.id
            WHERE e.id_usuario = ?
        ");
        $stmt->execute([$usuarioId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function create($data)
    {
        $stmt = $this->db->prepare("
            INSERT INTO encarregado (id_usuario, data_nascimento, n_identidade, profissao, relacao_educando, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $data['id_usuario'],
            $data['data_nascimento'],
            $data['n_identidade'],
            $data['profissao'],
            $data['relacao_educando']
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare("
            UPDATE encarregado
            SET data_nascimento = ?, n_identidade = ?, profissao = ?, relacao_educando = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $data['data_nascimento'],
            $data['n_identidade'],
            $data['profissao'],
            $data['relacao_educando'],
            $id
        ]);
        return $stmt->rowCount();
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM encarregado WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount();
    }

    public function existsByNIdentidade($nIdentidade, $excludeId = null)
    {
        $query = "SELECT COUNT(*) FROM encarregado WHERE n_identidade = ?";
        $params = [$nIdentidade];

        if ($excludeId) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
}