<?php

namespace App\Models;

class GroupModel
{
    public string $name;

    /** @var UserModel[] */
    public array $members;

    function __construct(string $name, array $members)
    {
        $this->name = $name;
        $this->members = $members;
    }
}