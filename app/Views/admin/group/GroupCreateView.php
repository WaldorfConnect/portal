<?php

use function App\Helpers\getCurrentUser;
use function App\Helpers\getRegions;

$currentUser = getCurrentUser();
?>
<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Startseite</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Administration</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/admin/groups') ?>">Gruppenadministration</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                Gruppe erstellen
            </li>
        </ol>
    </nav>
    <h1 class="header">Gruppe erstellen</h1>
</div>

<div class="row">
    <?= form_open_multipart('admin/group/create') ?>

    <div class="form-group row mb-3">
        <label for="inputName" class="col-form-label col-md-4 col-lg-3">Name</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputName" name="name" autocomplete="name"
                   placeholder="Name" required>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputWebsite" class="col-form-label col-md-4 col-lg-3">Website</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputWebsite" name="websiteUrl" type="url"
                   placeholder="https://example.com">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputLogo" class="col-form-label col-md-4 col-lg-3">Logo (z.B. 512x128)</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputLogo" name="logo" type="file" accept="image/png">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputImage" class="col-form-label col-md-4 col-lg-3">Bild (z.B. 1920x1080)</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputImage" name="image" type="file" accept="image/png">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputRegion" class="col-form-label col-md-4 col-lg-3">Region</label>
        <div class="col-md-8 col-lg-9">
            <select class="form-select" id="inputRegion" name="region" required>
                <?php foreach (getRegions() as $region): ?>
                    <?php if (!$region->mayManage($currentUser)): continue; endif; ?>
                    <option value="<?= $region->getId() ?>"><?= $region->getName() ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <button class="btn btn-primary btn-block" type="submit">Erstellen</button>
    <?= form_close() ?>
</div>