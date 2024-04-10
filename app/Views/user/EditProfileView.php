<?php

use function App\Helpers\getCurrentUser;
use function App\Helpers\getImageUrlById;

$self = getCurrentUser();
?>
    <div class="row">
        <h1 class="header">Profil bearbeiten</h1>
    </div>

<?= form_open_multipart('user/profile') ?>
    <div class="form-group row align-items-end mb-3">
        <label for="inputImage" class="col-form-label col-md-4 col-lg-3">Profilbild</label>
        <div class="col-md-2">
            <img class="img-thumbnail"
                 width="200"
                 height="200"
                 src="<?= getImageUrlById($self->getImageId(), 'assets/img/user_400x400.webp') ?>"
                 alt="Profilbild">
        </div>
        <div class="col-md-4">
            <input class="form-control" id="inputImage" name="image"
                   type="file">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputUsername" class="col-form-label col-md-4 col-lg-3">Benutzername</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputUsername" name="username"
                   value="<?= $self->getUsername() ?>" required disabled>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputFirstName" class="col-form-label col-md-4 col-lg-3">Vorname(n)</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputFirstName" name="firstName" autocomplete="name"
                   placeholder="Vorname(n)" value="<?= $self->getFirstName() ?>" required>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="lastName" class="col-form-label col-md-4 col-lg-3">Nachname</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputLastName" name="lastName" autocomplete="name"
                   placeholder="Nachname" value="<?= $self->getLastName() ?>" required>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputEmail" class="col-form-label col-md-4 col-lg-3">E-Mail</label>
        <div class="col-md-8 col-lg-9">
            <input type="email" class="form-control" id="inputEmail" name="email" autocomplete="email"
                   placeholder="E-Mail" value="<?= $self->getEmail() ?>" aria-describedby="emailHelp" required>
            <?php if (!$self->isEmailConfirmed()): ?>
                <span id="emailHelp" class="badge bg-warning">Warte auf Bestätigung</span>
                <?php if (session('resendSuccess')): ?>
                    <span id="resentBadge" class="badge bg-success">Erneut versandt</span>
                <?php endif; ?>
                <button type="button" class="btn btn-link btn-sm text-dark"
                        onclick="document.getElementById('resendEmailButton').click();">
                    Nach ein paar Minuten noch keine E-Mail erhalten? Erneut anfordern!
                </button>
            <?php else: ?>
                <span id="emailHelp" class="badge bg-success">E-Mail bestätigt</span>
            <?php endif; ?>
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
<?= form_close() ?>

<?= form_open('user/profile/resend', ['class' => 'd-none', 'id' => 'resendEmailForm'], ['userId' => $self->getId()]) ?>
    <button id="resendEmailButton" type="submit">E-Mail erneut versenden</button>
<?= form_close() ?>