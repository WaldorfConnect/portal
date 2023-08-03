<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use function App\Helpers\getGroupMembershipsByUserId;
use function App\Helpers\getGroupsByUserId;
use function App\Helpers\getSchoolById;

class User extends Entity
{
    protected $attributes = [
        'id' => null,
        'username' => null,
        'name' => null,
        'email' => null,
        'password' => null,
        'school_id' => null,
        'role' => null,
        'status' => null,
        'token' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'username' => 'string',
        'name' => 'string',
        'email' => 'string',
        'password' => 'string',
        'school_id' => 'integer',
        'role' => 'string',
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
     * @return School
     */
    public function getSchool(): School
    {
        return getSchoolById($this->getSchoolId());
    }

    public function setSchoolId(int $schoolId): void
    {
        $this->attributes['school_id'] = $schoolId;
    }

    /**
     * @return UserRole
     */
    public function getRole(): UserRole
    {
        return UserRole::from($this->attributes['role']);
    }

    public function setRole(UserRole $status): void
    {
        $this->attributes['role'] = $status->value;
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
     * @return GroupMembership[]
     */
    public function getGroupMemberships(): array
    {
        return getGroupMembershipsByUserId($this->getId());
    }
}