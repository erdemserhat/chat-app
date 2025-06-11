<?php

declare(strict_types=1);

namespace App\Domain\Message;

use App\Domain\DomainException\DomainException;

class MessageNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('The message you requested does not exist.');
    }
} 