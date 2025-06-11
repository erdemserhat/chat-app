<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Domain\User\User;
use App\Domain\ChatGroup\ChatGroup;
use App\Domain\Message\Message;
use DateTime;
use PHPUnit\Framework\TestCase;

class BasicIntegrationTest extends TestCase
{
    public function testChatFlowIntegration(): void
    {
        // Create a user
        $user = new User(1, 'test_user', new DateTime('2024-01-01 10:00:00'));

        // Create a chat group
        $chatGroup = new ChatGroup(
            id: 1,
            name: 'Test Group',
            createdBy: $user->getId(),
            createdAt: new DateTime('2024-01-01 10:05:00')
        );

        // Create a message
        $message = new Message(
            id: 1,
            groupId: $chatGroup->getId(),
            userId: $user->getId(),
            content: 'Hello from integration test!',
            createdAt: new DateTime('2024-01-01 10:10:00')
        );

        // Verify the integration
        $this->assertEquals($user->getId(), $chatGroup->getCreatedBy());
        $this->assertEquals($chatGroup->getId(), $message->getGroupId());
        $this->assertEquals($user->getId(), $message->getUserId());

        // Verify message belongs to the right context
        $this->assertTrue($this->messagesBelongsToUserAndGroup($message, $user, $chatGroup));
    }

    public function testMultipleUsersInGroup(): void
    {
        $user1 = new User(1, 'user1', new DateTime());
        $user2 = new User(2, 'user2', new DateTime());

        $chatGroup = new ChatGroup(1, 'Multi User Group', $user1->getId(), new DateTime());

        $message1 = new Message(1, $chatGroup->getId(), $user1->getId(), 'First message', new DateTime());
        $message2 = new Message(2, $chatGroup->getId(), $user2->getId(), 'Second message', new DateTime());

        $this->assertEquals($chatGroup->getId(), $message1->getGroupId());
        $this->assertEquals($chatGroup->getId(), $message2->getGroupId());
        $this->assertNotEquals($message1->getUserId(), $message2->getUserId());
    }

    private function messagesBelongsToUserAndGroup(Message $message, User $user, ChatGroup $chatGroup): bool
    {
        return $message->getUserId() === $user->getId()
            && $message->getGroupId() === $chatGroup->getId();
    }
}
