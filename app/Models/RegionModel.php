<?php

namespace App\Models;

class RegionModel
{
    public string $name;
    public string $displayName;

    /** @var GroupModel[] */
    public array $groups;

    function __construct(string $name, string $displayName, array $groups)
    {
        $this->name = $name;
        $this->displayName = $displayName;
        $this->groups = $groups;
    }
}