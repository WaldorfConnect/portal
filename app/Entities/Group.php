<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Group extends Entity
{
    protected $attributes = [
        'id' => null,
        'region_id' => null,
        'name' => null,
        'description' => null
    ];

    protected $casts = [
        'id' => 'integer',
        'region_id' => 'integer',
        'name' => 'string',
        'description' => 'string'
    ];

    /**
     * @return ?int
     */
    public function getId(): ?int
    {
        return $this->attributes['id'];
    }

    /**
     * @return ?int
     */
    public function getRegionId(): ?int
    {
        return $this->attributes['region_id'];
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->attributes['name'];
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->attributes['description'];
    }
}