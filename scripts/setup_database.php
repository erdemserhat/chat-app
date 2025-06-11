<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;

try {
    // Create database connection
    $connection = DriverManager::getConnection([
        'driver' => 'pdo_sqlite',
        'path' => __DIR__ . '/../data/chat.db',
    ]);

    echo "Creating database schema...\n";

    // Create tables
    $connection->executeStatement('
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ');

    $connection->executeStatement('
        CREATE TABLE IF NOT EXISTS chat_groups (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL UNIQUE,
            created_by INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id)
        )
    ');

    $connection->executeStatement('
        CREATE TABLE IF NOT EXISTS group_members (
            group_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (group_id, user_id),
            FOREIGN KEY (group_id) REFERENCES chat_groups(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ');

    $connection->executeStatement('
        CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            group_id INTEGER NOT NULL,
            user_id INTEGER NOT NULL,
            content TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (group_id) REFERENCES chat_groups(id),
            FOREIGN KEY (user_id) REFERENCES users(id)
        )
    ');

    echo "Schema created successfully!\n";
    echo "Adding sample data...\n";

    // Insert sample users
    $users = [
        ['alice_smith', '2024-01-01 09:00:00'],
        ['bob_johnson', '2024-01-01 09:15:00'],
        ['charlie_brown', '2024-01-01 09:30:00'],
        ['diana_prince', '2024-01-01 09:45:00'],
        ['edward_norton', '2024-01-01 10:00:00'],
        ['fiona_green', '2024-01-01 10:15:00']
    ];

    foreach ($users as $user) {
        $connection->insert('users', [
            'username' => $user[0],
            'created_at' => $user[1]
        ]);
    }

    // Insert sample groups
    $groups = [
        ['Development Team', 1, '2024-01-01 10:00:00'],
        ['Product Management', 2, '2024-01-01 10:30:00'],
        ['Design Squad', 3, '2024-01-01 11:00:00'],
        ['Quality Assurance', 4, '2024-01-01 11:30:00'],
        ['Marketing Hub', 5, '2024-01-01 12:00:00']
    ];

    foreach ($groups as $group) {
        $connection->insert('chat_groups', [
            'name' => $group[0],
            'created_by' => $group[1],
            'created_at' => $group[2]
        ]);
    }

    // Insert group memberships
    $memberships = [
        // Development Team (group 1)
        [1, 1, '2024-01-01 10:00:00'], // alice (creator)
        [1, 2, '2024-01-01 10:05:00'], // bob
        [1, 3, '2024-01-01 10:10:00'], // charlie
        [1, 6, '2024-01-01 10:15:00'], // fiona

        // Product Management (group 2)
        [2, 2, '2024-01-01 10:30:00'], // bob (creator)
        [2, 1, '2024-01-01 10:35:00'], // alice
        [2, 4, '2024-01-01 10:40:00'], // diana

        // Design Squad (group 3)
        [3, 3, '2024-01-01 11:00:00'], // charlie (creator)
        [3, 4, '2024-01-01 11:05:00'], // diana
        [3, 5, '2024-01-01 11:10:00'], // edward

        // Quality Assurance (group 4)
        [4, 4, '2024-01-01 11:30:00'], // diana (creator)
        [4, 1, '2024-01-01 11:35:00'], // alice
        [4, 6, '2024-01-01 11:40:00'], // fiona

        // Marketing Hub (group 5)
        [5, 5, '2024-01-01 12:00:00'], // edward (creator)
        [5, 2, '2024-01-01 12:05:00'], // bob
        [5, 6, '2024-01-01 12:10:00'], // fiona
    ];

    foreach ($memberships as $membership) {
        $connection->insert('group_members', [
            'group_id' => $membership[0],
            'user_id' => $membership[1],
            'joined_at' => $membership[2]
        ]);
    }

    // Insert sample messages
    $messages = [
        // Development Team messages
        [1, 1, 'Welcome everyone to our development team! 🚀', '2024-01-01 10:20:00'],
        [1, 2, 'Great to be here! Ready to build something amazing.', '2024-01-01 10:25:00'],
        [1, 3, 'Should we start with the project architecture?', '2024-01-01 10:30:00'],
        [1, 6, 'I suggest we follow Clean Architecture principles.', '2024-01-01 10:35:00'],
        [1, 1, 'Excellent idea! Let\'s implement SOLID principles too.', '2024-01-01 10:40:00'],
        [1, 2, 'I\'ll set up the CI/CD pipeline this week.', '2024-01-01 10:45:00'],

        // Product Management messages
        [2, 2, 'Let\'s discuss the product roadmap for Q1.', '2024-01-01 10:50:00'],
        [2, 1, 'We should prioritize user authentication first.', '2024-01-01 10:55:00'],
        [2, 4, 'Agreed! Security should be our top priority.', '2024-01-01 11:00:00'],
        [2, 2, 'I\'ll prepare the user stories for next sprint.', '2024-01-01 11:05:00'],

        // Design Squad messages
        [3, 3, 'Time to create some beautiful interfaces! 🎨', '2024-01-01 11:15:00'],
        [3, 4, 'I\'ve prepared the initial wireframes.', '2024-01-01 11:20:00'],
        [3, 5, 'The color palette looks fantastic!', '2024-01-01 11:25:00'],
        [3, 3, 'Let\'s ensure our design is mobile-first.', '2024-01-01 11:30:00'],

        // Quality Assurance messages
        [4, 4, 'QA team, let\'s ensure we deliver quality! ✅', '2024-01-01 11:45:00'],
        [4, 1, 'I\'ll help with automated testing setup.', '2024-01-01 11:50:00'],
        [4, 6, 'We should implement both unit and integration tests.', '2024-01-01 11:55:00'],
        [4, 4, 'Great! Code coverage should be above 90%.', '2024-01-01 12:00:00'],

        // Marketing Hub messages
        [5, 5, 'Marketing team, let\'s plan our launch strategy! 📈', '2024-01-01 12:15:00'],
        [5, 2, 'I can help with the technical documentation.', '2024-01-01 12:20:00'],
        [5, 6, 'We should focus on developer communities.', '2024-01-01 12:25:00'],
        [5, 5, 'Perfect! Let\'s create compelling content.', '2024-01-01 12:30:00'],
    ];

    foreach ($messages as $message) {
        $connection->insert('messages', [
            'group_id' => $message[0],
            'user_id' => $message[1],
            'content' => $message[2],
            'created_at' => $message[3]
        ]);
    }

    echo "Sample data inserted successfully!\n";
    echo "\n=== Database Setup Complete! ===\n";
    echo "Users: 6\n";
    echo "Groups: 5\n";
    echo "Messages: " . count($messages) . "\n";
    echo "Database location: data/chat.db\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 