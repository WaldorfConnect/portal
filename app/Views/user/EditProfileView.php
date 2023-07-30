<?php

use App\Entities\UserStatus;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getRegions;
use function App\Helpers\getSchoolsByRegionId;

?>
<h1 class="header">Profil bearbeiten</h1>

<form method="post">
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

    <div class="form-group row mb-3">
        <label for="inputUsername" class="col-form-label col-md-4 col-lg-3">Benutzername</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputUsername" name="username"
                   value="<?= ($user = getCurrentUser())->getUsername() ?>" required disabled>
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
            <?php if ($user->getStatus() == UserStatus::PENDING_EMAIL): ?>
                <span id="emailHelp" class="badge bg-warning">Warte auf Bestätigung</span>
            <?php else: ?>
                <span id="emailHelp" class="badge bg-success">E-Mail bestätigt</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputSchool" class="col-form-label col-md-4 col-lg-3">Schule</label>
        <div class="col-md-8 col-lg-9">
            <select class="form-select" id="inputSchool" name="school" required>
                <?php foreach (getRegions() as $region): ?>
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

    <button class="btn btn-primary btn-block" type="submit">Speichern</button>
</form>