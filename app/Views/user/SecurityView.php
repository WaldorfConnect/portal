<?php

use function App\Helpers\getCurrentUser;

$self = getCurrentUser();
?>

    <div class="row">
        <h1 class="header">Sicherheit</h1>
    </div>

<?= form_open_multipart('user/security') ?>
    <h3 class="subheader">Passwort</h3>

    <div class="input-group row mb-3">
        <label for="inputCurrentPassword" class="col-form-label col-md-4 col-lg-3">Aktuelles Passwort</label>
        <div class="col-md-8 col-lg-9">
            <input type="password" class="form-control" id="inputCurrentPassword" name="currentPassword"
                   autocomplete="current-password" aria-describedby="currentPasswordHelp">
            <small id="currentPasswordHelp" class="form-text text-muted">Dieses Feld muss nur gesetzt werden, wenn du
                dein Passwort ändern möchtest.</small>
        </div>
    </div>

    <div class="input-group row mb-3">
        <label for="inputNewPassword" class="col-form-label col-md-4 col-lg-3">Neues Passwort</label>
        <div class="col-md-8 col-lg-9">
            <input type="password" class="form-control" id="inputNewPassword" name="newPassword"
                   autocomplete="new-password" aria-describedby="newPasswordHelp">
            <small id="newPasswordHelp" class="form-text text-muted">Dieses Feld muss nur gesetzt werden, wenn du
                dein Passwort ändern möchtest.</small>
        </div>
    </div>

    <div class="input-group row mb-3">
        <label for="inputConfirmedNewPassword" class="col-form-label col-md-4 col-lg-3">Neues Passwort wiederholen</label>
        <div class="col-md-8 col-lg-9">
            <input type="password" class="form-control" id="inputConfirmedNewPassword" name="confirmedNewPassword"
                   autocomplete="new-password" aria-describedby="confirmedNewPasswordHelp">
            <small id="confirmedNewPasswordHelp" class="form-text text-muted">Dieses Feld muss nur gesetzt werden, wenn du
                dein Passwort ändern möchtest.</small>
        </div>
    </div>

    <h3 class="subheader">Zwei-Faktor-Authentifizierung (2FA)</h3>

    <div class="input-group row mb-3">
        <div class="col-10">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="inputTOTP" name="totp"
                    <?= $self->getTOTPSecret() ? 'checked' : '' ?>>
                <label class="form-check-label" for="inputTOTP">
                    Authentifizierungsapp (TOTP) aktivieren
                </label>
            </div>
        </div>
    </div>

    <button class="btn btn-primary btn-block" type="submit">Speichern</button>
<?= form_close() ?>