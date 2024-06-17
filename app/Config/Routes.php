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

$routes->get('/user/profile', 'User\UserProfileController::profile', ['filter' => LoggedInFilter::class]);
$routes->post('/user/profile', 'User\UserProfileController::handleProfile', ['filter' => LoggedInFilter::class]);
$routes->post('/user/profile/resend', 'User\UserProfileController::handleProfileResendConfirmationEmail', ['filter' => LoggedInFilter::class]);
$routes->get('/user/confirm', 'User\UserController::handleConfirm');

$routes->get('/user/settings', 'User\UserSettingsController::settings', ['filter' => LoggedInFilter::class]);
$routes->post('/user/settings', 'User\UserSettingsController::handleSettings', ['filter' => LoggedInFilter::class]);

$routes->get('/user/security', 'User\UserSecurityController::security', ['filter' => LoggedInFilter::class]);
$routes->post('/user/security', 'User\UserSecurityController::handleSecurity', ['filter' => LoggedInFilter::class]);
$routes->post('/user/security/totp', 'User\UserSecurityController::handleTOTPEnable', ['filter' => LoggedInFilter::class]);
$routes->get('/user/security/reset_password', 'User\UserSecurityController::resetPassword', ['filter' => LoggedOutFilter::class]);
$routes->post('/user/security/reset_password', 'User\UserSecurityController::handleResetPassword', ['filter' => LoggedOutFilter::class]);

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

$routes->get('/admin', 'Admin\AdminController::index', ['filter' => AdminFilter::class]);

$routes->get('/admin/users', 'Admin\AdminUserController::users', ['filter' => AdminFilter::class]);
$routes->post('/admin/user/activate', 'Admin\AdminUserController::activateUser', ['filter' => AdminFilter::class]);
$routes->post('/admin/user/deactivate', 'Admin\AdminUserController::deactivateUser', ['filter' => AdminFilter::class]);
$routes->post('/admin/user/accept', 'Admin\AdminUserController::acceptUser', ['filter' => AdminFilter::class]);
$routes->post('/admin/user/delete', 'Admin\AdminUserController::handleDeleteUser', ['filter' => AdminFilter::class]);
$routes->get('/admin/user/edit/(:num)', 'Admin\AdminUserController::editUser/$1', ['filter' => AdminFilter::class]);
$routes->post('/admin/user/edit', 'Admin\AdminUserController::handleEditUser', ['filter' => AdminFilter::class]);

$routes->get('/admin/organisations', 'Admin\AdminOrganisationController::organisations', ['filter' => AdminFilter::class]);
$routes->get('/admin/organisation/create', 'Admin\AdminOrganisationController::createOrganisation', ['filter' => AdminFilter::class]);
$routes->post('/admin/organisation/create', 'Admin\AdminOrganisationController::handleCreateOrganisation', ['filter' => AdminFilter::class]);
$routes->post('/admin/organisation/delete', 'Admin\AdminOrganisationController::handleDeleteOrganisation', ['filter' => AdminFilter::class]);

$routes->get('/admin/regions', 'Admin\AdminRegionController::regions', ['filter' => AdminFilter::class]);
$routes->get('/admin/region/create', 'Admin\AdminRegionController::createRegion', ['filter' => AdminFilter::class]);
$routes->post('/admin/region/create', 'Admin\AdminRegionController::handleCreateRegion', ['filter' => AdminFilter::class]);
$routes->post('/admin/region/delete', 'Admin\AdminRegionController::handleDeleteRegion', ['filter' => AdminFilter::class]);
$routes->get('/admin/region/edit/(:num)', 'Admin\AdminRegionController::editRegion/$1', ['filter' => AdminFilter::class]);
$routes->post('/admin/region/edit', 'Admin\AdminRegionController::handleEditRegion', ['filter' => AdminFilter::class]);

$routes->get('/oidc/authorize', 'OIDCController::authorize', ['filter' => LoggedInFilter::class]);
$routes->post('/oidc/access_token', 'OIDCController::accessToken');
$routes->get('/oidc/logout', 'OIDCController::logout');

$routes->get('/search', 'SearchController::index', ['filter' => LoggedInFilter::class]);

$routes->get('/notifications', 'NotificationController::index', ['filter' => LoggedInFilter::class]);
$routes->post('/notification/(:num)/delete', 'NotificationController::handleDelete/$1', ['filter' => LoggedInFilter::class]);

$routes->cli('/cron_mail', 'CronController::mail');
$routes->cli('/cron_notifications', 'CronController::notifications');
$routes->cli('/cron_ldap', 'CronController::ldap');
$routes->cli('/cron_nextcloud', 'CronController::nextcloud');