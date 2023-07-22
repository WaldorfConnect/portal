<main>
    <div class="container login">

        <form method="post">
            <div class="card register">
                <div class="card-header header-plain">
                    <img class="mb-2 navbar-brand-logo" src="<?= base_url('/') ?>/assets/img/banner.svg"
                         alt="WaldorfConnect Logo">
                    <h1 class="h2">Passwort zurücksetzen</h1>
                </div>
                <div class="card-body">
                    <?php if ($error = session('error')): ?>
                        <div class="alert alert-danger">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <p>Du hast dein Passwort vergessen? Kein Problem! Gib hier deinen Benutzernamen ein und klicke auf
                        Passwort zurücksetzen. Wir senden dir dann einen Link zum Zurücksetzen deines Passworts per E-Mail!</p>

                    <div class="mb-3">
                        <label for="inputUsername" class="sr-only">Benutzername</label>
                        <input class="form-control" id="inputUsername" name="username"
                               autocomplete="username" placeholder="Benutzername" required autofocus>
                    </div>
                </div>
                <div class="card-footer footer-plain">
                    <button class="btn btn-primary btn-block" type="submit">Passwort zurücksetzen</button>
                    <a class="btn btn-link text-dark"
                       href="<?= base_url('/login') ?>">Zurück zur Anmeldung</a>
                </div>
            </div>
        </form>