<?php
declare(strict_types=1);

namespace App\Support;

use PDO;
use PDOException;

final class Database
{
    private static array $config = [];
    private static ?PDO $connection = null;

    public static function configure(array $config): void
    {
        self::$config = $config;
    }

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            self::$config['host'],
            self::$config['port'],
            self::$config['database'],
            self::$config['charset']
        );

        self::$connection = new PDO($dsn, self::$config['username'], self::$config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        self::$connection->exec("SET time_zone = '+00:00'");

        return self::$connection;
    }

    public static function available(): bool
    {
        try {
            self::connection()->query('SELECT 1');
            return true;
        } catch (PDOException) {
            return false;
        }
    }

    public static function beginTransaction(): void
    {
        self::connection()->beginTransaction();
    }

    public static function commit(): void
    {
        self::connection()->commit();
    }

    public static function rollBack(): void
    {
        if (self::connection()->inTransaction()) {
            self::connection()->rollBack();
        }
    }
}

