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

$user = getCurrentUser();
$entries = getSearchEntries($query);
?>

<div class="row justify-content-center">
    <div class="col-lg-10 col-sm-12">
        <div class="text-center mb-5">
            <p><b>Du suchst eine Datei oder einen Ordner?</b><br>Verwende hierfür bitte die Suchfunktion in <a
                        href="https://cloud.waldorfconnect.de/">unserer Cloud</a>.</p>
        </div>

        <hr>

        <?php if (count($entries) > 0): ?>
            <ul class="list-group">
                <?php foreach ($entries as $entry): ?>
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
        <?php else: ?>
            <div class="text-center">
                <h3>Keine Suchergebnisse gefunden!</h3>
            </div>
        <?php endif; ?>
    </div>
</div>
