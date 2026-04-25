<?php

class ProfessorTurmaDisciplina
{
    public ?int $id;
    public int $id_professor;
    public int $id_turma;
    public int $id_disciplina;

    public string $created_at;
    public string $updated_at;

    public function __construct($data = [])
    {        
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->id_professor = $data['id_professor'] ?? 0;
        $this->id_turma = $data['id_turma'] ?? 0;
        $this->id_disciplina = $data['id_disciplina'] ?? 0;
        $this->created_at = $data['created_at'] ?? '';
        $this->updated_at = $data['updated_at'] ?? '';
    }
}