<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use function App\Helpers\getOrganisationById;
use function App\Helpers\getUserById;

class Membership extends Entity
{
    protected $attributes = [
        'id' => null,
        'user_id' => null,
        'organisation_id' => null,
        'status' => null
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'organisation_id' => 'integer',
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

    public function getUser(): User
    {
        return getUserById($this->getUserId());
    }

    /**
     * @return ?int
     */
    public function getOrganisationId(): ?int
    {
        return $this->attributes['organisation_id'];
    }

    public function setOrganisationId(string $organisationId): void
    {
        $this->attributes['organisation_id'] = $organisationId;
    }

    public function getOrganisation(): Organisation
    {
        return getOrganisationById($this->getOrganisationId());
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