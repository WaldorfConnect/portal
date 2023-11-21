<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use function App\Helpers\getRegionById;
use function App\Helpers\getUsersBySchoolId;

class School extends Entity
{
    protected $attributes = [
        'id' => null,
        'name' => null,
        'short_name' => null,
        'region_id' => null,
        'address' => null,
        'email_bureau' => null,
        'email_smv' => null,
        'state_id' => null,
        'image_author' => null,
        'website_url' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'short_name' => 'string',
        'region_id' => 'integer',
        'address' => 'string',
        'email_bureau' => 'string',
        'email_smv' => 'string',
        'state_id' => 'integer',
        'image_author' => 'string',
        'website_url' => 'string'
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
    public function getShortName(): string
    {
        return $this->attributes['short_name'];
    }

    public function setShortName(string $shortName): void
    {
        $this->attributes['short_name'] = $shortName;
    }

    /**
     * @return ?int
     */
    public function getRegionId(): ?int
    {
        return $this->attributes['region_id'];
    }

    public function setRegionId(int $regionId): void
    {
        $this->attributes['region_id'] = $regionId;
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
    public function getAddress(): string
    {
        return $this->attributes['address'];
    }

    public function setAddress(string $address): void
    {
        $this->attributes['address'] = $address;
    }

    /**
     * @return string
     */
    public function getEmailBureau(): string
    {
        return $this->attributes['email_bureau'];
    }

    public function setEmailBureau(string $emailBureau): void
    {
        $this->attributes['email_bureau'] = $emailBureau;
    }

    /**
     * @return ?string
     */
    public function getEmailSMV(): ?string
    {
        return $this->attributes['email_smv'];
    }

    public function setEmailSMV(string $emailSMV): void
    {
        $this->attributes['email_smv'] = $emailSMV;
    }

    /**
     * @return ?string
     */
    public function getStateId(): ?string
    {
        return $this->attributes['state_id'];
    }

    public function setStateId(string $stateId): void
    {
        $this->attributes['state_id'] = $stateId;
    }

    /**
     * @return ?string
     */
    public function getImageAuthor(): ?string
    {
        return $this->attributes['image_author'];
    }

    public function setImageAuthor(string $imageAuthor): void
    {
        $this->attributes['image_author'] = $imageAuthor;
    }

    /**
     * @return ?string
     */
    public function getWebsiteUrl(): ?string
    {
        return $this->attributes['website_url'];
    }

    public function setWebsiteUrl(string $websiteUrl): void
    {
        $this->attributes['website_url'] = $websiteUrl;
    }

    /**
     * @return User[]
     */
    public function getStudents(): array
    {
        return getUsersBySchoolId($this->getId());
    }

    public function mayManage(User $user): bool
    {
        if ($user->getRole() == UserRole::GLOBAL_ADMIN) {
            return true;
        }

        if ($user->getRole() == UserRole::REGION_ADMIN && $this->getRegionId() == $user->getSchool()->getRegionId()) {
            return true;
        }

        if ($user->getRole() == UserRole::SCHOOL_ADMIN && $this->getId() == $user->getSchoolId()) {
            return true;
        }

        return false;
    }
}