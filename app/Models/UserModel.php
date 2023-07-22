<?php

namespace App\Models;

class UserModel
{
    public string $username;
    public string $displayName;
    public string $email;

    /** @var GroupModel[] */
    public array $groups;

    function __construct(string $username, string $displayName, string $email, array $groups)
    {
        $this->username = $username;
        $this->displayName = $displayName;
        $this->groups = $groups;
        $this->email = $email;
    }
}