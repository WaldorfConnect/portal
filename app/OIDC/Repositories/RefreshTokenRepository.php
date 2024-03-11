<?php

namespace App\OIDC\Repositories;

use App\Entities\OIDC\RefreshToken;
use App\Models\OIDC\RefreshTokenModel;
use App\OIDC\Entities\RefreshTokenEntity;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use ReflectionException;

class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
    /**
     * @throws ReflectionException
     */
    public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntity): void
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setId($refreshTokenEntity->getIdentifier());
        $refreshToken->setAccessTokenId($refreshTokenEntity->getAccessToken()->getIdentifier());
        $refreshToken->setExpirationDate($refreshTokenEntity->getExpiryDateTime());
        $this->getRefreshTokenModel()->insert($refreshToken);
    }

    public function revokeRefreshToken($tokenId): void
    {
        $this->getRefreshTokenModel()->delete($tokenId);
    }

    public function isRefreshTokenRevoked($tokenId): bool
    {
        return $this->getRefreshTokenModel()->find($tokenId) == null;
    }

    public function getNewRefreshToken(): RefreshTokenEntity
    {
        return new RefreshTokenEntity();
    }

    private function getRefreshTokenModel(): RefreshTokenModel
    {
        return new RefreshTokenModel();
    }
}