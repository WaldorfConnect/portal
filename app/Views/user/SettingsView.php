<?php

use function App\Helpers\getCurrentUser;

$self = getCurrentUser();
?>

    <div class="row">
        <h1 class="header">Einstellungen</h1>
    </div>

<?= form_open_multipart('user/settings') ?>
    <h3 class="subheader">E-Mail</h3>

    <div class="form-group row mb-3">
        <div class="col-10">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="inputEmailNotification" name="emailNotification"
                    <?= $self->wantsEmailNotification() ? 'checked' : '' ?>>
                <label class="form-check-label" for="inputEmailNotification">
                    Über Benachrichtigungen informieren
                </label>
            </div>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="inputNewsletter" name="newsletter"
                    <?= $self->wantsEmailNewsletter() ? 'checked' : '' ?>>
                <label class="form-check-label" for="inputNewsletter">
                    Newsletter von Organisationen erhalten
                </label>
            </div>
        </div>
    </div>

    <button class="btn btn-primary btn-block" type="submit">Speichern</button>
<?= form_close() ?>