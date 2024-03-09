<?php

namespace App\Repositories;

use League\OAuth2\Server\AbstractServer;
use League\OAuth2\Server\Entity\RefreshTokenEntity;
use League\OAuth2\Server\Storage\RefreshTokenInterface;

class RefreshTokenRepository implements RefreshTokenInterface
{
    public function get($token)
    {
        // TODO: Implement get() method.
    }

    public function create($token, $expireTime, $accessToken)
    {
        // TODO: Implement create() method.
    }

    public function delete(RefreshTokenEntity $token)
    {
        // TODO: Implement delete() method.
    }

    public function setServer(AbstractServer $server)
    {
        // TODO: Implement setServer() method.
    }
}