<?php

namespace App\Models;

class Aviso
{
    public ?int $id;
    public string $titulo;
    public string $conteudo;
    public string $destinatario;
    public string $prioridade;
    public int $user_id;
    public string $created_at;
    public string $updated_at;

    public function __construct(array $data = [])
    {
        $this->id           = isset($data['id']) ? (int)$data['id'] : null;
        $this->titulo       = $data['titulo'] ?? '';
        $this->conteudo     = $data['conteudo'] ?? '';
        $this->destinatario = $data['destinatario'] ?? 'geral';
        $this->prioridade   = $data['prioridade'] ?? 'normal';
        $this->user_id      = isset($data['user_id']) ? (int)$data['user_id'] : 0;
        $this->created_at   = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updated_at   = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }
}