<?php

declare(strict_types=1);

namespace App\Application\Settings;

interface SettingsInterface
{
    /**
     * @return array<string, mixed>
     */
    public function get(): array;

    public function getDatabasePath(): string;

    public function getLogPath(): string;
}
