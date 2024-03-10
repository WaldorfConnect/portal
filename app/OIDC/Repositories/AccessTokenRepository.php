<?php

namespace App\OIDC\Repositories;

use App\Entities\OIDC\AccessToken;
use App\Models\OIDC\AccessTokenModel;
use App\OIDC\Entities\AccessTokenEntity;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use ReflectionException;

class AccessTokenRepository implements AccessTokenRepositoryInterface
{
    /**
     * @throws ReflectionException
     */
    public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity): void
    {
        $accessToken = new AccessToken();
        $accessToken->setId($accessTokenEntity->getIdentifier());
        $accessToken->setClientId($accessTokenEntity->getClient()->getIdentifier());
        $accessToken->setUserId($accessTokenEntity->getUserIdentifier());
        $accessToken->setScopes(json_encode($accessTokenEntity->getScopes()));
        $accessToken->setExpirationDate($accessTokenEntity->getExpiryDateTime());
        $this->getAccessTokenModel()->save($accessToken);
    }

    public function revokeAccessToken($tokenId): void
    {
        $this->getAccessTokenModel()->delete($tokenId);
    }

    public function isAccessTokenRevoked($tokenId): bool
    {
        return $this->getAccessTokenModel()->find($tokenId) == null;
    }

    public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null): AccessTokenEntity
    {
        $accessToken = new AccessTokenEntity();
        $accessToken->setClient($clientEntity);
        foreach ($scopes as $scope) {
            $accessToken->addScope($scope);
        }
        $accessToken->setUserIdentifier($userIdentifier);
        return $accessToken;
    }

    private function getAccessTokenModel(): AccessTokenModel
    {
        return new AccessTokenModel();
    }
}