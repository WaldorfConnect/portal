<?php

namespace App\Repositories;

use App\Entities\Image;
use CodeIgniter\Model;
use League\OAuth2\Server\AbstractServer;
use League\OAuth2\Server\Storage\ScopeInterface;

class ScopeRepository implements ScopeInterface
{
    public function get($scope, $grantType = null, $clientId = null)
    {
        // TODO: Implement get() method.
    }

    public function setServer(AbstractServer $server)
    {
        // TODO: Implement setServer() method.
    }
}