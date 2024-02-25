<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use function App\Helpers\isRegionAdmin;

class Region extends Entity
{
    protected $attributes = [
        'id' => null,
        'name' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
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

    public function isManageableBy(User $user): bool
    {
        return $user->isGlobalAdmin() || isRegionAdmin($user->getId(), $this->getId());
    }
}