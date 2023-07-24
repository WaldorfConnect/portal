<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class GroupMembership extends Entity
{
    protected $attributes = [
        'id' => null,
        'user_id' => null,
        'group_id' => null,
        'status' => null
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'group_id' => 'integer',
        'status' => 'string'
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
    public function getUserId(): ?int
    {
        return $this->attributes['user_id'];
    }

    /**
     * @return ?int
     */
    public function getGroupId(): ?int
    {
        return $this->attributes['group_id'];
    }

    /**
     * @return MembershipStatus
     */
    public function getStatus(): MembershipStatus
    {
        return MembershipStatus::from($this->attributes['status']);
    }
}