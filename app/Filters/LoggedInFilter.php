<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use function App\Helpers\isLoggedIn;

class LoggedInFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('user');
        if (!isLoggedIn()) {
            $query = urlencode("?{$request->getUri()->getQuery()}");
            return redirect()->to(site_url('login') . "?return={$request->getUri()->getPath()}{$query}");
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}