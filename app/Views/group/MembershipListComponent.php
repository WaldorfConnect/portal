<ul class="list-group">
    <?php foreach ($user->getMemberships() as $membership): ?>
        <li class="list-group-item">
            <div class="flex-container">
                <div class="flex-main">
                    <?php $group = $membership->getGroup(); ?>
                    <?php if ($group->getParentId()): ?>
                        <?= $group->getParent()->getName() ?>
                        <br>
                        <small><?= $group->getName() ?></small>
                    <?php else: ?>
                        <?= $group->getName() ?>
                    <?php endif; ?>
                </div>
                <div class="flex-actions">
                    <div class="me-2">
                        <?= $membership->getStatus()->badge() ?>
                    </div>
                    <a class="btn btn-sm btn-outline-primary"
                       href="<?= base_url('group') ?>/<?= $group->getId() ?>">
                        Öffnen
                    </a>
                </div>
            </div>
        </li>
    <?php endforeach; ?>
</ul>