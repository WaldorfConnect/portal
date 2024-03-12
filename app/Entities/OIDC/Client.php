<?php

namespace App\Entities\OIDC;

use CodeIgniter\Entity\Entity;

class Client extends Entity
{
    protected $attributes = [
        'id' => null,
        'name' => null,
        'redirect_uri' => null,
        'secret' => null,
        'confidential' => false
    ];

    protected $casts = [
        'id' => 'string',
        'name' => 'string',
        'redirect_uri' => 'string',
        'secret' => 'string',
        'confidential' => 'boolean'
    ];

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->attributes['id'];
    }

    public function setId(string $id): void
    {
        $this->attributes['id'] = $id;
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
    public function getRedirectUri(): string
    {
        return $this->attributes['redirect_uri'];
    }

    public function setRedirectUri(string $redirectUri): void
    {
        $this->attributes['redirect_uri'] = $redirectUri;
    }

    /**
     * @return string
     */
    public function getSecret(): string
    {
        return $this->attributes['secret'];
    }

    public function setSecret(string $secret): void
    {
        $this->attributes['secret'] = $secret;
    }

    /**
     * @return bool
     */
    public function isConfidential(): bool
    {
        return $this->attributes['confidential'];
    }

    public function setConfidential(bool $confidential): void
    {
        $this->attributes['confidential'] = $confidential;
    }
}