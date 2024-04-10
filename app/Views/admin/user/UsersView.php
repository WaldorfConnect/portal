<?php

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
</div>

<div class="row">
    <table class="table table-striped table-bordered" data-locale="<?= service('request')->getLocale(); ?>"
           data-toggle="table" data-search="true" data-height="1000" data-pagination="true"
           data-show-columns="true" data-cookie="true" data-cookie-id-table="user"
           data-search-highlight="true" data-show-columns-toggle-all="true">
        <thead>
        <tr>
            <th data-field="username" data-sortable="true" scope="col">Benutzername</th>
            <th data-field="firstName" data-sortable="true" scope="col">Vorname(n)</th>
            <th data-field="lastName" data-sortable="true" scope="col">Nachname</th>
            <th data-field="admin" data-sortable="true" scope="col">Admin</th>
            <th data-field="emailConfirmed" data-sortable="true" scope="col">E-Mail bestätigt</th>
            <th data-field="passwordReset" data-sortable="true" scope="col">Passwort zurückgesetzt</th>
            <th data-field="action" scope="col">Aktion</th>
        </tr>
        </thead>
        <tbody>
        <?php $currentUser = getCurrentUser() ?>
        <?php foreach (getUsers() as $user): ?>
            <tr>
                <td id="td-id-<?= $user->getId() ?>" class="td-class-<?= $user->getId() ?>"
                    data-title="<?= $user->getUsername() ?>"><?= $user->getUsername() ?></td>
                <td><?= $user->getFirstName() ?></td>
                <td><?= $user->getLastName() ?></td>
                <td><?= $user->isAdmin() ? 'Ja' : 'Nein' ?></td>
                <td><?= $user->isEmailConfirmed() ? 'Ja' : 'Nein' ?></td>
                <td><?= $user->isPasswordReset() ? 'Ja' : 'Nein' ?></td>
                <td>
                    <div class="btn-group">
                        <a class="btn btn-primary btn-sm"
                           href="<?= base_url('admin/user/edit') . '/' . $user->getId() ?>"><i
                                    class="fas fa-pen"></i>
                        </a>
                    </div>

                    <?= form_open('admin/user/delete', ['class' => 'btn-group inline', 'onsubmit' => "return confirm('Möchtest du den Benutzer {$user->getName()} wirklich löschen?');"]) ?>
                    <?= form_hidden('id', $user->getId()) ?>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i>
                    </button>
                    <?= form_close() ?>

                    <?php if ($user->isAccepted()): ?>
                        <?php if ($user->isActive()): ?>
                            <?= form_open('admin/user/deactivate', ['class' => 'btn-group inline']) ?>
                            <?= form_hidden('id', $user->getId()) ?>
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-lock"></i>
                            </button>
                            <?= form_close() ?>
                        <?php else: ?>
                            <?= form_open('admin/user/activate', ['class' => 'btn-group inline']) ?>
                            <?= form_hidden('id', $user->getId()) ?>
                            <button type="submit" class="btn btn-success btn-sm">
                                <i class="fas fa-lock-open"></i>
                            </button>
                            <?= form_close() ?>
                        <?php endif; ?>
                    <?php else: ?>
                        <?= form_open('admin/user/accept', ['class' => 'btn-group inline']) ?>
                        <?= form_hidden('id', $user->getId()) ?>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-thumbs-up"></i>
                        </button>
                        <?= form_close() ?>

                        <?= form_open('admin/user/deny', ['class' => 'btn-group inline']) ?>
                        <?= form_hidden('id', $user->getId()) ?>
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-thumbs-down"></i>
                        </button>
                        <?= form_close() ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>