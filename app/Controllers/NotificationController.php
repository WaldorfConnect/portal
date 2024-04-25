<?php

namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;
use Exception;
use Throwable;
use function App\Helpers\deleteNotification;
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

        try {
            deleteNotification($id);

            return redirect()->back();
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Fehler beim Löschen der Benachrichtigung: ' . $e);
        }
    }
}