<?php

use App\Entities\MembershipStatus;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getImageAuthorById;
use function App\Helpers\getMembershipsByGroupId;
use function App\Helpers\getMembership;
use function App\Helpers\getRegions;

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
            <li class="breadcrumb-item" aria-current="page">
                <a href="<?= base_url('/group/' . $group->getId()) ?>"><?= $group->getName() ?></a>
            </li>
        <?php else: ?>
            <li class="breadcrumb-item" aria-current="page">
                <a href="<?= base_url('/group/' . $group->getId()) ?>"><?= $group->getName() ?></a>
            </li>
        <?php endif; ?>
        <li class="breadcrumb-item active" aria-current="page">
            Bearbeiten
        </li>
    </ol>
</nav>

<h1 class="header">
    <?= $group->getDisplayName() ?>
    <?php if (($membership = getMembership(getCurrentUser()->getId(), $group->getId()))): ?>
        <?= $membership->getStatus()->badge() ?>
    <?php endif; ?>
</h1>

<div class="row">
    <?= form_open_multipart("group/{$group->getId()}/edit") ?>

    <div class="form-group row mb-3">
        <label for="inputName" class="col-form-label col-md-4 col-lg-3">Name</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputName" name="name" autocomplete="name"
                   placeholder="Name" value="<?= $group->getName() ?>"
                <?= $currentUser->isAdmin() ? 'required' : 'disabled' ?>>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputRegion" class="col-form-label col-md-4 col-lg-3">Region</label>
        <div class="col-md-8 col-lg-9">
            <select class="form-select" id="inputRegion"
                    name="region" <?= $currentUser->isAdmin() && !$group->getParentId() ? 'required' : 'disabled' ?>>
                <?php foreach (getRegions() as $region): ?>
                    <option value="<?= $region->getId() ?>" <?= $region->getId() == $group->getRegionId() ? "selected" : "" ?>>
                        <?= $region->getName() ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputAddress" class="col-form-label col-md-4 col-lg-3">Adresse</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputAddress" name="address" type="text"
                   placeholder="Musterstraße 1, 12345 Musterstadt"
                   value="<?= $group->getAddress() ?? '' ?>">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputWebsite" class="col-form-label col-md-4 col-lg-3">Website</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputWebsite" name="website" type="url"
                   placeholder="https://example.com"
                   value="<?= $group->getWebsite() ?? '' ?>">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputEmail" class="col-form-label col-md-4 col-lg-3">E-Mail</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputEmail" name="email" type="email"
                   placeholder="mail@example.com"
                   value="<?= $group->getEmail() ?? '' ?>">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputPhone" class="col-form-label col-md-4 col-lg-3">Telefon</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputPhone" name="phone" type="tel"
                   placeholder="+49 123 456789"
                   value="<?= $group->getPhone() ?? '' ?>">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputLatitude" class="col-form-label col-md-4 col-lg-3">Breitengrad</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputLatitude" name="latitude" type="number"
                   min="-90.0" max="90.0" step="0.00000001" pattern="^\d*\.\d{5,8}$" placeholder="49,8430556"
                   value="<?= $group->getLatitude() ?? '' ?>">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputLongitude" class="col-form-label col-md-4 col-lg-3">Längengrad</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputLongitude" name="longitude" type="number"
                   min="-180.0" max="180.0" step="0.00000001" pattern="^\d*\.\d{5,8}$" placeholder="9,9019444"
                   value="<?= $group->getLongitude() ?? '' ?>">
        </div>
    </div>

    <hr class="mt-3 mb-3">

    <div class="form-group row mb-3">
        <label for="inputLogo" class="col-form-label col-md-4 col-lg-3">Logo <small>(ca. 512x128 | max.
                1MB)</small></label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputLogo" name="logo" type="file"
                   accept="image/png, image/jpg, image/jpeg, image/gif, image/webp, image/svg+xml">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputLogoAuthor" class="col-form-label col-md-4 col-lg-3">Autor des Logos</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputLogoAuthor" name="logoAuthor" type="text"
                   value="<?= getImageAuthorById($group->getLogoId()) ?>">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputImage" class="col-form-label col-md-4 col-lg-3">Bild <small>(ca. 1920x1080 | max. 2MB)</small></label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputImage" name="image" type="file"
                   accept="image/png, image/jpg, image/jpeg, image/gif, image/webp">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputImageAuthor" class="col-form-label col-md-4 col-lg-3">Autor des Bildes</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputImageAuthor" name="imageAuthor" type="text"
                   value="<?= getImageAuthorById($group->getImageId()) ?>">
        </div>
    </div>

    <hr class="mt-3 mb-3">

    <div class="mb-3">
        <label for="description" class="form-label">Beschreibung</label>
        <textarea class="form-control" id="description"
                  name="description"><?= $group->getDescription() ?></textarea>
    </div>

    <button id="submitButton" class="btn btn-primary btn-block" type="submit">Bearbeiten</button>
    <?= form_close() ?>
</div>

<script>
    $(document).ready(function () {
        $('#description').summernote();
        // Fix for summernote cuz dropdowns broken in BS5
        $("button[data-toggle='dropdown']").each(function (index) {
            $(this).removeAttr("data-toggle").attr("data-bs-toggle", "dropdown");
        });
    });
</script>

<script src="<?= base_url('/') ?>assets/js/enforceFileUploadSizeLimits.js"></script>