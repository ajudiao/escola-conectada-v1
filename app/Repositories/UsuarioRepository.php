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
    public function create(array $data): int
    {
        $sql = "INSERT INTO usuarios (
                    nome_completo,
                    email,
                    senha,
                    telefone,
                    telefone_alternativo,
                    perfil,
                    foto,
                    endereco,
                    created_at,
                    updated_at
                ) VALUES (
                    :nome_completo,
                    :email,
                    :senha,
                    :telefone,
                    :telefone_alternativo,
                    :perfil,
                    :foto,
                    :endereco,
                    :created_at,
                    :updated_at
                )";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            'nome_completo'       => $data['nome_completo'],
            'email'               => $data['email'],
            'senha'               => $data['senha'],
            'telefone'            => $data['telefone'],
            'telefone_alternativo' => $data['telefone_alternativo'] ?? '',
            'perfil'              => $data['perfil'],
            'foto'                => $data['foto'] ?? null,
            'endereco'            => $data['endereco'] ?? '',
            'created_at'          => $data['created_at'] ?? date('Y-m-d H:i:s'),
            'updated_at'          => $data['updated_at'] ?? date('Y-m-d H:i:s'),
        ]);

        return (int) $this->conn->lastInsertId();
    }

    /**
     * Verificar duplicidade (email)
     */
    public function existsByEmail(string $email, int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
        $params = ['email' => $email];

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

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
                    nome_completo = :nome_completo,
                    email = :email,
                    telefone = :telefone,
                    telefone_alternativo = :telefone_alternativo,
                    endereco = :endereco
                " . (!empty($data['senha']) ? ", senha = :senha" : "") . "
                WHERE id = :id";

        $params = [
            'id' => $id,
            'nome_completo' => $data['nome_completo'],
            'email' => $data['email'],
            'telefone' => $data['telefone'],
            'telefone_alternativo' => $data['telefone_alternativo'] ?? '',
            'endereco' => $data['endereco'] ?? ''
        ];

        if (!empty($data['senha'])) {
            $params['senha'] = password_hash($data['senha'], PASSWORD_DEFAULT);
        }

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Deletar usuário
     */
    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM usuarios WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}