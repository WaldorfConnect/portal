<?php

use function App\Helpers\getOrganisationsByRegionId;
use function App\Helpers\getRegions;
use function App\Helpers\getSchoolsByRegionId;

?>
    <main>
    <div class="container login">

    <div class="card register">
        <div class="card-header header-plain">
            <img class="mb-2 navbar-brand-logo" src="<?= base_url('/') ?>/assets/img/banner_small.png"
                 alt="WaldorfConnect Logo">
            <h1 class="h2">Registrieren</h1>
        </div>
        <?php if (session('success')): ?>
            <?= form_open('register/resend', '', ['userId' => session('userId')]) ?>
                <div class="card-body">

                    <?php if (session('resend')): ?>

                        <?php if (session('resend') === 'success'): ?>
                            <div class="alert alert-success">
                                <b>Erneuter Versand erfolgreich!</b> Wir haben dir eine weitere E-Mail mit einem
                                Bestätigungslink an <?= session('email') ?> gesendet. Bitte klicke auf diesen Link,
                                um mit der Registrierung fortzufahren!
                            </div>
                        <?php else: ?>
                            <div class="alert alert-danger">
                                <b>Erneuter Versand fehlgeschlagen!</b> Wir haben versucht dir eine weitere E-Mail mit
                                Bestätigungslink an <?= session('email') ?> zu senden, aber etwas ist
                                schiefgegangen. Wende dich bitte an technik@waldorfconnect.de!
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="alert alert-success">
                            <b>Registrierung erfolgreich!</b> Dein Account wurde angelegt. Wir haben dir nun eine <b>E-Mail mit
                                einem Bestätigungslink</b> an <?= session('email') ?> gesendet. Bitte klicke auf diesen Link, um mit
                            der Registrierung fortzufahren!<br>Dein Benutzername lautet: <b><?= session('username') ?></b>.
                        </div>
                    <?php endif; ?>

                    <button class="btn btn-link text-dark" type="submit">Nach ein paar Minuten noch keine E-Mail erhalten? Erneut anfordern!</button>
                </div>
            <?= form_close(); ?>
        <?php else: ?>
            <?= form_open('register') ?>
                <div class="card-body">
                    <?php if ($error = session('error')): ?>
                        <div class="alert alert-danger">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <h3>Persönliche Angaben</h3>

                    <div class="mb-3">
                        <label for="inputName" class="sr-only">Vorname(n)</label>
                        <input class="form-control" id="inputName" name="name" autocomplete="name"
                               placeholder="Vor- und Nachname" value="<?= old('name') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="inputEmail" class="sr-only">E-Mail</label>
                        <input type="email" class="form-control" id="inputEmail" name="email" autocomplete="email"
                               placeholder="E-Mail" value="<?= old('email') ?>" required>
                    </div>

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

                    <h3 class="mt-5">Organisationsangaben</h3>

                    <div class="mb-3">
                        <label for="inputSchool" class="form-label">Schule</label>
                        <select class="form-select" id="inputSchool" name="school" required>
                            <?php foreach (getRegions() as $region): ?>
                                <optgroup label="<?= $region->getName() ?>">
                                    <?php foreach (getSchoolsByRegionId($region->getId()) as $school): ?>
                                        <option <?= $school->getId() == old('school') ? 'selected' : '' ?>
                                                value="<?= $school->getId() ?>"><?= $school->name ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="inputGroups" class="form-label">Organisationen/Gruppen</label>
                        <select class="form-select" id="inputGroups" name="groups[]" aria-describedby="groupsHelp" multiple
                                required>
                            <?php foreach (getRegions() as $region): ?>
                                <optgroup label="<?= $region->getName() ?>">
                                    <?php foreach (getOrganisationsByRegionId($region->getId()) as $group): ?>
                                        <option <?= !is_null(old('groups')) && in_array($group->getId(), old('groups')) ? 'selected' : '' ?>
                                                value="<?= $group->getId() ?>"><?= $group->name ?></option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                        <small id="groupsHelp" class="form-text text-muted">Zur Auswahl mehrerer Gruppen auf
                            Desktop-Geräten die STRG-Taste gedrückt halten.</small>
                    </div>

                </div>
                <div class="card-footer footer-plain">
                    <button class="btn btn-primary btn-block" type="submit">Registrieren</button>
                    <a class="btn btn-link text-dark"
                       href="<?= base_url('/login') ?>">Bereits registriert? Jetzt anmelden!</a>
                </div>
            <?= form_close(); ?>
        <?php endif; ?>
    </div>