<?php

namespace App\Repositories;

use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    public function getClientEntity($clientIdentifier)
    {
        // TODO: Implement getClientEntity() method.
    }

    public function validateClient($clientIdentifier, $clientSecret, $grantType)
    {
        // TODO: Implement validateClient() method.
    }
}