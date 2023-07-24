<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use function App\Helpers\isLoggedIn;

class LoggedOutFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('user');
        if (isLoggedIn()) {
            return redirect()->to(site_url('/'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}