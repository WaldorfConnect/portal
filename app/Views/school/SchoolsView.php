<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            Schulen
        </li>
    </ol>
</nav>

<h1 class="header">Alle Schulen</h1>

<?php foreach (\App\Helpers\getRegions() as $region): ?>
    <?php $schools = \App\Helpers\getSchoolsByRegionId($region->getId()) ?>
    <?php if (empty($schools)): continue; endif; ?>

    <h3 class="subheader"><?= $region->getName() ?></h3>
    <?php foreach ($schools as $school): ?>
        <div class="accordion accordion-flush" id="school<?= $school->getId() ?>">
            <div class="accordion-item">
                <h2 class="accordion-header" id="schoolhead<?= $school->getId() ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#schoolcollapse<?= $school->getId() ?>"
                            aria-expanded="true" aria-controls="schoolcollapse<?= $school->getId() ?>">
                        <?= $school->getName() ?>
                    </button>
                </h2>
                <div id="schoolcollapse<?= $school->getId() ?>" class="accordion-collapse collapse"
                     aria-labelledby="schoolhead<?= $school->getId() ?>"
                     data-bs-parent="#school<?= $school->getId() ?>">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table>
                                    <tr>
                                        <img src="<?= base_url('/') ?>assets/img/school/<?= $school->getId() ?>/logo.png"
                                             style="width: 512px; height: auto"
                                             onerror="this.src = 'https://placehold.co/512x128.png?text=Leider%20haben%20wir%20f%C3%BCr%20diese%20Schule%20noch%20kein%20Logo!'"
                                             alt="Logo <?= $school->getName() ?>">
                                    </tr>
                                    <tr>
                                        <th>Schulnummer:&nbsp;</th>
                                        <td><?= $school->getStateId() ?></td>
                                    </tr>
                                    <tr>
                                        <th>Schulname:&nbsp;</th>
                                        <td><?= $school->getName() ?></td>
                                    </tr>
                                    <tr>
                                        <th>Kurzname:&nbsp;</th>
                                        <td><?= $school->getShortName() ?></td>
                                    </tr>
                                    <tr>
                                        <th>Adresse:&nbsp;</th>
                                        <td><?= $school->getAddress() ?></td>
                                    </tr>
                                    <tr>
                                        <th>E-Mail (Verwaltung):&nbsp;</th>
                                        <td>
                                            <a href="mailto:<?= $school->getEmailBureau() ?>"><?= $school->getEmailBureau() ?></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>E-Mail (SMV):&nbsp;</th>
                                        <td>
                                            <?php if (is_null($school->getEmailSMV())): ?>
                                                <i>(nicht definiert)</i>
                                            <?php else: ?>
                                                <a href="mailto:<?= $school->getEmailSMV() ?>"><?= $school->getEmailSMV() ?></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            Aktionen:&nbsp;
                                        </th>
                                        <td>
                                            <br>
                                            <a class="btn btn-primary btn-sm" href="school/<?= $school->getId() ?>">
                                                Übersichtseite öffnen
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <img src="<?= base_url('/') ?>assets/img/school/<?= $school->getId() ?>/image.png"
                                     style="max-width: 100%; width: auto; height: auto; border-radius: 10px;"
                                     onerror="this.src = 'https://placehold.co/1920x1080.png?text=Leider%20haben%20wir%20f%C3%BCr%20diese%20Schule%20noch%20kein%20Bild!'"
                                     alt="Logo <?= $school->getName() ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>





