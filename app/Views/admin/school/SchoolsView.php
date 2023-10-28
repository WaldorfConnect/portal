<?php

use App\Entities\UserStatus;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getSchools;
use function App\Helpers\getUsers;

?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Administration</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            Schuladministration
        </li>
    </ol>
</nav>

<h1 class="header">Schuladministration</h1>

<p>
    Hier werden alle Schulen angezeigt, die sich in deinem administrativen Zuständigkeitsbereich befinden.
</p>

<table class="table table-striped table-bordered" data-locale="<?= service('request')->getLocale(); ?>"
       data-toggle="table" data-search="true" data-height="1000" data-pagination="true"
       data-show-columns="true" data-cookie="true" data-cookie-id-table="user"
       data-search-highlight="true" data-show-columns-toggle-all="true">
    <thead>
    <tr>
        <th data-field="id" data-sortable="true" scope="col">Name</th>
        <th data-field="description" data-sortable="true" scope="col">Region</th>
        <th data-field="action" scope="col">Aktion</th>
    </tr>
    </thead>
    <tbody>
    <?php $currentUser = getCurrentUser() ?>
    <?php foreach (getSchools() as $school): ?>
        <?php if (!$school->mayManage($currentUser)): continue; endif; ?>

        <tr>
            <td id="td-id-<?= $school->getId() ?>" class="td-class-<?= $school->getId() ?>"
                data-title="<?= $school->getName() ?>"><?= $school->getName() ?></td>
            <td><?= $school->getRegion()->getName() ?></td>
            <td>
                <div class="btn-group gap-2" role="group">
                    <a class="btn btn-primary btn-sm"
                       href="<?= base_url('school') . '/' . $school->getId() ?>">
                        <i class="fas fa-info-circle"></i>
                    </a>
                    <a class="btn btn-primary btn-sm"
                       href="<?= base_url('admin/school/edit') . '/' . $school->getId() ?>"><i class="fas fa-pen"></i>
                    </a>
                    <div>
                        <?= form_open('admin/school/delete') ?>
                        <?= form_hidden('id', $school->getId()) ?>
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