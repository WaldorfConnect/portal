<?php

use function App\Helpers\getCurrentUser;

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
                    <a class="btn btn-primary btn-lg" href="https://cloud.waldorfconnect.de/apps/spreed/"
                       target="_blank">
                        <i class="fas fa-message fa-3x"></i><br>Chat
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col mb-4">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                Mitgliedschaften
            </div>
            <div class="card-body">
                <ul class="list-group">
                    <?php foreach ($user->getMemberships() as $membership): ?>
                        <li class="list-group-item">
                            <div class="flex-container">
                                <div class="flex-main">
                                    <?php $organisation = $membership->getOrganisation(); ?>
                                    <?php if ($organisation->getParentId()): ?>
                                        <?= $organisation->getParent()->getName() ?>
                                        <br>
                                        <small><?= $organisation->getName() ?></small>
                                    <?php else: ?>
                                        <?= $organisation->getName() ?>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-actions">
                                    <div class="me-2">
                                        <?= $membership->getStatus()->badge() ?>
                                    </div>
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="<?= base_url('organisation') ?>/<?= $organisation->getId() ?>">
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
                    <a class="btn btn-block btn-outline-primary" href="/organisations">
                        Alle Organisationen anzeigen
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col mb-4">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                Mitteilungen
            </div>
            <div class="card-body">
                <p class="text-center">Diese Funktion folgt demnächst!</p>
            </div>
            <div class="card-footer footer-plain">
            </div>
        </div>
    </div>
</div>