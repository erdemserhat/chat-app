<?php

declare(strict_types=1);

namespace Tests\Scenarios;

use App\Domain\User\User;
use App\Domain\ChatGroup\ChatGroup;
use App\Domain\Message\Message;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Realistic chat scenarios that simulate real-world usage patterns
 */
class ChatScenarioTest extends TestCase
{
    public function testNewUserJoinsGroupAndSendsFirstMessage(): void
    {
        // Scenario: A new user registers, joins a group, and sends their first message

        // 1. User registers
        $newUser = new User(1, 'alice_newcomer', new DateTime('2024-01-01 09:00:00'));

        // 2. User discovers an existing group
        $welcomeGroup = new ChatGroup(
            id: 1,
            name: 'Welcome to Chat App!',
            createdBy: 999, // Some admin user
            createdAt: new DateTime('2024-01-01 08:00:00')
        );

        // 3. User sends their first message (introduction)
        $firstMessage = new Message(
            id: 1,
            groupId: $welcomeGroup->getId(),
            userId: $newUser->getId(),
            content: "Hi everyone! I'm new here. Looking forward to chatting with you all! 👋",
            createdAt: new DateTime('2024-01-01 09:15:00')
        );

        // Verify the scenario
        $this->assertEquals($newUser->getId(), $firstMessage->getUserId());
        $this->assertEquals($welcomeGroup->getId(), $firstMessage->getGroupId());
        $this->assertStringContainsString('new here', $firstMessage->getContent());
        $this->assertGreaterThan($newUser->getCreatedAt(), $firstMessage->getCreatedAt());
    }

    public function testActiveGroupConversation(): void
    {
        // Scenario: Multiple users having an active conversation in a group

        $developer1 = new User(1, 'sarah_dev', new DateTime('2024-01-01'));
        $developer2 = new User(2, 'mike_backend', new DateTime('2024-01-01'));
        $developer3 = new User(3, 'lisa_frontend', new DateTime('2024-01-01'));

        $projectGroup = new ChatGroup(
            id: 1,
            name: 'Project Alpha Development',
            createdBy: $developer1->getId(),
            createdAt: new DateTime('2024-01-01 08:00:00')
        );

        // Simulate a typical work conversation
        $messages = [
            new Message(
                1,
                $projectGroup->getId(),
                $developer1->getId(),
                "Good morning team! Ready for the sprint planning?",
                new DateTime('2024-01-01 09:00:00')
            ),

            new Message(
                2,
                $projectGroup->getId(),
                $developer2->getId(),
                "Morning Sarah! Yes, I've prepared the backend estimates.",
                new DateTime('2024-01-01 09:02:00')
            ),

            new Message(
                3,
                $projectGroup->getId(),
                $developer3->getId(),
                "Hi team! Frontend mockups are ready for review. Should we schedule a call?",
                new DateTime('2024-01-01 09:05:00')
            ),

            new Message(
                4,
                $projectGroup->getId(),
                $developer1->getId(),
                "Great work everyone! Let's do a quick call at 10 AM. I'll send the meeting link.",
                new DateTime('2024-01-01 09:07:00')
            )
        ];

        // Verify conversation flow
        $this->assertCount(4, $messages);

        // Verify chronological order
        for ($i = 1; $i < count($messages); $i++) {
            $this->assertGreaterThan(
                $messages[$i - 1]->getCreatedAt(),
                $messages[$i]->getCreatedAt(),
                'Messages should be in chronological order'
            );
        }

        // Verify all messages are in the same group
        foreach ($messages as $message) {
            $this->assertEquals($projectGroup->getId(), $message->getGroupId());
        }

        // Verify different users participated
        $uniqueUsers = array_unique(array_map(fn($msg) => $msg->getUserId(), $messages));
        $this->assertCount(3, $uniqueUsers, 'All three users should have participated');
    }

    public function testEmergencyNotificationScenario(): void
    {
        // Scenario: Critical system alert in a tech support group

        $supportLead = new User(1, 'alex_support_lead', new DateTime('2024-01-01'));
        $devOps = new User(2, 'jordan_devops', new DateTime('2024-01-01'));
        $manager = new User(3, 'sam_manager', new DateTime('2024-01-01'));

        $emergencyGroup = new ChatGroup(
            id: 1,
            name: '🚨 Critical Alerts',
            createdBy: $supportLead->getId(),
            createdAt: new DateTime('2024-01-01')
        );

        // Emergency scenario: Database is down
        $emergencyAlert = new Message(
            id: 1,
            groupId: $emergencyGroup->getId(),
            userId: $supportLead->getId(),
            content: "🚨 CRITICAL: Main database is down! Customer transactions failing. @channel",
            createdAt: new DateTime('2024-01-01 14:30:00')
        );

        $responseMessage = new Message(
            id: 2,
            groupId: $emergencyGroup->getId(),
            userId: $devOps->getId(),
            content: "On it! Investigating connection issues. Will update in 5 minutes.",
            createdAt: new DateTime('2024-01-01 14:31:00')
        );

        $managerMessage = new Message(
            id: 3,
            groupId: $emergencyGroup->getId(),
            userId: $manager->getId(),
            content: "Thanks for the quick response. Keep me posted. Preparing customer communication.",
            createdAt: new DateTime('2024-01-01 14:32:00')
        );

        // Verify emergency response timing
        $responseTime = $responseMessage->getCreatedAt()->getTimestamp() -
                       $emergencyAlert->getCreatedAt()->getTimestamp();
        $this->assertLessThan(120, $responseTime, 'Emergency response should be within 2 minutes');

        // Verify alert formatting
        $this->assertStringContainsString('🚨', $emergencyAlert->getContent());
        $this->assertStringContainsString('CRITICAL', $emergencyAlert->getContent());

        // Verify different stakeholders are involved
        $stakeholders = [$emergencyAlert->getUserId(), $responseMessage->getUserId(), $managerMessage->getUserId()];
        $this->assertCount(3, array_unique($stakeholders));
    }

    public function testWeekendCasualChatScenario(): void
    {
        // Scenario: Friends having casual weekend chat with mixed content types

        $friends = [
            new User(1, 'emma_photographer', new DateTime('2024-01-01')),
            new User(2, 'carlos_musician', new DateTime('2024-01-01')),
            new User(3, 'zoe_chef', new DateTime('2024-01-01'))
        ];

        $friendsGroup = new ChatGroup(
            id: 1,
            name: 'Weekend Warriors 🎉',
            createdBy: $friends[0]->getId(),
            createdAt: new DateTime('2024-01-06 10:00:00') // Saturday
        );

        // Weekend conversation with various content types
        $weekendMessages = [
            new Message(
                1,
                $friendsGroup->getId(),
                $friends[0]->getId(),
                "Beautiful morning for photography! Just captured sunrise at the lake 📸",
                new DateTime('2024-01-06 10:30:00')
            ),

            new Message(
                2,
                $friendsGroup->getId(),
                $friends[1]->getId(),
                "Sounds amazing! I'm jamming with my band today. New song coming together 🎸",
                new DateTime('2024-01-06 11:15:00')
            ),

            new Message(
                3,
                $friendsGroup->getId(),
                $friends[2]->getId(),
                "You guys are so productive! I'm experimenting with a new fusion recipe 👨‍🍳",
                new DateTime('2024-01-06 12:00:00')
            ),

            new Message(
                4,
                $friendsGroup->getId(),
                $friends[0]->getId(),
                "We should all meet up later! Potluck dinner at my place?",
                new DateTime('2024-01-06 12:30:00')
            ),

            new Message(
                5,
                $friendsGroup->getId(),
                $friends[1]->getId(),
                "I'm in! Can bring my guitar for some after-dinner music",
                new DateTime('2024-01-06 12:35:00')
            ),

            new Message(
                6,
                $friendsGroup->getId(),
                $friends[2]->getId(),
                "Perfect! I'll bring my experimental dish. Hope you're feeling adventurous 😄",
                new DateTime('2024-01-06 12:40:00')
            )
        ];

        // Verify weekend timing
        foreach ($weekendMessages as $message) {
            $day = $message->getCreatedAt()->format('N'); // 1-7, Monday-Sunday
            $this->assertEquals('6', $day, 'All messages should be on Saturday');
        }

        // Verify casual conversation characteristics
        $emojiCount = 0;
        $questionCount = 0;

        foreach ($weekendMessages as $message) {
            $content = $message->getContent();

            // Count emojis (simple check for common emoji characters)
            if (preg_match('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]/u', $content)) {
                $emojiCount++;
            }

            // Count questions
            if (strpos($content, '?') !== false) {
                $questionCount++;
            }
        }

        $this->assertGreaterThan(0, $emojiCount, 'Casual chat should contain emojis');
        $this->assertGreaterThan(0, $questionCount, 'Casual chat should contain questions');

        // Verify all friends participated
        $participants = array_unique(array_map(fn($msg) => $msg->getUserId(), $weekendMessages));
        $this->assertCount(3, $participants, 'All friends should participate in weekend chat');
    }

    public function testLateNightStudyGroupScenario(): void
    {
        // Scenario: Students cramming for exams with time pressure

        $students = [
            new User(1, 'study_alex', new DateTime('2024-01-01')),
            new User(2, 'study_brooklyn', new DateTime('2024-01-01')),
            new User(3, 'study_connor', new DateTime('2024-01-01'))
        ];

        $studyGroup = new ChatGroup(
            id: 1,
            name: 'CS 201 Final Exam Prep',
            createdBy: $students[0]->getId(),
            createdAt: new DateTime('2024-01-15 20:00:00')
        );

        // Late night study session
        $studyMessages = [
            new Message(
                1,
                $studyGroup->getId(),
                $students[0]->getId(),
                "Anyone else panicking about the algorithms section? 😰",
                new DateTime('2024-01-15 23:30:00')
            ),

            new Message(
                2,
                $studyGroup->getId(),
                $students[1]->getId(),
                "YES! I can't wrap my head around dynamic programming",
                new DateTime('2024-01-15 23:32:00')
            ),

            new Message(
                3,
                $studyGroup->getId(),
                $students[2]->getId(),
                "Let's work through the knapsack problem together. I think I understand it",
                new DateTime('2024-01-15 23:35:00')
            ),

            new Message(
                4,
                $studyGroup->getId(),
                $students[0]->getId(),
                "That would be amazing! Should we do a quick video call?",
                new DateTime('2024-01-15 23:36:00')
            ),

            new Message(
                5,
                $studyGroup->getId(),
                $students[1]->getId(),
                "Coffee break first? My brain is fried ☕",
                new DateTime('2024-01-16 00:15:00')
            ) // Past midnight
        ];

        // Verify late night timing
        $lateNightMessages = array_filter($studyMessages, function ($message) {
            $hour = (int) $message->getCreatedAt()->format('H');
            return $hour >= 23 || $hour <= 2; // 11 PM to 2 AM
        });

        $this->assertGreaterThan(3, count($lateNightMessages), 'Most messages should be late night');

        // Verify academic stress indicators
        $stressIndicators = ['panic', 'can\'t', 'fried', 'help', '😰', '☕'];
        $stressMessageCount = 0;

        foreach ($studyMessages as $message) {
            $content = strtolower($message->getContent());
            foreach ($stressIndicators as $indicator) {
                if (strpos($content, strtolower($indicator)) !== false) {
                    $stressMessageCount++;
                    break;
                }
            }
        }

        $this->assertGreaterThan(2, $stressMessageCount, 'Study group should show stress indicators');

        // Verify collaboration effort
        $collaborationKeywords = ['together', 'help', 'work through', 'video call'];
        $collaborationFound = false;

        foreach ($studyMessages as $message) {
            $content = strtolower($message->getContent());
            foreach ($collaborationKeywords as $keyword) {
                if (strpos($content, $keyword) !== false) {
                    $collaborationFound = true;
                    break 2;
                }
            }
        }

        $this->assertTrue($collaborationFound, 'Study group should show collaboration');
    }
}
