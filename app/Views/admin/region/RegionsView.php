<?php

use App\Entities\UserRole;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getRegions;
use function App\Helpers\getSchools;

$currentUser = getCurrentUser();
?>
<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="/">Startseite</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Administration</a></li>
            <li class="breadcrumb-item active" aria-current="page">
                Regionsadministration
            </li>
        </ol>
    </nav>

    <h1 class="header">Regionsadministration</h1>

    <p>
        Hier werden alle Regionen angezeigt, in denen sich Gruppen bzw. Schulen befinden können.<br><br>
        <b>Achtung!</b> Die Angabe des Kürzels <u>muss</u> nach ISO 3166-1/ISO 3166-2 erfolgen.
        Es ist <u>keine Mehrfachnennung</u>
        möglich, d. h. bei Landesverbänden, die mehrere Bundesländer
        einschließen muss <u>ein</u> Bundesland ausgewählt werden.
        Hier bietet es sich z. B. an das Bundesland der Geschäftsstelle der ortsansässigen W-LSV zu wählen.<br>
        Die ISO 3166-2 Codes für alle deutschsprachigen Ländern finden sich hier:<br>
    </p>

    <div>
        <ul>
            <li><a href="https://de.wikipedia.org/wiki/ISO_3166-2:DE">Deutschland</a></li>
            <li><a href="https://de.wikipedia.org/wiki/ISO_3166-2:AT">Österreich</a></li>
            <li><a href="https://de.wikipedia.org/wiki/ISO_3166-2:CH">Schweiz</a></li>
            <li><a href="https://de.wikipedia.org/wiki/ISO_3166-2:LU">Luxemburg</a></li>
        </ul>
    </div>

    <?php if ($success = session('error')): ?>
        <br><br>
        <div class="col-md-12">
            <div class="alert alert-danger">
                <?= $success ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($success = session('success')): ?>
        <br><br>
        <div class="col-md-12">
            <div class="alert alert-success">
                <?= $success ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="row">
    <div class="col-md-5 w-auto ms-auto">
        <a class="btn btn-primary"
           href="<?= base_url('admin/region/create') ?>">
            <i class="fas fa-plus-square"></i> Region erstellen
        </a>
    </div>
</div>

<div class="row">
    <table class="table table-striped table-bordered" data-locale="<?= service('request')->getLocale(); ?>"
           data-toggle="table" data-search="true" data-height="1000" data-pagination="true"
           data-show-columns="true" data-cookie="true" data-cookie-id-table="user"
           data-search-highlight="true" data-show-columns-toggle-all="true">
        <thead>
        <tr>
            <th data-field="id" data-sortable="true" scope="col">Name</th>
            <th data-field="description" data-sortable="true" scope="col">ISO 3166-2</th>
            <th data-field="action" scope="col">Aktion</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach (getRegions() as $region): ?>
            <tr>
                <td id="td-id-<?= $region->getId() ?>" class="td-class-<?= $region->getId() ?>"
                    data-title="<?= $region->getName() ?>"><?= $region->getName() ?></td>
                <td><?= $region->getIsoCode() ?></td>
                <td>
                    <div class="btn-group gap-2" role="group">
                        <a class="btn btn-primary btn-sm"
                           href="<?= base_url('admin/region/edit') . '/' . $region->getId() ?>"><i
                                    class="fas fa-pen"></i>
                        </a>
                        <div>
                            <?= form_open('admin/region/delete') ?>
                            <?= form_hidden('id', $region->getId()) ?>
                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?= form_close() ?>
                        </div>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>