<?php

use function App\Helpers\getCurrentUser;
use function App\Helpers\getOrganisations;

?>
<div class="row">


    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Startseite</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Administration</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                Organisationsadministration
            </li>
        </ol>
    </nav>
    <h1 class="header">Organisationsadministration</h1>
    <p>
        Hier werden alle Organisationen angezeigt, die sich in deinem administrativen Zuständigkeitsbereich befinden.
    </p>
</div>

<div class="row">
    <div class="col-md-5 w-auto ms-auto">
        <a class="btn btn-primary"
           href="<?= base_url('admin/organisation/create') ?>">
            <i class="fas fa-plus-square"></i> Organisation erstellen
        </a>
    </div>
</div>

<div class="row">
    <table class="table table-striped table-bordered" data-locale="<?= service('request')->getLocale(); ?>"
           data-toggle="table" data-search="true" data-height="1000" data-pagination="true"
           data-show-columns="true" data-cookie="true" data-cookie-id-table="user"
           data-search-highlight="true" data-show-columns-toggle-all="true">
        <thead>
        <tr>
            <th data-field="id" data-sortable="true" scope="col">Name</th>
            <th data-field="name" data-sortable="true" scope="col">Region</th>
            <th data-field="action" scope="col">Aktion</th>
        </tr>
        </thead>
        <tbody>
        <?php $currentUser = getCurrentUser() ?>
        <?php foreach (getOrganisations() as $organisation): ?>
            <?php if (!$organisation->isManageableBy($currentUser)): continue; endif; ?>

            <tr>
                <td id="td-id-<?= $organisation->getId() ?>" class="td-class-<?= $organisation->getId() ?>"
                    data-title="<?= $organisation->getDisplayName() ?>"><?= $organisation->getDisplayName() ?></td>
                <td><?= $organisation->getRegion()->getName() ?></td>
                <td>
                    <div class="btn-group">
                        <a class="btn btn-primary btn-sm"
                           href="<?= base_url('organisation') . '/' . $organisation->getId() ?>">
                            <i class="fas fa-info-circle"></i>
                        </a>
                        <a class="btn btn-primary btn-sm"
                           href="<?= base_url("organisation/{$organisation->getId()}/edit") ?>">
                            <i class="fas fa-pen"></i>
                        </a>
                    </div>

                    <?= form_open('admin/organisation/delete', ['class' => 'btn-group inline', 'onsubmit' => "return confirm('Möchtest du die Organisation {$organisation->getName()} wirklich löschen?');"]) ?>
                    <?= form_hidden('id', $organisation->getId()) ?>
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