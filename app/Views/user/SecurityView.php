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
        <label for="inputPassword" class="col-form-label col-md-4 col-lg-3">Passwort</label>
        <div class="col-md-8 col-lg-9">
            <input type="password" class="form-control" id="inputPassword" name="password"
                   autocomplete="new-password" aria-describedby="passwordHelp">
            <small id="passwordHelp" class="form-text text-muted">Dieses Feld muss nur gesetzt werden, wenn das Passwort
                geändert werden soll.</small>
        </div>
    </div>

    <div class="input-group row mb-3">
        <label for="inputConfirmedPassword" class="col-form-label col-md-4 col-lg-3">Passwort wiederholen</label>
        <div class="col-md-8 col-lg-9">
            <input type="password" class="form-control" id="inputConfirmedPassword" name="confirmedPassword"
                   autocomplete="new-password" aria-describedby="confirmedPasswordHelp">
            <small id="confirmedPasswordHelp" class="form-text text-muted">Dieses Feld muss nur gesetzt werden, wenn das
                Passwort geändert werden soll.</small>
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