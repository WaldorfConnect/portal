<?php

namespace App\Helpers;

use App\Entities\Notification;
use App\Models\NotificationModel;
use DateTime;
use ReflectionException;

/**
 * Returns a user's notifications.
 *
 * @param int $userId
 * @param int $limit
 * @param bool $read
 * @return Notification[]
 */
function getNotificationsByUserId(int $userId, int $limit = 0, bool $read = false): array
{
    $model = getNotificationModel()
        ->where('user_id', $userId)
        ->where('deleted_at', '')
        ->orderBy('created_at', 'DESC');
    if ($limit > 0) {
        $model = $model->limit($limit);
    }

    $notifications = $model->findAll();
    if ($read) {
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
function countNotificationsByUserId(int $userId): int
{
    return getNotificationModel()->where('user_id', $userId)->countAllResults();
}

/**
 * Create a new notification.
 *
 * @throws ReflectionException
 */
function createNotification(int $userId, string $subject, string $body): void
{
    $notification = new Notification();
    $notification->setUserId($userId);
    $notification->setSubject($subject);
    $notification->setBody($body);
    getNotificationModel()->insert($notification);
}

/**
 * @param Notification[] $notifications
 * @return void
 */
function readNotifications(array $notifications): void
{
    $now = new DateTime();

    foreach ($notifications as $notification) {
        if ($notification->getReadDate()) {
            continue;
        }

        $notification->setReadDate($now);
        try {
            getNotificationModel()->save($notification);
        } catch (ReflectionException $e) {
        }
    }
}

/**
 * Delete a notification.
 *
 * @param int $id
 * @return void
 * @throws ReflectionException
 */
function deleteNotification(int $id): void
{
    $notification = getNotificationById($id);
    $notification->setDeleteDate(new DateTime());
    getNotificationModel()->save($notification);
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