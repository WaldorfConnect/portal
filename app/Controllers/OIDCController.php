<?php

namespace App\Controllers;

use App\OIDC\Entities\UserEntity;
use App\OIDC\Http\RequestWrapper;
use App\OIDC\Http\ResponseWrapper;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use Exception;
use League\OAuth2\Server\Exception\OAuthServerException;
use Throwable;
use function App\Helpers\createAuthorizationServer;
use function App\Helpers\getCurrentUser;

class OIDCController extends BaseController
{
    public function authorize(): string|Response
    {
        try {
            $server = createAuthorizationServer();
        } catch (Throwable $e) {
            log_message('error', 'Failed to create authorization server: {exception}', ['exception' => $e]);

            $this->response->setStatusCode(500);
            return "Failed to create authorization server";
        }

        $wrappedRequest = new RequestWrapper($this->request);
        $wrappedResponse = new ResponseWrapper(Services::response());

        try {
            $authRequest = $server->validateAuthorizationRequest($wrappedRequest);
            $authRequest->setUser(new UserEntity(getCurrentUser()->getUsername()));
            $authRequest->setAuthorizationApproved(true);
            $server->completeAuthorizationRequest($authRequest, $wrappedResponse);
        } catch (OAuthServerException $e) {
            log_message('error', 'Failed to handle authorization request: {exception}', ['exception' => $e]);
            $e->generateHttpResponse($wrappedResponse);
        }

        $response = $wrappedResponse->getHandle();
        return $response->getBody() ?? $response;
    }

    public function accessToken(): string|Response
    {
        try {
            $server = createAuthorizationServer();
        } catch (Throwable $e) {
            log_message('error', 'Failed to create authorization server: {exception}', ['exception' => $e]);

            $this->response->setStatusCode(500);
            return "Failed to create authorization server";
        }

        $wrappedRequest = new RequestWrapper($this->request);
        $wrappedResponse = new ResponseWrapper(Services::response());

        try {
            $server->respondToAccessTokenRequest($wrappedRequest, $wrappedResponse);
        } catch (OAuthServerException $e) {
            log_message('error', 'Failed to handle access token request: {exception}', ['exception' => $e]);
            $e->generateHttpResponse($wrappedResponse);
        }

        $response = $wrappedResponse->getHandle();
        return $response->getBody() ?? $response;
    }

    public function logout(): RedirectResponse
    {
        $redirectUri = $this->request->getGet('post_logout_redirect_uri');

        session()->remove('user_id');
        return redirect()->to($redirectUri);
    }
}