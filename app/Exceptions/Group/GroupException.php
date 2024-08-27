<?php

namespace App\Exceptions\Group;

use Exception;
use Throwable;

class GroupException extends Exception
{
    function __construct($message = '', Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}