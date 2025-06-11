<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\ChatGroup\ChatGroup;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class ChatGroupRepository
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    /**
     * @throws Exception
     */
    public function create(string $name, int $createdBy): ChatGroup
    {
        $this->connection->insert('chat_groups', [
            'name' => $name,
            'created_by' => $createdBy,
        ]);

        $id = (int) $this->connection->lastInsertId();
        
        $result = $this->connection->fetchAssociative(
            'SELECT * FROM chat_groups WHERE id = ?',
            [$id]
        );

        if (!$result) {
            throw new Exception('Failed to create chat group');
        }

        return new ChatGroup(
            $result['id'],
            $result['name'],
            $result['created_by'],
            new \DateTime($result['created_at'])
        );
    }

    /**
     * @throws Exception
     */
    public function addMember(int $groupId, int $userId): void
    {
        $this->connection->insert('group_members', [
            'group_id' => $groupId,
            'user_id' => $userId,
        ]);
    }

    /**
     * @throws Exception
     */
    public function removeMember(int $groupId, int $userId): bool
    {
        $deleted = $this->connection->delete('group_members', [
            'group_id' => $groupId,
            'user_id' => $userId,
        ]);

        return $deleted > 0;
    }

    /**
     * @throws Exception
     */
    public function getGroup(int $groupId): ?ChatGroup
    {
        $result = $this->connection->fetchAssociative(
            'SELECT * FROM chat_groups WHERE id = ?',
            [$groupId]
        );

        if (!$result) {
            return null;
        }

        return new ChatGroup(
            $result['id'],
            $result['name'],
            $result['created_by'],
            new \DateTime($result['created_at'])
        );
    }

    /**
     * @throws Exception
     */
    public function getGroupMembers(int $groupId): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT u.* FROM users u 
            INNER JOIN group_members gm ON u.id = gm.user_id 
            WHERE gm.group_id = ?',
            [$groupId]
        );
    }

    /**
     * @throws Exception
     */
    public function getUserGroups(int $userId): array
    {
        $results = $this->connection->fetchAllAssociative(
            'SELECT cg.* FROM chat_groups cg 
            INNER JOIN group_members gm ON cg.id = gm.group_id 
            WHERE gm.user_id = ?',
            [$userId]
        );

        return array_map(function ($result) {
            return new ChatGroup(
                $result['id'],
                $result['name'],
                $result['created_by'],
                new \DateTime($result['created_at'])
            );
        }, $results);
    }

    /**
     * @throws Exception
     */
    public function findAll(): array
    {
        $results = $this->connection->fetchAllAssociative(
            'SELECT cg.*, u.username as creator_name 
            FROM chat_groups cg 
            INNER JOIN users u ON cg.created_by = u.id 
            ORDER BY cg.created_at DESC'
        );

        return array_map(function ($result) {
            return new ChatGroup(
                $result['id'],
                $result['name'],
                $result['created_by'],
                new \DateTime($result['created_at'])
            );
        }, $results);
    }

    /**
     * @throws Exception
     */
    public function isMember(int $groupId, int $userId): bool
    {
        $result = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM group_members 
            WHERE group_id = ? AND user_id = ?',
            [$groupId, $userId]
        );

        return (int) $result > 0;
    }

    /**
     * @throws Exception
     */
    public function isGroupCreator(int $groupId, int $userId): bool
    {
        $result = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM chat_groups 
            WHERE id = ? AND created_by = ?',
            [$groupId, $userId]
        );

        return (int) $result > 0;
    }
} 