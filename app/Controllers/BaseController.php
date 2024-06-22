<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var array
     */
    protected $helpers = ['group', 'region', 'user', 'ldap', 'mail', 'cron', 'sync', 'image', 'form', 'filesystem', 'oidc', 'search', 'notification'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * Constructor.
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
    }

    public function render($name, $data = null, $renderNavbar = true): string
    {
        $renderedContent = view('components/header');

        if ($renderNavbar) {
            $renderedContent .= view('components/navbar');
        }

        if (!is_null($data)) {
            $renderedContent .= view($name, $data);
        } else {
            $renderedContent .= view($name);
        }

        $renderedContent .= view('components/footer');
        return $renderedContent;
    }
}
