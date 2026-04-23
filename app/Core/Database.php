<?php
namespace App\Core;

use PDO;
use PDOException;
use App\Core\Schema;

class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $config = require BASE_PATH . '/config/database.php';

            Schema::ensureDatabaseExists($config);

            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            if (!empty($config['port'])) {
                $dsn .= ";port={$config['port']}";
            }

            try {
                self::$instance = new PDO($dsn, $config['user'], $config['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die("Erro ao conectar no banco de dados: " . $e->getMessage());
            }

            Schema::ensureTables(self::$instance);
        }

        return self::$instance;
    }
}