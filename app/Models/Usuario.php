<?php

namespace App\Models;

class Usuario
{
    public ?int $id;
    public string $nome_completo;
    public string $email;
    public string $senha;
    public string $telefone;
    public ?string $telefone_alternativo;
    public string $perfil;
    public ?string $foto;
    public ?string $endereco;
    public string $updated_at;
    public string $created_at;

    public function __construct(array $data = [])
    {
        $this->id                   = isset($data['id']) ? (int)$data['id'] : null;
        $this->nome_completo        = $data['nome_completo'] ?? '';
        $this->email                = $data['email'] ?? '';
        $this->senha                = $data['senha'] ?? '';
        $this->telefone             = $data['telefone'] ?? '';
        $this->telefone_alternativo = $data['telefone_alternativo'] ?? null;
        $this->perfil               = $data['perfil'] ?? 'Administrador';
        $this->foto                 = $data['foto'] ?? null;
        $this->endereco             = $data['endereco'] ?? null;
        $this->updated_at           = $data['updated_at'] ?? date('Y-m-d H:i:s');   
        $this->created_at           = $data['created_at'] ?? date('Y-m-d H:i:s');
    }
}