<?php

namespace App\Models;

class Usuario
{
    public ?int $id;
    public string $nome_completo;
    public string $email;
    public string $telefone;
    public string $senha;
    public string $perfil;
    public ?string $foto;
    public string $created_at;

    public function __construct(array $data = [])
    {
        $this->id                   = isset($data['id']) ? (int)$data['id'] : null;
        $this->nome_completo        = $data['nome_completo'] ?? '';
        $this->email                = $data['email'] ?? '';
        $this->telefone             = $data['telefone'] ?? '';
        $this->senha                = $data['senha'] ?? '';
        $this->perfil               = $data['perfil'] ?? 'Administrador';
        $this->created_at           = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->foto                 = $data['foto'] ?? null;
    }
}