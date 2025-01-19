<?php

namespace App\Helpers;

use App\Entities\Notification;
use App\Models\NotificationModel;
use CodeIgniter\CLI\CLI;
use DateTime;
use DateTimeImmutable;
use Exception;
use Throwable;

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

                    log_message('info', "Set notification mail reoccurrence for {$user->getUsername()} to {$now->format('d.m.Y H:i:s')} ...");
                    $mail = true;
                } catch (Throwable $e) {
                    log_message('error', 'Error updating notification mail date: {exception}', ['exception' => $e]);
                }
            }
        }

        if ($mail) {
            if ($unreadNotificationsCount == 1) {
                $subject = 'Eine ungelesene Benachrichtigung';
            } else {
                $subject = $unreadNotificationsCount . ' ungelesene Benachrichtigungen';
            }

            try {
                queueMail($user->getId(), $subject,
                    view('mail/UnreadNotifications', ['user' => $user, 'notification' => $firstNotification, 'count' => $unreadNotificationsCount - 1]));

                log_message('info', "Notification reminder mail queued: 'username={$user->getUsername()}'");
            } catch (Throwable $e) {
                log_message('error', "Error queueing notification reminder mail: 'username={$user->getUsername()}': {exception}", ['exception' => $e]);
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

    $notifications = $model->findAll($limit);
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
 * @param int $userId
 * @param string $subject
 * @param string $body
 */
function createNotification(int $userId, string $subject, string $body): void
{
    $notification = new Notification();
    $notification->setUserId($userId);
    $notification->setSubject($subject);
    $notification->setBody($body);

    try {
        $id = getNotificationModel()->insert($notification);
        log_message('info', "Notification created: 'notificationId={$id},userId={$userId},subject={$subject}'");
    } catch (Throwable $e) {
        log_message('error', "Error inserting notification: 'userId={$userId},subject={$subject}': {exception}", ['exception' => $e]);
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
        } catch (Throwable $e) {
            log_message('error', "Error saving notification: 'notificationId={$clonedNotification->getId()},userId={$clonedNotification->getUserId()}': {exception}", ['exception' => $e]);
        }

        log_message('info', "Notification read: 'notificationId={$notification->getId()}'");
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
    log_message('info', "Notification deleted: 'notificationId={$id}'");
}

/**
 * Delete all notifications of a user.
 *
 * @param int $userId
 * @return void
 */
function deleteAllNotifications(int $userId): void
{
    getNotificationModel()->where('user_id', $userId)->delete();
    log_message('info', "Notifications deleted: 'userId={$userId}'");
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