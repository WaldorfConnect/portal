<?php

namespace App\Controllers;

use App\OIDC\Http\RequestWrapper;
use App\OIDC\Http\ResponseWrapper;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use function App\Helpers\accessTokenRequest;
use function App\Helpers\authorizationRequest;
use function App\Helpers\logout;

class OIDCController extends BaseController
{
    public function authorize(): string|Response
    {
        $wrappedRequest = new RequestWrapper($this->request);
        $wrappedResponse = new ResponseWrapper(Services::response());

        authorizationRequest($wrappedRequest, $wrappedResponse);
        $response = $wrappedResponse->getHandle();
        return $response->getBody() ?? $response;
    }

    public function accessToken(): string|Response
    {
        $wrappedRequest = new RequestWrapper($this->request);
        $wrappedResponse = new ResponseWrapper(Services::response());

        accessTokenRequest($wrappedRequest, $wrappedResponse);
        $response = $wrappedResponse->getHandle();
        return $response->getBody() ?? $response;
    }

    public function logout(): RedirectResponse
    {
        $redirectUri = $this->request->getGet('post_logout_redirect_uri');

        logout(true);
        return redirect()->to($redirectUri);
    }
}