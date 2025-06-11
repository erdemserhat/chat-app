<?php

declare(strict_types=1);

namespace App\Application\Actions;

use JsonSerializable;

class ActionPayload implements JsonSerializable
{
    private ?array $data;
    private ?ActionError $error;

    public function __construct(?array $data = null, ?ActionError $error = null)
    {
        $this->data = $data;
        $this->error = $error;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function getError(): ?ActionError
    {
        return $this->error;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        if ($this->error !== null) {
            return [
                'error' => $this->error
            ];
        }

        return $this->data ?? [];
    }
} 