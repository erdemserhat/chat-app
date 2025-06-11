<?php

declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Actions\ActionError;
use App\Application\Actions\ActionPayload;
use App\Domain\DomainException\DomainRecordNotFoundException;
use App\Domain\User\UserNotFoundException;
use App\Domain\ChatGroup\ChatGroupNameAlreadyExistsFoundException;
use App\Domain\Message\MessageNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpNotImplementedException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Throwable;

class HttpErrorHandler extends SlimErrorHandler
{
    /**
     * @inheritdoc
     */
    protected function respond(): Response
    {
        $exception = $this->exception;
        $statusCode = 500;
        $error = new ActionError(
            ActionError::SERVER_ERROR,
            'An internal server error occurred'
        );

        if ($exception instanceof HttpNotFoundException) {
            $statusCode = 404;
            $error = new ActionError(
                ActionError::RESOURCE_NOT_FOUND,
                'The requested resource was not found'
            );
        } elseif ($exception instanceof HttpMethodNotAllowedException) {
            $statusCode = 405;
            $error = new ActionError(
                ActionError::NOT_ALLOWED,
                'Method not allowed'
            );
        } elseif ($exception instanceof HttpUnauthorizedException) {
            $statusCode = 401;
            $error = new ActionError(
                ActionError::UNAUTHENTICATED,
                'Unauthorized'
            );
        } elseif ($exception instanceof HttpForbiddenException) {
            $statusCode = 403;
            $error = new ActionError(
                ActionError::FORBIDDEN,
                'Forbidden'
            );
        } elseif ($exception instanceof HttpBadRequestException) {
            $statusCode = 400;
            $error = new ActionError(
                ActionError::BAD_REQUEST,
                $exception->getMessage()
            );
        } elseif ($exception instanceof HttpNotImplementedException) {
            $statusCode = 501;
            $error = new ActionError(
                ActionError::NOT_IMPLEMENTED,
                'Not implemented'
            );
        } elseif ($exception instanceof DomainRecordNotFoundException) {
            $statusCode = 404;
            $error = new ActionError(
                ActionError::RESOURCE_NOT_FOUND,
                $exception->getMessage()
            );
        } elseif ($exception instanceof UserNotFoundException) {
            $statusCode = 404;
            $error = new ActionError(
                ActionError::RESOURCE_NOT_FOUND,
                'User not found'
            );
        } elseif ($exception instanceof ChatGroupNameAlreadyExistsFoundException) {
            $statusCode = 404;
            $error = new ActionError(
                ActionError::RESOURCE_NOT_FOUND,
                'Chat group not found'
            );
        } elseif ($exception instanceof MessageNotFoundException) {
            $statusCode = 404;
            $error = new ActionError(
                ActionError::RESOURCE_NOT_FOUND,
                'Message not found'
            );
        }

        if (
            !($exception instanceof HttpException)
            && $exception instanceof Throwable
            && $this->displayErrorDetails
        ) {
            $error->setDescription($exception->getMessage());
        }

        $payload = new ActionPayload(null, $error);
        $encodedPayload = json_encode($payload, JSON_PRETTY_PRINT);

        $response = $this->responseFactory->createResponse($statusCode);
        $response->getBody()->write($encodedPayload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
