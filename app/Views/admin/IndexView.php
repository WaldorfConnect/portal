<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            Administration
        </li>
    </ol>
</nav>

<h1 class="header">Administration</h1>

<?php

use function App\Helpers\getCurrentUser;
use function App\Helpers\getOrganisations;
use function App\Helpers\getUsers;

$user = getCurrentUser() ?>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-2">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header text-center">Organisationen</div>
            <div class="card-body text-center">
                <span class="card-title display-2"><?= count(getOrganisations()) ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header text-center">Nutzer</div>
            <div class="card-body text-center">
                <span class="card-title display-2"><?= count(getUsers()) ?></span>
            </div>
        </div>
    </div>
</div>

<hr>

<div class="row">
    <div class="d-grid gap-2">
        <a href="<?= base_url('admin/users') ?>" class="btn btn-primary btn-lg">
            <i class="fas fa-users fa-2x mb-2"></i><br>
            Benutzer
        </a>
        <?php if ($user->isAdmin()): ?>
            <a href="<?= base_url('admin/organisations') ?>" class="btn btn-primary btn-lg">
                <i class="fas fa-people-group fa-2x mb-2"></i><br>
                Organisationen
            </a>
        <?php endif; ?>
        <?php if ($user->isAdmin()): ?>
            <a href="<?= base_url('admin/regions') ?>"
               class="btn btn-primary btn-lg <?= !$user->isAdmin() ? "disabled" : "" ?>">
                <i class="fas fa-landmark fa-2x mb-2"></i><br>
                Regionen
            </a>
        <?php endif; ?>
    </div>
</div>
