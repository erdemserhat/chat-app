<?php

declare(strict_types=1);

namespace Tests\Domain\ChatGroup;

use App\Domain\ChatGroup\ChatGroup;
use DateTime;
use PHPUnit\Framework\TestCase;

class ChatGroupTest extends TestCase
{
    public function testChatGroupCanBeCreated(): void
    {
        $id = 1;
        $name = 'General Chat';
        $createdBy = 100;
        $createdAt = new DateTime('2024-01-01 10:00:00');

        $chatGroup = new ChatGroup($id, $name, $createdBy, $createdAt);

        $this->assertEquals($id, $chatGroup->getId());
        $this->assertEquals($name, $chatGroup->getName());
        $this->assertEquals($createdBy, $chatGroup->getCreatedBy());
        $this->assertEquals($createdAt, $chatGroup->getCreatedAt());
    }

    public function testChatGroupPropertiesAreCorrect(): void
    {
        $chatGroup = new ChatGroup(
            id: 42,
            name: 'Development Team',
            createdBy: 5,
            createdAt: new DateTime('2024-01-20 09:15:30')
        );

        $this->assertIsInt($chatGroup->getId());
        $this->assertIsString($chatGroup->getName());
        $this->assertIsInt($chatGroup->getCreatedBy());
        $this->assertInstanceOf(DateTime::class, $chatGroup->getCreatedAt());
    }

    public function testChatGroupCanHaveSpecialCharactersInName(): void
    {
        $name = 'Test Group #1 - Special!';
        $chatGroup = new ChatGroup(1, $name, 1, new DateTime());

        $this->assertEquals($name, $chatGroup->getName());
    }

    public function testChatGroupCanHaveEmptyName(): void
    {
        $name = '';
        $chatGroup = new ChatGroup(1, $name, 1, new DateTime());

        $this->assertEquals($name, $chatGroup->getName());
    }
} 