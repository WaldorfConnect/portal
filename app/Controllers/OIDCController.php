<?php

namespace App\Controllers;

use App\OIDC\Entities\UserEntity;
use App\OIDC\Http\RequestWrapper;
use App\OIDC\Http\ResponseWrapper;
use CodeIgniter\HTTP\Response;
use League\OAuth2\Server\Exception\OAuthServerException;
use function App\Helpers\getAuthorizationServer;

class OIDCController extends BaseController
{
    public function authorize(): Response
    {
        $server = getAuthorizationServer();

        $wrappedRequest = new RequestWrapper($this->request);
        $wrappedResponse = new ResponseWrapper($this->response);

        try {
            $authRequest = $server->validateAuthorizationRequest($wrappedRequest);
            $authRequest->setUser(new UserEntity('lgroschke'));
            $authRequest->setAuthorizationApproved(true);
            $wrappedResponse = $server->completeAuthorizationRequest($authRequest, $wrappedResponse);
        } catch (OAuthServerException $e) {
            $wrappedResponse = $e->generateHttpResponse($wrappedResponse);
        }

        return $wrappedResponse->getHandle();
    }

    public function accessToken(): Response
    {
        $server = getAuthorizationServer();

        $wrappedRequest = new RequestWrapper($this->request);
        $wrappedResponse = new ResponseWrapper($this->response);

        try {
            $server->respondToAccessTokenRequest($wrappedRequest, $wrappedResponse);
        } catch (OAuthServerException $e) {
            $wrappedResponse =
        }

    }
}