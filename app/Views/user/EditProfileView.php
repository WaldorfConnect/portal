<?php

use function App\Helpers\getGroupsByRegionId;
use function App\Helpers\getRegions;
use function App\Helpers\getSchoolsByRegionId;

?>
<h1 class="header">Profil bearbeiten</h1>

<form method="post">
    <h2>Persönliche Angaben</h2>

    <div class="form-group row mb-3">
        <label for="inputUsername" class="col-form-label col-md-4 col-lg-3">Benutzername</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputUsername" name="username"
                   value="<?= \App\Helpers\getCurrentUser()->getUsername() ?>" required disabled>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputName" class="col-form-label col-md-4 col-lg-3">Vor- und Nachname</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputName" name="name" autocomplete="name"
                   placeholder="Vor- und Nachname" value="<?= \App\Helpers\getCurrentUser()->getName() ?>" required>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputEmail" class="col-form-label col-md-4 col-lg-3">E-Mail</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputEmail" name="email" autocomplete="email"
                   placeholder="E-Mail" value="<?= \App\Helpers\getCurrentUser()->getEmail() ?>" required>
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

    <h2 class="mt-5">Organisationsangaben</h2>

    <div class="form-group row mb-3">
        <label for="inputSchool" class="col-form-label col-md-4 col-lg-3">Schule</label>
        <div class="col-md-8 col-lg-9">
            <select class="form-select" id="inputSchool" name="school" required>
                <?php foreach (getRegions() as $region): ?>
                    <optgroup label="<?= $region->getName() ?>">
                        <?php foreach (getSchoolsByRegionId($region->getId()) as $school): ?>
                            <option><?= $school->name ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputGroups" class="col-form-label col-md-4 col-lg-3">Organisationen/Gruppen</label>
        <div class="col-md-8 col-lg-9">
            <select class="form-select" id="inputGroups" name="groups[]" multiple required>
                <?php foreach (getRegions() as $region): ?>
                    <optgroup label="<?= $region->getName() ?>">
                        <?php foreach (getGroupsByRegionId($region->getId()) as $group): ?>
                            <option><?= $group->name ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <button class="btn btn-primary btn-block mb-3" type="submit">Speichern</button>
    <button class="btn btn-danger btn-block" type="submit">Profil löschen</button>
</form>