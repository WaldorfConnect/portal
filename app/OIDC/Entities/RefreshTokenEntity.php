<?php

namespace App\OIDC\Entities;

use DateTimeImmutable;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

class RefreshTokenEntity implements RefreshTokenEntityInterface
{
    use RefreshTokenTrait, EntityTrait;

    /**
     * @param DateTimeImmutable $dateTime
     */
    public function setExpiryDateTime(DateTimeImmutable $dateTime): void
    {
        $this->expiryDateTime = $dateTime;
    }
}