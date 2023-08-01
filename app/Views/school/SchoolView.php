<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('/schools') ?>">Schulen</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            <?= $school->getName() ?>
        </li>
    </ol>
</nav>

<h1 class="header"><?= $school->getName() ?></h1>

<h3 class="subheader">Allgemeines</h3>
<div class="row">
    <div class="col-md-6">
        <table>
            <tr>
                <img src="<?= base_url('/') ?>assets/img/school/<?= $school->getId() ?>/logo.png"
                     class="img-thumbnail mb-3"
                     style="max-width: 100%; width: 512px; height: auto"
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
        </table>
    </div>
    <div class="col-md-6">
        <a href="<?= base_url('/') ?>assets/img/school/<?= $school->getId() ?>/image.png" data-toggle="lightbox">
            <img src="<?= base_url('/') ?>assets/img/school/<?= $school->getId() ?>/image.png"
                 class="img-thumbnail mt-3"
                 style="max-width: 100%; width: auto; height: auto; border-radius: 10px;"
                 onerror="this.src = 'https://placehold.co/1920x1080.png?text=Leider%20haben%20wir%20f%C3%BCr%20diese%20Schule%20noch%20kein%20Bild!'"
                 alt="Logo <?= $school->getName() ?>">
        </a>
    </div>
</div>

<h3 class="subheader">Administrator*innen</h3>
<div class="text-center">
    Diese Funktion folgt bald!
</div>

<h3 class="subheader">Schüler*innen</h3>
<div class="text-center">
    Diese Funktion folgt bald!
</div>