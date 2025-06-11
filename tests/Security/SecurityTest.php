<?php

declare(strict_types=1);

namespace Tests\Security;

use App\Domain\User\User;
use App\Domain\ChatGroup\ChatGroup;
use App\Domain\Message\Message;
use DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Security-focused tests for potential vulnerabilities
 */
class SecurityTest extends TestCase
{
    public function testXSSAttemptInUsername(): void
    {
        // Security test: XSS attempt in username
        $xssUsername = '<script>alert("XSS")</script>';
        $user = new User(1, $xssUsername, new DateTime());

        // The domain model should store the raw input
        // XSS prevention should happen at the presentation layer
        $this->assertEquals($xssUsername, $user->getUsername());
        $this->assertStringContainsString('<script>', $user->getUsername());
    }

    public function testXSSAttemptInGroupName(): void
    {
        // Security test: XSS attempt in group name
        $xssGroupName = '<img src="x" onerror="alert(1)">';
        $group = new ChatGroup(
            id: 1,
            name: $xssGroupName,
            createdBy: 1,
            createdAt: new DateTime()
        );

        $this->assertEquals($xssGroupName, $group->getName());
        $this->assertStringContainsString('<img', $group->getName());
    }

    public function testXSSAttemptInMessageContent(): void
    {
        // Security test: XSS attempt in message content
        $xssMessage = '<iframe src="javascript:alert(\'XSS\')"></iframe>';
        $message = new Message(
            id: 1,
            groupId: 1,
            userId: 1,
            content: $xssMessage,
            createdAt: new DateTime()
        );

        $this->assertEquals($xssMessage, $message->getContent());
        $this->assertStringContainsString('<iframe', $message->getContent());
    }

    public function testSQLInjectionAttemptInUsername(): void
    {
        // Security test: SQL injection attempt in username
        $sqlInjectionUsername = "admin'; DROP TABLE users; --";
        $user = new User(1, $sqlInjectionUsername, new DateTime());

        // Domain model should store raw input - SQL injection prevention
        // should happen at the repository layer with prepared statements
        $this->assertEquals($sqlInjectionUsername, $user->getUsername());
        $this->assertStringContainsString("DROP TABLE", $user->getUsername());
    }

    public function testCommandInjectionAttemptInMessage(): void
    {
        // Security test: Command injection attempt
        $commandInjection = "Hello; rm -rf /; echo 'hacked'";
        $message = new Message(
            id: 1,
            groupId: 1,
            userId: 1,
            content: $commandInjection,
            createdAt: new DateTime()
        );

        $this->assertEquals($commandInjection, $message->getContent());
        $this->assertStringContainsString('rm -rf', $message->getContent());
    }

    public function testDirectoryTraversalAttemptInGroupName(): void
    {
        // Security test: Directory traversal attempt
        $traversalAttempt = '../../../etc/passwd';
        $group = new ChatGroup(
            id: 1,
            name: $traversalAttempt,
            createdBy: 1,
            createdAt: new DateTime()
        );

        $this->assertEquals($traversalAttempt, $group->getName());
        $this->assertStringContainsString('../', $group->getName());
    }

    public function testLDAPInjectionAttemptInUsername(): void
    {
        // Security test: LDAP injection attempt
        $ldapInjection = 'admin)(cn=*))(|(cn=*';
        $user = new User(1, $ldapInjection, new DateTime());

        $this->assertEquals($ldapInjection, $user->getUsername());
        $this->assertStringContainsString(')(cn=', $user->getUsername());
    }

    public function testHeaderInjectionAttemptInMessage(): void
    {
        // Security test: HTTP header injection attempt
        $headerInjection = "Normal message\r\nSet-Cookie: admin=true\r\nLocation: evil.com";
        $message = new Message(
            id: 1,
            groupId: 1,
            userId: 1,
            content: $headerInjection,
            createdAt: new DateTime()
        );

        $this->assertEquals($headerInjection, $message->getContent());
        $this->assertStringContainsString('Set-Cookie', $message->getContent());
        $this->assertStringContainsString("\r\n", $message->getContent());
    }

    public function testXMLExternalEntityAttemptInMessage(): void
    {
        // Security test: XXE (XML External Entity) attempt
        $xxeAttempt = '<?xml version="1.0"?><!DOCTYPE root [<!ENTITY read SYSTEM ' .
            '"file:///etc/passwd">]><root>&read;</root>';
        $message = new Message(
            id: 1,
            groupId: 1,
            userId: 1,
            content: $xxeAttempt,
            createdAt: new DateTime()
        );

        $this->assertEquals($xxeAttempt, $message->getContent());
        $this->assertStringContainsString('<!ENTITY', $message->getContent());
        $this->assertStringContainsString('file:///', $message->getContent());
    }

    public function testCSRFTokenAttemptInUsername(): void
    {
        // Security test: CSRF token-like string in username
        $csrfUsername = 'user_abc123def456ghi789csrf_token';
        $user = new User(1, $csrfUsername, new DateTime());

        $this->assertEquals($csrfUsername, $user->getUsername());
        $this->assertStringContainsString('csrf_token', $user->getUsername());
    }

    public function testJSONInjectionAttemptInMessage(): void
    {
        // Security test: JSON injection attempt
        $jsonInjection = '{"admin":true,"role":"administrator"}';
        $message = new Message(
            id: 1,
            groupId: 1,
            userId: 1,
            content: $jsonInjection,
            createdAt: new DateTime()
        );

        $this->assertEquals($jsonInjection, $message->getContent());
        $this->assertStringContainsString('"admin":true', $message->getContent());
    }

    public function testScriptTagVariationsInMessage(): void
    {
        // Security test: Various script tag bypasses
        $scriptVariations = [
            '<SCRIPT>alert("XSS")</SCRIPT>',
            '<script src="data:text/javascript,alert(1)"></script>',
            '<svg onload="alert(1)">',
            'javascript:alert(1)',
            'data:text/html,<script>alert(1)</script>'
        ];

        foreach ($scriptVariations as $index => $scriptAttempt) {
            $message = new Message(
                id: $index + 1,
                groupId: 1,
                userId: 1,
                content: $scriptAttempt,
                createdAt: new DateTime()
            );

            $this->assertEquals($scriptAttempt, $message->getContent());
        }
    }

    public function testUnicodeNormalizationAttack(): void
    {
        // Security test: Unicode normalization attack
        $unicodeAttack = 'admin\u0041\u0064\u006D\u0069\u006E'; // "adminAdmin" in unicode
        $user = new User(1, $unicodeAttack, new DateTime());

        $this->assertEquals($unicodeAttack, $user->getUsername());
        $this->assertStringContainsString('\u', $user->getUsername());
    }

    public function testNullByteInjectionInGroupName(): void
    {
        // Security test: Null byte injection
        $nullByteAttack = "legitimate_name\x00.evil.php";
        $group = new ChatGroup(
            id: 1,
            name: $nullByteAttack,
            createdBy: 1,
            createdAt: new DateTime()
        );

        $this->assertEquals($nullByteAttack, $group->getName());
        $this->assertStringContainsString("\x00", $group->getName());
    }

    public function testExcessivelyLongPayloadDOS(): void
    {
        // Security test: DoS attempt with excessively long content
        $longPayload = str_repeat('A', 1000000); // 1MB of 'A's
        $message = new Message(
            id: 1,
            groupId: 1,
            userId: 1,
            content: $longPayload,
            createdAt: new DateTime()
        );

        $this->assertEquals($longPayload, $message->getContent());
        $this->assertEquals(1000000, strlen($message->getContent()));
    }

    public function testSpecialCharacterBombInMessage(): void
    {
        // Security test: Unicode bombs and special character attacks
        $specialChars = str_repeat('💣', 1000) . str_repeat('🔥', 1000);
        $message = new Message(
            id: 1,
            groupId: 1,
            userId: 1,
            content: $specialChars,
            createdAt: new DateTime()
        );

        $this->assertEquals($specialChars, $message->getContent());
        $this->assertStringContainsString('💣', $message->getContent());
        $this->assertStringContainsString('🔥', $message->getContent());
    }

    public function testPathTraversalInMultipleFormats(): void
    {
        // Security test: Path traversal in different formats
        $pathTraversalAttempts = [
            '../../../../etc/passwd',
            '..\\..\\..\\..\\windows\\system32\\config\\sam',
            '%2e%2e%2f%2e%2e%2f%2e%2e%2f%etc%2fpasswd',
            '....//....//....//etc/passwd',
            '..\\..\\..\\.\\windows\\system32\\drivers\\etc\\hosts'
        ];

        foreach ($pathTraversalAttempts as $index => $attempt) {
            $group = new ChatGroup(
                id: $index + 1,
                name: $attempt,
                createdBy: 1,
                createdAt: new DateTime()
            );

            $this->assertEquals($attempt, $group->getName());
        }
    }

    public function testSessionFixationAttemptInUsername(): void
    {
        // Security test: Session fixation attempt
        $sessionFixation = 'user;PHPSESSID=attacker_controlled_session_id';
        $user = new User(1, $sessionFixation, new DateTime());

        $this->assertEquals($sessionFixation, $user->getUsername());
        $this->assertStringContainsString('PHPSESSID', $user->getUsername());
    }
}
