<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            Gruppen
        </li>
    </ol>
</nav>

<h1 class="header">Alle Gruppen</h1>

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

<?php use function App\Helpers\countGroupMembers;
use function App\Helpers\getGroupsByRegionId;
use function App\Helpers\getRegions;

foreach (getRegions() as $region): ?>
    <?php $groups = getGroupsByRegionId($region->getId()) ?>
    <?php if (empty($groups)): continue; endif; ?>

    <h3 class="subheader"><?= $region->getName() ?></h3>
    <?php foreach ($groups as $group): ?>
        <div class="accordion accordion-flush" id="group<?= $group->getId() ?>">
            <div class="accordion-item">
                <h2 class="accordion-header" id="grouphead<?= $group->getId() ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#groupcollapse<?= $group->getId() ?>"
                            aria-expanded="true" aria-controls="groupcollapse<?= $group->getId() ?>">
                        <?= $group->getName() ?>&nbsp;
                        <?php if (($count = countGroupMembers($group->getId())) == 0): ?>
                            <span class="badge bg-danger">
                                Keine Mitglieder
                            </span>
                        <?php elseif ($count == 1): ?>
                            <span class="badge bg-success">
                                ein Mitglied
                            </span>
                        <?php else: ?>
                            <span class="badge bg-success">
                                <?= $count ?> Mitglieder
                            </span>
                        <?php endif; ?>
                    </button>
                </h2>
                <div id="groupcollapse<?= $group->getId() ?>" class="accordion-collapse collapse"
                     aria-labelledby="grouphead<?= $group->getId() ?>"
                     data-bs-parent="#group<?= $group->getId() ?>">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table>
                                    <tr>
                                        <?php
                                        $groupLogoPath = "/assets/img/group/" . $group->getId() . "/logo.png";
                                        if (is_file($_SERVER['DOCUMENT_ROOT'] . $groupLogoPath)) {
                                            $groupLogoSrc = base_url($groupLogoPath);
                                        } else {
                                            $groupLogoSrc = base_url('/assets/img/placeholders/group-logo_512x128.png');
                                        }
                                        ?>
                                        <img class="img-thumbnail mb-3" src="<?= $groupLogoSrc ?>"
                                             style="max-width: 100%; width: 512px; height: auto"
                                             alt="Logo <?= $group->getName() ?>" loading="lazy">
                                    </tr>
                                    <tr>
                                        <th>Name:&nbsp;</th>
                                        <td><?= $group->getName() ?></td>
                                    </tr>
                                    <tr>
                                        <th>
                                            Aktionen:&nbsp;
                                        </th>
                                        <td>
                                            <?= form_open('group/join', ['onsubmit' => "return confirm('Möchtest du der Gruppe {$group->getName()} wirklich beitreten?');"]) ?>
                                            <?= form_hidden('id', $group->getId()) ?>
                                            <a class="btn btn-primary btn-sm" href="group/<?= $group->getId() ?>">
                                                <i class="fas fa-people-group"></i> Gruppenseite öffnen
                                            </a>
                                            <button type="submit" class="btn btn-success btn-sm">
                                                <i class="fas fa-sign-in"></i> Beitrittsanfrage senden
                                            </button>
                                            <?= form_close() ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <figure>
                                    <?php
                                    $groupImagePath = "/assets/img/group/" . $group->getId() . "/image.png";
                                    if (is_file($_SERVER['DOCUMENT_ROOT'] . $groupImagePath)) {
                                        $groupImageSrc = base_url($groupImagePath);
                                    } else {
                                        $groupImageSrc = base_url('/assets/img/placeholders/group-image_1920x1080.png');
                                    }
                                    ?>
                                    <a href="<?= $groupImageSrc ?>" data-toggle="lightbox">
                                        <img class="img-thumbnail mt-3" src="<?= $groupImageSrc ?>"
                                             style="max-width: 100%; width: auto; height: auto; border-radius: 10px;"
                                             alt="Logo <?= $group->getName() ?>" loading="lazy">
                                    </a>
                                    <figcaption>
                                        <small><?= !is_null($group->getImageAuthor()) ? '&copy;&nbsp;' . $group->getImageAuthor() : '' ?></small>
                                    </figcaption>
                                </figure>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endforeach; ?>