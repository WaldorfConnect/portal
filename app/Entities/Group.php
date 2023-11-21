<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use function App\Helpers\getGroupMembership;
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
        'website_url' => null,
    ];

    protected $casts = [
        'id' => 'integer',
        'region_id' => 'integer',
        'name' => 'string',
        'description' => 'string',
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
    public function getName(): string
    {
        return $this->attributes['name'];
    }

    public function setName(string $name): void
    {
        $this->attributes['name'] = $name;
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
     * @return GroupMembership[]
     */
    public function getMemberships(): array
    {
        return getGroupMembershipsByGroupId($this->getId());
    }

    public function mayManage(User $user): bool
    {
        if ($user->getRole() == UserRole::GLOBAL_ADMIN) {
            return true;
        }

        if ($user->getRole() == UserRole::REGION_ADMIN && $this->getRegionId() == $user->getSchool()->getRegionId()) {
            return true;
        }

        $membership = getGroupMembership($user->getId(), $this->getId());
        if ($membership && $membership->getStatus() == MembershipStatus::ADMIN) {
            return true;
        }

        return false;
    }
}