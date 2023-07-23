<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class IndexController extends BaseController
{
    public function index(): string|RedirectResponse
    {
        return $this->render('IndexView');
    }
}
