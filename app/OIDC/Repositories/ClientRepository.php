<?php

namespace App\OIDC\Repositories;

use App\Entities\OIDC\Client;
use App\Models\OIDC\ClientModel;
use App\OIDC\Entities\ClientEntity;
use League\OAuth2\Server\Repositories\ClientRepositoryInterface;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getClientEntity($clientIdentifier): ?ClientEntity
    {
        $client = $this->getClientByIdentifier($clientIdentifier);
        if (!$client) {
            return null;
        }

        $clientEntity = new ClientEntity();
        $clientEntity->setIdentifier($client->getId());
        $clientEntity->setName($client->getName());
        $clientEntity->setRedirectUri($client->getRedirectUri());
        $clientEntity->setIsConfidential($client->isConfidential());

        return $clientEntity;
    }

    /**
     * {@inheritdoc}
     */
    public function validateClient($clientIdentifier, $clientSecret, $grantType): bool
    {
        $client = $this->getClientByIdentifier($clientIdentifier);
        if (!$client) {
            return false;
        }

        return $client->getSecret() == $clientSecret;
    }

    /**
     * Returns the DBO of a client.
     *
     * @param string $identifier
     * @return ?Client
     */
    private function getClientByIdentifier(string $identifier): ?object
    {
        return $this->getClientModel()->find($identifier);
    }

    private function getClientModel(): ClientModel
    {
        return new ClientModel();
    }
}