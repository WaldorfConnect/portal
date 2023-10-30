<?php

use App\Entities\UserRole;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getSchools;

$currentUser = getCurrentUser();
?>
<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Startseite</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Administration</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/admin/regions') ?>">Regionsadministration</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                Region erstellen
            </li>
        </ol>
    </nav>
    <h1 class="header">Region erstellen</h1>
</div>

<div class="row">
    <?= form_open('admin/region/create') ?>

    <div class="form-group row mb-3">
        <label for="inputName" class="col-form-label col-md-4 col-lg-3">Name</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputName" name="name" autocomplete="name"
                   placeholder="Name" required>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputISO" class="col-form-label col-md-4 col-lg-3">ISO 3166-2 / ISO 3166-2</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputISO" name="iso" autocomplete="iso"
                   placeholder="ISO Code" required>
        </div>
    </div>

    <button class="btn btn-primary btn-block" type="submit">Erstellen</button>
    <?= form_close() ?>
</div>