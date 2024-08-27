<?php

namespace App\Exceptions\Group;

use App\Exceptions\Auth\AuthException;
use Throwable;

class GroupNotAdminException extends GroupException
{
    function __construct($message = '', Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}