<?php

namespace App\Controllers;

use App\OIDC\Entities\UserEntity;
use App\OIDC\Http\RequestWrapper;
use App\OIDC\Http\ResponseWrapper;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use function App\Helpers\getAuthorizationServer;
use function App\Helpers\getCurrentUser;

class OIDCController extends BaseController
{
    public function authorize(): string|Response
    {
        $server = getAuthorizationServer();

        $wrappedRequest = new RequestWrapper($this->request);
        $wrappedResponse = new ResponseWrapper(Services::response());

        try {
            $authRequest = $server->validateAuthorizationRequest($wrappedRequest);
            $authRequest->setUser(new UserEntity(getCurrentUser()->getUsername()));
            $authRequest->setAuthorizationApproved(true);
            $server->completeAuthorizationRequest($authRequest, $wrappedResponse);
        } catch (OAuthServerException $e) {
            $e->generateHttpResponse($wrappedResponse);
        }

        $response = $wrappedResponse->getHandle();
        return $response->getBody() ?? $response;
    }

    public function accessToken(): string|Response
    {
        $server = getAuthorizationServer();

        $wrappedRequest = new RequestWrapper($this->request);
        $wrappedResponse = new ResponseWrapper(Services::response());

        try {
            $server->respondToAccessTokenRequest($wrappedRequest, $wrappedResponse);
        } catch (OAuthServerException $e) {
            $e->generateHttpResponse($wrappedResponse);
        }

        $response = $wrappedResponse->getHandle();
        return $response->getBody() ?? $response;
    }
}