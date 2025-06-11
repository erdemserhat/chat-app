<?php

declare(strict_types=1);

namespace App\Infrastructure\Logger;

use Monolog\Handler\StreamHandler;
use Monolog\Logger as MonologLogger;

class Logger
{
    private static ?MonologLogger $logger = null;

    public static function getLogger(): MonologLogger
    {
        if (self::$logger === null) {
            self::$logger = new MonologLogger('chat-app');

            // Create logs directory if it doesn't exist
            $logDir = __DIR__ . '/../../../logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }

            // Add file handler
            self::$logger->pushHandler(new StreamHandler($logDir . '/app.log', MonologLogger::DEBUG));
        }

        return self::$logger;
    }

    public static function info(string $message, array $context = []): void
    {
        self::getLogger()->info($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::getLogger()->error($message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        self::getLogger()->debug($message, $context);
    }
}
