<?php

namespace App\Exceptions\User;

use Throwable;

class UserInactiveException extends UserException
{
    function __construct($message = '', Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}