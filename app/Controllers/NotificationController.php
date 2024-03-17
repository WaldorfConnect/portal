<?php

namespace App\Controllers;

use App\OIDC\Entities\UserEntity;
use App\OIDC\Http\RequestWrapper;
use App\OIDC\Http\ResponseWrapper;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use League\OAuth2\Server\Exception\OAuthServerException;
use function App\Helpers\deleteNotification;
use function App\Helpers\getAuthorizationServer;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getNotificationById;

class NotificationController extends BaseController
{
    public function index(): string
    {
        return $this->render('notification/NotificationsView');
    }

    public function handleDelete(int $id): RedirectResponse|string
    {
        $notification = getNotificationById($id);
        $self = getCurrentUser();

        if ($notification->getUserId() != $self->getId()) {
            return redirect()->back()->with('error', 'Du darfst nur deine Benachrichtigungen löschen.');
        }

        deleteNotification($id);
        return redirect()->back();
    }
}