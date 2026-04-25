<?php

class Classe
{
    public ?int $id;
    public string $nome;
    public string $descricao;

    public string $created_at;
    public string $updated_at;

    public function __construct($data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->nome = $data['nome'] ?? '';
        $this->descricao = $data['descricao'] ?? '';
        $this->created_at = $data['created_at'] ?? '';
        $this->updated_at = $data['updated_at'] ?? '';
    }
}