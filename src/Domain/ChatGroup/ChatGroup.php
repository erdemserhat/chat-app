<?php

declare(strict_types=1);

namespace App\Domain\ChatGroup;

use DateTime;

class ChatGroup
{
    public function __construct(
        private readonly int $id,
        private readonly string $name,
        private readonly int $createdBy,
        private readonly DateTime $createdAt
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
} 