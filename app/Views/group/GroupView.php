<?php

use App\Entities\MembershipStatus;
use App\Entities\UserRole;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getGroupMembershipsByGroupId;
use function App\Helpers\getGroupMembership;

$currentUser = getCurrentUser();
$ownMembership = getGroupMembership($currentUser->getId(), $group->getId());
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('/groups') ?>">Gruppen</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            <?= $group->getName() ?>
        </li>
    </ol>
</nav>

<h1 class="header">
    <?= $group->getName() ?>
    <?php if (($membership = getGroupMembership(getCurrentUser()->getId(), $group->getId()))): ?>
        <?= $membership->getStatus()->badge() ?>
    <?php endif; ?>
</h1>

<h3 class="subheader">Allgemeines</h3>
<div class="row">
    <div class="col-md-6">
        <table>
            <tr>
                <img src="<?= base_url('/') ?>assets/img/group/<?= $group->getId() ?>/logo.png"
                     class="img-thumbnail mb-3"
                     style="max-width: 100%; width: 512px; height: auto"
                     onerror="this.src = 'https://placehold.co/512x128.png?text=Leider%20haben%20wir%20f%C3%BCr%20diese%20Gruppe%20noch%20kein%20Logo!'"
                     alt="Logo <?= $group->getName() ?>">
            </tr>
            <tr>
                <th>Name:&nbsp;</th>
                <td><?= $group->getName() ?></td>
            </tr>
            <tr>
                <th>Beschreibung:&nbsp;</th>
                <td><?= $group->getDescription() ?></td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <figure>
            <a href="<?= base_url('/') ?>assets/img/group/<?= $group->getId() ?>/image.png" data-toggle="lightbox">
                <img src="<?= base_url('/') ?>assets/img/group/<?= $group->getId() ?>/image.png"
                     class="img-thumbnail mt-3"
                     style="max-width: 100%; width: auto; height: auto; border-radius: 10px;"
                     onerror="this.src = 'https://placehold.co/1920x1080.png?text=Leider%20haben%20wir%20f%C3%BCr%20diese%20Gruppe%20noch%20kein%20Bild!'"
                     alt="Logo <?= $group->getName() ?>">
            </a>
            <figcaption>
                <small><?= !is_null($group->getImageAuthor()) ? '&copy;&nbsp;' . $group->getImageAuthor() : '' ?></small>
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
        <th data-field="userRole" data-sortable="true" scope="col">Nutzerrolle</th>
        <th data-field="groupRole" data-sortable="true" scope="col">Gruppenrolle</th>
        <th data-field="actions" data-sortable="true" scope="col">Aktionen</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach (getGroupMembershipsByGroupId($group->getId()) as $membership): ?>
        <?php if ($membership->getStatus() == MembershipStatus::PENDING): ?>
            <?php if ($ownMembership->getStatus() != MembershipStatus::ADMIN): continue; endif; ?>

            <tr>
                <td id="td-id-<?= ($user = $membership->getUser())->getId() ?>"
                    class="td-class-<?= $user->getId() ?>"
                    data-title="<?= $user->getName() ?>"><?= $user->getName() ?></td>
                <td><?= $user->getRole()->badge() ?></td>
                <td><?= $membership->getStatus()->badge() ?></td>
                <td>
                    <?= form_open('group/accept') ?>
                    <?= form_hidden('groupId', $group->getId()) ?>
                    <?= form_hidden('userId', $user->getId()) ?>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check-circle"></i> Akzeptieren
                    </button>
                    <?= form_close() ?>

                    <?= form_open('group/deny') ?>
                    <?= form_hidden('groupId', $group->getId()) ?>
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
                <td><?= $user->getRole()->badge() ?></td>
                <td><?= $membership->getStatus()->badge() ?></td>
                <?php if ($ownMembership->getStatus() == MembershipStatus::ADMIN || $group->mayManage($currentUser)): ?>
                    <td>
                        <?php if ($membership->getStatus() == MembershipStatus::ADMIN): ?>
                            <?= form_open('group/change_user_status') ?>
                            <?= form_hidden('groupId', $group->getId()) ?>
                            <?= form_hidden('userId', $user->getId()) ?>
                            <?= form_hidden('status', MembershipStatus::USER->value) ?>
                            <button type="submit" class="btn btn-primary btn-sm mt-1">
                                <i class="fas fa-arrow-down"></i> Zurückstufen
                            </button>
                            <?= form_close() ?>
                        <?php else: ?>
                            <?= form_open('group/change_user_status') ?>
                            <?= form_hidden('groupId', $group->getId()) ?>
                            <?= form_hidden('userId', $user->getId()) ?>
                            <?= form_hidden('status', MembershipStatus::ADMIN->value) ?>
                            <button type="submit" class="btn btn-danger btn-sm mt-1">
                                <i class="fas fa-arrow-up"></i> Zu Admin ernennen
                            </button>
                            <?= form_close() ?>
                        <?php endif; ?>

                        <?= form_open('group/kick_user') ?>
                        <?= form_hidden('groupId', $group->getId()) ?>
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