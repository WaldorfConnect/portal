<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            Suche
        </li>
    </ol>
</nav>

<h1 class="header">Suche: <?= $query ?></h1>

<?php

use function App\Helpers\getCurrentUser;
use function App\Helpers\getOrganisations;
use function App\Helpers\getSearchEntries;
use function App\Helpers\getUsers;

$user = getCurrentUser() ?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-sm-12">
        <ul class="list-group">
            <?php foreach (getSearchEntries($query) as $entry): ?>
                <li class="list-group-item">
                    <div class="flex-container">
                        <div class="flex-main">
                            <?= $entry->getBadge() ?> <?= $entry->getName() ?>
                        </div>
                        <div class="flex-actions">
                            <?php foreach ($entry->getUrls() as $key => $value): ?>
                                <a class="btn btn-sm btn-outline-primary"
                                   href="<?= $value ?>" target="_blank">
                                    <?= $key ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>