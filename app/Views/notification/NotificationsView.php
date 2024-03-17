<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            Benachrichtigungen
        </li>
    </ol>
</nav>

<h1 class="header">Benachrichtigungen</h1>

<?php

use function App\Helpers\getCurrentUser;
use function App\Helpers\getOrganisations;
use function App\Helpers\getSearchEntries;
use function App\Helpers\getUsers;

$user = getCurrentUser() ?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-sm-12">
        <?= view('notification/NotificationListComponent', ['limit' => 0]) ?>
    </div>
</div>
