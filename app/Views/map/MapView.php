<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            Karte
        </li>
    </ol>
</nav>

<?php

use function App\Helpers\getCurrentUser;
use function App\Helpers\getGroups;
use function App\Helpers\getSearchEntries;
use function App\Helpers\getUsers;

$user = getCurrentUser();
?>

<div class="row justify-content-center">
    <div class="col-lg-12 col-sm-12">
        <?= view('map/MapComponent', ['height' => 700]) ?>
    </div>
</div>