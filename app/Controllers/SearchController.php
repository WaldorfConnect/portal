<?php

namespace App\Controllers;

use App\OIDC\Entities\UserEntity;
use App\OIDC\Http\RequestWrapper;
use App\OIDC\Http\ResponseWrapper;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use League\OAuth2\Server\Exception\OAuthServerException;
use function App\Helpers\getAuthorizationServer;
use function App\Helpers\getCurrentUser;

class SearchController extends BaseController
{
    public function index(): string
    {
        $query = esc(trim($this->request->getGet('query')));

        return $this->render('search/SearchView', ['query' => $query]);
    }
}