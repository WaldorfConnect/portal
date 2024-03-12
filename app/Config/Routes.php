<?php

use App\Filters\AdminFilter;
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
$routes->post('/register/resend', 'AuthenticationController::handleRegisterResendConfirmationEmail', ['filter' => LoggedOutFilter::class]);

$routes->get('/user/reset_password', 'UserController::resetPassword', ['filter' => LoggedOutFilter::class]);
$routes->post('/user/reset_password', 'UserController::handleResetPassword', ['filter' => LoggedOutFilter::class]);

$routes->get('/user/profile', 'UserController::profile', ['filter' => LoggedInFilter::class]);
$routes->post('/user/profile', 'UserController::handleProfile', ['filter' => LoggedInFilter::class]);
$routes->post('/user/profile/resend', 'UserController::handleProfileResendConfirmationEmail', ['filter' => LoggedInFilter::class]);

$routes->get('/user/confirm', 'UserController::handleConfirm');

$routes->get('/organisations', 'OrganisationController::list', ['filter' => LoggedInFilter::class]);
$routes->get('/organisation/(:num)', 'OrganisationController::organisation/$1', ['filter' => LoggedInFilter::class]);

$routes->post('/organisation/(:num)/join', 'OrganisationController::handleJoin/$1', ['filter' => LoggedInFilter::class]);
$routes->post('/organisation/(:num)/leave', 'OrganisationController::handleLeave/$1', ['filter' => LoggedInFilter::class]);
$routes->post('/organisation/(:num)/add_member', 'OrganisationController::handleAddMember/$1', ['filter' => LoggedInFilter::class]);
$routes->post('/organisation/(:num)/add_workgroup', 'OrganisationController::handleAddWorkgroup/$1', ['filter' => LoggedInFilter::class]);
$routes->get('/organisation/(:num)/edit', 'OrganisationController::edit/$1', ['filter' => LoggedInFilter::class]);
$routes->post('/organisation/(:num)/edit', 'OrganisationController::handleEdit/$1', ['filter' => LoggedInFilter::class]);
$routes->post('/organisation/(:num)/accept', 'OrganisationController::handleAcceptJoin/$1', ['filter' => LoggedInFilter::class]);
$routes->post('/organisation/(:num)/deny', 'OrganisationController::handleDenyJoin/$1', ['filter' => LoggedInFilter::class]);
$routes->post('/organisation/(:num)/delete', 'OrganisationController::handleDelete/$1', ['filter' => LoggedInFilter::class]);
$routes->post('/organisation/(:num)/membership_status', 'OrganisationController::handleChangeMembershipStatus/$1', ['filter' => LoggedInFilter::class]);
$routes->post('/organisation/(:num)/kick_user', 'OrganisationController::handleKickUser/$1', ['filter' => LoggedInFilter::class]);

$routes->get('/admin', 'AdminController::index', ['filter' => AdminFilter::class]);
$routes->get('/admin/debug', 'AdminController::debug', ['filter' => AdminFilter::class]);

$routes->get('/admin/users', 'AdminController::users', ['filter' => AdminFilter::class]);
$routes->post('/admin/user/activate', 'AdminController::activateUser', ['filter' => AdminFilter::class]);
$routes->post('/admin/user/deactivate', 'AdminController::deactivateUser', ['filter' => AdminFilter::class]);
$routes->post('/admin/user/accept', 'AdminController::acceptUser', ['filter' => AdminFilter::class]);
$routes->post('/admin/user/delete', 'AdminController::handleDeleteUser', ['filter' => AdminFilter::class]);
$routes->get('/admin/user/edit/(:num)', 'AdminController::editUser/$1', ['filter' => AdminFilter::class]);
$routes->post('/admin/user/edit', 'AdminController::handleEditUser', ['filter' => AdminFilter::class]);

$routes->get('/admin/organisations', 'AdminController::organisations', ['filter' => AdminFilter::class]);
$routes->get('/admin/organisation/create', 'AdminController::createOrganisation', ['filter' => AdminFilter::class]);
$routes->post('/admin/organisation/create', 'AdminController::handleCreateOrganisation', ['filter' => AdminFilter::class]);
$routes->post('/admin/organisation/delete', 'AdminController::handleDeleteOrganisation', ['filter' => AdminFilter::class]);

$routes->get('/admin/regions', 'AdminController::regions', ['filter' => AdminFilter::class]);
$routes->get('/admin/region/create', 'AdminController::createRegion', ['filter' => AdminFilter::class]);
$routes->post('/admin/region/create', 'AdminController::handleCreateRegion', ['filter' => AdminFilter::class]);
$routes->post('/admin/region/delete', 'AdminController::handleDeleteRegion', ['filter' => AdminFilter::class]);
$routes->get('/admin/region/edit/(:num)', 'AdminController::editRegion/$1', ['filter' => AdminFilter::class]);
$routes->post('/admin/region/edit', 'AdminController::handleEditRegion', ['filter' => AdminFilter::class]);

$routes->get('/oidc/authorize', 'OIDCController::authorize', ['filter' => LoggedInFilter::class]);
$routes->post('/oidc/access_token', 'OIDCController::accessToken');
$routes->get('/oidc/logout', 'OIDCController::logout');

$routes->cli('/cron_mail', 'CronController::mail');
$routes->cli('/cron_ldap', 'CronController::ldap');
$routes->cli('/cron_nextcloud', 'CronController::nextcloud');