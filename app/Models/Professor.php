<?php

class Professor
{
    public ?int $id;
    public int $id_usuario;

    public string $grau_academico;
    public string $area_formacao;
    public string $instituicao_formacao;
    public ?int $ano_conclusao;
    public ?string $numero_certificado;
    public ?string $certificado_pdf;
    
    public string $created_at;
    public string $updated_at;

    public function __construct($data = [])
    {   
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->id_usuario = $data['id_usuario'] ?? 0;
        $this->grau_academico = $data['grau_academico'] ?? '';
        $this->area_formacao = $data['area_formacao'] ?? '';
        $this->instituicao_formacao = $data['instituicao_formacao'] ?? '';
        $this->ano_conclusao = isset($data['ano_conclusao']) ? (int)$data['ano_conclusao'] : null;
        $this->numero_certificado = $data['numero_certificado'] ?? null;
        $this->certificado_pdf = $data['certificado_pdf'] ?? null;
    }
}