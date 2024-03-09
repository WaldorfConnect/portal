<?php

namespace App\Repositories;

use Idaas\OpenID\Repositories\ClaimRepositoryInterface;

class UserRepository implements \Idaas\OpenID\Repositories\UserRepositoryInterface
{

    public function getClaims(ClaimRepositoryInterface $claimRepository, ScopeEntityInterface $scope)
    {
        // TODO: Implement getClaims() method.
    }

    public function getAttributes(UserEntityInterface $userEntity, $claims, $scopes)
    {
        // TODO: Implement getAttributes() method.
    }

    public function getUserByIdentifier($identifier): ?UserEntityInterface
    {
        // TODO: Implement getUserByIdentifier() method.
    }
}