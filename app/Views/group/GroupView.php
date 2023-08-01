<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('/groups') ?>">Gruppen</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            <?= $group->getName() ?>
        </li>
    </ol>
</nav>

<h1 class="header"><?= $group->getName() ?></h1>

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
        <a href="<?= base_url('/') ?>assets/img/group/<?= $group->getId() ?>/image.png" data-toggle="lightbox">
            <img src="<?= base_url('/') ?>assets/img/group/<?= $group->getId() ?>/image.png"
                 class="img-thumbnail mt-3"
                 style="max-width: 100%; width: auto; height: auto; border-radius: 10px;"
                 onerror="this.src = 'https://placehold.co/1920x1080.png?text=Leider%20haben%20wir%20f%C3%BCr%20diese%20Gruppe%20noch%20kein%20Bild!'"
                 alt="Logo <?= $group->getName() ?>">
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