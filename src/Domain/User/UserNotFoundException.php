<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\DomainException\DomainException;

class UserNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('The user you requested does not exist.');
    }
} 