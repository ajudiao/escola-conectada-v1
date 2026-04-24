<?php

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Repositories\CarRepository;
use App\Repositories\ClienteRepository;
use App\Repositories\VendaRepository;

class DashboardController extends Controller
{
    public function index()
    {
        echo $this->view('admin/dashboard', [
            'userName' => $_SESSION['user_nome'] ?? 'Admin',
            'title' => "Dashboard - Administração | Escola Conectada",
        ]);
    }

    // Endpoint AJAX para buscar vendas de um mês específico
    public function getSalesData()
    {
        header('Content-Type: application/json');
        
        $month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        
        $vendaRepo = new VendaRepository();
        $salesByDay = $vendaRepo->getSalesByDay($year, $month);
        $lastDayWithSales = $vendaRepo->getLastDayWithSales($year, $month);
        
        echo json_encode([
            'sales' => $salesByDay,
            'lastDay' => $lastDayWithSales
        ]);
        exit;
    }

    public function configuracoes()
    {
        // Verificar se é administrador
        if (!isset($_SESSION['user_perfil']) || $_SESSION['user_perfil'] !== 'Administrador') {
            header('Location: /admin');
            exit;
        }

        $this->view('dashboard/configuracoes', [
            'title' => 'Configurações - ' . APP_NAME,
        ]);
    }

    public function backup()
    {
        // Verificar se é administrador
        if (!isset($_SESSION['user_perfil']) || $_SESSION['user_perfil'] !== 'Administrador') {
            header('Location: /admin');
            exit;
        }

        try {
            $database = DB_NAME;
            $backupDir = __DIR__ . '/../../../storage/backups/';

            if (!is_dir($backupDir)) {
                mkdir($backupDir, 0777, true);
            }

            $filename = 'backup_' . $database . '_' . date('Y-m-d_H-i-s') . '.sql';
            $backupPath = $backupDir . $filename;

            $mysqldump = trim(shell_exec('which mysqldump'));
            if (empty($mysqldump)) {
                throw new \Exception('mysqldump não encontrado no servidor.');
            }

            $command = sprintf(
                '%s --host=%s --user=%s --password=%s --single-transaction --quick --skip-lock-tables %s > %s 2>&1',
                escapeshellarg($mysqldump),
                escapeshellarg(DB_HOST),
                escapeshellarg(DB_USER),
                escapeshellarg(DB_PASS),
                escapeshellarg($database),
                escapeshellarg($backupPath)
            );

            exec($command, $output, $returnVar);
            $commandOutput = trim(implode("\n", $output));

            if ($returnVar === 0 && file_exists($backupPath)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Backup criado com sucesso!', 'filename' => $filename]);
            } else {
                throw new \Exception('Erro ao criar o backup. ' . $commandOutput);
            }
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
        exit;
    }

    public function exportCsv()
    {
        // Verificar se é administrador
        if (!isset($_SESSION['user_perfil']) || $_SESSION['user_perfil'] !== 'Administrador') {
            header('Location: /admin');
            exit;
        }

        try {
            $table = $_GET['table'] ?? 'veiculos';
            $pdo = \App\Core\Database::getInstance();

            // Tabelas permitidas
            $allowedTables = ['veiculos', 'clientes', 'vendas', 'usuarios', 'categorias', 'marcas'];
            if (!in_array($table, $allowedTables)) {
                throw new \Exception('Tabela inválida.');
            }

            $stmt = $pdo->query("SELECT * FROM $table");
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($rows)) {
                throw new \Exception('Nenhum dado para exportar.');
            }

            // Header do CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $table . '_' . date('Y-m-d') . '.csv"');

            $output = fopen('php://output', 'w');
            
            // Cabeçalhos
            fputcsv($output, array_keys($rows[0]), ';');
            
            // Dados
            foreach ($rows as $row) {
                fputcsv($output, $row, ';');
            }

            fclose($output);
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
        }
        exit;
    }

    public function deleteAllData()
    {
        // Verificar se é administrador
        if (!isset($_SESSION['user_perfil']) || $_SESSION['user_perfil'] !== 'Administrador') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
            exit;
        }

        $pdo = null;
        try {
            $pdo = \App\Core\Database::getInstance();
            $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

            $tables = [
                'historico_compras',
                'reservas',
                'veiculo_imagens',
                'vendas',
                'veiculos',
                'clientes',
                'noticias',
                'categorias',
                'marcas',
            ];

            foreach ($tables as $table) {
                $pdo->exec('TRUNCATE TABLE `' . $table . '`');
            }

            $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Todos os dados foram excluídos com sucesso.']);
        } catch (\Exception $e) {
            if ($pdo) {
                $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            }
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir dados: ' . $e->getMessage()]);
        }
        exit;
    }
}

