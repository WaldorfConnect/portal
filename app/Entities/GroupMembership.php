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

    public function setUserId(string $userId): void
    {
        $this->attributes['user_id'] = $userId;
    }

    /**
     * @return ?int
     */
    public function getGroupId(): ?int
    {
        return $this->attributes['group_id'];
    }

    public function setGroupId(string $groupId): void
    {
        $this->attributes['group_id'] = $groupId;
    }

    /**
     * @return MembershipStatus
     */
    public function getStatus(): MembershipStatus
    {
        return MembershipStatus::from($this->attributes['status']);
    }

    public function setStatus(MembershipStatus $status): void
    {
        $this->attributes['status'] = $status->value;
    }
}