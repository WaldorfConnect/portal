<?php

namespace App\Exceptions\Auth;

use Throwable;

class AuthCredentialsInvalidException extends AuthException
{
    function __construct($message = '', Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}