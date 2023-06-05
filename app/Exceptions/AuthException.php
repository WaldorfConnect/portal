<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class AuthException extends Exception
{
    function __construct($message = '', Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}