<?php

use App\Entities\UserRole;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getRegions;
use function App\Helpers\getSchools;

$currentUser = getCurrentUser();
?>
<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Startseite</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Administration</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/admin/groups') ?>">Gruppenadministration</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= $group->getName() ?>
            </li>
        </ol>
    </nav>

    <h1 class="header">Gruppe bearbeiten: <?= $group->getName() ?></h1>
</div>

