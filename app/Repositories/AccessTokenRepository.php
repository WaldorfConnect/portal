<?php

namespace App\Repositories;

use Idaas\OpenID\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Entity\AccessTokenEntity as EntitiesAccessTokenEntityInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{

    public function storeClaims(EntitiesAccessTokenEntityInterface $token, array $claims)
    {
        // TODO: Implement storeClaims() method.
    }

    public function getAccessToken($tokenId)
    {
        // TODO: Implement getAccessToken() method.
    }
}