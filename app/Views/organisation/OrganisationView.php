<?php

use App\Entities\MembershipStatus;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getMembershipsByOrganisationId;
use function App\Helpers\getMembership;

$currentUser = getCurrentUser();
$ownMembership = getMembership($currentUser->getId(), $organisation->getId());
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('/organisations') ?>">Organisationen</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            <?= $organisation->getName() ?>
        </li>
    </ol>
</nav>

<h1 class="header">
    <?= $organisation->getName() ?>
    <?php if (($membership = getMembership(getCurrentUser()->getId(), $organisation->getId()))): ?>
        <?= $membership->getStatus()->badge() ?>
    <?php endif; ?>
</h1>

<?php if ($success = session('error')): ?>
    <div class="col-md-12">
        <div class="alert alert-danger">
            <?= $success ?>
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
                                $groupLogoPath = "/assets/img/group/" . $organisation->getId() . "/logo";
                                if (is_file($_SERVER['DOCUMENT_ROOT'] . $groupLogoPath . '.svg')) {
                                    $groupLogoSrc = base_url($groupLogoPath . '.svg');
                                } else if (is_file($_SERVER['DOCUMENT_ROOT'] . $groupLogoPath . '.webp')) {
                                    $groupLogoSrc = base_url($groupLogoPath . '.webp');
                                } else {
                                    $groupLogoSrc = base_url('/assets/img/placeholders/group-logo_512x128.webp');
                                }
                                ?>
                                <img class="img-thumbnail mb-3" src="<?= $groupLogoSrc ?>"
                                     alt="Logo <?= $organisation->getName() ?>">
                            </tr>
                            <tr>
                                <th>Name:&nbsp;</th>
                                <td><?= $organisation->getName() ?></td>
                            </tr>
                            <?php if (!empty($organisation->getWebsiteUrl())): ?>
                                <tr>
                                    <th>Website:&nbsp;</th>
                                    <td>
                                        <a href="<?= $organisation->getWebsiteUrl() ?>"><?= parse_url($organisation->getWebsiteUrl())['host'] ?></a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <div class="col-lg-6">
                        <figure>
                            <?php
                            $groupImagePath = "/assets/img/group/" . $organisation->getId() . "/image.webp";
                            if (is_file($_SERVER['DOCUMENT_ROOT'] . $groupImagePath)) {
                                $groupImageSrc = base_url($groupImagePath);
                            } else {
                                $groupImageSrc = base_url('/assets/img/placeholders/group-image_1920x1080.webp');
                            }
                            ?>
                            <a href="<?= $groupImageSrc ?>" data-toggle="lightbox">
                                <img class="img-thumbnail mt-3" src="<?= $groupImageSrc ?>"
                                     alt="Logo <?= $organisation->getName() ?>">
                            </a>
                            <figcaption>
                                <small><?= !is_null($organisation->getImageAuthor()) ? '&copy;&nbsp;' . $organisation->getImageAuthor() : '' ?></small>
                            </figcaption>
                        </figure>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-lg-12">
                        <?= $organisation->getDescription() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">Aktionen</div>
            <div class="card-body">
                <?= form_open('group/join', ['onsubmit' => "return confirm('Möchtest du der Gruppe {$organisation->getName()} wirklich beitreten?');"]) ?>
                <?= form_hidden('id', $organisation->getId()) ?>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-sign-in"></i> Beitrittsanfrage senden
                </button>
                <?= form_close() ?>
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
                       data-toggle="table" data-search="true" data-height="800" data-pagination="true"
                       data-show-columns="true" data-cookie="true" data-cookie-id-table="user"
                       data-search-highlight="true" data-show-columns-toggle-all="true">
                    <thead>
                    <tr>
                        <th data-field="name" data-sortable="true" scope="col">Vor- und Nachname</th>
                        <th data-field="groupRole" data-sortable="true" scope="col">Gruppenrolle</th>
                        <?php if ($organisation->isManageableBy($currentUser)): ?>
                            <th data-field="actions" data-sortable="true" scope="col">Aktionen</th>
                        <?php endif; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach (getMembershipsByOrganisationId($organisation->getId()) as $membership): ?>
                        <?php if ($membership->getStatus() == MembershipStatus::PENDING): ?>
                            <?php if (!$organisation->isManageableBy($currentUser)): continue; endif; ?>

                            <tr>
                                <td id="td-id-<?= ($user = $membership->getUser())->getId() ?>"
                                    class="td-class-<?= $user->getId() ?>"
                                    data-title="<?= $user->getName() ?>"><?= $user->getName() ?></td>
                                <td><?= $membership->getStatus()->badge() ?></td>
                                <td>
                                    <?= form_open('group/accept') ?>
                                    <?= form_hidden('organisationId', $organisation->getId()) ?>
                                    <?= form_hidden('userId', $user->getId()) ?>
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-check-circle"></i> Akzeptieren
                                    </button>
                                    <?= form_close() ?>

                                    <?= form_open('group/deny') ?>
                                    <?= form_hidden('organisationId', $organisation->getId()) ?>
                                    <?= form_hidden('userId', $user->getId()) ?>
                                    <button type="submit" class="btn btn-danger btn-sm mt-1">
                                        <i class="fas fa-x"></i> Ablehnen
                                    </button>
                                    <?= form_close() ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td id="td-id-<?= ($user = $membership->getUser())->getId() ?>"
                                    class="td-class-<?= $user->getId() ?>"
                                    data-title="<?= $user->getName() ?>"><?= $user->getName() ?></td>
                                <td><?= $membership->getStatus()->badge() ?></td>
                                <?php if ($organisation->isManageableBy($currentUser)): ?>
                                    <td>
                                        <?php if ($membership->getStatus() == MembershipStatus::ADMIN): ?>
                                            <?= form_open('group/change_user_status') ?>
                                            <?= form_hidden('organisationId', $organisation->getId()) ?>
                                            <?= form_hidden('userId', $user->getId()) ?>
                                            <?= form_hidden('status', MembershipStatus::USER->value) ?>
                                            <button type="submit" class="btn btn-primary btn-sm mt-1">
                                                <i class="fas fa-arrow-down"></i> Zurückstufen
                                            </button>
                                            <?= form_close() ?>
                                        <?php else: ?>
                                            <?= form_open('group/change_user_status') ?>
                                            <?= form_hidden('organisationId', $organisation->getId()) ?>
                                            <?= form_hidden('userId', $user->getId()) ?>
                                            <?= form_hidden('status', MembershipStatus::ADMIN->value) ?>
                                            <button type="submit" class="btn btn-danger btn-sm mt-1">
                                                <i class="fas fa-arrow-up"></i> Zu Admin ernennen
                                            </button>
                                            <?= form_close() ?>
                                        <?php endif; ?>

                                        <?= form_open('group/kick_user') ?>
                                        <?= form_hidden('organisationId', $organisation->getId()) ?>
                                        <?= form_hidden('userId', $user->getId()) ?>
                                        <button type="submit" class="btn btn-danger btn-sm mt-1">
                                            <i class="fas fa-trash"></i> Entfernen
                                        </button>
                                        <?= form_close() ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

