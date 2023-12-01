<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use function App\Helpers\getMembership;
use function App\Helpers\getMembershipsByUserId;
use function App\Helpers\isGroupAdmin;
use function App\Helpers\isRegionAdmin;

class User extends Entity
{
    protected $attributes = [
        'id' => null,
        'username' => null,
        'name' => null,
        'email' => null,
        'password' => null,
        'global_admin' => null,
        'status' => null,
        'token' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'username' => 'string',
        'name' => 'string',
        'email' => 'string',
        'password' => 'string',
        'global_admin' => 'boolean',
        'status' => 'string',
        'token' => 'string',
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
    public function getUsername(): string
    {
        return $this->attributes['username'];
    }

    public function setUsername(string $username): void
    {
        $this->attributes['username'] = $username;
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
    public function getFirstName(): string
    {
        $fullName = $this->getName();
        $position = strripos($fullName, ' ');
        return substr($fullName, 0, $position);
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        $fullName = $this->getName();
        $position = strripos($fullName, ' ');
        return substr($fullName, $position + 1);
    }

    public function setName(string $name): void
    {
        $this->attributes['name'] = $name;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->attributes['email'];
    }

    public function setEmail(string $email): void
    {
        $this->attributes['email'] = $email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->attributes['password'];
    }

    public function setPassword(string $password): void
    {
        $this->attributes['password'] = $password;
    }

    /**
     * @return int?
     */
    public function getSchoolId(): int
    {
        return $this->attributes['school_id'];
    }

    /**
     * @return bool
     */
    public function isGlobalAdmin(): bool
    {
        return $this->attributes['global_admin'];
    }

    public function setGlobalAdmin(bool $globalAdmin): void
    {
        $this->attributes['global_admin'] = $globalAdmin;
    }

    /**
     * @return UserStatus
     */
    public function getStatus(): UserStatus
    {
        return UserStatus::from($this->attributes['status']);
    }

    public function setStatus(UserStatus $status): void
    {
        $this->attributes['status'] = $status->value;

        // Remove token if target state isn't tokenized
        if (!$status->isTokenized()) {
            $this->setToken(null);
        }
    }

    /**
     * @return ?string
     */
    public function getToken(): ?string
    {
        return $this->attributes['token'];
    }

    public function setToken(?string $token): void
    {
        $this->attributes['token'] = $token;
    }

    /**
     * @return Membership[]
     */
    public function getMemberships(): array
    {
        return getMembershipsByUserId($this->getId());
    }

    /**
     * Returns whether the user has any administrative powers.
     *
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->isGlobalAdmin() || isRegionAdmin($this->getId()) || isGroupAdmin($this->getId());
    }

    /**
     * Returns if the current user may manage the given target.
     *
     * @param User $target
     * @return bool
     */
    public function mayManage(User $target): bool
    {
        # Everyone may manage himself
        if ($this->getId() == $target->getId()) {
            return true;
        }

        # A global admin may manage anybody
        if ($this->isGlobalAdmin()) {
            return true;
        }

        foreach ($target->getMemberships() as $membership) {
            # We may NOT manage if target user is admin himself
            if ($membership->getStatus() == MembershipStatus::ADMIN) {
                continue;
            }

            # We may manage if we're admin of the group
            $ownMembership = getMembership($this->getId(), $membership->getGroupId());
            if ($ownMembership && $ownMembership->getStatus() == MembershipStatus::ADMIN) {
                return true;
            }

            # We may manage if we're admin of the group's region
            if (isRegionAdmin($this->getId(), $membership->getGroup()->getRegionId())) {
                return true;
            }
        }

        return false;
    }
}