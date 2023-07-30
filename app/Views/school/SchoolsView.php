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
                            <div class="col-md-6" style="text-align: right">
                                <img src="<?= base_url('/') ?>assets/img/school/<?= $school->getId() ?>/image.png"
                                     style="width: 80%; height: auto">
                            </div>
                            <div class="col-md-6">
                                <table>
                                    <tr>
                                        <img src="<?= base_url('/') ?>assets/img/school/<?= $school->getId() ?>/logo.png"
                                             style="width: 80%; height: auto">
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
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>





