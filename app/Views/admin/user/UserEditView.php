<?php

use function App\Helpers\getCurrentUser;

$currentUser = getCurrentUser();
?>
<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Startseite</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Administration</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/admin/users') ?>">Benutzeradministration</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= $user->getName() ?>
            </li>
        </ol>
    </nav>
    <h1 class="header">Benutzer bearbeiten: <?= $user->getName() ?></h1>
</div>

<div class="row">
    <?= form_open('admin/user/edit') ?>
    <?= form_hidden('id', strval($user->getId())) ?>

    <div class="form-group row mb-3">
        <label for="inputUsername" class="col-form-label col-md-4 col-lg-3">Benutzername</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputUsername" name="username"
                   value="<?= $user->getUsername() ?>" required disabled>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputFirstName" class="col-form-label col-md-4 col-lg-3">Vorname(n)</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputFirstName" name="firstName" autocomplete="name"
                   placeholder="Vorname" value="<?= $user->getFirstName() ?>" required>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputLastName" class="col-form-label col-md-4 col-lg-3">Nachname</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputLastName" name="lastName" autocomplete="name"
                   placeholder="Nachname" value="<?= $user->getLastName() ?>" required>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputEmail" class="col-form-label col-md-4 col-lg-3">E-Mail</label>
        <div class="col-md-8 col-lg-9">
            <input type="email" class="form-control" id="inputEmail" name="email" autocomplete="email"
                   placeholder="E-Mail" value="<?= $user->getEmail() ?>" aria-describedby="emailHelp" required>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputPassword" class="col-form-label col-md-4 col-lg-3">Passwort</label>
        <div class="col-md-8 col-lg-9">
            <input type="password" class="form-control" id="inputPassword" name="password"
                   autocomplete="new-password" aria-describedby="passwordHelp">
            <small id="passwordHelp" class="form-text text-muted">Dieses Feld muss nur gesetzt werden, wenn das Passwort
                geändert werden soll.</small>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputConfirmedPassword" class="col-form-label col-md-4 col-lg-3">Passwort wiederholen</label>
        <div class="col-md-8 col-lg-9">
            <input type="password" class="form-control" id="inputConfirmedPassword" name="confirmedPassword"
                   autocomplete="new-password" aria-describedby="confirmedPasswordHelp">
            <small id="confirmedPasswordHelp" class="form-text text-muted">Dieses Feld muss nur gesetzt werden, wenn das
                Passwort geändert werden soll.</small>
        </div>
    </div>

    <button class="btn btn-primary btn-block" type="submit">Bearbeiten</button>
    <?= form_close() ?>
</div>