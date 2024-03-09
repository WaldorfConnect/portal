<?php

namespace App\Repositories;

use Idaas\OpenID\Repositories\ClaimRepositoryInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;

class ClaimRepository implements ClaimRepositoryInterface
{

    public function getClaimEntityByIdentifier($identifier, $type, $essential)
    {
        // TODO: Implement getClaimEntityByIdentifier() method.
    }

    public function getClaimsByScope(ScopeEntityInterface $scope): iterable
    {
        // TODO: Implement getClaimsByScope() method.
    }

    public function claimsRequestToEntities(array $json = null)
    {
        // TODO: Implement claimsRequestToEntities() method.
    }
}