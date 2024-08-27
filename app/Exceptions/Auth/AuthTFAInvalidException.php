<?php

namespace App\Exceptions\Auth;

use Throwable;

class AuthTFAInvalidException extends AuthException
{
    function __construct($message = '', Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}