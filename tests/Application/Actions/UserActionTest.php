<?php

declare(strict_types=1);

namespace Tests\Application\Actions;

use App\Application\Actions\UserAction;
use App\Application\Services\ResponseService;
use App\Application\Validators\UserValidator;
use App\Domain\User\User;
use App\Infrastructure\Repository\UserRepository;
use DateTime;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class UserActionTest extends TestCase
{
    private UserRepository|MockObject $userRepository;
    private UserValidator|MockObject $validator;
    private ResponseService|MockObject $responseService;
    private UserAction $userAction;
    private ServerRequestInterface|MockObject $request;
    private ResponseInterface|MockObject $response;
    private StreamInterface|MockObject $stream;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->validator = $this->createMock(UserValidator::class);
        $this->responseService = $this->createMock(ResponseService::class);
        $this->userAction = new UserAction($this->userRepository, $this->validator, $this->responseService);
        
        $this->request = $this->createMock(ServerRequestInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->stream = $this->createMock(StreamInterface::class);
        
        $this->response->method('getBody')->willReturn($this->stream);
        $this->response->method('withStatus')->willReturnSelf();
        $this->response->method('withHeader')->willReturnSelf();
    }

    public function testCreateUserSuccessfully(): void
    {
        // Arrange
        $userData = ['username' => 'john_doe'];
        $expectedUser = new User(1, 'john_doe', new DateTime());

        $this->request->method('getParsedBody')->willReturn($userData);
        $this->validator->method('validateCreateRequest')->with($userData)->willReturn(null);
        $this->validator->method('sanitizeUsername')->with('john_doe')->willReturn('john_doe');
        $this->userRepository->method('findByUsername')->with('john_doe')->willReturn(null);
        $this->userRepository->method('create')->with('john_doe')->willReturn($expectedUser);
        $this->responseService->method('success')->willReturn($this->response);

        // Act
        $result = $this->userAction->create($this->request, $this->response);

        // Assert
        $this->assertSame($this->response, $result);
    }

    public function testCreateUserWithValidationError(): void
    {
        // Arrange
        $userData = ['username' => ''];
        $validationError = new \App\Application\Actions\ActionError('VALIDATION_ERROR', 'Username is required');

        $this->request->method('getParsedBody')->willReturn($userData);
        $this->validator->method('validateCreateRequest')->with($userData)->willReturn($validationError);
        $this->responseService->method('error')->willReturn($this->response);

        // Act
        $result = $this->userAction->create($this->request, $this->response);

        // Assert
        $this->assertSame($this->response, $result);
    }

    public function testCreateUserWithExistingUsername(): void
    {
        // Arrange
        $userData = ['username' => 'existing_user'];
        $existingUser = new User(1, 'existing_user', new DateTime());

        $this->request->method('getParsedBody')->willReturn($userData);
        $this->validator->method('validateCreateRequest')->with($userData)->willReturn(null);
        $this->validator->method('sanitizeUsername')->with('existing_user')->willReturn('existing_user');
        $this->userRepository->method('findByUsername')->with('existing_user')->willReturn($existingUser);
        $this->responseService->method('conflict')->willReturn($this->response);

        // Act
        $result = $this->userAction->create($this->request, $this->response);

        // Assert
        $this->assertSame($this->response, $result);
    }

    public function testGetUserSuccessfully(): void
    {
        // Arrange
        $userId = 1;
        $user = new User($userId, 'test_user', new DateTime('2024-01-01 10:00:00'));
        $args = ['id' => (string)$userId];

        $this->validator->method('validateUserId')->with($userId)->willReturn(null);
        $this->userRepository->method('findById')->with($userId)->willReturn($user);
        $this->responseService->method('success')->willReturn($this->response);

        // Act
        $result = $this->userAction->get($this->request, $this->response, $args);

        // Assert
        $this->assertSame($this->response, $result);
    }

    public function testGetUserNotFound(): void
    {
        // Arrange
        $userId = 999;
        $args = ['id' => (string)$userId];

        $this->validator->method('validateUserId')->with($userId)->willReturn(null);
        $this->userRepository->method('findById')->with($userId)->willReturn(null);
        $this->responseService->method('notFound')->willReturn($this->response);

        // Act
        $result = $this->userAction->get($this->request, $this->response, $args);

        // Assert
        $this->assertSame($this->response, $result);
    }

    public function testGetUserWithInvalidId(): void
    {
        // Arrange
        $args = ['id' => 'invalid'];
        $validationError = new \App\Application\Actions\ActionError('VALIDATION_ERROR', 'Valid user ID is required');

        $this->validator->method('validateUserId')->with(0)->willReturn($validationError);
        $this->responseService->method('error')->willReturn($this->response);

        // Act
        $result = $this->userAction->get($this->request, $this->response, $args);

        // Assert
        $this->assertSame($this->response, $result);
    }

    public function testListUsersSuccessfully(): void
    {
        // Arrange
        $users = [
            new User(1, 'user1', new DateTime('2024-01-01')),
            new User(2, 'user2', new DateTime('2024-01-02'))
        ];
        $this->userRepository->method('findAll')->willReturn($users);
        $this->responseService->method('success')->willReturn($this->response);

        // Act
        $result = $this->userAction->list($this->request, $this->response);

        // Assert
        $this->assertSame($this->response, $result);
    }
} 