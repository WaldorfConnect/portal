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
            <li class="breadcrumb-item"><a href="<?= base_url('/admin/organisations') ?>">Organisationsadministration</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= $organisation->getName() ?>
            </li>
        </ol>
    </nav>
    <h1 class="header">Organisation bearbeiten: <?= $organisation->getName() ?></h1>
</div>

<div class="row">
    <?= form_open_multipart('admin/organisation/edit') ?>
    <?= form_hidden('id', $organisation->getId()) ?>

    <div class="form-group row mb-3">
        <label for="inputName" class="col-form-label col-md-4 col-lg-3">Name</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputName" name="name" autocomplete="name"
                   placeholder="Name" value="<?= $organisation->getName() ?>" required>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputWebsite" class="col-form-label col-md-4 col-lg-3">Website</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputWebsite" name="websiteUrl" type="url"
                   placeholder="https://example.com" value="<?= $organisation->getWebsiteUrl() ?>">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputLogo" class="col-form-label col-md-4 col-lg-3">Logo (ca. 512x128 | max. 1MB)</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputLogo" name="logo" type="file"
                   accept="image/png, image/jpg, image/jpeg, image/gif, image/webp, image/svg+xml">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputImage" class="col-form-label col-md-4 col-lg-3">Bild (ca. 1920x1080 | max. 2MB)</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputImage" name="image" type="file"
                   accept="image/png, image/jpg, image/jpeg, image/gif, image/webp">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputRegion" class="col-form-label col-md-4 col-lg-3">Region</label>
        <div class="col-md-8 col-lg-9">
            <select class="form-select" id="inputRegion" name="region" required>
                <?php foreach (getRegions() as $region): ?>
                    <?php if (!$region->isManageableBy($currentUser)): continue; endif; ?>
                    <option value="<?= $region->getId() ?>" <?= $region->getId() == $organisation->getRegionId() ? "selected" : "" ?>>
                        <?= $region->getName() ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <button id="submitButton" class="btn btn-primary btn-block" type="submit">Bearbeiten</button>
    <?= form_close() ?>
</div>

<script src="<?= base_url('/') ?>/assets/js/enforceFileUploadSizeLimits.js"></script>