<?php

namespace App\Helpers;

use App\OIDC\Repositories\AccessTokenRepository;
use App\OIDC\Repositories\AuthCodeRepository;
use App\OIDC\Repositories\ClientRepository;
use App\OIDC\Repositories\IdentityRepository;
use App\OIDC\Repositories\RefreshTokenRepository;
use App\OIDC\Repositories\ScopeRepository;
use DateInterval;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use OpenIDConnectServer\ClaimExtractor;
use OpenIDConnectServer\IdTokenResponse;

function getAuthorizationServer(): AuthorizationServer
{
    $clientRepository = new ClientRepository();
    $scopeRepository = new ScopeRepository();
    $accessTokenRepository = new AccessTokenRepository();
    $authCodeRepository = new AuthCodeRepository();
    $refreshTokenRepository = new RefreshTokenRepository();

    $responseType = new IdTokenResponse(new IdentityRepository(), new ClaimExtractor());

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