<?php

namespace App\OIDC\Entities;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\Traits\ClientTrait;
use League\OAuth2\Server\Entities\Traits\EntityTrait;

class ClientEntity implements ClientEntityInterface
{
    use EntityTrait, ClientTrait;

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string|string[] $redirectUri
     */
    public function setRedirectUri(array|string $redirectUri): void
    {
        $this->redirectUri = $redirectUri;
    }

    /**
     * @param bool $isConfidential
     */
    public function setIsConfidential(bool $isConfidential): void
    {
        $this->isConfidential = $isConfidential;
    }
}