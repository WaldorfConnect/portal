<?php

namespace App\Models;

class GroupModel
{
    public string $name;
    public string $description;

    /** @var UserModel[] */
    public array $members;

    function __construct(string $name, string $description, array $members)
    {
        $this->name = $name;
        $this->description = $description;
        $this->members = $members;
    }
}