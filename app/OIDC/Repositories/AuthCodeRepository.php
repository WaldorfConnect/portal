<?php

namespace App\OIDC\Repositories;

use App\Entities\OIDC\AuthCode;
use App\Models\OIDC\AuthCodeModel;
use App\OIDC\Entities\AuthCodeEntity;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use ReflectionException;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    /**
     * @throws ReflectionException
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity): void
    {
        $authCode = new AuthCode();
        $authCode->setId($authCodeEntity->getIdentifier());
        $authCode->setClientId($authCodeEntity->getClient()->getIdentifier());
        $authCode->setUserId($authCodeEntity->getUserIdentifier());
        $authCode->setScopes(json_encode($authCodeEntity->getScopes()));
        $authCode->setExpirationDate($authCodeEntity->getExpiryDateTime());
        $this->getAuthCodeModel()->insert($authCode);
    }

    public function revokeAuthCode($codeId): void
    {
        $this->getAuthCodeModel()->delete($codeId);
    }

    public function isAuthCodeRevoked($codeId): bool
    {
        return $this->getAuthCodeModel()->find($codeId) == null;
    }

    public function getNewAuthCode(): AuthCodeEntity
    {
        return new AuthCodeEntity();
    }

    private function getAuthCodeModel(): AuthCodeModel
    {
        return new AuthCodeModel();
    }
}