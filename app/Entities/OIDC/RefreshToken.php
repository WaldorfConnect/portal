<?php

namespace App\Entities\OIDC;

use CodeIgniter\Entity\Entity;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;

class RefreshToken extends Entity
{
    protected $attributes = [
        'id' => null,
        'access_token_id' => null,
        'expire_at' => null
    ];

    protected $casts = [
        'id' => 'string',
        'access_token_id' => 'string',
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
    public function getAccessTokenId(): string
    {
        return $this->attributes['access_token_id'];
    }

    public function setAccessTokenId(string $accessTokenId): void
    {
        $this->attributes['access_token_id'] = $accessTokenId;
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