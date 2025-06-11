<?php

declare(strict_types=1);

namespace App\Domain\Message;

class Message
{
    private int $id;
    private int $groupId;
    private int $userId;
    private string $content;
    private \DateTime $createdAt;

    public function __construct(int $id, int $groupId, int $userId, string $content, \DateTime $createdAt)
    {
        $this->id = $id;
        $this->groupId = $groupId;
        $this->userId = $userId;
        $this->content = $content;
        $this->createdAt = $createdAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }
}
