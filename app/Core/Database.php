<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $pdo = null;

    public static function connect(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        $config = Application::config('database');
        if (!$config) {
            throw new RuntimeException('Database configuration missing.');
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            self::$pdo = new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                $config['options'] ?? [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            throw new RuntimeException('Could not connect to database: ' . $e->getMessage(), (int)$e->getCode(), $e);
        }

        return self::$pdo;
    }

    public static function pdo(): PDO
    {
        return self::connect();
    }

    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $result = self::query($sql, $params)->fetch();
        return $result ?: null;
    }

    public static function fetchValue(string $sql, array $params = []): mixed
    {
        $stmt = self::query($sql, $params);
        return $stmt->fetchColumn();
    }

    public static function insert(string $table, array $data): int
    {
        $columns = array_keys($data);
        $fields = implode(', ', array_map(fn($col) => "`{$col}`", $columns));
        $placeholders = implode(', ', array_map(fn($col) => ":{$col}", $columns));

        $sql = "INSERT INTO `{$table}` ({$fields}) VALUES ({$placeholders})";
        self::query($sql, $data);

        return (int)self::connect()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $fields = implode(', ', array_map(fn($col) => "`{$col}` = :set_{$col}", array_keys($data)));
        $sql = "UPDATE `{$table}` SET {$fields} WHERE {$where}";

        $params = [];
        foreach ($data as $col => $val) {
            $params["set_{$col}"] = $val;
        }
        $params = array_merge($params, $whereParams);

        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    public static function delete(string $table, string $where, array $whereParams = []): int
    {
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = self::query($sql, $whereParams);
        return $stmt->rowCount();
    }

    public static function beginTransaction(): bool
    {
        return self::connect()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::connect()->commit();
    }

    public static function rollBack(): bool
    {
        return self::connect()->rollBack();
    }

    public static function inTransaction(): bool
    {
        return self::connect()->inTransaction();
    }

    public static function transaction(callable $callback): mixed
    {
        self::beginTransaction();
        try {
            $result = $callback();
            self::commit();
            return $result;
        } catch (\Throwable $e) {
            if (self::inTransaction()) {
                self::rollBack();
            }
            throw $e;
        }
    }
}
