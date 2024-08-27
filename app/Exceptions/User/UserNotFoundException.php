<?php

namespace App\Exceptions\User;

use App\Exceptions\Auth\AuthException;
use App\Exceptions\Group\GroupException;
use Throwable;

class UserNotFoundException extends UserException
{
    function __construct($message = '', Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}