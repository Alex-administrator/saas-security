<?php
declare(strict_types=1);

namespace App\Repositories;

use App\Support\Database;
use PDO;

abstract class BaseRepository
{
    protected PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::connection();
    }

    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        $result = $statement->fetch();
        return $result === false ? null : $result;
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll() ?: [];
    }

    protected function execute(string $sql, array $params = []): bool
    {
        $statement = $this->pdo->prepare($sql);
        return $statement->execute($params);
    }
}

