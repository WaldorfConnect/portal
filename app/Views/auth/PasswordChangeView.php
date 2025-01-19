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
                <h1 class="h2">Passwort ändern</h1>
            </div>

            <div class="card-body">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <p>Dein Passwort muss geändert werden!</p>

                <div class="mb-3">
                    <label for="inputNewPassword" class="sr-only">Neues Passwort</label>
                    <input type="password" class="form-control" id="inputNewPassword" name="newPassword"
                           autocomplete="new-password" required>
                </div>

                <div class="mb-3">
                    <label for="inputNewPasswordConfirmation" class="sr-only">Neues Passwort bestätigen</label>
                    <input type="password" class="form-control" id="inputNewPasswordConfirmation" name="newPasswordConfirmation"
                           autocomplete="new-password" required>
                </div>
            </div>
            <div class="card-footer footer-plain">
                <button class="btn btn-primary btn-block" type="submit">Bestätigen</button>
            </div>
        </div>
<?= form_close(); ?>