<?php

declare(strict_types=1);

namespace App\Application\Validators;

use App\Application\Actions\ActionError;

class UserValidator
{
    public function validateCreateRequest(array $data): ?ActionError
    {
        $username = isset($data['username']) ? trim($data['username']) : null;

        if (empty($username)) {
            return new ActionError(ActionError::VALIDATION_ERROR, 'Username is required');
        }

        if (strlen($username) < 3) {
            return new ActionError(ActionError::VALIDATION_ERROR, 'Username must be at least 3 characters long');
        }

        if (strlen($username) > 50) {
            return new ActionError(ActionError::VALIDATION_ERROR, 'Username cannot exceed 50 characters');
        }

        if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
            return new ActionError(
                ActionError::VALIDATION_ERROR,
                'Username can only contain letters, numbers, dots, hyphens and underscores'
            );
        }

        return null;
    }

    public function validateUserId(?int $userId): ?ActionError
    {
        if (!$userId || $userId <= 0) {
            return new ActionError(ActionError::VALIDATION_ERROR, 'Valid user ID is required');
        }

        return null;
    }

    public function sanitizeUsername(?string $username): ?string
    {
        if ($username === null) {
            return null;
        }

        return trim($username);
    }
}
