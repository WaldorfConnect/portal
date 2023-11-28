<?php

use function App\Helpers\getCurrentUser;
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

<?php $errors = session('error'); if ($errors): ?>
    <div class="col-md-12">
        <div class="alert alert-danger">
            <?php if (is_array($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <?= esc($error) ?><br>
                <?php endforeach; ?>
            <?php else: ?>
                <?= $errors ?>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($success = session('success')): ?>
    <div class="col-md-12">
        <div class="alert alert-success">
            <?= $success ?>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">Informationen</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <table>
                            <tr>
                                <?php
                                $schoolLogoPath = "/assets/img/school/" . $school->getId() . "/logo.webp";
                                if (is_file($_SERVER['DOCUMENT_ROOT'] . $schoolLogoPath)) {
                                    $schoolLogoSrc = base_url($schoolLogoPath);
                                } else {
                                    $schoolLogoSrc = base_url('/assets/img/placeholders/school-logo_512x128.webp');
                                }
                                ?>
                                <img class="img-thumbnail mb-3" src="<?= $schoolLogoSrc ?>"
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
                            <?php if (!empty($school->getWebsiteUrl())): ?>
                                <tr>
                                    <th>Website:&nbsp;</th>
                                    <td>
                                        <a href="<?= $school->getWebsiteUrl() ?>"><?= parse_url($school->getWebsiteUrl())['host'] ?></a>
                                    </td>
                                </tr>
                            <?php endif; ?>
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
                    <div class="col-lg-6">
                        <figure>
                            <?php
                            $schoolImagePath = "/assets/img/school/" . $school->getId() . "/image.webp";
                            if (is_file($_SERVER['DOCUMENT_ROOT'] . $schoolImagePath)) {
                                $schoolImageSrc = base_url($schoolImagePath);
                            } else {
                                $schoolImageSrc = base_url('/assets/img/placeholders/school-image_1920x1080.webp');
                            }
                            ?>
                            <a href="<?= $schoolImageSrc ?>" data-toggle="lightbox">
                                <img class="img-thumbnail mt-3" src="<?= $schoolImageSrc ?>"
                                     alt="Logo <?= $school->getName() ?>">
                            </a>
                            <figcaption>
                                <small><?= !is_null($school->getImageAuthor()) ? '&copy;&nbsp;' . $school->getImageAuthor() : '' ?></small>
                            </figcaption>
                        </figure>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">Aktionen</div>
            <div class="card-body text-center">
                <?php if(getCurrentUser()->getRole()->isAdmin()): ?>
                    <a class="btn btn-success"
                       href="<?= base_url('admin/school/edit/' . $school->getId()) ?>?return=<?= uri_string() ?>">
                        <i class="fas fa-edit"></i> Bearbeiten
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header">Nutzer*innen</div>
            <div class="card-body">
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
            </div>
        </div>
    </div>
</div>