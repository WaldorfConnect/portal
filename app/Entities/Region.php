<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use function App\Helpers\isRegionAdmin;

class Region extends Entity
{
    protected $attributes = [
        'id' => null,
        'name' => null,
        'iso_code' => null
    ];

    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'iso_code' => 'string'
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

    public function setName(string $name): void
    {
        $this->attributes['name'] = $name;
    }

    /**
     * @return string
     */
    public function getIsoCode(): string
    {
        return $this->attributes['iso_code'];
    }

    public function setIsoCode(string $isoCode): void
    {
        $this->attributes['iso_code'] = $isoCode;
    }

    public function isManageableBy(User $user): bool
    {
        return $user->isGlobalAdmin() || isRegionAdmin($user->getId(), $this->getId());
    }
}