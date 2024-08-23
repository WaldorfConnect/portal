<?php

use function App\Helpers\getCurrentUser;

?>

<?php if (!($user = getCurrentUser())->getTOTPSecret()): ?>
    <div class="col-md-12">
        <div class="alert alert-warning">
            <b>Sicherheitshinweis!</b> Du hast die Zwei-Faktor-Authentifizierung noch nicht aktiviert.<br>Wir empfehlen
            dir, dein Konto mit einem <a href="<?= base_url('user/security') ?>">zweiten Authentifizierungsweg</a> zu
            schützen!
        </div>
    </div>
<?php endif; ?>

<h1 class="header">Willkommen <?= $user->getName() ?></h1>

<div class="row justify-content-center">
    <div class="col-lg-5 col-sm-12">
        <?= form_open('search', 'method="get"') ?>
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Gruppen, Benutzer, ... suchen"
                   aria-label="Suchbegriff" aria-describedby="search" name="query" required>
            <button class="btn btn-outline-secondary" type="submit" id="search"><i class="fas fa-magnifying-glass"></i>
                Suchen
            </button>
        </div>
        <?= form_close() ?>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mt-5">
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
            </div>
            <div class="card-footer footer-plain">
                <div class="text-center">
                    <a class="btn btn-block btn-outline-primary" href="/groups">
                        <i class="fas fa-people-group"></i> Alle Gruppen anzeigen
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col mb-4">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                Benachrichtigungen
            </div>
            <div class="card-body">
                <?= view('notification/NotificationListComponent', ['limit' => 5]) ?>
            </div>
            <div class="card-footer footer-plain">
                <div class="text-center">
                    <a class="btn btn-block btn-outline-primary" href="/notifications">
                        <i class="fas fa-bell"></i> Alle Benachrichtigungen anzeigen
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 mt-5">
    <div class="col mb-4">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                Karte
            </div>
            <div class="card-body">
                <?= view('map/MapComponent', ['height' => 300]) ?>
            </div>
            <div class="card-footer footer-plain">
                <div class="text-center">
                    <a class="btn btn-block btn-outline-primary" href="/map">
                        <i class="fas fa-map-location"></i> Karte im Vollbild anzeigen
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!--<div class="col mb-4">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                Deine Favoriten
            </div>
            <div class="card-body">
            </div>
            <div class="card-footer footer-plain">
                <div class="text-center">
                    <a class="btn btn-block btn-outline-primary" href="/favorites">
                        <i class="fas fa-star"></i> Deine Favoriten verwalten
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col mb-4">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                Online in den letzten 24 Stunden
            </div>
            <div class="card-body">
            </div>
        </div>
    </div>-->
</div>