<?php

use App\Entities\MembershipStatus;
use function App\Helpers\getChildGroupsByParentId;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getImageAuthorById;
use function App\Helpers\getImageUrlById;
use function App\Helpers\getMembershipsByGroupId;
use function App\Helpers\getMembership;
use function App\Helpers\getUsers;

$currentUser = getCurrentUser();
$ownMembership = getMembership($currentUser->getId(), $group->getId());
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('/groups') ?>">Gruppen</a></li>
        <?php if ($parent = $group->getParent()): ?>
            <li class="breadcrumb-item" aria-current="page">
                <a href="<?= base_url('/group/' . $parent->getId()) ?>"><?= $parent->getName() ?></a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                <?= $group->getName() ?>
            </li>
        <?php else: ?>
            <li class="breadcrumb-item active" aria-current="page">
                <?= $group->getName() ?>
            </li>
        <?php endif; ?>
    </ol>
</nav>

<h1 class="header">
    <?= $group->getDisplayName() ?>
    <?php if (($membership = getMembership(getCurrentUser()->getId(), $group->getId()))): ?>
        <?= $membership->getStatus()->badge() ?>
    <?php endif; ?>
</h1>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">Informationen</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6">
                        <table>
                            <tr>
                                <img class="img-thumbnail mb-3"
                                     src="<?= getImageUrlById($group->getLogoId(), 'assets/img/group-logo_512x128.webp') ?>"
                                     alt="Logo <?= $group->getName() ?>">
                            </tr>
                            <tr>
                                <th>Name:&nbsp;</th>
                                <td><?= $group->getName() ?></td>
                            </tr>

                            <?php if ($address = $group->getAddress()): ?>
                                <tr>
                                    <th>Adresse:&nbsp;</th>
                                    <td>
                                        <a href="https://www.openstreetmap.org/search?query=<?= $address ?>"
                                           target="_blank"><?= $address ?></a>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php if ($url = $group->getWebsite()): ?>
                                <tr>
                                    <th>Website:&nbsp;</th>
                                    <td>
                                        <a href="<?= $url ?>"
                                           target="_blank"><?= parse_url($url)['host'] ?></a>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php if ($email = $group->getEmail()): ?>
                                <tr>
                                    <th>E-Mail:&nbsp;</th>
                                    <td>
                                        <a href="mailto:<?= $email ?>"><?= $email ?></a>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php if ($phone = $group->getPhone()): ?>
                                <tr>
                                    <th>Telefon:&nbsp;</th>
                                    <td>
                                        <a href="tel:<?= $phone ?>"><?= $phone ?></a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                    <div class="col-lg-6">
                        <figure>
                            <?php $groupImageSrc = getImageUrlById($group->getImageId(), 'assets/img/group-image_1920x1080.webp'); ?>
                            <a href="<?= $groupImageSrc ?>" data-toggle="lightbox">
                                <img class="img-thumbnail mt-3" src="<?= $groupImageSrc ?>"
                                     alt="Logo <?= $group->getName() ?>">
                            </a>
                            <figcaption>
                                <small><?= getImageAuthorById($group->getImageId()) ?></small>
                            </figcaption>
                        </figure>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-lg-12">
                        <?= $group->getDescription() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">Aktionen</div>
            <div class="card-body">
                <?php if ($group->getLatitude() && $group->getLongitude()): ?>
                    <a class="btn btn-primary btn-lg btn-block mb-3"
                       href="<?= base_url('map') ?>?group=<?= $group->getId() ?>">
                        <i class="fas fa-map-marker"></i> Position auf Karte zeigen
                    </a>
                <?php endif; ?>

                <?php if ($membership = getMembership($currentUser->getId(), $group->getId())): ?>
                    <a class="btn btn-primary btn-lg btn-block mb-3"
                       href="https://cloud.waldorfconnect.de/apps/files/files?dir=/<?= $group->getFolderMountPoint() ?>">
                        <i class="fas fa-cloud"></i> Ordner in Cloud öffnen
                    </a>

                    <hr>
                    <?= form_open("group/{$group->getId()}/leave", ['onsubmit' => "return confirm('Möchtest du die Gruppe {$group->getName()} wirklich verlassen?');"]) ?>
                    <?= form_hidden('id', $group->getId()) ?>
                    <button type="submit" class="btn btn-danger btn-lg btn-block mb-3">
                        <i class="fas fa-sign-out"></i> Verlassen
                    </button>
                    <?= form_close() ?>
                <?php else: ?>
                    <?php if ((!$parent = $group->getParent()) || getMembership($currentUser->getId(), $parent->getId())): ?>
                        <?= form_open("group/{$group->getId()}/join", ['onsubmit' => "return confirm('Möchtest du der Gruppe {$group->getName()} wirklich beitreten?');"]) ?>
                        <?= form_hidden('id', $group->getId()) ?>
                        <button type="submit" class="btn btn-success btn-lg btn-block mb-3">
                            <i class="fas fa-sign-in"></i> Beitrittsanfrage senden
                        </button>
                        <?= form_close() ?>
                    <?php endif; ?>
                <?php endif; ?>

                <?php if (($membership && $membership->getStatus() == MembershipStatus::ADMIN) || $currentUser->isAdmin()): ?>
                    <a href="<?= $group->getId() ?>/edit"
                       class="btn btn-primary btn-lg btn-block">
                        <i class="fas fa-pen"></i> Bearbeiten
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php if (!$group->getParentId()): ?>
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    Untergruppen
                    <?php if (($membership && $membership->getStatus() == MembershipStatus::ADMIN) || $currentUser->isAdmin()): ?>
                        <div class="justify-content-between align-items-center">
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#subgroupModal">
                                <i class="fas fa-add"></i> Untergruppe hinzufügen
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <?php foreach (getChildGroupsByParentId($group->getId()) as $child): ?>
                            <li class="list-group-item">
                                <div class="flex-container">
                                    <div class="flex-main">
                                        <?= $child->getName() ?>
                                    </div>
                                    <div class="flex-actions">
                                        <a class="btn btn-sm btn-outline-primary"
                                           href="<?= base_url('group') ?>/<?= $child->getId() ?>">
                                            Öffnen
                                        </a>
                                        <?php if (($membership && $membership->getStatus() == MembershipStatus::ADMIN) || $currentUser->isAdmin()): ?>
                                            <?= form_open("group/{$child->getId()}/delete", ['onsubmit' => "return confirm('Möchtest du die Untergruppe {$child->getName()} wirklich löschen? Dabei gehen ALLE gespeicherten Informationen inkl. Cloud-Ordner verloren.');"]) ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger ms-2">
                                                Löschen
                                            </button>
                                            <?= form_close() ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                Mitglieder
                <?php if (($membership && $membership->getStatus() == MembershipStatus::ADMIN) || $currentUser->isAdmin()): ?>
                    <div class="justify-content-between align-items-center">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#memberModal">
                            <i class="fas fa-add"></i> Mitglied hinzufügen
                        </button>
                    </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered" data-locale="<?= service('request')->getLocale(); ?>"
                       data-toggle="table" data-search="true" data-height="800" data-pagination="true"
                       data-show-columns="true" data-cookie="true" data-cookie-id-table="user"
                       data-search-highlight="true" data-show-columns-toggle-all="true">
                    <thead>
                    <tr>
                        <th data-field="name" data-sortable="true" scope="col">Vor- und Nachname</th>
                        <th data-field="groupRole" data-sortable="true" scope="col">Gruppenrolle</th>
                        <?php if ($group->isManageableBy($currentUser)): ?>
                            <th data-field="actions" data-sortable="true" scope="col">Aktionen</th>
                        <?php endif; ?>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach (getMembershipsByGroupId($group->getId()) as $membership): ?>
                        <?php if ($membership->getStatus() == MembershipStatus::PENDING): ?>
                            <?php if (!$group->isManageableBy($currentUser)): continue; endif; ?>

                            <tr>
                                <td id="td-id-<?= ($user = $membership->getUser())->getId() ?>"
                                    class="td-class-<?= $user->getId() ?>"
                                    data-title="<?= $user->getName() ?>"><?= $user->getUrl() ?></td>
                                <td><?= $membership->getStatus()->badge() ?></td>
                                <td>
                                    <?= form_open("group/{$group->getId()}/accept") ?>
                                    <?= form_hidden('userId', $user->getId()) ?>
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-check-circle"></i> Akzeptieren
                                    </button>
                                    <?= form_close() ?>

                                    <?= form_open("group/{$group->getId()}/deny") ?>
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
                                    data-title="<?= $user->getName() ?>"><?= $user->getUrl() ?></td>
                                <td><?= $membership->getStatus()->badge() ?></td>
                                <?php if ($group->isManageableBy($currentUser)): ?>
                                    <td>
                                        <?php if ($membership->getStatus() == MembershipStatus::ADMIN): ?>
                                            <?= form_open("group/{$group->getId()}/membership_status", ['class' => 'btn-group']) ?>
                                            <?= form_hidden('userId', $user->getId()) ?>
                                            <?= form_hidden('status', MembershipStatus::USER->value) ?>
                                            <button type="submit" class="btn btn-primary btn-sm mt-1">
                                                <i class="fas fa-arrow-down"></i> Zurückstufen
                                            </button>
                                            <?= form_close() ?>
                                        <?php else: ?>
                                            <?= form_open("group/{$group->getId()}/membership_status", ['class' => 'btn-group inline']) ?>
                                            <?= form_hidden('userId', $user->getId()) ?>
                                            <?= form_hidden('status', MembershipStatus::ADMIN->value) ?>
                                            <button type="submit" class="btn btn-primary btn-sm mt-1">
                                                <i class="fas fa-arrow-up"></i> Zum Admin ernennen
                                            </button>
                                            <?= form_close() ?>
                                        <?php endif; ?>

                                        <?= form_open("group/{$group->getId()}/kick_user", ['class' => 'btn-group inline']) ?>
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

<div class="modal fade" id="subgroupModal" tabindex="-1" aria-labelledby="subgroupModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= form_open("group/{$group->getId()}/add_subgroup") ?>
            <div class="modal-header">
                <h5 class="modal-title" id="subgroupModalLabel">Untergruppe hinzufügen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
            </div>
            <div class="modal-body">
                <p>Bitte gib den Namen der neuen Untergruppe an.</p>
                <div class="form-group">
                    <label for="inputName" class="sr-only">Name</label>
                    <input class="form-control" id="inputName" name="name" autocomplete="name" placeholder="Name">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">Erstellen</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<div class="modal fade" id="memberModal" tabindex="-1" aria-labelledby="memberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <?= form_open("group/{$group->getId()}/add_member") ?>
            <div class="modal-header">
                <h5 class="modal-title" id="memberModalLabel">Mitglieder hinzufügen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Schließen"></button>
            </div>
            <div class="modal-body">
                <p>Bitte wähle ein oder mehrere Mitglieder, die der Gruppe hinzugefügt werden sollen.<br>
                    <small>(Zur Mehrfachauswahl die STRG-Taste gedrückt halten.)</small>
                </p>
                <div class="form-group">
                    <label for="inputMember" class="sr-only">Mitglied wählen</label>
                    <select class="form-select" id="inputMember" name="member[]" size="20" multiple>
                        <?php if ($parent = $group->getParent()): ?>
                            <?php foreach (getUsers() as $user): ?>
                                <option value="<?= $user->getId() ?>">
                                    <?= $user->getName() ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach (getUsers() as $user): ?>
                                <option value="<?= $user->getId() ?>">
                                    <?= $user->getName() ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">Hinzufügen</button>
            </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

