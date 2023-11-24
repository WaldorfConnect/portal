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
            <li class="breadcrumb-item active" aria-current="page">
                Schuladministration
            </li>
        </ol>
    </nav>
    <h1 class="header">Schuladministration</h1>
    <p>
        Hier werden alle Schulen angezeigt, die sich in deinem administrativen Zuständigkeitsbereich befinden.
    </p>

    <?php $errors = session('error'); if ($errors): ?>
        <div class="col-md-12">
            <div class="alert alert-danger">
                <?php if (is_array($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <?= esc($error) ?><br>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?= $errors ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($success = session('success')): ?>
        <div class="col-md-12">
            <div class="alert alert-success">
                <?= $success ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-md-5 w-auto ms-auto">
        <?php if ($currentUser->getRole() == UserRole::REGION_ADMIN || $currentUser->getRole() == UserRole::GLOBAL_ADMIN): ?>
            <a class="btn btn-primary"
               href="<?= base_url('admin/school/create') ?>">
                <i class="fas fa-plus-square"></i> Schule erstellen
            </a>
        <?php endif; ?>
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
            <th data-field="description" data-sortable="true" scope="col">Region</th>
            <th data-field="action" scope="col">Aktion</th>
        </tr>
        </thead>
        <tbody>
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
                           href="<?= base_url('admin/school/edit') . '/' . $school->getId() ?>"><i
                                    class="fas fa-pen"></i>
                        </a>
                        <div>
                            <?= form_open('admin/school/delete', ['onsubmit' => "return confirm('Möchtest du die Schule {$school->getName()} wirklich löschen?');"]) ?>
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
</div>