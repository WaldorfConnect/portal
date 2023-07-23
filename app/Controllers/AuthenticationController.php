<?php

namespace App\Controllers;

use App\Exceptions\AuthException;
use App\Exceptions\LDAPException;
use CodeIgniter\HTTP\RedirectResponse;
use function App\Helpers\getGroups;
use function App\Helpers\getSchools;
use function App\Helpers\login;
use function App\Helpers\logout;

class AuthenticationController extends BaseController
{
    public function login(): string|RedirectResponse
    {
        return $this->render('auth/LoginView', [], false);
    }

    public function register(): string
    {
        return $this->render('auth/RegisterView', ['schools' => getSchools(), 'groups' => getGroups()], false);
    }

    public function handleLogin(): RedirectResponse
    {
        $username = trim($this->request->getPost('username'));
        $password = trim($this->request->getPost('password'));

        try {
            login($username, $password);
        } catch (AuthException $e) {
            return redirect('login')->with('error', 'Ungültige Zugangsdaten!');
        } catch (LDAPException $e) {
            return redirect('login')->with('error', $e->getMessage());
        }

        return redirect('/');
    }

    public function editProfile(): string
    {
        return $this->render('user/EditProfileView', ['schools' => getSchools(), 'groups' => getGroups()]);
    }

    public function resetPassword(): string
    {
        return $this->render('user/PasswortResetView', [], false);
    }

    public function logout(): RedirectResponse
    {
        logout();
        return redirect('login');
    }
}
