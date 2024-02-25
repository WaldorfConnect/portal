<?php

use function App\Helpers\getCurrentUser;

$currentUser = getCurrentUser();
?>
<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Startseite</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Administration</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/admin/regions') ?>">Regionsadministration</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= $region->getName() ?>
            </li>
        </ol>
    </nav>
    <h1 class="header">Region bearbeiten: <?= $region->getName() ?></h1>
</div>

<div class="row">
    <?= form_open('admin/region/edit') ?>
    <?= form_hidden('id', $region->getId()) ?>

    <div class="form-group row mb-3">
        <label for="inputName" class="col-form-label col-md-4 col-lg-3">Name</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputName" name="name" autocomplete="name"
                   placeholder="Name" value="<?= $region->getName() ?>" required>
        </div>
    </div>

    <button class="btn btn-primary btn-block" type="submit">Bearbeiten</button>
    <?= form_close() ?>
</div>