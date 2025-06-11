<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Application\Services\ResponseService;
use App\Application\Validators\ChatGroupValidator;
use App\Domain\User\UserNotFoundException;
use App\Infrastructure\Logger\Logger;
use App\Infrastructure\Repository\ChatGroupRepository;
use App\Infrastructure\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ChatGroupAction
{
    public function __construct(
        private readonly ChatGroupRepository $chatGroupRepository,
        private readonly UserRepository $userRepository,
        private readonly ChatGroupValidator $validator,
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
                Logger::error('Invalid chat group creation request', ['data' => $data]);
                return $this->responseService->error($response, $validationError, 400);
            }

            $name = $this->validator->sanitizeGroupName($data['name']);
            $userId = (int) $data['user_id'];

            // Check if user exists
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                Logger::error('User not found for chat group creation', ['user_id' => $userId]);
                return $this->responseService->notFound($response, 'User not found');
            }

            // Create group
            $group = $this->chatGroupRepository->create($name, $user->getId());

            Logger::info('Chat group created successfully', [
                'group_id' => $group->getId(),
                'name' => $group->getName(),
                'created_by' => $user->getId()
            ]);

            return $this->responseService->success($response, [
                'id' => $group->getId(),
                'name' => $group->getName(),
                'created_by' => $group->getCreatedBy(),
                'created_at' => $group->getCreatedAt()->format('Y-m-d H:i:s')
            ], 201);
        } catch (UniqueConstraintViolationException $e) {
            Logger::error('Group name already exists', ['name' => $name ?? 'unknown']);
            return $this->responseService->conflict($response, 'A group with this name already exists');
        } catch (\Exception $e) {
            Logger::error('Error creating chat group', ['error' => $e->getMessage()]);
            return $this->responseService->serverError($response, 'An error occurred while creating the chat group');
        }
    }

    public function list(Request $request, Response $response): Response
    {
        try {
            $groups = $this->chatGroupRepository->findAll();

            Logger::info('Chat groups listed successfully', ['count' => count($groups)]);

            $groupData = array_map(function ($group) {
                return [
                    'id' => $group->getId(),
                    'name' => $group->getName(),
                    'created_by' => $group->getCreatedBy(),
                    'created_at' => $group->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }, $groups);

            return $this->responseService->success($response, $groupData);
        } catch (\Exception $e) {
            Logger::error('Error listing chat groups', ['error' => $e->getMessage()]);
            return $this->responseService->serverError(
                $response,
                'An error occurred while retrieving the chat group list'
            );
        }
    }

    public function join(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $request->getParsedBody();

            // Validate request
            $validationError = $this->validator->validateJoinRequest($data, $args);
            if ($validationError) {
                Logger::error('Invalid join request', ['data' => $data, 'args' => $args]);
                return $this->responseService->error($response, $validationError, 400);
            }

            $userId = (int) $data['user_id'];
            $groupId = (int) $args['id'];

            // Check if group exists
            $group = $this->chatGroupRepository->getGroup($groupId);
            if (!$group) {
                Logger::error('Chat group not found for join request', ['group_id' => $groupId]);
                return $this->responseService->notFound($response, 'Chat group not found');
            }

            // Check if user exists
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                Logger::error('User not found for join request', ['user_id' => $userId]);
                return $this->responseService->notFound($response, 'User not found');
            }

            // Check if user is already a member
            if ($this->chatGroupRepository->isMember($groupId, $userId)) {
                Logger::error('User is already a member of the group', [
                    'user_id' => $userId,
                    'group_id' => $groupId
                ]);
                return $this->responseService->validationError(
                    $response,
                    'User is already a member of this group'
                );
            }

            // Add user to group
            $this->chatGroupRepository->addMember($groupId, $userId);

            Logger::info('User joined chat group successfully', [
                'user_id' => $userId,
                'group_id' => $groupId
            ]);

            return $this->responseService->success($response, [
                'message' => 'Successfully joined the group',
                'group_name' => $group->getName(),
                'user_id' => $userId
            ]);
        } catch (\Exception $e) {
            Logger::error('Error joining chat group', ['error' => $e->getMessage()]);
            return $this->responseService->serverError($response, 'An error occurred while joining the chat group');
        }
    }

    public function leave(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $request->getParsedBody();

            // Validate request
            $validationError = $this->validator->validateLeaveRequest($data, $args);
            if ($validationError) {
                Logger::error('Invalid leave request', ['data' => $data, 'args' => $args]);
                return $this->responseService->error($response, $validationError, 400);
            }

            $userId = (int) $data['user_id'];
            $groupId = (int) $args['id'];

            // Check if group exists
            $group = $this->chatGroupRepository->getGroup($groupId);
            if (!$group) {
                Logger::error('Chat group not found for leave request', ['group_id' => $groupId]);
                return $this->responseService->notFound($response, 'Chat group not found');
            }

            // Check if user exists
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                Logger::error('User not found for leave request', ['user_id' => $userId]);
                return $this->responseService->notFound($response, 'User not found');
            }

            // Check if user is a member
            if (!$this->chatGroupRepository->isMember($groupId, $userId)) {
                Logger::error('User is not a member of the group', [
                    'user_id' => $userId,
                    'group_id' => $groupId
                ]);
                return $this->responseService->validationError($response, 'User is not a member of this group');
            }

            // Check if user is group creator (prevent creator from leaving)
            if ($this->chatGroupRepository->isGroupCreator($groupId, $userId)) {
                Logger::error('Group creator cannot leave the group', [
                    'user_id' => $userId,
                    'group_id' => $groupId
                ]);
                return $this->responseService->forbidden(
                    $response,
                    'Group creator cannot leave the group. Transfer ownership or delete the group instead.'
                );
            }

            // Remove user from group
            $removed = $this->chatGroupRepository->removeMember($groupId, $userId);

            if (!$removed) {
                Logger::error('Failed to remove user from group', [
                    'user_id' => $userId,
                    'group_id' => $groupId
                ]);
                return $this->responseService->serverError($response, 'Failed to leave the group');
            }

            Logger::info('User left chat group successfully', [
                'user_id' => $userId,
                'group_id' => $groupId
            ]);

            return $this->responseService->success($response, [
                'message' => 'Successfully left the group',
                'group_name' => $group->getName(),
                'user_id' => $userId
            ]);
        } catch (\Exception $e) {
            Logger::error('Error leaving chat group', ['error' => $e->getMessage()]);
            return $this->responseService->serverError($response, 'An error occurred while leaving the chat group');
        }
    }
}
