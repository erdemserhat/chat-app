<?php

declare(strict_types=1);

namespace Tests\Performance;

use App\Domain\User\User;
use App\Domain\ChatGroup\ChatGroup;
use App\Domain\Message\Message;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Performance tests for chat application scenarios
 */
class PerformanceTest extends TestCase
{
    public function testCreateManyUsersPerformance(): void
    {
        // Performance test: Creating many users
        $startTime = microtime(true);
        $users = [];

        for ($i = 1; $i <= 1000; $i++) {
            $users[] = new User(
                id: $i,
                username: "user_{$i}",
                createdAt: new DateTime()
            );
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertCount(1000, $users);
        $this->assertLessThan(1.0, $executionTime, 'Creating 1000 users should take less than 1 second');
    }

    public function testCreateManyGroupsPerformance(): void
    {
        // Performance test: Creating many groups
        $startTime = microtime(true);
        $groups = [];

        for ($i = 1; $i <= 500; $i++) {
            $groups[] = new ChatGroup(
                id: $i,
                name: "Group {$i}",
                createdBy: 1,
                createdAt: new DateTime()
            );
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertCount(500, $groups);
        $this->assertLessThan(0.5, $executionTime, 'Creating 500 groups should take less than 0.5 seconds');
    }

    public function testCreateManyMessagesPerformance(): void
    {
        // Performance test: Creating many messages
        $startTime = microtime(true);
        $messages = [];

        for ($i = 1; $i <= 2000; $i++) {
            $messages[] = new Message(
                id: $i,
                groupId: 1,
                userId: ($i % 10) + 1, // Rotate through 10 users
                content: "This is message number {$i}",
                createdAt: new DateTime()
            );
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertCount(2000, $messages);
        $this->assertLessThan(1.0, $executionTime, 'Creating 2000 messages should take less than 1 second');
    }

    public function testLargeGroupConversationPerformance(): void
    {
        // Performance test: Large group with many participants and messages
        $startTime = microtime(true);

        // Create 100 users
        $users = [];
        for ($i = 1; $i <= 100; $i++) {
            $users[] = new User($i, "user_{$i}", new DateTime());
        }

        // Create a large group
        $largeGroup = new ChatGroup(
            id: 1,
            name: 'Large Group Performance Test',
            createdBy: 1,
            createdAt: new DateTime()
        );

        // Create 1000 messages from different users
        $messages = [];
        for ($i = 1; $i <= 1000; $i++) {
            $randomUser = $users[array_rand($users)];
            $messages[] = new Message(
                id: $i,
                groupId: $largeGroup->getId(),
                userId: $randomUser->getId(),
                content: "Message {$i} from {$randomUser->getUsername()}",
                createdAt: new DateTime()
            );
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertCount(100, $users);
        $this->assertCount(1000, $messages);
        $this->assertLessThan(2.0, $executionTime, 'Large group simulation should take less than 2 seconds');
    }

    public function testMemoryUsageWithManyObjects(): void
    {
        // Performance test: Memory usage with many objects
        $memoryBefore = memory_get_usage();

        $objects = [];

        // Create 1000 users, 100 groups, and 5000 messages
        for ($i = 1; $i <= 1000; $i++) {
            $objects[] = new User($i, "user_{$i}", new DateTime());
        }

        for ($i = 1; $i <= 100; $i++) {
            $objects[] = new ChatGroup($i, "Group {$i}", 1, new DateTime());
        }

        for ($i = 1; $i <= 5000; $i++) {
            $objects[] = new Message(
                $i,
                ($i % 100) + 1,
                ($i % 1000) + 1,
                "Message {$i}",
                new DateTime()
            );
        }

        $memoryAfter = memory_get_usage();
        $memoryUsed = $memoryAfter - $memoryBefore;

        $this->assertCount(6100, $objects); // 1000 + 100 + 5000
        $this->assertLessThan(50 * 1024 * 1024, $memoryUsed, 'Memory usage should be less than 50MB for 6100 objects');
    }

    public function testStringProcessingPerformance(): void
    {
        // Performance test: String processing with various content types
        $startTime = microtime(true);

        $complexContents = [
            str_repeat('Simple message. ', 100),
            '🎉' . str_repeat('emoji test 💫✨🌟 ', 50),
            "Code snippet:\n```php\n" . str_repeat('$var = "test"; ', 20) . "\n```",
            str_repeat('Unicode: España 中国 Россия ', 30),
            str_repeat('<script>alert("test")</script> ', 10),
            json_encode(array_fill(0, 50, ['key' => 'value', 'number' => 123])),
        ];

        $messages = [];
        foreach ($complexContents as $index => $content) {
            for ($i = 0; $i < 100; $i++) {
                $messages[] = new Message(
                    id: ($index * 100) + $i + 1,
                    groupId: 1,
                    userId: 1,
                    content: $content,
                    createdAt: new DateTime()
                );
            }
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertCount(600, $messages); // 6 content types * 100 each
        $this->assertLessThan(1.0, $executionTime, 'Complex string processing should take less than 1 second');
    }

    public function testDateTimeCreationPerformance(): void
    {
        // Performance test: DateTime object creation
        $startTime = microtime(true);

        $dates = [];
        for ($i = 0; $i < 10000; $i++) {
            $dates[] = new DateTime();
        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertCount(10000, $dates);
        $this->assertLessThan(1.0, $executionTime, 'Creating 10000 DateTime objects should take less than 1 second');
    }

    public function testSimulateHighConcurrencyScenario(): void
    {
        // Performance test: Simulate high concurrency scenario
        $startTime = microtime(true);

        // Simulate 50 concurrent users each sending 20 messages
        $allMessages = [];
        $baseTime = new DateTime();

        for ($userId = 1; $userId <= 50; $userId++) {
            for ($messageNum = 1; $messageNum <= 20; $messageNum++) {
                $timestamp = clone $baseTime;
                $timestamp->modify("+{$messageNum} seconds");

                $allMessages[] = new Message(
                    id: (($userId - 1) * 20) + $messageNum,
                    groupId: 1,
                    userId: $userId,
                    content: "Message {$messageNum} from user {$userId}",
                    createdAt: $timestamp
                );
            }
        }

        // Sort messages by timestamp to simulate real order
        usort($allMessages, function ($a, $b) {
            return $a->getCreatedAt() <=> $b->getCreatedAt();
        });

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertCount(1000, $allMessages); // 50 users * 20 messages
        $this->assertLessThan(2.0, $executionTime, 'High concurrency simulation should take less than 2 seconds');

        // Verify chronological order
        for ($i = 1; $i < count($allMessages); $i++) {
            $this->assertGreaterThanOrEqual(
                $allMessages[$i - 1]->getCreatedAt(),
                $allMessages[$i]->getCreatedAt()
            );
        }
    }

    public function testArrayManipulationPerformance(): void
    {
        // Performance test: Array manipulation with domain objects
        $startTime = microtime(true);

        // Create base data
        $users = [];
        for ($i = 1; $i <= 100; $i++) {
            $users[] = new User($i, "user_{$i}", new DateTime());
        }

        $messages = [];
        for ($i = 1; $i <= 1000; $i++) {
            $messages[] = new Message(
                $i,
                1,
                ($i % 100) + 1,
                "Message {$i}",
                new DateTime()
            );
        }

        // Perform various array operations
        $usersByName = [];
        foreach ($users as $user) {
            $usersByName[$user->getUsername()] = $user;
        }

        $messagesByUser = [];
        foreach ($messages as $message) {
            $userId = $message->getUserId();
            if (!isset($messagesByUser[$userId])) {
                $messagesByUser[$userId] = [];
            }
            $messagesByUser[$userId][] = $message;
        }

        $recentMessages = array_slice($messages, -50); // Last 50 messages

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertCount(100, $usersByName);
        $this->assertCount(100, $messagesByUser); // 100 different users
        $this->assertCount(50, $recentMessages);
        $this->assertLessThan(0.5, $executionTime, 'Array manipulation should take less than 0.5 seconds');
    }
}
