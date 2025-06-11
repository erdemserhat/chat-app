<?php

declare(strict_types=1);

namespace App\Domain\ChatGroup;

use App\Domain\DomainException\DomainRecordNotFoundException;

    class ChatGroupNameAlreadyExistsFoundException extends DomainRecordNotFoundException
{
    public $message = 'The chat group name is already in use';
}