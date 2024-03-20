<?php

use function App\Helpers\getCurrentUser;
use function App\Helpers\getNotificationsByUserId;

$notifications = getNotificationsByUserId(getCurrentUser()->getId(), $limit, true);
?>

<?php if (count($notifications) > 0): ?>
    <ul class="list-group">
        <?php foreach ($notifications as $notification): ?>
            <li class="list-group-item">
                <div class="row">
                    <div class="col-8">
                        <h5 class="mb-2"><?= $notification->getSubject() ?></h5>
                        <p><?= $notification->getBody() ?></p>
                    </div>
                    <div class="col-4 text-end">
                        <p class="mb-1"><?= $notification->getCreateDate()->format('d.m. H:i:s') ?></p>
                        <?= form_open(base_url("notification/{$notification->getId()}/delete"), 'method="post"') ?>
                        <button type="submit" class="btn btn-outline-danger btn-block btn-lg">
                            <i class="fas fa-times"></i>
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


