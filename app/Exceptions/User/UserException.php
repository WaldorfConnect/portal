<?php

namespace App\Exceptions\User;

use Exception;
use Throwable;

class UserException extends Exception
{
    function __construct($message = '', Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}