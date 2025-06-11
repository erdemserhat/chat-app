<?php

declare(strict_types=1);

use App\Application\Actions\ChatGroupAction;
use App\Application\Actions\MessageAction;
use App\Application\Actions\UserAction;
use App\Application\Services\ResponseService;
use App\Application\Validators\ChatGroupValidator;
use App\Application\Validators\UserValidator;
use App\Application\Settings\SettingsInterface;
use App\Infrastructure\Repository\ChatGroupRepository;
use App\Infrastructure\Repository\MessageRepository;
use App\Infrastructure\Repository\UserRepository;
use DI\ContainerBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Psr\Container\ContainerInterface;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        Connection::class => function (ContainerInterface $c) {
            $settings = $c->get(SettingsInterface::class);
            $dbSettings = $settings->get()['db'];

            return DriverManager::getConnection([
                'driver' => 'pdo_sqlite',
                'path' => $dbSettings['path'],
            ]);
        },
        UserRepository::class => function (ContainerInterface $c) {
            return new UserRepository($c->get(Connection::class));
        },
        ChatGroupRepository::class => function (ContainerInterface $c) {
            return new ChatGroupRepository($c->get(Connection::class));
        },
        MessageRepository::class => function (ContainerInterface $c) {
            return new MessageRepository($c->get(Connection::class));
        },
        UserValidator::class => \DI\autowire(),
        ChatGroupValidator::class => \DI\autowire(),
        ResponseService::class => \DI\autowire(),
        UserAction::class => \DI\autowire(),
        ChatGroupAction::class => \DI\autowire(),
        MessageAction::class => \DI\autowire(),
    ]);
}; 