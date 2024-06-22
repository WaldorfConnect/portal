<main>
    <div class="container login">

        <?= form_open('login') ?>
        <?php if (isset($return)): ?>
            <?= form_hidden('return', $return) ?>
        <?php endif; ?>

        <div class="card login">
            <div class="card-header header-plain">
                <img class="mb-2 navbar-brand-logo" src="<?= base_url('/') ?>assets/img/banner_small.png"
                     alt="WaldorfConnect Logo">
                <h1 class="h2">Anmelden</h1>
            </div>
            <div class="card-body">
                <?php if ($error = session('error')): ?>
                    <div class="alert alert-danger">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <div class="mb-3">
                    <label for="inputUsername" class="sr-only">Benutzername</label>
                    <input class="form-control" id="inputUsername" name="username"
                           value="<?= old('username') ?>" autocomplete="username"
                           placeholder="Benutzername" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="inputPassword" class="sr-only">Passwort</label>
                    <input type="password" class="form-control" id="inputPassword" name="password"
                           autocomplete="current-password"
                           placeholder="Passwort" required>
                </div>
            </div>
            <div class="card-footer footer-plain">
                <button class="btn btn-primary btn-block" type="submit">Anmelden</button>
                <a class="btn btn-link text-dark"
                   href="<?= base_url('/user/reset_password') ?>">Passwort vergessen?</a>
                <a class="btn btn-link text-dark"
                   href="https://wiki.waldorfconnect.de/books/faq/page/benutzername-vergessen">Benutzername vergessen?</a>
                <a class="btn btn-link text-dark"
                   href="<?= base_url('/register') ?>">Noch kein Konto? Jetzt registrieren!</a>
            </div>
        </div>
<?= form_close(); ?>