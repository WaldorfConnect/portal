<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

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
     * @return string
     */
    public function getName(): string
    {
        return $this->attributes['name'];
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return $this->attributes['short_name'];
    }

    /**
     * @return ?int
     */
    public function getRegionId(): ?int
    {
        return $this->attributes['region_id'];
    }

    /**
     * @return string
     */
    public function getAddress(): string
    {
        return $this->attributes['address'];
    }

    /**
     * @return string
     */
    public function getEmailBureau(): string
    {
        return $this->attributes['email_bureau'];
    }

    /**
     * @return ?string
     */
    public function getEmailSMV(): ?string
    {
        return $this->attributes['email_smv'];
    }

    /**
     * @return ?string
     */
    public function getStateId(): ?string
    {
        return $this->attributes['state_id'];
    }

    /**
     * @return ?string
     */
    public function getImageAuthor(): ?string
    {
        return $this->attributes['image_author'];
    }
}