<?php

namespace App\Models;

class Marca
{
    public ?int $id;
    public string $nome;

    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->nome = $data['nome'] ?? '';
    }
}