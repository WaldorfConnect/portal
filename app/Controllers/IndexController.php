<?php

namespace App\Controllers;

use App\Exceptions\AuthException;
use App\Exceptions\LDAPException;
use CodeIgniter\HTTP\RedirectResponse;
use function App\Helpers\getCurrentUser;
use function App\Helpers\handleException;

class IndexController extends BaseController
{
    public function index(): string|RedirectResponse
    {
        try {
            $user = getCurrentUser();

            if (!is_null($user)) {
                return $this->render('IndexView');
            }
        } catch (AuthException|LDAPException $e) {
            return handleException($e);
        }

        return redirect('login');
    }

    public function error(): string
    {
        return $this->render('ErrorView');
    }

}
