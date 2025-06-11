<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Services\ResponseService;
use App\Application\Validators\UserValidator;
use App\Infrastructure\Logger\Logger;
use App\Infrastructure\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class UserAction
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserValidator $validator,
        private readonly ResponseService $responseService
    ) {
    }

    public function create(Request $request, Response $response): Response
    {
        try {
            $data = $request->getParsedBody();

            // Validate request
            $validationError = $this->validator->validateCreateRequest($data);
            if ($validationError) {
                Logger::error('Invalid user creation request', ['data' => $data]);
                return $this->responseService->error($response, $validationError, 400);
            }

            $username = $this->validator->sanitizeUsername($data['username']);

            // Check if username already exists
            $existingUser = $this->userRepository->findByUsername($username);
            if ($existingUser !== null) {
                Logger::error('Username already exists', ['username' => $username]);
                return $this->responseService->conflict($response, 'This username is already taken');
            }

            // Create user
            $user = $this->userRepository->create($username);

            Logger::info('User created successfully', [
                'user_id' => $user->getId(),
                'username' => $user->getUsername()
            ]);

            return $this->responseService->success($response, [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
            ], 201);
        } catch (\Exception $e) {
            Logger::error('Error creating user', ['error' => $e->getMessage()]);
            return $this->responseService->serverError($response, 'An error occurred while creating the user');
        }
    }

    public function get(Request $request, Response $response, array $args): Response
    {
        try {
            $userId = isset($args['id']) ? (int) $args['id'] : null;

            // Validate user ID
            $validationError = $this->validator->validateUserId($userId);
            if ($validationError) {
                Logger::error('Invalid user ID in request arguments', ['user_id' => $userId]);
                return $this->responseService->error($response, $validationError, 400);
            }

            // Find user
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                Logger::error('User not found', ['user_id' => $userId]);
                return $this->responseService->notFound($response, 'User not found');
            }

            Logger::info('User retrieved successfully', [
                'user_id' => $user->getId(),
                'username' => $user->getUsername()
            ]);

            return $this->responseService->success($response, [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            Logger::error('Error retrieving user', ['error' => $e->getMessage()]);
            return $this->responseService->serverError($response, 'An error occurred while retrieving user information');
        }
    }

    public function list(Request $request, Response $response): Response
    {
        try {
            $users = $this->userRepository->findAll();

            Logger::info('Users listed successfully', ['count' => count($users)]);

            $userList = array_map([$this, 'formatUser'], $users);

            return $this->responseService->success($response, $userList);
        } catch (\Exception $e) {
            Logger::error('Error listing users', ['error' => $e->getMessage()]);
            return $this->responseService->serverError($response, 'An error occurred while retrieving user list');
        }
    }

    private function formatUser($user): array
    {
        return [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s')
        ];
    }
}
