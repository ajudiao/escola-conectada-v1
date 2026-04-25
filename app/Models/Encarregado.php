<?php

class Encarregado
{
    public ?int $id;
    public int $id_usuario;

    public string $data_nascimento;
    public string $n_identidade;
    public string $profissao;
    public string $relacao_educando;

    public string $created_at;
    public string $updated_at;

    public function __construct($data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->id_usuario = $data['id_usuario'] ?? 0;
        $this->data_nascimento = $data['data_nascimento'] ?? '';
        $this->n_identidade = $data['n_identidade'] ?? '';
        $this->profissao = $data['profissao'] ?? '';
        $this->relacao_educando = $data['relacao_educando'] ?? '';
        $this->created_at = $data['created_at'] ?? '';
        $this->updated_at = $data['updated_at'] ?? '';
    }
}