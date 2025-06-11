<?php

declare(strict_types=1);

namespace App\Application\Services;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use Psr\Http\Message\ResponseInterface as Response;

class ResponseService
{
    public function success(Response $response, $data = null, int $statusCode = 200): Response
    {
        $payload = new ActionPayload($data);
        $response->getBody()->write(json_encode($payload));
        return $response->withStatus($statusCode)->withHeader('Content-Type', 'application/json');
    }

    public function error(Response $response, ActionError $error, int $statusCode = 400): Response
    {
        $payload = new ActionPayload(null, $error);
        $response->getBody()->write(json_encode($payload));
        return $response->withStatus($statusCode)->withHeader('Content-Type', 'application/json');
    }

    public function validationError(Response $response, string $message): Response
    {
        $error = new ActionError(ActionError::VALIDATION_ERROR, $message);
        return $this->error($response, $error, 400);
    }

    public function notFound(Response $response, string $message): Response
    {
        $error = new ActionError(ActionError::RESOURCE_NOT_FOUND, $message);
        return $this->error($response, $error, 404);
    }

    public function conflict(Response $response, string $message): Response
    {
        $error = new ActionError(ActionError::VALIDATION_ERROR, $message);
        return $this->error($response, $error, 409);
    }

    public function serverError(Response $response, string $message): Response
    {
        $error = new ActionError(ActionError::SERVER_ERROR, $message);
        return $this->error($response, $error, 500);
    }

    public function forbidden(Response $response, string $message): Response
    {
        $error = new ActionError(ActionError::FORBIDDEN, $message);
        return $this->error($response, $error, 403);
    }
} 