<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use function App\Helpers\getOrganisationById;
use function App\Helpers\getMembershipsByOrganisationId;
use function App\Helpers\getRegionById;
use function App\Helpers\isOrganisationAdmin;
use function App\Helpers\isRegionAdmin;

class Organisation extends Entity
{
    protected $attributes = [
        'id' => null,
        'parent_id' => null,
        'name' => null,
        'short_name' => null,
        'region_id' => null,
        'address' => null,
        'description' => null,
        'website_url' => null,
        'email_office' => null,
        'email_students' => null,
        'image_author' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'parent_id' => 'integer',
        'name' => 'string',
        'short_name' => 'string',
        'region_id' => 'integer',
        'address' => 'string',
        'description' => 'string',
        'website_url' => 'string',
        'email_office' => 'string',
        'email_students' => 'string',
        'image_author' => 'string',
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
    public function getParentId(): ?int
    {
        return $this->attributes['parent_id'];
    }

    public function setParentId(int $parentOrganisationId): void
    {
        $this->attributes['parent_id'] = $parentOrganisationId;
    }

    /**
     * @return Organisation
     */
    public function getParent(): Organisation
    {
        return getOrganisationById($this->getParentId());
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
     * @return ?string
     */
    public function getAddress(): ?string
    {
        return $this->attributes['address'];
    }

    public function setAddress(string $address): void
    {
        $this->attributes['address'] = $address;
    }

    /**
     * @return ?string
     */
    public function getDescription(): ?string
    {
        return $this->attributes['description'];
    }

    public function setDescription(string $description): void
    {
        $this->attributes['description'] = $description;
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
     * @return ?string
     */
    public function getEmailOffice(): ?string
    {
        return $this->attributes['email_office'];
    }

    public function setEmailOffice(string $emailOffice): void
    {
        $this->attributes['email_office'] = $emailOffice;
    }

    /**
     * @return ?string
     */
    public function getEmailStudents(): ?string
    {
        return $this->attributes['email_students'];
    }

    public function setEmailStudents(string $emailStudents): void
    {
        $this->attributes['email_students'] = $emailStudents;
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
     * @return Membership[]
     */
    public function getMemberships(): array
    {
        return getMembershipsByOrganisationId($this->getId());
    }

    public function isManageableBy(User $user): bool
    {
        return $user->isAdmin() || isOrganisationAdmin($user->getId(), $this->getId());
    }
}