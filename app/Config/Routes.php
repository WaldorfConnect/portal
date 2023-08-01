<?php

namespace Config;

// Create a new instance of our RouteCollection class.
use App\Filters\AdminFilter;
use App\Filters\GlobalAdminFilter;
use App\Filters\LoggedInFilter;
use App\Filters\LoggedOutFilter;

$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('IndexController');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'IndexController::index', ['filter' => LoggedInFilter::class]);

$routes->get('/login', 'AuthenticationController::login', ['filter' => LoggedOutFilter::class]);
$routes->post('/login', 'AuthenticationController::handleLogin', ['filter' => LoggedOutFilter::class]);

$routes->get('/logout', 'AuthenticationController::logout', ['filter' => LoggedInFilter::class]);

$routes->get('/register', 'AuthenticationController::register', ['filter' => LoggedOutFilter::class]);
$routes->post('/register', 'AuthenticationController::handleRegister', ['filter' => LoggedOutFilter::class]);

$routes->get('/user/reset_password', 'UserController::resetPassword', ['filter' => LoggedOutFilter::class]);
$routes->post('/user/reset_password', 'UserController::handleResetPassword', ['filter' => LoggedOutFilter::class]);

$routes->get('/user/profile', 'UserController::profile', ['filter' => LoggedInFilter::class]);
$routes->post('/user/profile', 'UserController::handleProfile', ['filter' => LoggedInFilter::class]);

$routes->get('/user/confirm', 'UserController::handleConfirm');

$routes->get('/schools', 'SchoolController::list', ['filter' => LoggedInFilter::class]);
$routes->get('/school/(:num)', 'SchoolController::school/$1', ['filter' => LoggedInFilter::class]);

$routes->get('/groups', 'GroupController::list', ['filter' => LoggedInFilter::class]);
$routes->get('/group/(:num)', 'GroupController::group/$1', ['filter' => LoggedInFilter::class]);

$routes->get('/admin', 'AdminController::index', ['filter' => AdminFilter::class]);
$routes->get('/admin/ldap', 'AdminController::ldap', ['filter' => GlobalAdminFilter::class]);
$routes->get('/admin/users', 'AdminController::users', ['filter' => GlobalAdminFilter::class]);

$routes->cli('/sync', 'SynchronisationController::index');

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
