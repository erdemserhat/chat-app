<?php

declare(strict_types=1);

namespace Tests\Domain\User;

use App\Domain\User\User;
use DateTime;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCanBeCreated(): void
    {
        $id = 1;
        $username = 'test_user';
        $createdAt = new DateTime('2024-01-01 10:00:00');

        $user = new User($id, $username, $createdAt);

        $this->assertEquals($id, $user->getId());
        $this->assertEquals($username, $user->getUsername());
        $this->assertEquals($createdAt, $user->getCreatedAt());
    }

    public function testUserPropertiesAreCorrect(): void
    {
        $user = new User(
            id: 123,
            username: 'john_doe',
            createdAt: new DateTime('2024-01-15 14:30:00')
        );

        $this->assertIsInt($user->getId());
        $this->assertIsString($user->getUsername());
        $this->assertInstanceOf(DateTime::class, $user->getCreatedAt());
    }

    public function testUserCanHaveSpecialCharactersInUsername(): void
    {
        $username = 'user_with-special.chars';
        $user = new User(1, $username, new DateTime());

        $this->assertEquals($username, $user->getUsername());
    }
}
