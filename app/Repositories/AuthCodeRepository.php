<?php

namespace App\Repositories;

use App\Entities\Image;
use CodeIgniter\Model;
use League\OAuth2\Server\AbstractServer;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AuthCodeInterface;
use League\OAuth2\Server\Storage\ScopeInterface;

class AuthCodeRepository implements AuthCodeInterface
{
    public function create($token, $expireTime, $sessionId, $redirectUri)
    {
        // TODO: Implement create() method.
    }

    public function getScopes(AuthCodeEntity $token)
    {
        // TODO: Implement getScopes() method.
    }

    public function associateScope(AuthCodeEntity $token, ScopeEntity $scope)
    {
        // TODO: Implement associateScope() method.
    }

    public function delete(AuthCodeEntity $token)
    {
        // TODO: Implement delete() method.
    }

    public function get($code)
    {
        // TODO: Implement get() method.
    }

    public function setServer(AbstractServer $server)
    {
        // TODO: Implement setServer() method.
    }
}