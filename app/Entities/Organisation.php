<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;
use function App\Helpers\getMembership;
use function App\Helpers\getOrganisationById;
use function App\Helpers\getMembershipsByOrganisationId;
use function App\Helpers\getRegionById;

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
        'website' => null,
        'email' => null,
        'phone' => null,
        'image_id' => null,
        'logo_id' => null,
        'folder_id' => null,
        'chat_id' => null
    ];

    protected $casts = [
        'id' => 'integer',
        'parent_id' => 'integer',
        'name' => 'string',
        'short_name' => 'string',
        'region_id' => 'integer',
        'address' => 'string',
        'description' => 'string',
        'website' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'image_id' => 'string',
        'logo_id' => 'string',
        'folder_id' => 'integer',
        'chat_id' => 'string'
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

    public function setParentId(?int $parentOrganisationId): void
    {
        $this->attributes['parent_id'] = $parentOrganisationId;
    }

    /**
     * @return ?Organisation
     */
    public function getParent(): ?Organisation
    {
        if (is_null($this->getParentId())) return null;
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

    public function getDisplayName(): string
    {
        $parent = $this->getParent();
        return ($parent ? $parent->getName() . ' / ' : '') . $this->getName();
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
    public function getWebsite(): ?string
    {
        return $this->attributes['website'];
    }

    public function setWebsite(string $websiteUrl): void
    {
        $this->attributes['website'] = $websiteUrl;
    }

    /**
     * @return ?string
     */
    public function getEmail(): ?string
    {
        return $this->attributes['email'];
    }

    public function setEmail(string $email): void
    {
        $this->attributes['email'] = $email;
    }

    /**
     * @return ?string
     */
    public function getPhone(): ?string
    {
        return $this->attributes['phone'];
    }

    public function setPhone(string $phone): void
    {
        $this->attributes['phone'] = $phone;
    }

    /**
     * @return ?string
     */
    public function getLogoId(): ?string
    {
        return $this->attributes['logo_id'];
    }

    public function setLogoId(string $logoId): void
    {
        $this->attributes['logo_id'] = $logoId;
    }

    /**
     * @return ?string
     */
    public function getImageId(): ?string
    {
        return $this->attributes['image_id'];
    }

    public function setImageId(string $imageId): void
    {
        $this->attributes['image_id'] = $imageId;
    }

    /**
     * @return ?int
     */
    public function getFolderId(): ?int
    {
        return $this->attributes['folder_id'];
    }

    public function setFolderId(int $folderId): void
    {
        $this->attributes['folder_id'] = $folderId;
    }

    public function getFolderMountPoint(): string
    {
        $parent = $this->getParent();
        return ($parent ? $parent->getName() . '/' : '') . $this->getName();
    }

    public function getChatId(): ?string
    {
        return $this->attributes['chat_id'];
    }

    public function setChatId(string $chatId): void
    {
        $this->attributes['chat_id'] = $chatId;
    }

    /**
     * @return Membership[]
     */
    public function getMemberships(): array
    {
        return getMembershipsByOrganisationId($this->getId());
    }

    public function getUrl(): string
    {
        return "<a href=\"organisation/{$this->getId()}\">{$this->getDisplayName()}</a>";
    }

    public function isManageableBy(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $membership = getMembership($user->getId(), $this->getId());
        return $membership && $membership->getStatus() == MembershipStatus::ADMIN;
    }
}