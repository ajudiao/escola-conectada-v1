<?php

class Turma
{
    public ?int $id;
    public string $codigo;
    public int $id_classe;
    public int $sala;
    public string $turno;
    public int $ano_lectivo;
    public int $quantidade_alunos;

    public string $created_at;
    public string $updated_at;

    public function __construct($data = [])
    {        
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->codigo = $data['codigo'] ?? '';
        $this->id_classe = $data['id_classe'] ?? 0;
        $this->sala = $data['sala'] ?? 0;
        $this->turno = $data['turno'] ?? '';
        $this->ano_lectivo = $data['ano_lectivo'] ?? 0;
        $this->quantidade_alunos = $data['quantidade_alunos'] ?? 0;
        $this->created_at = $data['created_at'] ?? '';
        $this->updated_at = $data['updated_at'] ?? '';
    }
}