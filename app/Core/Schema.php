<?php

namespace App\Core;

use PDO;
use PDOException;

class Schema
{
    public static function ensureDatabaseExists(array $config): void
    {
        $host = $config['host'] ?? 'localhost';
        $charset = $config['charset'] ?? 'utf8mb4';
        $user = $config['user'] ?? 'root';
        $password = $config['password'] ?? '';
        $dbname = $config['dbname'] ?? null;

        if (!$dbname) {
            throw new \RuntimeException('Database name is missing in configuration.');
        }

        $dsn = "mysql:host={$host};charset={$charset}";

        try {
            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die('Erro ao conectar no MySQL: ' . $e->getMessage());
        }

        $collation = $charset === 'utf8mb4' ? 'utf8mb4_unicode_ci' : 'utf8_general_ci';
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET {$charset} COLLATE {$collation}");
    }

    public static function ensureTables(PDO $connection): void
    {
        self::ensureMigrationsTable($connection);

        $migrationFiles = glob(BASE_PATH . '/database/migrations/*.sql');
        if (empty($migrationFiles)) {
            return;
        }

        sort($migrationFiles, SORT_STRING);

        $executed = self::getExecutedMigrations($connection);
        $currentBatch = self::getNextBatch($connection);

        foreach ($migrationFiles as $file) {
            $migrationName = basename($file);
            if (in_array($migrationName, $executed, true)) {
                continue;
            }

            self::executeSqlFile($connection, $file);
            self::recordMigration($connection, $migrationName, $currentBatch);
        }
    }

    private static function ensureMigrationsTable(PDO $connection): void
    {
        $connection->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT(11) NOT NULL AUTO_INCREMENT,
                migration VARCHAR(255) NOT NULL,
                batch INT(11) NOT NULL,
                executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY migration (migration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    private static function getExecutedMigrations(PDO $connection): array
    {
        $stmt = $connection->query('SELECT migration FROM migrations');
        if ($stmt === false) {
            return [];
        }

        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'migration');
    }

    private static function getNextBatch(PDO $connection): int
    {
        $stmt = $connection->query('SELECT MAX(batch) AS max_batch FROM migrations');
        if ($stmt === false) {
            return 1;
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return isset($row['max_batch']) && $row['max_batch'] !== null ? ((int)$row['max_batch'] + 1) : 1;
    }

    private static function recordMigration(PDO $connection, string $migrationName, int $batch): void
    {
        $stmt = $connection->prepare('INSERT INTO migrations (migration, batch) VALUES (:migration, :batch)');
        $stmt->execute(['migration' => $migrationName, 'batch' => $batch]);
    }

    private static function executeSqlFile(PDO $connection, string $filePath): void
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return;
        }

        $sql = file_get_contents($filePath);
        if ($sql === false) {
            return;
        }

        $sql = preg_replace('/--.*\n/', '', $sql);
        $sql = preg_replace('/\/\*[\s\S]*?\*\//', '', $sql);

        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $statement) {
            if ($statement === '') {
                continue;
            }

            try {
                $connection->exec($statement);
            } catch (PDOException $e) {
                continue;
            }
        }
    }
}
