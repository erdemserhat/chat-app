<?php

declare(strict_types=1);

namespace App\Application\Factory;

use App\Application\Handlers\HttpErrorHandler;
use App\Application\Handlers\ShutdownHandler;
use App\Application\ResponseEmitter\ResponseEmitter;
use App\Application\Settings\SettingsInterface;
use App\Infrastructure\Logger\Logger;
use App\Infrastructure\Repository\ChatGroupRepository;
use App\Infrastructure\Repository\MessageRepository;
use App\Infrastructure\Repository\UserRepository;
use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

class ChatAppFactory
{
    /**
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \Exception
     */
    public function create(): void
    {
        // Instantiate PHP-DI ContainerBuilder
        $containerBuilder = new ContainerBuilder();

        // Add settings
        $settings = require __DIR__ . '/../../../config/settings.php';
        $settings($containerBuilder);

        // Add dependencies
        $dependencies = require __DIR__ . '/../../../config/dependencies.php';
        $dependencies($containerBuilder);

        // Build PHP-DI Container instance
        $container = $containerBuilder->build();

        // Create App instance
        AppFactory::setContainer($container);
        $app = AppFactory::create();

        // Register middleware
        $middleware = require __DIR__ . '/../../../config/middleware.php';
        $middleware($app);

        // Register routes
        $routes = require __DIR__ . '/../../../config/routes.php';
        $routes($app);

        /** @var SettingsInterface $settings */
        $settings = $container->get(SettingsInterface::class);

        $displayErrorDetails = $settings->get()['displayErrorDetails'] ?? false;
        $logError = $settings->get()['logError'] ?? false;
        $logErrorDetails = $settings->get()['logErrorDetails'] ?? false;

        // Create Request object from globals
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request = $serverRequestCreator->createServerRequestFromGlobals();

        // Create Error Handler
        $responseFactory = $app->getResponseFactory();
        $errorHandler = new HttpErrorHandler($app->getCallableResolver(), $responseFactory);

        // Create Shutdown Handler
        $shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails, $logError, $logErrorDetails);
        register_shutdown_function($shutdownHandler);

        // Add Error Middleware
        $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, $logError, $logErrorDetails);
        $errorMiddleware->setDefaultErrorHandler($errorHandler);

        // Add Body Parsing Middleware
        $app->addBodyParsingMiddleware();

        // Add Routing Middleware
        $app->addRoutingMiddleware();

        // Run app
        $app->run();
    }
}
