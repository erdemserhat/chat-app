<?php

declare(strict_types=1);

namespace Tests\Domain\ChatGroup;

use App\Domain\ChatGroup\ChatGroupNameAlreadyExistsFoundException;
use PHPUnit\Framework\TestCase;

class ChatGroupNameAlreadyExistsFoundExceptionTest extends TestCase
{
    public function testExceptionCanBeCreated(): void
    {
        $exception = new ChatGroupNameAlreadyExistsFoundException();

        $this->assertInstanceOf(ChatGroupNameAlreadyExistsFoundException::class, $exception);
        $this->assertEquals('The chat group name is already in use', $exception->getMessage());
    }

    public function testExceptionIsThrowable(): void
    {
        $this->expectException(ChatGroupNameAlreadyExistsFoundException::class);
        $this->expectExceptionMessage('The chat group name is already in use');

        throw new ChatGroupNameAlreadyExistsFoundException();
    }
} 