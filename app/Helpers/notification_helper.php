<?php

namespace App\Helpers;

use App\Entities\Notification;
use App\Models\NotificationModel;
use CodeIgniter\CLI\CLI;
use DateTime;
use DateTimeImmutable;
use Exception;

/**
 * Queues notifications mails.
 *
 * @return void
 */
function queueNotificationMails(): void
{
    $now = new DateTimeImmutable();
    foreach (getUsers() as $user) {
        // Skip if users has email notifications disabled
        if (!$user->wantsEmailNotification())
            continue;

        $unreadNotifications = getNotificationsByUserId($user->getId(), 0, false);
        $unreadNotificationsCount = count($unreadNotifications);

        $firstNotification = null;
        $mail = false;
        foreach ($unreadNotifications as $notification) {
            if (!$firstNotification) {
                $firstNotification = $notification;
            }

            if (is_null($notification->getMailDate()) || $now->diff($notification->getMailDate())->days >= 3) {
                try {
                    $notification->setMailDate($now);
                    getNotificationModel()->save($notification);

                    $mail = true;
                } catch (Exception $e) {
                    // TODO handle exception
                }
            }
        }

        if ($mail) {
            if ($unreadNotificationsCount == 1) {
                $subject = 'eine ungelesene Benachrichtigung';
            } else {
                $subject = $unreadNotificationsCount . ' ungelesene Benachrichtigungen';
            }

            try {
                queueMail($user->getId(), $subject,
                    view('mail/NewNotificationsEmail', ['user' => $user, 'notification' => $firstNotification, 'count' => $unreadNotificationsCount - 1]));

                CLI::write("Queueing notification mail for {$user->getUsername()} ...");
            } catch (Exception $e) {
                // TODO handle exception
            }
        }
    }
}

/**
 * Returns a user's notifications.
 *
 * @param int $userId
 * @param int $limit
 * @param bool|null $isRead
 * @param bool $setRead
 * @return Notification[]
 */
function getNotificationsByUserId(int $userId, int $limit = 0, bool $isRead = null, bool $setRead = false): array
{
    $model = getNotificationModel()
        ->where('user_id', $userId)
        ->orderBy('created_at', 'DESC');

    if (!is_null($isRead)) {
        $model = $model->where('read_at' . ($isRead ? ' IS NOT NULL' : ''));
    }

    if ($limit > 0) {
        $model = $model->limit($limit);
    }

    $notifications = $model->findAll();
    if ($setRead) {
        readNotifications($notifications);
    }

    return $notifications;
}

/**
 * Returns a count of a user's notifications.
 *
 * @param int $userId
 * @return int
 */
function countUnreadNotificationsByUserId(int $userId): int
{
    return getNotificationModel()->where('user_id', $userId)->where('read_at')->countAllResults();
}

/**
 * Create a new notification.
 *
 */
function createNotification(int $userId, string $subject, string $body): void
{
    $notification = new Notification();
    $notification->setUserId($userId);
    $notification->setSubject($subject);
    $notification->setBody($body);

    try {
        getNotificationModel()->insert($notification);
    } catch (Exception $e) {
        // TODO handle exception
    }
}

/**
 * @param Notification[] $notifications
 * @return void
 */
function readNotifications(array $notifications): void
{
    $now = new DateTime();

    foreach ($notifications as $notification) {
        $clonedNotification = clone $notification;

        if ($clonedNotification->getReadDate()) {
            continue;
        }

        $clonedNotification->setReadDate($now);
        try {
            getNotificationModel()->save($clonedNotification);
        } catch (Exception $e) {
            // TODO handle exception
        }
    }
}

/**
 * Delete a notification.
 *
 * @param int $id
 * @return void
 */
function deleteNotification(int $id): void
{
    getNotificationModel()->delete($id);
}

/**
 * @param int $id
 * @return ?Notification
 */
function getNotificationById(int $id): ?object
{
    return getNotificationModel()->find($id);
}

/**
 * @return NotificationModel
 */
function getNotificationModel(): NotificationModel
{
    return new NotificationModel();
}