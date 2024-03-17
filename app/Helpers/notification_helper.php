<?php

namespace App\Helpers;

use App\Entities\Notification;
use App\Models\NotificationModel;
use ReflectionException;

/**
 * Returns a user's notifications.
 *
 * @param int $userId
 * @param int $limit
 * @return Notification[]
 */
function getNotificationsByUserId(int $userId, int $limit = 0): array
{
    if ($limit > 0) {
        return getNotificationModel()->where('user_id', $userId)->limit($limit)->orderBy('created_at', 'DESC')->findAll();
    }

    return getNotificationModel()->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();
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