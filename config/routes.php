<?php

declare(strict_types=1);


use App\Application\Actions\ChatGroupAction;
use App\Application\Actions\MessageAction;
use App\Application\Actions\UserAction;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->group('/api', function (Group $group) {
        // User routes
        $group->group('/users', function (Group $group) {
            $group->get('', [UserAction::class, 'list']);
            $group->post('', [UserAction::class, 'create']);
            $group->get('/{id}', [UserAction::class, 'get']);
        });

        // Chat group routes
        $group->group('/groups', function (Group $group) {
            $group->get('', [ChatGroupAction::class, 'list']);
            $group->post('', [ChatGroupAction::class, 'create']);
            $group->post('/{id}/join', [ChatGroupAction::class, 'join']);
            $group->post('/{id}/leave', [ChatGroupAction::class, 'leave']);
        });

        // Message routes
        $group->group('/messages', function (Group $group) {
            $group->get('/group/{group_id}', [MessageAction::class, 'list']);
            $group->post('/group/{group_id}', [MessageAction::class, 'create']);
        });
    });

    // Root route
    $app->get('/', function ($request, $response) {
        $response->getBody()->write(json_encode([
            'message' => 'Welcome to Chat API',
            'version' => '1.0.0',
            'endpoints' => [
                'users' => [
                    'GET /api/users' => 'List all users',
                    'POST /api/users' => 'Create a new user',
                    'GET /api/users/{id}' => 'Get user details'
                ],
                'groups' => [
                    'GET /api/groups' => 'List all chat groups',
                    'POST /api/groups' => 'Create a new chat group',
                    'POST /api/groups/{id}/join' => 'Join a chat group',
                    'POST /api/groups/{id}/leave' => 'Leave a chat group'
                ],
                'messages' => [
                    'GET /api/messages/group/{groupId}' => 'List messages in a group',
                    'POST /api/messages/group/{groupId}' => 'Send a message to a group'
                ]
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });
}; 