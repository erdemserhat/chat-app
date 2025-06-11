<?php

declare(strict_types=1);

namespace Tests\EdgeCases;

use App\Domain\User\User;
use App\Domain\ChatGroup\ChatGroup;
use App\Domain\Message\Message;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Edge cases and boundary conditions that might occur in real usage
 */
class ChatEdgeCasesTest extends TestCase
{
    public function testUserWithExtremelyLongUsername(): void
    {
        // Edge case: User with maximum allowed username length
        $longUsername = str_repeat('a', 255); // Assuming 255 char limit
        $user = new User(1, $longUsername, new DateTime());

        $this->assertEquals($longUsername, $user->getUsername());
        $this->assertEquals(255, strlen($user->getUsername()));
    }

    public function testGroupWithUnicodeAndEmojiName(): void
    {
        // Edge case: Group names with international characters and emojis
        $unicodeName = '🌍 International Team - España 中国 Россия 🚀';
        $group = new ChatGroup(
            id: 1,
            name: $unicodeName,
            createdBy: 1,
            createdAt: new DateTime()
        );

        $this->assertEquals($unicodeName, $group->getName());
        $this->assertStringContainsString('🌍', $group->getName());
        $this->assertStringContainsString('España', $group->getName());
    }

    public function testMessageWithOnlyEmojisAndSymbols(): void
    {
        // Edge case: Message containing only emojis and symbols
        $emojiMessage = '🎉🎊💫✨🌟💥🔥💯👍🤝💪🚀';
        $message = new Message(
            id: 1,
            groupId: 1,
            userId: 1,
            content: $emojiMessage,
            createdAt: new DateTime()
        );

        $this->assertEquals($emojiMessage, $message->getContent());
        $this->assertGreaterThan(10, mb_strlen($message->getContent()));
    }

    public function testVeryLongMessageContent(): void
    {
        // Edge case: Maximum length message
        $longMessage = str_repeat('This is a very long message. ', 100); // ~3000 characters
        $message = new Message(
            id: 1,
            groupId: 1,
            userId: 1,
            content: $longMessage,
            createdAt: new DateTime()
        );

        $this->assertEquals($longMessage, $message->getContent());
        $this->assertGreaterThan(2000, strlen($message->getContent()));
    }

    public function testMessageWithSpecialCharactersAndCode(): void
    {
        // Edge case: Message containing code snippets and special characters
        $codeMessage = "Here's a quick fix:\n```php\nfunction test(\$var) {\n    return \$var > 0 ? 'positive' : 'negative';\n}\n```\nThis should handle the edge case where \$var === 0";
        
        $message = new Message(
            id: 1,
            groupId: 1,
            userId: 1,
            content: $codeMessage,
            createdAt: new DateTime()
        );

        $this->assertStringContainsString('```php', $message->getContent());
        $this->assertStringContainsString('function', $message->getContent());
        $this->assertStringContainsString('$var', $message->getContent());
    }

    public function testConcurrentMessageTimestamps(): void
    {
        // Edge case: Messages sent at the exact same timestamp
        $timestamp = new DateTime('2024-01-01 12:00:00.123456');
        
        $message1 = new Message(1, 1, 1, 'First message', $timestamp);
        $message2 = new Message(2, 1, 2, 'Second message', $timestamp);
        $message3 = new Message(3, 1, 3, 'Third message', $timestamp);

        $this->assertEquals($timestamp, $message1->getCreatedAt());
        $this->assertEquals($timestamp, $message2->getCreatedAt());
        $this->assertEquals($timestamp, $message3->getCreatedAt());
        
        // They should still be distinguishable by ID
        $this->assertNotEquals($message1->getId(), $message2->getId());
        $this->assertNotEquals($message2->getId(), $message3->getId());
    }

    public function testGroupCreatedInFuture(): void
    {
        // Edge case: Group with future creation date (clock synchronization issue)
        $futureDate = new DateTime('+1 hour');
        $group = new ChatGroup(
            id: 1,
            name: 'Future Group',
            createdBy: 1,
            createdAt: $futureDate
        );

        $this->assertGreaterThan(new DateTime(), $group->getCreatedAt());
    }

    public function testUserCreatedAtUnixEpoch(): void
    {
        // Edge case: User created at Unix epoch (edge case for date handling)
        $epochDate = new DateTime('1970-01-01 00:00:00');
        $user = new User(1, 'epoch_user', $epochDate);

        $this->assertEquals($epochDate, $user->getCreatedAt());
        $this->assertEquals(0, $user->getCreatedAt()->getTimestamp());
    }

    public function testMessageWithNullBytesAndControlCharacters(): void
    {
        // Edge case: Message with control characters and null bytes
        $maliciousContent = "Normal text\x00\x01\x02\x03\x04\x05" . "More text\n\r\t";
        $message = new Message(
            id: 1,
            groupId: 1,
            userId: 1,
            content: $maliciousContent,
            createdAt: new DateTime()
        );

        $this->assertEquals($maliciousContent, $message->getContent());
    }

    public function testRapidFireMessaging(): void
    {
        // Edge case: User sending multiple messages in rapid succession
        $baseTime = new DateTime('2024-01-01 12:00:00');
        $rapidMessages = [];

        for ($i = 0; $i < 10; $i++) {
            $timestamp = clone $baseTime;
            $timestamp->modify("+{$i} milliseconds");
            
            $rapidMessages[] = new Message(
                id: $i + 1,
                groupId: 1,
                userId: 1,
                content: "Rapid message #{$i}",
                createdAt: $timestamp
            );
        }

        // Verify all messages are from the same user
        foreach ($rapidMessages as $message) {
            $this->assertEquals(1, $message->getUserId());
            $this->assertEquals(1, $message->getGroupId());
        }

        // Verify timestamps are in order
        for ($i = 1; $i < count($rapidMessages); $i++) {
            $this->assertGreaterThanOrEqual(
                $rapidMessages[$i-1]->getCreatedAt(),
                $rapidMessages[$i]->getCreatedAt()
            );
        }
    }

    public function testUserWithNumericUsername(): void
    {
        // Edge case: Username that looks like a number
        $numericUsername = '12345';
        $user = new User(999, $numericUsername, new DateTime());

        $this->assertEquals($numericUsername, $user->getUsername());
        $this->assertTrue(is_numeric($user->getUsername()));
        $this->assertNotEquals($user->getId(), $user->getUsername());
    }

    public function testGroupWithSingleCharacterName(): void
    {
        // Edge case: Group with minimal name length
        $singleChar = 'A';
        $group = new ChatGroup(
            id: 1,
            name: $singleChar,
            createdBy: 1,
            createdAt: new DateTime()
        );

        $this->assertEquals($singleChar, $group->getName());
        $this->assertEquals(1, strlen($group->getName()));
    }

    public function testMessageWithOnlyWhitespace(): void
    {
        // Edge case: Message containing only various whitespace characters
        $whitespaceMessage = "   \t\n\r   ";
        $message = new Message(
            id: 1,
            groupId: 1,
            userId: 1,
            content: $whitespaceMessage,
            createdAt: new DateTime()
        );

        $this->assertEquals($whitespaceMessage, $message->getContent());
        $this->assertEquals('', trim($message->getContent()));
    }

    public function testLeapYearTimestampHandling(): void
    {
        // Edge case: Leap year date handling
        $leapYearDate = new DateTime('2024-02-29 12:00:00'); // 2024 is a leap year
        $message = new Message(
            id: 1,
            groupId: 1,
            userId: 1,
            content: 'Leap year message!',
            createdAt: $leapYearDate
        );

        $this->assertEquals('2024-02-29', $message->getCreatedAt()->format('Y-m-d'));
        $this->assertEquals(29, (int) $message->getCreatedAt()->format('d'));
    }

    public function testMaximumIntegerIds(): void
    {
        // Edge case: Maximum integer values for IDs
        $maxInt = PHP_INT_MAX;
        
        $user = new User($maxInt, 'max_user', new DateTime());
        $group = new ChatGroup(
            id: $maxInt,
            name: 'Max Group',
            createdBy: $maxInt,
            createdAt: new DateTime()
        );
        $message = new Message(
            id: $maxInt,
            groupId: $maxInt,
            userId: $maxInt,
            content: 'Max ID message',
            createdAt: new DateTime()
        );

        $this->assertEquals($maxInt, $user->getId());
        $this->assertEquals($maxInt, $group->getId());
        $this->assertEquals($maxInt, $message->getId());
    }

    public function testDaylightSavingTimeTransition(): void
    {
        // Edge case: Messages around daylight saving time transition
        $beforeDST = new DateTime('2024-03-10 01:59:59'); // US Spring forward
        $afterDST = new DateTime('2024-03-10 03:00:00');   // 2 AM doesn't exist
        
        $message1 = new Message(1, 1, 1, 'Before DST', $beforeDST);
        $message2 = new Message(2, 1, 1, 'After DST', $afterDST);

        $this->assertGreaterThan($beforeDST, $afterDST);
        $this->assertEquals($beforeDST, $message1->getCreatedAt());
        $this->assertEquals($afterDST, $message2->getCreatedAt());
    }
} 