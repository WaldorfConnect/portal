<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use function App\Helpers\getGroupMembershipsByGroupId;
use function App\Helpers\getRegionById;

class Group extends Entity
{
    protected $attributes = [
        'id' => null,
        'region_id' => null,
        'name' => null,
        'description' => null,
        'image_author' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'region_id' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'image_author' => 'string'
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
     * @return Region
     */
    public function getRegion(): object
    {
        return getRegionById($this->getRegionId());
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

    /**
     * @return ?string
     */
    public function getImageAuthor(): ?string
    {
        return $this->attributes['image_author'];
    }

    /**
     * @return GroupMembership[]
     */
    public function getMemberships(): array
    {
        return getGroupMembershipsByGroupId($this->getId());
    }
}