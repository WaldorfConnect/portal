<?php

namespace App\Controllers;

use App\Exceptions\AuthException;
use App\Exceptions\LDAPException;
use CodeIgniter\HTTP\RedirectResponse;
use function App\Helpers\handleException;
use function App\Helpers\isLoggedIn;
use function App\Helpers\login;

class AuthenticationController extends BaseController
{
    public function login(): string|RedirectResponse
    {
        try {
            if (isLoggedIn()) {
                return redirect('/');
            }
        } catch (AuthException|LDAPException $e) {
            return handleException($e);
        }

        return $this->render('LoginView');
    }

    public function handleLogin(): RedirectResponse
    {
        $username = trim($this->request->getPost('username'));
        $password = trim($this->request->getPost('password'));

        try {
            login($username, $password);
        } catch (AuthException $e) {
            return redirect('login')->with('error', 'login.error.invalidCredentials');
        } catch (LDAPException $e) {
            return handleException($e);
        }

        return redirect('/');
    }
}
