<?php

use App\Entities\UserRole;
use App\Entities\UserStatus;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getRegions;
use function App\Helpers\getSchools;
use function App\Helpers\getSchoolsByRegionId;

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
    <?php if ($error = session('error')): ?>
        <div class="alert alert-danger mb-3">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <?php if (session('success')): ?>
        <div class="alert alert-success mb-3">
            Profil gespeichert.
        </div>
    <?php endif; ?>
</div>

<div class="row">
    <?= form_open('admin/user/edit') ?>
    <?= form_hidden('id', $user->getId()) ?>

    <div class="form-group row mb-3">
        <label for="inputUsername" class="col-form-label col-md-4 col-lg-3">Benutzername</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputUsername" name="username"
                   value="<?= $user->getUsername() ?>" required disabled>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputName" class="col-form-label col-md-4 col-lg-3">Vor- und Nachname</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputName" name="name" autocomplete="name"
                   placeholder="Vor- und Nachname" value="<?= $user->getName() ?>" required>
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
        <label for="inputSchool" class="col-form-label col-md-4 col-lg-3">Schule</label>
        <div class="col-md-8 col-lg-9">
            <select class="form-select" id="inputSchool" name="school" required>
                <?php foreach (getRegions() as $region): ?>
                    <?php if (!$region->mayManage($currentUser)): continue; endif; ?>

                    <optgroup label="<?= $region->getName() ?>">
                        <?php foreach (getSchoolsByRegionId($region->getId()) as $school): ?>
                            <option <?= $user->getSchoolId() == $school->getId() ? 'selected' : '' ?>
                                    value="<?= $school->getId() ?>"><?= $school->name ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputRole" class="col-form-label col-md-4 col-lg-3">Rolle</label>
        <div class="col-md-8 col-lg-9">
            <select class="form-select" id="inputRole" name="role"
                    required <?= $currentUser->getRole() != UserRole::GLOBAL_ADMIN ? "disabled" : "" ?>>
                <?php foreach (UserRole::cases() as $role): ?>
                    <option <?= $role == $user->getRole() ? 'selected' : '' ?>
                            value="<?= $role->value ?>"><?= $role->displayName() ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputStatus" class="col-form-label col-md-4 col-lg-3">Status</label>
        <div class="col-md-8 col-lg-9">
            <select class="form-select" id="inputStatus" name="status"
                    required <?= $currentUser->getRole() != UserRole::GLOBAL_ADMIN ? "disabled" : "" ?>>
                <?php foreach (UserStatus::cases() as $status): ?>
                    <option <?= $status == $user->getStatus() ? 'selected' : '' ?>
                            value="<?= $status->value ?>"><?= $status->displayName() ?></option>
                <?php endforeach; ?>
            </select>
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