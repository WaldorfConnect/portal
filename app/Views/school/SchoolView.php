<?php

use function App\Helpers\getUsersBySchoolId;

?>
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
        <figure>
            <a href="<?= base_url('/') ?>assets/img/school/<?= $school->getId() ?>/image.png" data-toggle="lightbox">
                <img src="<?= base_url('/') ?>assets/img/school/<?= $school->getId() ?>/image.png"
                     class="img-thumbnail mt-3"
                     style="max-width: 100%; width: auto; height: auto; border-radius: 10px;"
                     onerror="this.src = 'https://placehold.co/1920x1080.png?text=Leider%20haben%20wir%20f%C3%BCr%20diese%20Schule%20noch%20kein%20Bild!'"
                     alt="Logo <?= $school->getName() ?>">
            </a>
            <figcaption>
                <small><?= !is_null($school->getImageAuthor()) ? '&copy;&nbsp;' . $school->getImageAuthor() : '' ?></small>
            </figcaption>
        </figure>
    </div>
</div>

<h3 class="subheader">Nutzer*innen</h3>

<table class="table table-striped table-bordered" data-locale="<?= service('request')->getLocale(); ?>"
       data-toggle="table" data-search="true" data-height="500" data-pagination="true"
       data-show-columns="true" data-cookie="true" data-cookie-id-table="user"
       data-search-highlight="true" data-show-columns-toggle-all="true">
    <thead>
    <tr>
        <th data-field="name" data-sortable="true" scope="col">Vor- und Nachname</th>
        <th data-field="role" data-sortable="true" scope="col">Rolle</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach (getUsersBySchoolId($school->getId()) as $user): ?>
        <?php if (!$user->getStatus()->isReady()): continue; endif; ?>
        <tr>
            <td id="td-id-<?= $user->getId() ?>" class="td-class-<?= $user->getId() ?>"
                data-title="<?= $user->getName() ?>"><?= $user->getName() ?></td>
            <td><?= $user->getRole()->badge() ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>