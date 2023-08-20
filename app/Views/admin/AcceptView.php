<?php

use App\Entities\UserStatus;
use function App\Helpers\getUsers;

?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Administration</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            Freizugebende Benutzer
        </li>
    </ol>
</nav>

<h1 class="header">Freizugebende Benutzer</h1>

<p>
    Hier angezeigt werden die Benutzer, die sich in deinem administrativen Zuständigkeitsbereich registriert haben
    und nun freigegeben werden müssen.<br>Bitte lasse bei der Freigabe von Benutzern absolute sorgfalt walten!
    Wir möchten vermeiden, dass versehentlich unbefugten Zugriff erteilt wird.
</p>

<table class="table table-striped table-bordered" data-locale="<?= service('request')->getLocale(); ?>"
       data-toggle="table" data-search="true" data-height="1000" data-pagination="true"
       data-show-columns="true" data-cookie="true" data-cookie-id-table="user"
       data-search-highlight="true" data-show-columns-toggle-all="true">
    <thead>
    <tr>
        <th data-field="username" data-sortable="true" scope="col">Benutzername</th>
        <th data-field="name" data-sortable="true" scope="col">Vor- und Nachname</th>
        <th data-field="school" data-sortable="true" scope="col">Schule</th>
        <th data-field="action" scope="col">Aktion</th>
    </tr>
    </thead>
    <tbody>
    <?php $currentUser = \App\Helpers\getCurrentUser() ?>
    <?php foreach (getUsers() as $user): ?>
        <?php if ($user->getStatus() != UserStatus::PENDING_ACCEPT || !$currentUser->mayAdminister($user)): continue; endif; ?>

        <tr>
            <td id="td-id-<?= $user->getId() ?>" class="td-class-<?= $user->getId() ?>"
                data-title="<?= $user->getUsername() ?>"><?= $user->getUsername() ?></td>
            <td><?= $user->getName() ?></td>
            <td><?= $user->getSchool()->getName() ?></td>
            <td>
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
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>