<?php

declare(strict_types=1);

namespace App\Application\Actions;

use App\Domain\ChatGroup\ChatGroupNameAlreadyExistsFoundException;
use App\Domain\User\UserNotFoundException;
use App\Infrastructure\Logger\Logger;
use App\Infrastructure\Repository\MessageRepository;
use App\Infrastructure\Repository\ChatGroupRepository;
use App\Infrastructure\Repository\UserRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MessageAction
{
    private MessageRepository $messageRepository;
    private ChatGroupRepository $chatGroupRepository;
    private UserRepository $userRepository;

    public function __construct(
        MessageRepository $messageRepository,
        ChatGroupRepository $chatGroupRepository,
        UserRepository $userRepository
    ) {
        $this->messageRepository = $messageRepository;
        $this->chatGroupRepository = $chatGroupRepository;
        $this->userRepository = $userRepository;
    }

    public function create(Request $request, Response $response, array $args): Response
    {
        try {
            $data = $request->getParsedBody();

            // Assigning request and route parameters to local variables
            $groupId = isset($args['group_id']) ? (int)$args['group_id'] : null;
            $userId = isset($data['user_id']) ? (int)$data['user_id'] : null;
            $content = isset($data['content']) ? trim($data['content']) : null;

            // Validation check for required fields
            if (!$groupId || !$userId || empty($content)) {
                Logger::error('Invalid message creation request', ['data' => $data]);
                $error = new ActionError(
                    ActionError::VALIDATION_ERROR,
                    'Group ID, user ID, and content are required'
                );
                $payload = new ActionPayload(null, $error);
                $response->getBody()->write(json_encode($payload));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            // Check if group exists
            $group = $this->chatGroupRepository->getGroup($groupId);
            if (!$group) {
                Logger::error('Chat group not found', ['group_id' => $groupId]);
                $error = new ActionError(
                    ActionError::RESOURCE_NOT_FOUND,
                    'Chat group not found'
                );
                $payload = new ActionPayload(null, $error);
                $response->getBody()->write(json_encode($payload));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Check if user exists
            $user = $this->userRepository->findById($userId);
            if (!$user) {
                Logger::error('User not found', ['user_id' => $userId]);
                $error = new ActionError(
                    ActionError::RESOURCE_NOT_FOUND,
                    'User not found'
                );
                $payload = new ActionPayload(null, $error);
                $response->getBody()->write(json_encode($payload));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Check if user is a member of the group
            if (!$this->chatGroupRepository->isMember($groupId, $userId)) {
                Logger::error('User is not a member of the group', [
                    'user_id' => $userId,
                    'group_id' => $groupId
                ]);
                $error = new ActionError(
                    ActionError::FORBIDDEN,
                    'You must be a member of this group to send messages.'
                );
                $payload = new ActionPayload(null, $error);
                $response->getBody()->write(json_encode($payload));
                return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
            }

            // Create message
            $message = $this->messageRepository->create($groupId, $userId, $content);

            Logger::info('Message created successfully', [
                'message_id' => $message->getId(),
                'group_id' => $groupId,
                'user_id' => $userId
            ]);

            // Return success response
            $payload = new ActionPayload([
                'id' => $message->getId(),
                'group_id' => $message->getGroupId(),
                'user_id' => $message->getUserId(),
                'content' => $message->getContent(),
                'created_at' => $message->getCreatedAt()->format('Y-m-d H:i:s')
            ]);
            $response->getBody()->write(json_encode($payload));
            return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            // Handle unexpected error
            Logger::error('Error creating message', ['error' => $e->getMessage()]);
            $error = new ActionError(
                ActionError::SERVER_ERROR,
                'An error occurred while creating the message'
            );
            $payload = new ActionPayload(null, $error);
            $response->getBody()->write(json_encode($payload));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }

    public function list(Request $request, Response $response, array $args): Response
    {
        try {
            // Extract group ID from route parameters
            $groupId = isset($args['group_id']) ? (int)$args['group_id'] : null;

            // Validate group ID
            if (!$groupId) {
                Logger::error('Group ID parameter missing in message list request');
                $error = new ActionError(
                    ActionError::VALIDATION_ERROR,
                    'Group ID is required'
                );
                $payload = new ActionPayload(null, $error);
                $response->getBody()->write(json_encode($payload));
                return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
            }

            // Check if group exists
            $group = $this->chatGroupRepository->getGroup($groupId);
            if (!$group) {
                Logger::error('Chat group not found', ['group_id' => $groupId]);
                $error = new ActionError(
                    ActionError::RESOURCE_NOT_FOUND,
                    'Chat group not found'
                );
                $payload = new ActionPayload(null, $error);
                $response->getBody()->write(json_encode($payload));
                return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            }

            // Extract optional user ID from query parameters
            $queryParams = $request->getQueryParams();
            $userId = isset($queryParams['user_id']) ? (int)$queryParams['user_id'] : null;

            // If user ID is provided, validate user and group membership
            if ($userId) {
                $user = $this->userRepository->findById($userId);
                if (!$user) {
                    Logger::error('User not found', ['user_id' => $userId]);
                    $error = new ActionError(
                        ActionError::RESOURCE_NOT_FOUND,
                        'User not found'
                    );
                    $payload = new ActionPayload(null, $error);
                    $response->getBody()->write(json_encode($payload));
                    return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
                }

                if (!$this->chatGroupRepository->isMember($groupId, $userId)) {
                    Logger::error('User is not a member of the group', [
                        'user_id' => $userId,
                        'group_id' => $groupId
                    ]);
                    $error = new ActionError(
                        ActionError::FORBIDDEN,
                        'You must be a member of this group to view messages.'
                    );
                    $payload = new ActionPayload(null, $error);
                    $response->getBody()->write(json_encode($payload));
                    return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
                }
            }

            // Retrieve messages from the repository
            $messages = $this->messageRepository->findByGroupId($groupId);

            Logger::info('Messages listed successfully', [
                'group_id' => $groupId,
                'count' => count($messages)
            ]);


            // Fetch user info in advance to avoid redundant DB calls
            $usernames = [];
            foreach ($messages as $message) {
                $userId = $message->getUserId();
                if (!isset($usernames[$userId])) {
                    $user = $this->userRepository->findById($userId);
                    $usernames[$userId] = $user ? $user->getUsername() : 'Unknown';
                }
            }

            // Prepare the response payload
            $formattedMessages = [];
            foreach ($messages as $message) {
                $formattedMessages[] = [
                    'id' => $message->getId(),
                    'group_id' => $message->getGroupId(),
                    'username' => $usernames[$message->getUserId()],
                    'content' => $message->getContent(),
                    'created_at' => $message->getCreatedAt()->format('Y-m-d H:i:s')
                ];
            }

            $payload = new ActionPayload($formattedMessages);

            $response->getBody()->write(json_encode($payload));
            return $response->withHeader('Content-Type', 'application/json');
        } catch (\Exception $e) {
            // Handle unexpected error
            Logger::error('Error listing messages', ['error' => $e->getMessage()]);
            $error = new ActionError(
                ActionError::SERVER_ERROR,
                'An error occurred while retrieving messages'
            );
            $payload = new ActionPayload(null, $error);
            $response->getBody()->write(json_encode($payload));
            return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
        }
    }
}
