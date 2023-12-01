<?php

use App\Entities\UserStatus;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getUsers;

?>
<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Startseite</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Administration</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                Benutzeradministration
            </li>
        </ol>
    </nav>
    <h1 class="header">Benutzeradministration</h1>
    <p>
        Hier werden die Benutzer angezeigt, die sich in deinem administrativen Zuständigkeitsbereich befinden oder für
        diesen registriert haben und nun freigegeben werden müssen. <br>Bitte lasse hier äußerste sorgfalt walten!
        Wir möchten vermeiden, dass versehentlich unbefugten Zugriff erteilt wird, oder berechtigten Personen
        versehentlich
        der Zugriff verweigert wird.
    </p>

    <?php if ($success = session('error')): ?>
        <div class="col-md-12">
            <div class="alert alert-danger">
                <?= $success ?>
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
    <table class="table table-striped table-bordered" data-locale="<?= service('request')->getLocale(); ?>"
           data-toggle="table" data-search="true" data-height="1000" data-pagination="true"
           data-show-columns="true" data-cookie="true" data-cookie-id-table="user"
           data-search-highlight="true" data-show-columns-toggle-all="true">
        <thead>
        <tr>
            <th data-field="username" data-sortable="true" scope="col">Benutzername</th>
            <th data-field="name" data-sortable="true" scope="col">Vor- und Nachname</th>
            <th data-field="school" data-sortable="true" scope="col">Schule</th>
            <th data-field="role" data-sortable="true" scope="col">Globaler Admin</th>
            <th data-field="status" data-sortable="true" scope="col">Status</th>
            <th data-field="action" scope="col">Aktion</th>
        </tr>
        </thead>
        <tbody>
        <?php $currentUser = getCurrentUser() ?>
        <?php foreach (getUsers() as $user): ?>
            <?php if (!$currentUser->mayManage($user)): continue; endif; ?>
            <tr>
                <td id="td-id-<?= $user->getId() ?>" class="td-class-<?= $user->getId() ?>"
                    data-title="<?= $user->getUsername() ?>"><?= $user->getUsername() ?></td>
                <td><?= $user->getName() ?></td>
                <td><?= $user->isGlobalAdmin() ? 'Ja' : 'Nein' ?></td>
                <td><?= $user->getStatus()->badge() ?></td>
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
                        <div class="btn-group gap-2" role="group">
                            <a class="btn btn-primary btn-sm"
                               href="<?= base_url('admin/user/edit') . '/' . $user->getId() ?>"><i
                                        class="fas fa-pen"></i>
                            </a>
                            <div>
                                <?= form_open('admin/user/delete', ['onsubmit' => "return confirm('Möchtest du den Benutzer {$user->getName()} wirklich löschen?');"]) ?>
                                <?= form_hidden('id', $user->getId()) ?>
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?= form_close() ?>
                            </div>
                        </div>
                    <?php endif ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>