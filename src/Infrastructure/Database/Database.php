<?php

declare(strict_types=1);

namespace App\Infrastructure\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class Database
{
    private static ?Connection $connection = null;

    public static function getConnection(): Connection
    {
        if (self::$connection === null) {
            $params = [
                'driver' => 'pdo_sqlite',
                'path' => __DIR__ . '/../../../data/chat.db',
            ];

            self::$connection = DriverManager::getConnection($params);
        }

        return self::$connection;
    }

    public static function initialize(): void
    {
        $connection = self::getConnection();

        // Create database directory if it doesn't exist
        $dbDir = dirname($connection->getParams()['path']);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0777, true);
        }

        // Run migrations
        $migrations = glob(__DIR__ . '/migrations/*.sql');
        sort($migrations);

        foreach ($migrations as $migration) {
            $sql = file_get_contents($migration);
            $connection->executeStatement($sql);
        }
    }
}
