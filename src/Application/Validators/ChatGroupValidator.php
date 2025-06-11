<?php

declare(strict_types=1);

namespace App\Application\Validators;

use App\Application\Actions\ActionError;

class ChatGroupValidator
{
    public function validateCreateRequest(array $data): ?ActionError
    {
        $name = $data['name'] ?? null;
        $userId = isset($data['user_id']) ? (int) $data['user_id'] : null;

        if (empty($name)) {
            return new ActionError(ActionError::VALIDATION_ERROR, 'Group name is required');
        }

        if (strlen($name) < 2) {
            return new ActionError(ActionError::VALIDATION_ERROR, 'Group name must be at least 2 characters long');
        }

        if (strlen($name) > 100) {
            return new ActionError(ActionError::VALIDATION_ERROR, 'Group name cannot exceed 100 characters');
        }

        if (!$userId || $userId <= 0) {
            return new ActionError(ActionError::VALIDATION_ERROR, 'Valid user ID is required');
        }

        return null;
    }

    public function validateJoinRequest(array $data, array $args): ?ActionError
    {
        $userId = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $groupId = isset($args['id']) ? (int) $args['id'] : null;

        if (!$userId || $userId <= 0) {
            return new ActionError(ActionError::VALIDATION_ERROR, 'Valid user ID is required');
        }

        if (!$groupId || $groupId <= 0) {
            return new ActionError(ActionError::VALIDATION_ERROR, 'Valid group ID is required');
        }

        return null;
    }

    public function validateLeaveRequest(array $data, array $args): ?ActionError
    {
        $userId = isset($data['user_id']) ? (int) $data['user_id'] : null;
        $groupId = isset($args['id']) ? (int) $args['id'] : null;

        if (!$userId || $userId <= 0) {
            return new ActionError(ActionError::VALIDATION_ERROR, 'Valid user ID is required');
        }

        if (!$groupId || $groupId <= 0) {
            return new ActionError(ActionError::VALIDATION_ERROR, 'Valid group ID is required');
        }

        return null;
    }

    public function sanitizeGroupName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        return trim($name);
    }
}
