<?php

namespace App\Filters;

use App\Entities\UserRole;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use function App\Helpers\getCurrentUser;

class GlobalAdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('user');
        helper('user');
        $user = getCurrentUser();
        if (!$user) {
            return redirect()->to(site_url('login') . "?return={$request->getUri()->getPath()}");
        }

        if (getCurrentUser()->getRole() != UserRole::GLOBAL_ADMIN) {
            return redirect()->to(site_url('/'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}