<?php

use function App\Helpers\getCurrentUser;
use function App\Helpers\getImageUrlById;

$self = getCurrentUser();
?>
    <div class="row">
        <h1 class="header">Einstellungen</h1>
    </div>
<?= form_open_multipart('user/settings') ?>

<?php if ($error = session('error')): ?>
    <div class="alert alert-danger mb-3">
        <?= $error ?>
    </div>
<?php endif; ?>

<?php if ($success = session('success')): ?>
    <div class="alert alert-success mb-3">
        <?= $success ?>
    </div>
<?php endif; ?>
    <div class="form-group row mb-3">
        <div class="col-10">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="inputEmailNotification" name="emailNotification"
                    <?= $self->wantsEmailNotification() ? 'checked' : '' ?>>
                <label class="form-check-label" for="inputEmailNotification">
                    Über Benachrichtigungen per E-Mail informieren
                </label>
            </div>
        </div>
    </div>

    <button class="btn btn-primary btn-block" type="submit">Speichern</button>
<?= form_close() ?>