<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use function App\Helpers\getGroups;
use function App\Helpers\getSchools;
use function App\Helpers\getUserByUsernameAndPassword;
use function App\Helpers\login;
use function App\Helpers\logout;

class UserController extends BaseController
{
    public function profile(): string
    {
        return $this->render('user/EditProfileView');
    }

    public function handleProfile(): RedirectResponse
    {

    }

    public function resetPassword(): string
    {
        return $this->render('user/PasswortResetView', [], false);
    }

    public function handleResetPassword(): RedirectResponse
    {

    }
}
