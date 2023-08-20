<?php

use function App\Helpers\getUsers;

?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Administration</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            Benutzer
        </li>
    </ol>
</nav>

<h1 class="header">Benutzer</h1>

<table class="table table-striped table-bordered" data-locale="<?= service('request')->getLocale(); ?>"
       data-toggle="table" data-search="true" data-height="1000" data-pagination="true"
       data-show-columns="true" data-cookie="true" data-cookie-id-table="user"
       data-search-highlight="true" data-show-columns-toggle-all="true">
    <thead>
    <tr>
        <th data-field="username" data-sortable="true" scope="col">Benutzername</th>
        <th data-field="name" data-sortable="true" scope="col">Vor- und Nachname</th>
        <th data-field="school" data-sortable="true" scope="col">Schule</th>
        <th data-field="role" data-sortable="true" scope="col">Rolle</th>
        <th data-field="status" data-sortable="true" scope="col">Status</th>
        <th data-field="action" scope="col">Aktion</th>
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
                <?= form_open('admin/user/delete') ?>
                <?= form_hidden('id', $user->getId()) ?>
                <div class="btn-group d-flex gap-2" role="group">
                    <a class="btn btn-primary btn-sm" href="<?= base_url('admin/user/edit') . '/' . $user->getId() ?>">
                        <i class="fas fa-pen"></i>
                    </a>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                <?= form_close() ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>