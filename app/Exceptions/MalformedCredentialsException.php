<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class MalformedCredentialsException extends AuthException
{
    function __construct(Throwable $previous = null)
    {
        parent::__construct('malformed credentials', 0, $previous);
    }
}