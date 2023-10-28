<?php

use function App\Helpers\getCurrentUser;
use function App\Helpers\getGroups;

?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Administration</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            Gruppenadministration
        </li>
    </ol>
</nav>

<h1 class="header">Gruppenadministration</h1>

<p>
    Hier werden alle Gruppen angezeigt, die sich in deinem administrativen Zuständigkeitsbereich befinden.
</p>

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
    <?php foreach (getGroups() as $group): ?>
        <tr>
            <td id="td-id-<?= $group->getId() ?>" class="td-class-<?= $group->getId() ?>"
                data-title="<?= $group->getName() ?>"><?= $group->getName() ?></td>
            <td><?= $group->getRegion()->getName() ?></td>
            <td>
                <div class="btn-group gap-2" role="group">
                    <a class="btn btn-primary btn-sm"
                       href="<?= base_url('group') . '/' . $group->getId() ?>">
                        <i class="fas fa-info-circle"></i>
                    </a>
                    <a class="btn btn-primary btn-sm"
                       href="<?= base_url('admin/group/edit') . '/' . $group->getId() ?>">
                        <i class="fas fa-pen"></i>
                    </a>
                    <div>
                        <?= form_open('admin/group/delete') ?>
                        <?= form_hidden('id', $group->getId()) ?>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?= form_close() ?>
                    </div>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>