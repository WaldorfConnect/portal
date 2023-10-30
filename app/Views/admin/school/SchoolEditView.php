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
            <li class="breadcrumb-item"><a href="<?= base_url('/admin/schools') ?>">Schuladministration</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= $school->getName() ?>
            </li>
        </ol>
    </nav>
    <h1 class="header">Schule bearbeiten: <?= $school->getName() ?></h1>
</div>

<div class="row">
    <?= form_open('admin/school/edit') ?>
    <?= form_hidden('id', $school->getId()) ?>

    <div class="form-group row mb-3">
        <label for="inputName" class="col-form-label col-md-4 col-lg-3">Name</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputName" name="name" autocomplete="name"
                   placeholder="Name" value="<?= $school->getName() ?>" required>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputShortName" class="col-form-label col-md-4 col-lg-3">Kurzname</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputShortName" name="shortName" autocomplete="name"
                   placeholder="Kurzname" value="<?= $school->getShortName() ?>" required>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputAddress" class="col-form-label col-md-4 col-lg-3">Adresse</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputAddress" name="address" autocomplete="address"
                   placeholder="Adresse" value="<?= $school->getAddress() ?>" required>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputEmailBureau" class="col-form-label col-md-4 col-lg-3">E-Mail (Verwaltung)</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputEmailBureau" type="email" name="emailBureau" autocomplete="email"
                   placeholder="E-Mail (Verwaltung)" value="<?= $school->getEmailBureau() ?>" required>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputEmailSMV" class="col-form-label col-md-4 col-lg-3">E-Mail (SMV)</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputEmailSMV" type="email" name="emailSMV" autocomplete="email"
                   placeholder="E-Mail (SMV)" value="<?= $school->getEmailSMV() ?>">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputRegion" class="col-form-label col-md-4 col-lg-3">Region</label>
        <div class="col-md-8 col-lg-9">
            <select class="form-select" id="inputRegion" name="region" required>
                <?php foreach (getRegions() as $region): ?>
                    <?php if (!$region->mayManage($currentUser)): continue; endif; ?>
                    <option value="<?= $region->getId() ?>" <?= $region->getId() == $school->getRegionId() ? "selected" : "" ?>>
                        <?= $region->getName() ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <button class="btn btn-primary btn-block" type="submit">Bearbeiten</button>
    <?= form_close() ?>
</div>