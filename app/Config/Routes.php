<?php

use App\Filters\AdminFilter;
use App\Filters\GlobalAdminFilter;
use App\Filters\LoggedInFilter;
use App\Filters\LoggedOutFilter;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

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

$routes->post('/group/join', 'GroupController::handleJoin', ['filter' => LoggedInFilter::class]);
$routes->post('/group/accept', 'GroupController::handleAcceptJoin', ['filter' => LoggedInFilter::class]);
$routes->post('/group/deny', 'GroupController::handleDenyJoin', ['filter' => LoggedInFilter::class]);
$routes->post('/group/change_user_status', 'GroupController::handleChangeUserStatus', ['filter' => LoggedInFilter::class]);
$routes->post('/group/kick_user', 'GroupController::handleKickUser', ['filter' => LoggedInFilter::class]);

$routes->get('/admin', 'AdminController::index', ['filter' => AdminFilter::class]);
$routes->get('/admin/debug', 'AdminController::debug', ['filter' => GlobalAdminFilter::class]);

$routes->get('/admin/users', 'AdminController::users', ['filter' => AdminFilter::class]);
$routes->post('/admin/user/accept', 'AdminController::acceptUser', ['filter' => AdminFilter::class]);
$routes->post('/admin/user/deny', 'AdminController::denyUser', ['filter' => AdminFilter::class]);
$routes->post('/admin/user/delete', 'AdminController::handleDeleteUser', ['filter' => AdminFilter::class]);
$routes->get('/admin/user/edit/(:num)', 'AdminController::editUser/$1', ['filter' => AdminFilter::class]);
$routes->post('/admin/user/edit', 'AdminController::handleEditUser', ['filter' => AdminFilter::class]);

$routes->get('/admin/groups', 'AdminController::groups', ['filter' => AdminFilter::class]);
$routes->get('/admin/group/create', 'AdminController::createGroup', ['filter' => AdminFilter::class]);
$routes->post('/admin/group/create', 'AdminController::handleCreateGroup', ['filter' => AdminFilter::class]);
$routes->post('/admin/group/delete', 'AdminController::handleDeleteGroup', ['filter' => AdminFilter::class]);
$routes->get('/admin/group/edit/(:num)', 'AdminController::editGroup/$1', ['filter' => AdminFilter::class]);
$routes->post('/admin/group/edit', 'AdminController::handleEditGroup', ['filter' => AdminFilter::class]);

$routes->get('/admin/schools', 'AdminController::schools', ['filter' => AdminFilter::class]);
$routes->get('/admin/school/create', 'AdminController::createSchool', ['filter' => AdminFilter::class]);
$routes->post('/admin/school/create', 'AdminController::handleCreateSchool', ['filter' => AdminFilter::class]);
$routes->post('/admin/school/delete', 'AdminController::handleDeleteSchool', ['filter' => AdminFilter::class]);
$routes->get('/admin/school/edit/(:num)', 'AdminController::editSchool/$1', ['filter' => AdminFilter::class]);
$routes->post('/admin/school/edit', 'AdminController::handleEditSchool', ['filter' => AdminFilter::class]);

$routes->get('/admin/regions', 'AdminController::regions', ['filter' => GlobalAdminFilter::class]);
$routes->get('/admin/region/create', 'AdminController::createRegion', ['filter' => GlobalAdminFilter::class]);
$routes->post('/admin/region/create', 'AdminController::handleCreateRegion', ['filter' => GlobalAdminFilter::class]);
$routes->post('/admin/region/delete', 'AdminController::handleDeleteRegion', ['filter' => GlobalAdminFilter::class]);
$routes->get('/admin/region/edit/(:num)', 'AdminController::editRegion/$1', ['filter' => GlobalAdminFilter::class]);
$routes->post('/admin/region/edit', 'AdminController::handleEditRegion', ['filter' => GlobalAdminFilter::class]);

$routes->cli('/cron', 'CronController::index');