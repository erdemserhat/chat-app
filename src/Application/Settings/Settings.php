<?php

declare(strict_types=1);

namespace App\Application\Settings;

class Settings implements SettingsInterface
{
    private array $settings;

    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        return $this->settings;
    }

    public function getDatabasePath(): string
    {
        return $this->settings['database']['path'] ?? 'data/chat.db';
    }

    public function getLogPath(): string
    {
        return $this->settings['logger']['path'] ?? 'logs/app.log';
    }
}
