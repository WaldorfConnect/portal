<?php

namespace App\OIDC\Entities;

use League\OAuth2\Server\Entities\UserEntityInterface;
use OpenIDConnectServer\Entities\ClaimSetInterface;

class UserEntity implements ClaimSetInterface, UserEntityInterface
{
    private string $identifier;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public function getClaims(): array
    {
        return [
            'id' => $this->identifier
        ];
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}