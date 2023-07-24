<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Region extends Entity
{
    protected $attributes = [
        'id' => null,
        'name' => null,
        'iso_code' => null,
        'supervisor_group_id' => null
    ];

    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'iso_code' => 'string',
        'supervisor_group_id' => 'integer'
    ];

    /**
     * @return ?int
     */
    public function getId(): ?int
    {
        return $this->attributes['id'];
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
    public function getIsoCode(): string
    {
        return $this->attributes['iso_code'];
    }

    /**
     * @return ?int
     */
    public function getSupervisorGroupId(): ?int
    {
        return $this->attributes['supervisor_group_id'];
    }
}