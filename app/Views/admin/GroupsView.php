<?php

use App\Entities\UserStatus;
use function App\Helpers\getUsers;

?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Administration</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            Gruppen
        </li>
    </ol>
</nav>

<h1 class="header">Gruppen</h1>

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
        <th data-field="name" data-sortable="true" scope="col">Name</th>
        <th data-field="description" data-sortable="true" scope="col">Beschreibung</th>
        <th data-field="region" data-sortable="true" scope="col">Region</th>
    </tr>
    </thead>
    <tbody>
    <?php $currentUser = \App\Helpers\getCurrentUser() ?>
    <?php foreach (getUsers() as $user): ?>
        <?php if (!$currentUser->mayAdminister($user)): continue; endif; ?>

        <tr>
            <td id="td-id-<?= $user->getId() ?>" class="td-class-<?= $user->getId() ?>"
                data-title="<?= $user->getUsername() ?>"><?= $user->getUsername() ?></td>
            <td><?= $user->getName() ?></td>
            <td><?= $user->getSchool()->getName() ?></td>
            <td><?= $user->getRole()->name ?></td>
            <td><?= $user->getStatus()->name ?></td>
            <td>
                <?php if ($user->getStatus() == UserStatus::PENDING_ACCEPT): ?>
                    <?= form_open('admin/user/accept') ?>
                    <?= form_hidden('id', $user->getId()) ?>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Akzeptieren
                    </button>
                    <?= form_close() ?>

                    <?= form_open('admin/user/deny') ?>
                    <?= form_hidden('id', $user->getId()) ?>
                    <button type="submit" class="btn btn-danger mt-1">
                        <i class="fas fa-x"></i> Ablehnen
                    </button>
                    <?= form_close() ?>
                <?php else: ?>
                    <?= form_open('admin/user/delete') ?>
                    <?= form_hidden('id', $user->getId()) ?>
                    <div class="btn-group d-flex gap-2" role="group">
                        <a class="btn btn-primary btn-sm"
                           href="<?= base_url('admin/user/edit') . '/' . $user->getId() ?>">
                            <i class="fas fa-pen"></i>
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                    <?= form_close() ?>
                <?php endif ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>