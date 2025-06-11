<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\User\User;
use App\Infrastructure\Database\Database;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use DateTime;

class UserRepository
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * @throws Exception
     */
    public function create(string $username): User
    {
        $this->connection->insert('users', [
            'username' => $username,
        ]);

        $id = (int) $this->connection->lastInsertId();
        return $this->findById($id);
    }

    /**
     * @throws Exception
     */
    public function findByUsername(string $username): ?User
    {
        $result = $this->connection->fetchAssociative(
            'SELECT * FROM users WHERE username = ?',
            [$username]
        );

        if (!$result) {
            return null;
        }

        return new User(
            (int) $result['id'],
            $result['username'],
            new DateTime($result['created_at'])
        );
    }

    /**
     * @throws Exception
     */
    public function findById(int $id): ?User
    {
        $result = $this->connection->fetchAssociative(
            'SELECT * FROM users WHERE id = ?',
            [$id]
        );

        if (!$result) {
            return null;
        }

        return new User(
            (int) $result['id'],
            $result['username'],
            new DateTime($result['created_at'])
        );
    }

    public function findAll(): array
    {
        $results = $this->connection->fetchAllAssociative('SELECT * FROM users');
        
        return array_map(function ($result) {
            return new User(
                (int) $result['id'],
                $result['username'],
                new DateTime($result['created_at'])
            );
        }, $results);
    }
} 