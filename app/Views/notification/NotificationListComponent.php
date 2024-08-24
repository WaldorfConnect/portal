<?php

use function App\Helpers\getCurrentUser;
use function App\Helpers\getNotificationsByUserId;

$notifications = getNotificationsByUserId(getCurrentUser()->getId(), $limit, null, true);
?>
<?php if (count($notifications) > 0): ?>
    <div class="row">
        <?= form_open('notification/delete_all', ['onsubmit' => "return confirm('Möchtest du wirklich alle Benachrichtigungen löschen?');"]) ?>
        <div class="float-end mb-3">
            <button type="submit" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-trash"></i> Alle löschen
            </button>
        </div>
        <?= form_close() ?>
    </div>
    <ul class="list-group">
        <?php foreach ($notifications as $notification): ?>
            <li class="list-group-item <?= $notification->getReadDate() ? '' : 'notification-unread' ?>">
                <div class="row">
                    <div class="col-10">
                        <h5 class="mb-2"><?= $notification->getSubject() ?></h5>
                        <p><?= $notification->getBody() ?></p>
                        <span class="notification-meta"><?= $notification->getCreateDate()->format('d.m.Y H:i') ?>
                            &ndash; <?= $notification->getReadDate() ? 'gelesen' : 'ungelesen' ?></span>
                    </div>
                    <div class="col-2 text-end">
                        <?= form_open(base_url("notification/{$notification->getId()}/delete")) ?>
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?= form_close() ?>
                    </div>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <div class="text-center">
        <p>Keine Benachrichtigungen!</p>
    </div>
<?php endif; ?>


