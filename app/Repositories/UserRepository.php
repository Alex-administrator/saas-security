<?php
declare(strict_types=1);

namespace App\Repositories;

final class UserRepository extends BaseRepository
{
    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE email = :email LIMIT 1', ['email' => mb_strtolower($email)]);
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE id = :id LIMIT 1', ['id' => $id]);
    }

    public function create(array $data): int
    {
        $this->execute(
            'INSERT INTO users (name, email, password_hash, mfa_enabled, created_at, updated_at) VALUES (:name, :email, :password_hash, :mfa_enabled, UTC_TIMESTAMP(), UTC_TIMESTAMP())',
            [
                'name' => $data['name'],
                'email' => mb_strtolower($data['email']),
                'password_hash' => $data['password_hash'],
                'mfa_enabled' => (int) ($data['mfa_enabled'] ?? 0),
            ]
        );

        return (int) $this->pdo->lastInsertId();
    }
}

