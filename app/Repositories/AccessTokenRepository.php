<?php

namespace App\Repositories;

use Idaas\OpenID\Repositories\AccessTokenRepositoryInterface;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
    {
        // TODO: Implement persistNewAccessToken() method.
    }

    public function revokeAccessToken($tokenId)
    {
        // TODO: Implement revokeAccessToken() method.
    }

    public function storeClaims(AccessTokenEntityInterface $token, array $claims)
    {
        // TODO: Implement storeClaims() method.
    }

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
    {
        // TODO: Implement getNewToken() method.
    }

    public function isAccessTokenRevoked($tokenId)
    {
        // TODO: Implement isAccessTokenRevoked() method.
    }

    public function getAccessToken($tokenId)
    {
        // TODO: Implement getAccessToken() method.
    }
}