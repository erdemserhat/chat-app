<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Message\Message;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class MessageRepository
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * @throws Exception
     */
    public function create(int $groupId, int $userId, string $content): Message
    {
        $this->connection->insert('messages', [
            'group_id' => $groupId,
            'user_id' => $userId,
            'content' => $content,
        ]);

        $id = (int) $this->connection->lastInsertId();

        $result = $this->connection->fetchAssociative(
            'SELECT * FROM messages WHERE id = ?',
            [$id]
        );



        return new Message(
            $result['id'],
            $result['group_id'],
            $result['user_id'],
            $result['content'],
            new \DateTime($result['created_at'])
        );
    }

    /**
     * @throws Exception
     */
    public function findByGroupId(int $groupId): array
    {
        $results = $this->connection->fetchAllAssociative(
            'SELECT m.*, u.username 
            FROM messages m 
            INNER JOIN users u ON m.user_id = u.id 
            WHERE m.group_id = ? 
            ORDER BY m.created_at ASC',
            [$groupId]
        );

        return array_map(function ($result) {
            return new Message(
                $result['id'],
                $result['group_id'],
                $result['user_id'],
                $result['content'],
                new \DateTime($result['created_at'])
            );
        }, $results);
    }
}
