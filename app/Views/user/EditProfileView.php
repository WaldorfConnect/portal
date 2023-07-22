<h1 class="header">Profil bearbeiten</h1>

<form method="post">
    <h2>Persönliche Angaben</h2>

    <div class="mb-3">
        <label for="inputUsername" class="sr-only">Benutzername</label>
        <input class="form-control" id="inputUsername" name="username"
               value="<?= \App\Helpers\getCurrentUser()->username ?>" required disabled>
    </div>

    <div class="mb-3">
        <label for="inputName" class="sr-only">Vorname(n)</label>
        <input class="form-control" id="inputName" name="name" autocomplete="name"
               placeholder="Vor- und Nachname" value="<?= \App\Helpers\getCurrentUser()->displayName ?>" required>
    </div>

    <div class="mb-3">
        <label for="inputEmail" class="sr-only">E-Mail</label>
        <input class="form-control" id="inputEmail" name="email" autocomplete="email"
               placeholder="E-Mail" value="<?= \App\Helpers\getCurrentUser()->email ?>" required>
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

    <h2 class="mt-5">Organisationsangaben</h2>

    <div class="mb-3">
        <label for="inputSchool" class="sr-only">Schule</label>
        <select class="form-control" id="inputSchool" name="school" required>
        </select>
    </div>

    <div class="mb-3">
        <label for="inputGroups" class="sr-only">Organisationen/Gruppen</label>
        <select class="form-control" id="inputGroups" name="groups" multiple required>
        </select>
    </div>

    <button class="btn btn-primary btn-block" type="submit">Speichern</button>
</form>