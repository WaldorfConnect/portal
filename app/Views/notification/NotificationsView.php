<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            Benachrichtigungen
        </li>
    </ol>
</nav>

<h1 class="header">Benachrichtigungen</h1>

<p class="text-center mb-5">Du möchtest nicht mehr per E-Mail über ungelesene Benachrichtigungen informiert werden?<br>
    <a href="<?= base_url('user/settings') ?>">Dann ändere hier deine Benutzereinstellungen.</a></p>

<hr>

<?php

use function App\Helpers\getCurrentUser;

$user = getCurrentUser() ?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-sm-12">
        <?= view('notification/NotificationListComponent', ['limit' => 0]) ?>
    </div>
</div>
