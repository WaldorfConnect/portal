<?php

namespace App\Controllers;

use function App\Helpers\getUserByUsernameAndPassword;
use function App\Helpers\login;
use function App\Helpers\logout;

class AdminController extends BaseController
{
    public function index(): string
    {
        return $this->render('admin/IndexView');
    }

    public function ldap(): string
    {
        return $this->render('admin/LdapView');
    }
}
