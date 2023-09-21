<?php

use function App\Helpers\getCurrentUser;
use function App\Helpers\getGroupsByUserId;

?>
<h1 class="header">Willkommen <?= ($user = getCurrentUser())->getName() ?></h1>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3">
    <div class="col mb-4">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                Dienste
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 mx-auto">
                    <a class="btn btn-primary btn-lg" href="https://cloud.waldorfconnect.de/" target="_blank">
                        <i class="fas fa-cloud fa-3x"></i><br>Cloud
                    </a>
                    <!--<a class="btn btn-primary btn-lg" href="https://cloud.waldorfconnect.de/apps/spreed/"
                       target="_blank">
                        <i class="fas fa-message fa-3x"></i><br>Chat
                    </a>-->
                </div>
            </div>
        </div>
    </div>
    <div class="col mb-4">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                Gruppen
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php foreach ($user->getGroupMemberships() as $membership): ?>
                        <li class="list-group-item">
                            <div class="flex-container">
                                <div class="flex-main">
                                    <?= ($group = $membership->getGroup())->getName() ?>
                                    <?= $membership->getStatus()->badge() ?>
                                </div>
                                <div class="flex-actions">
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="<?= base_url('group') ?>/<?= $group->getId() ?>">
                                        Öffnen
                                    </a>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="card-footer footer-plain">
                <div class="text-center">
                    <a class="btn btn-block btn-outline-primary" href="/groups">
                        Alle Gruppen anzeigen
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col mb-4">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                Schule
            </div>
            <div class="card-body text-center">
                <div class="d-grid gap-2 mx-auto">
                    <a class="btn btn-primary btn-lg"
                       href="<?= base_url('school') ?>/<?= ($school = $user->getSchool())->getId() ?>">
                        <i class="fas fa-school fa-3x"></i><br><?= $school->getName() ?>
                    </a>
                </div>
            </div>
            <div class="card-footer footer-plain">
                <div class="text-center">
                    <a class="btn btn-block btn-outline-primary" href="/schools">
                        Alle Schulen anzeigen
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>