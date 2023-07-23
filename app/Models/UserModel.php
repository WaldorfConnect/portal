<?php

namespace App\Models;

class UserModel
{
    public string $username;
    public string $displayName;
    public string $email;
    public GroupModel $school;

    /** @var GroupModel[] */
    public array $groups;

    function __construct(string $username, string $displayName, string $email, GroupModel $school, array $groups)
    {
        $this->username = $username;
        $this->displayName = $displayName;
        $this->school = $school;
        $this->groups = $groups;
        $this->email = $email;
    }
}