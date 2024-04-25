<main>
    <div class="container login">

        <?= form_open('login') ?>
        <?= form_hidden('username', $username) ?>
        <?= form_hidden('password', $password) ?>

        <?php if (isset($return)): ?>
            <?= form_hidden('return', $return) ?>
        <?php endif; ?>

        <div class="card login">
            <div class="card-header header-plain">
                <img class="mb-2 navbar-brand-logo" src="<?= base_url('/') ?>assets/img/banner_small.png"
                     alt="WaldorfConnect Logo">
                <h1 class="h2">2FA-Code eingeben</h1>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        Authentifizierungscode ungültig
                    </div>
                <?php endif; ?>

                <p>Du hast die Zwei-Faktor-Authentifizierung aktiviert. Bitte gib deinen Authentifizierungscode aus
                    deiner Authentifizierungsapp hier ein!</p>

                <div class="mb-3">
                    <label for="inputTOTP" class="sr-only">Benutzername</label>
                    <input type="text" class="form-control" id="inputTOTP" name="totp"
                           placeholder="Authentifizierungscode" inputmode="numeric" pattern="[0-9]*"
                           autocomplete="one-time-code" required autofocus>
                </div>
            </div>
            <div class="card-footer footer-plain">
                <button class="btn btn-primary btn-block" type="submit">Bestätigen</button>
            </div>
        </div>
<?= form_close(); ?>