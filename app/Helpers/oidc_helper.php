<?php

namespace App\Helpers;

use App\OIDC\Entities\CustomIdTokenResponse;
use App\OIDC\Repositories\AccessTokenRepository;
use App\OIDC\Repositories\AuthCodeRepository;
use App\OIDC\Repositories\ClientRepository;
use App\OIDC\Repositories\IdentityRepository;
use App\OIDC\Repositories\RefreshTokenRepository;
use App\OIDC\Repositories\ScopeRepository;
use DateInterval;
use Exception;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use OpenIDConnectServer\ClaimExtractor;

/**
 * Create a new authorization server instance
 *
 * @return AuthorizationServer
 * @throws Exception
 */
function createAuthorizationServer(): AuthorizationServer
{
    $clientRepository = new ClientRepository();
    $scopeRepository = new ScopeRepository();
    $accessTokenRepository = new AccessTokenRepository();
    $authCodeRepository = new AuthCodeRepository();
    $refreshTokenRepository = new RefreshTokenRepository();

    $responseType = new CustomIdTokenResponse(new IdentityRepository(), new ClaimExtractor());

    // Create the authorization server with all necessary repositories
    $server = new AuthorizationServer(
        $clientRepository,
        $accessTokenRepository,
        $scopeRepository,
        getenv('oidc.privateKey'),
        getenv('oidc.publicKey'),
        $responseType
    );

    $grant = new AuthCodeGrant(
        $authCodeRepository,
        $refreshTokenRepository,
        new DateInterval('PT10M')
    );

    $grant->setRefreshTokenTTL(new DateInterval('P1M'));

    $server->enableGrantType(
        $grant,
        new DateInterval('PT1H')
    );

    return $server;
}