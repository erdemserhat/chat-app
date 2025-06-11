<?php

declare(strict_types=1);

namespace App\Domain\User;

use DateTime;

class User
{
    private int $id;
    private string $username;
    private DateTime $createdAt;

    public function __construct(int $id, string $username, DateTime $createdAt)
    {
        $this->id = $id;
        $this->username = $username;
        $this->createdAt = $createdAt;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
