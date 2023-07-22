<main>
    <div class="container login">

        <form method="post">
            <div class="card register">
                <div class="card-header header-plain">
                    <img class="mb-2 navbar-brand-logo" src="<?= base_url('/') ?>/assets/img/banner.svg"
                         alt="WaldorfConnect Logo">
                    <h1 class="h2">Registrieren</h1>
                </div>
                <div class="card-body">
                    <?php if ($error = session('error')): ?>
                        <div class="alert alert-danger">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="inputFirstName" class="sr-only">Vorname(n)</label>
                        <input class="form-control" id="inputFirstName" name="firstName" autocomplete="given-name"
                               placeholder="Vorname(n)" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="inputLastName" class="sr-only">Nachname</label>
                        <input class="form-control" id="inputLastName" name="lastName" autocomplete="family-name"
                               placeholder="Nachname" required>
                    </div>

                    <div class="mb-3">
                        <label for="inputEmail" class="sr-only">E-Mail</label>
                        <input class="form-control" id="inputEmail" name="email" autocomplete="email"
                               placeholder="E-Mail" required>
                    </div>

                    <div class="mb-3">
                        <label for="inputPassword" class="sr-only">Passwort</label>
                        <input type="password" class="form-control" id="inputPassword" name="password"
                               autocomplete="new-password" placeholder="Passwort" required>
                    </div>

                    <div class="mb-3">
                        <label for="inputConfirmedPassword" class="sr-only">Passwort wiederholen</label>
                        <input type="password" class="form-control" id="inputConfirmedPassword" name="confirmedPassword"
                               autocomplete="new-password" placeholder="Passwort wiederholen" required>
                    </div>
                </div>
                <div class="card-footer footer-plain">
                    <button class="btn btn-primary btn-block" type="submit">Registrieren</button>
                    <a class="btn btn-link text-dark"
                       href="<?= base_url('/login') ?>">Bereits registriert? Jetzt anmelden!</a>
                </div>
            </div>
        </form>