<?php

namespace App\Repositories;

use App\Core\Database;
use App\Models\Venda;
use DateTime;
use PDO;

class VendaRepository
{
    private PDO $conn;

    public function __construct()
    {
        // Usa o singleton do Database
        $this->conn = Database::getInstance();
    }


    public function findAllWithClient(): array
    {
        $stmt = $this->conn->query("SELECT 
        ven.id,
        cl.nome_completo AS cliente, 
        ma.nome AS marca, 
        ve.modelo, 
        us.nome AS vendedor, 
        ven.valor_pago, 
        ven.data_venda, 
        ven.status, 
        ven.observacoes 
        FROM vendas ven 
        JOIN clientes cl ON ven.id_cliente = cl.id 
        JOIN veiculos ve ON ven.id_veiculo = ve.id 
        JOIN usuarios us ON ven.id_vendedor = us.id 
        JOIN marcas ma ON ma.id = ve.id_marca
        ORDER BY ven.data_venda DESC
    ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentSales(int $limit = 4): array
    {
        $stmt = $this->conn->prepare("SELECT 
            ven.id,
            cl.nome_completo AS cliente, 
            ma.nome AS marca, 
            ve.modelo, 
            ven.valor_pago, 
            ven.data_venda, 
            ven.status 
            FROM vendas ven 
            JOIN clientes cl ON ven.id_cliente = cl.id 
            JOIN veiculos ve ON ven.id_veiculo = ve.id 
            JOIN marcas ma ON ma.id = ve.id_marca
            ORDER BY ven.data_venda DESC
            LIMIT :limit");
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesForReport(string $startDate, string $endDate): array
    {
        $stmt = $this->conn->prepare("SELECT 
            ven.id,
            cl.nome_completo AS cliente,
            CONCAT(ma.nome, ' ', ve.modelo) AS produto,
            ven.valor_pago,
            DATE_FORMAT(ven.data_venda, '%d/%m/%Y') AS data_venda,
            ven.status
            FROM vendas ven
            JOIN clientes cl ON ven.id_cliente = cl.id
            JOIN veiculos ve ON ven.id_veiculo = ve.id
            JOIN marcas ma ON ma.id = ve.id_marca
            WHERE ven.data_venda BETWEEN :start AND :end
            ORDER BY ven.data_venda DESC");

        $stmt->execute([
            ':start' => $startDate,
            ':end' => $endDate
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesCount(string $startDate, string $endDate): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM vendas WHERE data_venda BETWEEN :start AND :end");
        $stmt->execute([
            ':start' => $startDate,
            ':end' => $endDate
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function getRevenue(string $startDate, string $endDate): float
    {
        $stmt = $this->conn->prepare("SELECT COALESCE(SUM(valor_pago), 0) FROM vendas WHERE data_venda BETWEEN :start AND :end");
        $stmt->execute([
            ':start' => $startDate,
            ':end' => $endDate
        ]);

        return (float) $stmt->fetchColumn();
    }

    public function getAverageSale(string $startDate, string $endDate): float
    {
        $stmt = $this->conn->prepare("SELECT COALESCE(AVG(valor_pago), 0) FROM vendas WHERE data_venda BETWEEN :start AND :end");
        $stmt->execute([
            ':start' => $startDate,
            ':end' => $endDate
        ]);

        return (float) $stmt->fetchColumn();
    }

    public function getFinancialReport(string $startDate, string $endDate): array
    {
        $rows = $this->getSalesForReport($startDate, $endDate);
        $stmt = $this->conn->prepare("SELECT 
            COUNT(*) AS total_vendas,
            COALESCE(SUM(valor_pago), 0) AS receita,
            COALESCE(AVG(valor_pago), 0) AS media
            FROM vendas
            WHERE data_venda BETWEEN :start AND :end");
        $stmt->execute([
            ':start' => $startDate,
            ':end' => $endDate
        ]);
        $summary = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_vendas' => (int)$summary['total_vendas'],
            'receita' => (float)$summary['receita'],
            'media' => (float)$summary['media'],
            'rows' => $rows,
        ];
    }

    public function getPerformanceSummary(string $startDate, string $endDate): array
    {
        $stmt = $this->conn->prepare("SELECT 
            COUNT(*) AS total_vendas,
            COALESCE(SUM(valor_pago), 0) AS receita_total,
            COALESCE(AVG(valor_pago), 0) AS ticket_medio
            FROM vendas
            WHERE data_venda BETWEEN :start AND :end");
        $stmt->execute([
            ':start' => $startDate,
            ':end' => $endDate
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findVendaById(int $id): ?array
    {
        $sql = "SELECT 
                ven.id,
                cl.nome_completo AS cliente, 
                cl.email,
                cl.telefone,
                cl.identidade,
                ma.nome AS marca, 
                ve.modelo, 
                ve.cor,
                ve.quilometragem,
                ve.transmissao,
                ve.preco AS valor_veiculo,
                us.nome AS vendedor, 
                us.email AS vendedor_email,
                us.telefone AS vendedor_telefone,
                ven.valor_pago, 
                ven.desconto,
                ven.metodo_pagamento,
                ven.data_venda, 
                ven.status, 
                ven.observacoes 
            FROM vendas ven 
            JOIN clientes cl ON ven.id_cliente = cl.id 
            JOIN veiculos ve ON ven.id_veiculo = ve.id 
            JOIN usuarios us ON ven.id_vendedor = us.id 
            JOIN marcas ma ON ma.id = ve.id_marca
            WHERE ven.id = :id
            LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':id' => $id
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    // Criar nova venda
    public function create(Venda $venda): int|false
    {
        $stmt = $this->conn->prepare("
        INSERT INTO vendas 
        (id_cliente, id_veiculo, id_vendedor, valor_pago, desconto, metodo_pagamento, data_venda, status, observacoes)
        VALUES 
        (:id_cliente, :id_veiculo, :id_vendedor, :valor_pago, :desconto, :metodo_pagamento, :data_venda, :status, :observacoes)
        ");

        $success = $stmt->execute([
            ':id_cliente'   => $venda->id_cliente,
            ':id_veiculo'   => $venda->id_veiculo,
            ':id_vendedor'  => (int)$_SESSION['user_id'],
            ':valor_pago'  => $venda->valorPago,
            ':desconto'     => $venda->desconto,
            ':metodo_pagamento' => $venda->metodo_pagamento,
            ':data_venda'   => $venda->data_venda,
            ':status' => $venda->status,
            ':observacoes'  => $venda->observacoes
        ]);

        if ($success) {
            return (int) $this->conn->lastInsertId();
        }

        return false;
    }

    // ✏️ Atualizar venda
    public function update(Venda $venda): bool
    {
        $stmt = $this->conn->prepare("
            UPDATE venda SET
                id_cliente = :id_cliente,
                id_veiculo = :id_veiculo,
                id_vendedor = :id_vendedor,
                valor_pago = :valor_pago,
                desconto = :desconto,
                metodo_pagamento = :metodo_pagamento,
                data_venda = :data_venda,
                observacoes = :observacoes
            WHERE id = :id
        ");

        return $stmt->execute([
            ':id'           => $venda->id,
            ':id_cliente'   => $venda->id_cliente,
            ':id_veiculo'   => $venda->id_veiculo,
            ':id_vendedor'  => $venda->id_vendedor,
            ':valor_pago'  => $venda->valorPago,
            ':desconto'     => $venda->desconto,
            ':metodo_pagamento' => $venda->metodo_pagamento,
            ':data_venda'   => $venda->data_venda,
            ':observacoes'  => $venda->observacoes
        ]);
    }

    public function getTheNumbersSeles(): int
    {
        $stmt = $this->conn->query("SELECT COUNT(*) FROM vendas");
        return (int) $stmt->fetchColumn();
    }

    // Deletar venda
    public function delete(int $id): bool
    {
        $stmt = $this->conn->prepare("DELETE FROM venda WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function carroJaVendido(int $carroId): bool
    {
        $sql = "SELECT COUNT(*) FROM vendas WHERE id_veiculo = :carro_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':carro_id', $carroId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() > 0;
    }

    // Buscar vendas dos últimos 12 meses agrupadas por mês
    public function getSalesByMonth(int $months = 12): array
    {
        $sql = "SELECT 
                DATE_FORMAT(ven.data_venda, '%Y-%m') AS mes,
                MONTHNAME(ven.data_venda) AS nome_mes,
                MONTH(ven.data_venda) AS numero_mes,
                COUNT(*) AS quantidade,
                SUM(ven.valor_pago) AS total
            FROM vendas ven
            WHERE ven.data_venda >= DATE_SUB(NOW(), INTERVAL :months MONTH)
            AND ven.status = 'Concluido'
            GROUP BY mes, nome_mes, numero_mes
            ORDER BY ven.data_venda ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':months' => $months]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar vendas de um mês específico agrupadas por dia
    public function getSalesByDay(int $year, int $month): array
    {
        $sql = "SELECT 
                DATE_FORMAT(ven.data_venda, '%Y-%m-%d') AS dia,
                DAY(ven.data_venda) AS numero_dia,
                COUNT(*) AS quantidade,
                SUM(ven.valor_pago) AS total
            FROM vendas ven
            WHERE YEAR(ven.data_venda) = :year
            AND MONTH(ven.data_venda) = :month
            AND ven.status = 'Concluido'
            GROUP BY dia, numero_dia
            ORDER BY ven.data_venda ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':year' => $year, ':month' => $month]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Buscar o último dia do mês com vendas (ou dia atual se for mês atual)
    public function getLastDayWithSales(int $year, int $month): int
    {
        $currentDate = new DateTime();
        $selectedDate = new DateTime("$year-$month-01");
        
        // Se for o mês atual, usar dia de hoje
        if ($selectedDate->format('Y-m') === $currentDate->format('Y-m')) {
            return (int)$currentDate->format('d');
        }
        
        // Caso contrário, usar o último dia do mês
        $lastDay = new DateTime("$year-$month-01");
        $lastDay->modify('+1 month -1 day');
        return (int)$lastDay->format('d');
    }

    // Buscar marcas mais vendidas
    public function getTopBrands(int $limit = 5): array
    {
        $sql = "SELECT 
                m.nome AS marca,
                COUNT(ven.id) AS quantidade,
                SUM(ven.valor_pago) AS total
            FROM vendas ven
            JOIN veiculos ve ON ven.id_veiculo = ve.id
            JOIN marcas m ON ve.id_marca = m.id
            WHERE ven.status = 'Concluido'
            GROUP BY m.id, m.nome
            ORDER BY quantidade DESC
            LIMIT :limit";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function search(array $filters): array
    {
        $sql = "SELECT 
                hc.id AS historico_id,
                c.nome_completo AS cliente,
                
                v.modelo AS carro,
                v.ano,
                m.nome AS marca,
                
                hc.data_compra,
                hc.preco_compra,
                hc.metodo_pagamento,
                hc.status
            FROM historico_compras hc
            JOIN clientes c ON hc.cliente_id = c.id
            JOIN veiculos v ON hc.carro_id = v.id
            JOIN marcas m ON v.id_marca = m.id
            WHERE 1=1";

        $params = [];

        // Busca por cliente ou carro
        if (!empty($filters['nome'])) {
            $sql .= " AND (
            c.nome_completo LIKE :cliente 
            OR v.modelo LIKE :veiculo
        )";
            $params[':cliente'] = '%' . $filters['nome'] . '%';
            $params[':veiculo'] = '%' . $filters['nome'] . '%';
        }

        // Filtro por status
        if (!empty($filters['status'])) {
            $sql .= " AND hc.status = :status";
            $params[':status'] = $filters['status'];
        }

        $sql .= " ORDER BY hc.data_compra DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
