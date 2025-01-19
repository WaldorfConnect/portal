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
            <li class="breadcrumb-item active" aria-current="page">
                Regionsadministration
            </li>
        </ol>
    </nav>
    <h1 class="header">Regionsadministration</h1>
    <p>
        Hier werden alle Regionen angezeigt, in denen sich Gruppen bzw. Schulen befinden können.
    </p>
</div>

<div class="row">
    <div class="col-md-5 w-auto ms-auto">
        <a class="btn btn-primary"
           href="<?= base_url('admin/region/create') ?>">
            <i class="fas fa-plus-square"></i> Region erstellen
        </a>
    </div>
</div>

<div class="row">
    <table class="table table-striped table-bordered" data-locale="<?= service('request')->getLocale(); ?>"
           data-toggle="table" data-search="true" data-height="1000" data-pagination="true"
           data-show-columns="true" data-cookie="true" data-cookie-id-table="regions"
           data-search-highlight="true" data-show-columns-toggle-all="true">
        <thead>
        <tr>
            <th data-field="id" data-sortable="true" scope="col">Name</th>
            <th data-field="action" scope="col">Aktion</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach (getRegions() as $region): ?>
            <tr>
                <td id="td-id-<?= $region->getId() ?>" class="td-class-<?= $region->getId() ?>"
                    data-title="<?= $region->getName() ?>"><?= $region->getName() ?></td>
                <td>
                    <div class="btn-group">
                        <a class="btn btn-primary btn-sm"
                           href="<?= base_url('admin/region/edit') . '/' . $region->getId() ?>"><i
                                    class="fas fa-pen"></i>
                        </a>
                    </div>

                    <?= form_open('admin/region/delete', ['class' => 'btn-group inline', 'onsubmit' => "return confirm('Möchtest du die Region {$region->getName()} wirklich löschen?');"]) ?>
                    <?= form_hidden('id', strval($region->getId())) ?>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i>
                    </button>
                    <?= form_close() ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>