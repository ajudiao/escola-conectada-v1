<?php

class Aluno
{
    public ?int $id;
    public ?int $id_usuario;
    public string $nome_completo;
    public string $n_processo;
    public string $data_nascimento;
    public string $sexo;
    public string $nacionalidade;
    public ?string $email;
    public string $telefone;
    public int $id_turma;
    public int $id_encarregado;

    public string $created_at;
    public string $updated_at;

    public function __construct($data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        
        $this->id_usuario = isset($data['id_usuario']) ? (int)$data['id_usuario'] : null; // Está coluna deve existir na tabela aluno para relacionar com usuário - mas neste projecto o aluno não tem um usuário próprio, então pode ser null, apenas deixamos para futuras melhorias ou atualizações do sistema.

        $this->nome_completo = $data['nome_completo'] ?? '';
        $this->n_processo = $data['n_processo'] ?? '';
        $this->data_nascimento = $data['data_nascimento'] ?? '';
        $this->sexo = $data['sexo'] ?? '';
        $this->nacionalidade = $data['nacionalidade'] ?? '';
        $this->email = $data['email'] ?? null;
        $this->telefone = $data['telefone'] ?? '';
        $this->id_turma = $data['id_turma'] ?? 0;
        $this->id_encarregado = $data['id_encarregado'] ?? 0;
        $this->created_at = $data['created_at'] ?? '';
        $this->updated_at = $data['updated_at'] ?? '';
    }
}