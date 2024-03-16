<?php

use App\Entities\MembershipStatus;
use function App\Helpers\getCurrentUser;
use function App\Helpers\getImageAuthorById;
use function App\Helpers\getMembershipsByOrganisationId;
use function App\Helpers\getMembership;
use function App\Helpers\getRegions;

$currentUser = getCurrentUser();
$ownMembership = getMembership($currentUser->getId(), $organisation->getId());
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('/organisations') ?>">Organisationen</a></li>
        <?php if ($parent = $organisation->getParent()): ?>
            <li class="breadcrumb-item" aria-current="page">
                <a href="<?= base_url('/organisation/' . $parent->getId()) ?>"><?= $parent->getName() ?></a>
            </li>
            <li class="breadcrumb-item" aria-current="page">
                <a href="<?= base_url('/organisation/' . $organisation->getId()) ?>"><?= $organisation->getName() ?></a>
            </li>
        <?php else: ?>
            <li class="breadcrumb-item" aria-current="page">
                <a href="<?= base_url('/organisation/' . $organisation->getId()) ?>"><?= $organisation->getName() ?></a>
            </li>
        <?php endif; ?>
        <li class="breadcrumb-item active" aria-current="page">
            Bearbeiten
        </li>
    </ol>
</nav>

<h1 class="header">
    <?= $organisation->getDisplayName() ?>
    <?php if (($membership = getMembership(getCurrentUser()->getId(), $organisation->getId()))): ?>
        <?= $membership->getStatus()->badge() ?>
    <?php endif; ?>
</h1>

<div class="row">
    <?= form_open_multipart("organisation/{$organisation->getId()}/edit") ?>

    <div class="form-group row mb-3">
        <label for="inputName" class="col-form-label col-md-4 col-lg-3">Name</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputName" name="name" autocomplete="name"
                   placeholder="Name" value="<?= $organisation->getName() ?>"
                <?= $currentUser->isAdmin() ? 'required' : 'disabled' ?>>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputRegion" class="col-form-label col-md-4 col-lg-3">Region</label>
        <div class="col-md-8 col-lg-9">
            <select class="form-select" id="inputRegion"
                    name="region" <?= $currentUser->isAdmin() && !$organisation->getParentId() ? 'required' : 'disabled' ?>>
                <?php foreach (getRegions() as $region): ?>
                    <option value="<?= $region->getId() ?>" <?= $region->getId() == $organisation->getRegionId() ? "selected" : "" ?>>
                        <?= $region->getName() ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputWebsite" class="col-form-label col-md-4 col-lg-3">Website</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputWebsite" name="websiteUrl" type="url"
                   placeholder="https://example.com"
                   value="<?= $organisation->getWebsiteUrl() ?>" <?= $currentUser->isAdmin() ? '' : 'disabled' ?>>
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputLogo" class="col-form-label col-md-4 col-lg-3">Logo (ca. 512x128 | max. 1MB)</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputLogo" name="logo" type="file"
                   accept="image/png, image/jpg, image/jpeg, image/gif, image/webp, image/svg+xml">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputLogoAuthor" class="col-form-label col-md-4 col-lg-3">Author des Logos</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputLogoAuthor" name="logoAuthor" type="text"
                   value="<?= getImageAuthorById($organisation->getLogoId()) ?>">
        </div>
    </div>

    <div class=" form-group row mb-3">
        <label for="inputImage" class="col-form-label col-md-4 col-lg-3">Bild (ca. 1920x1080 | max. 2MB)</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputImage" name="image" type="file"
                   accept="image/png, image/jpg, image/jpeg, image/gif, image/webp">
        </div>
    </div>

    <div class="form-group row mb-3">
        <label for="inputImageAuthor" class="col-form-label col-md-4 col-lg-3">Author des Bildes</label>
        <div class="col-md-8 col-lg-9">
            <input class="form-control" id="inputImageAuthor" name="imageAuthor" type="text"
                   value="<?= getImageAuthorById($organisation->getImageId()) ?>">
        </div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Beschreibung</label>
        <textarea class="form-control" id="description"
                  name="description"><?= $organisation->getDescription() ?></textarea>
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