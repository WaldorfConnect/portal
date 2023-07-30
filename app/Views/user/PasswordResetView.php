<main>
    <div class="container login">

        <form method="post">
            <div class="card register">
                <div class="card-header header-plain">
                    <img class="mb-2 navbar-brand-logo" src="<?= base_url('/') ?>/assets/img/banner_small.png"
                         alt="WaldorfConnect Logo">
                    <h1 class="h2">Passwort zurücksetzen</h1>
                </div>
                <?php if (session('success')): ?>
                    <div class="card-body">
                        <div class="alert alert-success">
                            <b>Alles klar!</b> Sofern deine Angaben korrekt waren, haben wir dir auf deine
                            E-Mail-Adresse einen Link zum Zurücksetzen deines Passworts gesendet.
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card-body">
                        <?php if ($error = session('error')): ?>
                            <div class="alert alert-danger">
                                <?= $error ?>
                            </div>
                        <?php endif; ?>

                        <p>Du hast dein Passwort vergessen? Kein Problem! Gib hier deinen Benutzernamen und deine E-Mail
                            ein
                            und klicke auf Passwort zurücksetzen. Wir senden dir dann einen Link zum Zurücksetzen deines
                            Passworts per E-Mail!</p>

                        <div class="mb-3">
                            <label for="inputUsername" class="sr-only">Benutzername</label>
                            <input class="form-control" id="inputUsername" name="username"
                                   autocomplete="username" placeholder="Benutzername" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="inputEmail" class="sr-only">E-Mail</label>
                            <input type="email" class="form-control" id="inputEmail" name="email"
                                   autocomplete="email" placeholder="E-Mail" required>
                        </div>
                    </div>
                    <div class="card-footer footer-plain">
                        <button class="btn btn-primary btn-block" type="submit">Passwort zurücksetzen</button>
                        <a class="btn btn-link text-dark"
                           href="<?= base_url('/login') ?>">Zurück zur Anmeldung</a>
                    </div>
                <?php endif; ?>
            </div>
        </form>