<?php

namespace App\Helpers;

use App\OIDC\Entities\CustomIdTokenResponse;
use App\OIDC\Entities\UserEntity;
use App\OIDC\Http\RequestWrapper;
use App\OIDC\Http\ResponseWrapper;
use App\OIDC\Repositories\AccessTokenRepository;
use App\OIDC\Repositories\AuthCodeRepository;
use App\OIDC\Repositories\ClientRepository;
use App\OIDC\Repositories\IdentityRepository;
use App\OIDC\Repositories\RefreshTokenRepository;
use App\OIDC\Repositories\ScopeRepository;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use DateInterval;
use Exception;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AuthCodeGrant;
use OpenIDConnectServer\ClaimExtractor;
use Throwable;

function authorizationRequest(RequestWrapper $requestWrapper, ResponseWrapper $responseWrapper): void
{
    try {
        $server = createAuthorizationServer();
    } catch (Throwable $e) {
        log_message('error', 'Error creating OIDC server: {exception}', ['exception' => $e]);
        $responseWrapper->withStatus(500, 'Failed to create OIDC server');
        return;
    }

    try {
        $authRequest = $server->validateAuthorizationRequest($requestWrapper);
        $authRequest->setUser(new UserEntity(getCurrentUser()->getUsername()));
        $authRequest->setAuthorizationApproved(true);
        $server->completeAuthorizationRequest($authRequest, $responseWrapper);
    } catch (OAuthServerException $e) {
        log_message('error', 'Failed to handle authorization request: {exception}', ['exception' => $e]);
        $e->generateHttpResponse($responseWrapper);
    }
}

function accessTokenRequest(RequestWrapper $requestWrapper, ResponseWrapper $responseWrapper): void
{
    try {
        $server = createAuthorizationServer();
    } catch (Throwable $e) {
        log_message('error', 'Error creating OIDC server: {exception}', ['exception' => $e]);
        $responseWrapper->withStatus(500, 'Failed to create OIDC server');
        return;
    }

    try {
        $server->respondToAccessTokenRequest($requestWrapper, $responseWrapper);
    } catch (OAuthServerException $e) {
        log_message('error', 'Failed to handle access token request: {exception}', ['exception' => $e]);
        $e->generateHttpResponse($responseWrapper);
    }
}

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