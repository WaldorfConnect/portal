<?php

namespace App\OIDC\Repositories;

use App\OIDC\Entities\UserEntity;
use OpenIDConnectServer\Repositories\IdentityProviderInterface;

class IdentityRepository implements IdentityProviderInterface
{
    public function getUserEntityByIdentifier($identifier): UserEntity
    {
        return new UserEntity($identifier);
    }
}