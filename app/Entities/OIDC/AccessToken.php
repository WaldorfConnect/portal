<?php

namespace App\Entities\OIDC;

use CodeIgniter\Entity\Entity;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

class AccessToken extends Entity
{
    protected $attributes = [
        'id' => null,
        'client_id' => null,
        'user_id' => null,
        'scopes' => null,
        'expire_at' => null
    ];

    protected $casts = [
        'id' => 'string',
        'client_id' => 'string',
        'user_id' => 'string',
        'scopes' => 'string',
        'expire_at' => 'timestamp'
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
    public function getClientId(): string
    {
        return $this->attributes['name'];
    }

    public function setClientId(string $clientId): void
    {
        $this->attributes['client_id'] = $clientId;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->attributes['user_id'];
    }

    public function setUserId(string $userId): void
    {
        $this->attributes['user_id'] = $userId;
    }

    /**
     * @return string
     */
    public function getScopes(): string
    {
        return $this->attributes['scopes'];
    }

    public function setScopes(string $scopes): void
    {
        $this->attributes['scopes'] = $scopes;
    }

    public function getExpirationDate(): ?DateTimeImmutable
    {
        $formattedDate = $this->attributes['expire_at'];
        if (!$formattedDate) return null;

        return DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $formattedDate);
    }

    public function setExpirationDate(DateTimeInterface $time): void
    {
        $this->attributes['expire_at'] = $time->format('Y-m-d H:i:s');
    }
}