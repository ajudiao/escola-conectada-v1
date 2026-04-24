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
}