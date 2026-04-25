<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Aviso;
use PDO;

class AvisoRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = Database::getInstance();
    }

    /**
     * Criar aviso
     */
    public function create(Aviso $aviso): int
    {
        $sql = "INSERT INTO avisos (
                    titulo, conteudo, destinatario, prioridade, user_id, created_at, updated_at
                ) VALUES (
                    :titulo, :conteudo, :destinatario, :prioridade, :user_id, :created_at, :updated_at
                )";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'titulo'       => $aviso->titulo,
            'conteudo'     => $aviso->conteudo,
            'destinatario' => $aviso->destinatario,
            'prioridade'   => $aviso->prioridade,
            'user_id'      => $aviso->user_id,
            'created_at'   => $aviso->created_at,
            'updated_at'   => $aviso->updated_at,
        ]);

        return (int)$this->conn->lastInsertId();
    }

    /**
     * Buscar todos os avisos
     */
    public function findAll(): array
    {
        $stmt = $this->conn->query("SELECT * FROM avisos ORDER BY created_at DESC");
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $avisos = [];
        foreach ($data as $row) {
            $avisos[] = new Aviso($row);
        }

        return $avisos;
    }

    /**
     * Buscar avisos com filtros
     */
    public function findFiltered(
        ?string $search = null,
        ?string $destinatario = null,
        ?string $prioridade = null
    ): array {
        $sql = "SELECT * FROM avisos WHERE 1=1";
        $params = [];

        // Filtro de busca (título ou conteúdo)
        if (!empty($search)) {
            $sql .= " AND (titulo LIKE :search OR conteudo LIKE :search)";
            $params['search'] = "%{$search}%";
        }

        // Filtro de destinatário
        if (!empty($destinatario)) {
            $sql .= " AND destinatario = :destinatario";
            $params['destinatario'] = $destinatario;
        }

        // Filtro de prioridade
        if (!empty($prioridade)) {
            $sql .= " AND prioridade = :prioridade";
            $params['prioridade'] = $prioridade;
        }

        $sql .= " ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $avisos = [];
        foreach ($data as $row) {
            $avisos[] = new Aviso($row);
        }

        return $avisos;
    }

    /**
     * Buscar aviso por ID
     */
    public function findById(int $id): ?Aviso
    {
        $stmt = $this->conn->prepare("SELECT * FROM avisos WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? new Aviso($data) : null;
    }

    /**
     * Atualizar aviso
     */
    public function update(Aviso $aviso): bool
    {
        $sql = "UPDATE avisos SET 
                    titulo = :titulo,
                    conteudo = :conteudo,
                    destinatario = :destinatario,
                    prioridade = :prioridade,
                    updated_at = :updated_at
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            'id'            => $aviso->id,
            'titulo'        => $aviso->titulo,
            'conteudo'      => $aviso->conteudo,
            'destinatario'  => $aviso->destinatario,
            'prioridade'    => $aviso->prioridade,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Deletar aviso
     */
    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM avisos WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}