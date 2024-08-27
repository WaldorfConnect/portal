<?php

namespace App\Exceptions\Group;

use App\Exceptions\Auth\AuthException;
use Throwable;

class GroupAlreadyMemberException extends GroupException
{
    public int $userId;

    function __construct($userId = null, $message = '', Throwable $previous = null)
    {
        parent::__construct($message, $previous);
        $this->userId = $userId;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }
}