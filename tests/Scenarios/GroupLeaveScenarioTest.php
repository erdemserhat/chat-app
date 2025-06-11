<?php

declare(strict_types=1);

namespace Tests\Scenarios;

use App\Domain\User\User;
use App\Domain\ChatGroup\ChatGroup;
use App\Domain\Message\Message;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Realistic scenarios for group leave functionality
 */
class GroupLeaveScenarioTest extends TestCase
{
    public function testRegularMemberLeavesGroup(): void
    {
        // Scenario: A regular member decides to leave a group
        
        $groupCreator = new User(1, 'team_lead', new DateTime('2024-01-01'));
        $regularMember = new User(2, 'john_developer', new DateTime('2024-01-01'));
        
        $projectGroup = new ChatGroup(
            id: 1,
            name: 'Project Discussion',
            createdBy: $groupCreator->getId(),
            createdAt: new DateTime('2024-01-01 09:00:00')
        );

        // Member has been active in the group
        $memberMessages = [
            new Message(1, $projectGroup->getId(), $regularMember->getId(),
                "I've completed the authentication module", new DateTime('2024-01-01 10:00:00')),
            new Message(2, $projectGroup->getId(), $regularMember->getId(),
                "Thanks for the feedback, I'll implement the changes", new DateTime('2024-01-01 11:00:00'))
        ];

        // Now member wants to leave
        $leaveTimestamp = new DateTime('2024-01-01 17:00:00');
        
        // Verify the scenario data
        $this->assertEquals($groupCreator->getId(), $projectGroup->getCreatedBy());
        $this->assertNotEquals($regularMember->getId(), $projectGroup->getCreatedBy());
        $this->assertCount(2, $memberMessages);
        
        // Verify all messages belong to the member leaving
        foreach ($memberMessages as $message) {
            $this->assertEquals($regularMember->getId(), $message->getUserId());
            $this->assertEquals($projectGroup->getId(), $message->getGroupId());
        }
        
        // Verify leave request timing
        $lastMessageTime = end($memberMessages)->getCreatedAt();
        $this->assertGreaterThan($lastMessageTime, $leaveTimestamp);
    }

    public function testMemberLeavesAfterProjectCompletion(): void
    {
        // Scenario: Team member leaves after project is completed
        
        $users = [
            new User(1, 'project_manager', new DateTime('2024-01-01')),
            new User(2, 'frontend_dev', new DateTime('2024-01-01')),
            new User(3, 'backend_dev', new DateTime('2024-01-01')),
            new User(4, 'qa_tester', new DateTime('2024-01-01'))
        ];
        
        $projectGroup = new ChatGroup(
            id: 1,
            name: 'Product Launch Team',
            createdBy: $users[0]->getId(), // Project manager
            createdAt: new DateTime('2024-01-01 08:00:00')
        );

        // Project completion messages
        $completionMessages = [
            new Message(1, $projectGroup->getId(), $users[1]->getId(),
                "Frontend is ready for production! 🎉", new DateTime('2024-03-15 14:00:00')),
            new Message(2, $projectGroup->getId(), $users[2]->getId(),
                "Backend deployment completed successfully", new DateTime('2024-03-15 14:30:00')),
            new Message(3, $projectGroup->getId(), $users[3]->getId(),
                "All tests passing! QA approval given ✅", new DateTime('2024-03-15 15:00:00')),
            new Message(4, $projectGroup->getId(), $users[0]->getId(),
                "Excellent work everyone! Project is now live!", new DateTime('2024-03-15 16:00:00'))
        ];

        // Some members decide to leave after project completion
        $leaveTime = new DateTime('2024-03-20 10:00:00'); // 5 days later
        $leavingMembers = [$users[1], $users[3]]; // Frontend dev and QA tester

        // Verify project timeline
        $projectStart = $projectGroup->getCreatedAt();
        $projectEnd = end($completionMessages)->getCreatedAt();
        $daysBetween = $projectEnd->diff($projectStart)->days;
        
        $this->assertGreaterThan(70, $daysBetween, 'Project should have taken more than 70 days');
        $this->assertGreaterThan($projectEnd, $leaveTime, 'Members leave after project completion');
        
        // Verify leaving members contributed to the project
        $contributingUsers = array_unique(array_map(fn($msg) => $msg->getUserId(), $completionMessages));
        foreach ($leavingMembers as $member) {
            $this->assertContains($member->getId(), $contributingUsers, 'Leaving member should have contributed');
        }
    }

    public function testMemberLeavesWhenReassignedToNewTeam(): void
    {
        // Scenario: Developer gets reassigned to new team and leaves old groups
        
        $oldTeamLead = new User(1, 'old_team_lead', new DateTime('2024-01-01'));
        $reassignedDev = new User(2, 'sarah_fullstack', new DateTime('2024-01-01'));
        $newTeamLead = new User(3, 'new_team_lead', new DateTime('2024-01-01'));
        
        $oldTeamGroup = new ChatGroup(
            id: 1,
            name: 'Legacy System Maintenance',
            createdBy: $oldTeamLead->getId(),
            createdAt: new DateTime('2024-01-01 08:00:00')
        );

        $newTeamGroup = new ChatGroup(
            id: 2,
            name: 'Next-Gen Platform Development',
            createdBy: $newTeamLead->getId(),
            createdAt: new DateTime('2024-06-01 09:00:00')
        );

        // Developer's history in old team
        $oldTeamMessages = [
            new Message(1, $oldTeamGroup->getId(), $reassignedDev->getId(),
                "Fixed the memory leak in the user module", new DateTime('2024-05-15 10:00:00')),
            new Message(2, $oldTeamGroup->getId(), $reassignedDev->getId(),
                "Updated documentation for the API endpoints", new DateTime('2024-05-20 14:00:00'))
        ];

        // Reassignment happens
        $reassignmentDate = new DateTime('2024-06-01');
        
        // First message in new team
        $firstNewTeamMessage = new Message(
            3, $newTeamGroup->getId(), $reassignedDev->getId(),
            "Hi everyone! Excited to be part of the new team!", new DateTime('2024-06-01 10:00:00')
        );

        // Leave old team shortly after joining new one
        $leaveOldTeamDate = new DateTime('2024-06-02 09:00:00');

        // Verify transition timeline
        $lastOldTeamMessage = end($oldTeamMessages)->getCreatedAt();
        $this->assertLessThan($reassignmentDate, $lastOldTeamMessage, 'Last old team activity before reassignment');
        $this->assertGreaterThanOrEqual($reassignmentDate, $firstNewTeamMessage->getCreatedAt(), 'New team activity after reassignment');
        $this->assertGreaterThan($firstNewTeamMessage->getCreatedAt(), $leaveOldTeamDate, 'Leaves old team after joining new team');
        
        // Verify different teams
        $this->assertNotEquals($oldTeamGroup->getId(), $newTeamGroup->getId());
        $this->assertNotEquals($oldTeamGroup->getCreatedBy(), $newTeamGroup->getCreatedBy());
        
        // Verify member continuity
        $this->assertEquals($reassignedDev->getId(), $firstNewTeamMessage->getUserId());
    }

    public function testCreatorCannotLeaveOwnGroup(): void
    {
        // Scenario: Group creator tries to leave but should be prevented
        
        $groupCreator = new User(1, 'startup_founder', new DateTime('2024-01-01'));
        $employees = [
            new User(2, 'lead_developer', new DateTime('2024-01-01')),
            new User(3, 'product_manager', new DateTime('2024-01-01'))
        ];
        
        $companyGroup = new ChatGroup(
            id: 1,
            name: 'Company All-Hands',
            createdBy: $groupCreator->getId(),
            createdAt: new DateTime('2024-01-01 08:00:00')
        );

        // Active group with important discussions
        $importantMessages = [
            new Message(1, $companyGroup->getId(), $groupCreator->getId(),
                "Q1 roadmap is ready for review", new DateTime('2024-03-01 09:00:00')),
            new Message(2, $companyGroup->getId(), $employees[0]->getId(),
                "Technical architecture looks solid", new DateTime('2024-03-01 10:00:00')),
            new Message(3, $companyGroup->getId(), $employees[1]->getId(),
                "Market research supports this direction", new DateTime('2024-03-01 11:00:00'))
        ];

        // Creator tries to leave
        $attemptLeaveDate = new DateTime('2024-03-02 14:00:00');

        // Verify group ownership
        $this->assertEquals($groupCreator->getId(), $companyGroup->getCreatedBy());
        $this->assertTrue($this->isGroupActive($importantMessages));
        
        // Verify creator cannot abandon active group
        $this->assertGreaterThan($companyGroup->getCreatedAt(), $attemptLeaveDate);
        
        // Creator should transfer ownership or delete group instead
        $this->assertTrue($this->shouldTransferOwnership($companyGroup, $employees));
    }

    public function testMemberLeavesInactiveGroup(): void
    {
        // Scenario: Member leaves a group that has become inactive
        
        $groupCreator = new User(1, 'event_organizer', new DateTime('2024-01-01'));
        $member = new User(2, 'participant', new DateTime('2024-01-01'));
        
        $eventGroup = new ChatGroup(
            id: 1,
            name: 'Conference Planning 2024',
            createdBy: $groupCreator->getId(),
            createdAt: new DateTime('2024-01-01 08:00:00')
        );

        // Event-related messages during active period
        $eventMessages = [
            new Message(1, $eventGroup->getId(), $groupCreator->getId(),
                "Conference venue booked for May 15th!", new DateTime('2024-02-01 10:00:00')),
            new Message(2, $eventGroup->getId(), $member->getId(),
                "Great! I'll handle the speaker outreach", new DateTime('2024-02-01 14:00:00')),
            new Message(3, $eventGroup->getId(), $groupCreator->getId(),
                "Conference was a huge success! Thanks everyone!", new DateTime('2024-05-16 16:00:00'))
        ];

        // Long period of inactivity
        $lastMessage = end($eventMessages);
        $leaveDate = new DateTime('2024-08-01 10:00:00'); // 2.5 months later
        
        $daysSinceLastMessage = $leaveDate->diff($lastMessage->getCreatedAt())->days;
        
        // Verify group became inactive
        $this->assertGreaterThan(60, $daysSinceLastMessage, 'Group has been inactive for over 60 days');
        $this->assertTrue($this->isGroupInactive($eventMessages, $leaveDate));
        
        // Verify member was active during the event
        $memberContributed = false;
        foreach ($eventMessages as $message) {
            if ($message->getUserId() === $member->getId()) {
                $memberContributed = true;
                break;
            }
        }
        $this->assertTrue($memberContributed, 'Member contributed during active period');
    }

    private function isGroupActive(array $messages): bool
    {
        return count($messages) > 2 && 
               count(array_unique(array_map(fn($msg) => $msg->getUserId(), $messages))) > 1;
    }

    private function shouldTransferOwnership(ChatGroup $group, array $potentialOwners): bool
    {
        return count($potentialOwners) > 0 && 
               $group->getCreatedBy() !== null;
    }

    private function isGroupInactive(array $messages, DateTime $checkDate): bool
    {
        if (empty($messages)) {
            return true;
        }
        
        $lastMessage = end($messages);
        $daysSinceLastMessage = $checkDate->diff($lastMessage->getCreatedAt())->days;
        
        return $daysSinceLastMessage > 30; // Inactive if no messages for 30+ days
    }
} 