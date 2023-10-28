<main>
    <div class="container login">

        <?= form_open('user/reset_password') ?>
        <div class="card register">
            <div class="card-header header-plain">
                <img class="mb-2 navbar-brand-logo" src="<?= base_url('/') ?>/assets/img/banner_small.png"
                     alt="WaldorfConnect Logo">
                <h1 class="h2">Passwort zurücksetzen</h1>
            </div>
            <?php if (isset($success)): ?>
                <div class="card-body">
                    <div class="alert alert-success">
                        <b>Geschafft!</b> Dein Passwort wurde zurückgesetzt. Du kannst dich nun mit deinem neuen
                        Passwort anmelden!
                    </div>
                    <div class="card-footer footer-plain">
                        <a class="btn btn-link text-dark" href="<?= base_url('/login') ?>">Zur Anmeldung</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card-body">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-danger">
                            <?= $success ?>
                        </div>
                    <?php endif; ?>

                    <p>Gib hier nun ein neues Passwort ein!</p>

                    <div class="mb-3">
                        <label for="inputPassword" class="sr-only">Passwort</label>
                        <input type="password" class="form-control" id="inputPassword" name="password"
                               autocomplete="new-password" placeholder="Passwort" required>
                    </div>

                    <div class="mb-3">
                        <label for="inputConfirmedPassword" class="sr-only">Passwort wiederholen</label>
                        <input type="password" class="form-control" id="inputConfirmedPassword"
                               name="confirmedPassword"
                               autocomplete="new-password" placeholder="Passwort wiederholen" required>
                    </div>

                    <input name="token" value="<?= $user->getToken() ?>" type="hidden">
                </div>
                <div class="card-footer footer-plain">
                    <button class="btn btn-primary btn-block" type="submit">Passwort zurücksetzen</button>
                </div>
            <?php endif; ?>
        </div>
<?= form_close(); ?>