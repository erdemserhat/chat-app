<?php

declare(strict_types=1);

namespace Tests\Domain\Message;

use App\Domain\Message\Message;
use DateTime;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testMessageCanBeCreated(): void
    {
        $id = 1;
        $groupId = 10;
        $userId = 20;
        $content = 'Hello, world!';
        $createdAt = new DateTime('2024-01-01 12:00:00');

        $message = new Message($id, $groupId, $userId, $content, $createdAt);

        $this->assertEquals($id, $message->getId());
        $this->assertEquals($groupId, $message->getGroupId());
        $this->assertEquals($userId, $message->getUserId());
        $this->assertEquals($content, $message->getContent());
        $this->assertEquals($createdAt, $message->getCreatedAt());
    }

    public function testMessagePropertiesAreCorrect(): void
    {
        $message = new Message(
            id: 123,
            groupId: 456,
            userId: 789,
            content: 'Test message content',
            createdAt: new DateTime('2024-01-25 16:45:00')
        );

        $this->assertIsInt($message->getId());
        $this->assertIsInt($message->getGroupId());
        $this->assertIsInt($message->getUserId());
        $this->assertIsString($message->getContent());
        $this->assertInstanceOf(DateTime::class, $message->getCreatedAt());
    }

    public function testMessageCanHaveEmptyContent(): void
    {
        $message = new Message(1, 1, 1, '', new DateTime());

        $this->assertEquals('', $message->getContent());
    }

    public function testMessageCanHaveLongContent(): void
    {
        $longContent = str_repeat('This is a very long message. ', 50);
        $message = new Message(1, 1, 1, $longContent, new DateTime());

        $this->assertEquals($longContent, $message->getContent());
        $this->assertGreaterThan(100, strlen($message->getContent()));
    }

    public function testMessageCanHaveSpecialCharacters(): void
    {
        $content = 'Message with émojis 🚀 and special chars: @#$%^&*()';
        $message = new Message(1, 1, 1, $content, new DateTime());

        $this->assertEquals($content, $message->getContent());
    }
}
