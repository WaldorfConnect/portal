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
    <?= form_open('admin/group/create') ?>

    <div class="form-group row mb-3">
        <label for="inputName" class="col-form-label col-md-4 col-lg-3">Name</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputName" name="name" autocomplete="name"
                   placeholder="Name" required>
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