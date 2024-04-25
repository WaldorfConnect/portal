<?php

use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use function App\Helpers\getCurrentUser;

$self = getCurrentUser();
?>

<div class="row">
    <h1 class="header">Sicherheit</h1>
</div>

<?= form_open('user/security/totp') ?>
<?= form_hidden('secret', $secret) ?>

<h3 class="subheader">Zwei-Faktor-Authentifizierung aktivieren</h3>

<?php
$qr = new QRCode(new QROptions(['outputType' => QROutputInterface::GDIMAGE_PNG]));
$image = $qr->render($secret)
?>

<img src="<?= $image ?>" alt="Zwei-Faktor-Schlüssel als QR-Code" height="200" width="200">

<p>Scanne diesen QR-Code mit deiner Authentifizierungsapp (z. B. Authy oder Google Authenticator)<br>
    ... oder gib, falls dein Endgerät keine QR-Codes unterstützt, folgenden Text ein:</p>

<div class="input-group">
    <label class="sr-only" for="secret">Zwei-Faktor-Schlüssel als Text</label>
    <input id="secret" class="form-control" disabled value="<?= $secret ?>">

    <button type="button" class="btn btn-primary" onclick="clipboard()">
        <i class="fas fa-clipboard"></i> In Zwischenablage kopieren
    </button>
</div>

<hr>

<div class="input-group row mb-3">
    <label for="inputKey" class="col-form-label col-md-4 col-lg-3">Authentifizierungscode</label>
    <div class="col-md-8 col-lg-9">
        <input type="text" class="form-control" id="inputKey" name="key" aria-describedby="keyHelp" inputmode="numeric"
               pattern="[0-9]*" autocomplete="one-time-code">
        <small id="keyHelp" class="form-text text-muted">Gib hier den Authentifizierungscode aus deiner
            Authentifizierungsapp ein.</small>
    </div>
</div>

<button class="btn btn-primary btn-block" type="submit">Aktivieren</button>
<?= form_close() ?>

<script>
    function clipboard() {
        const secret = document.getElementById('secret');

        secret.select();
        secret.setSelectionRange(0, 99999);

        navigator.clipboard.writeText(secret.value);
    }
</script>
