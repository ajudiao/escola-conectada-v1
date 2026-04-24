<?php

namespace App\Models;

class Venda
{
    public ?int $id;
    public int $id_cliente;
    public int $id_veiculo;
    public int $id_vendedor = 0;
    public float $valorPago;
    public float $desconto;
    public string $metodo_pagamento;
    public string $data_venda;
    public string $status;
    public ?string $observacoes;

    public function __construct(array $data = [])
    {
        $this->id = isset($data['id']) ? (int)$data['id'] : null;
        $this->id_cliente = isset($data['id_cliente']) ? (int)$data['id_cliente'] : 0;
        $this->id_veiculo = isset($data['id_veiculo']) ? (int)$data['id_veiculo'] : 0;

        $valorVeiculo = isset($data['valor_veiculo']) ? (float)$data['valor_veiculo'] : 0.0;
        $this->desconto = isset($data['desconto']) ? (float)$data['desconto'] : 0.0;
        $this->metodo_pagamento = $data['metodo_pagamento'] ?? 'Desconecido';

        $taxas = 500; // depois tornar isso dinâmico depois

        // cálculo do total
        $this->valorPago = $valorVeiculo - ($valorVeiculo * ($this->desconto / 100)) + $taxas;

        $this->data_venda = $data['data_venda'] ?? date('Y-m-d');
        $this->status = $data['status'] ?? "Concluido";
        $this->observacoes = $data['observacoes'] ?? null;
    }
}
