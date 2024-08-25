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
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            <?= $user->getName() ?>
        </li>
    </ol>
</nav>

<h1 class="header">
    <?= $user->getName() ?>
</h1>

<div class="row">
    <div class="col-lg-4 col-md-12 col-sm-12 text-center">
        <img class="img-thumbnail mb-3"
             src="<?= getImageUrlById($user->getImageId(), 'assets/img/user_400x400.webp') ?>"
             alt="Profilbild">

        <div class="card shadow-sm mt-3 mb-3">
            <div class="card-header text-center">
                Aktivität
            </div>
            <div class="card-body text-center">
                <b>Letzte Anmeldung:</b> <?= $user->getLastLoginDate()->format('d.m.Y H:i') ?>
            </div>
        </div>
    </div>
    <div class="col-lg-8 col-md-12 col-sm-12">
        <div class="card shadow-sm mb-3">
            <div class="card-header text-center">
                Informationen
            </div>
            <div class="card-body">
                <h4 class="text-center">Persönliche Angaben</h4>
                <table class="table table-borderless">
                    <tr>
                        <td class="text-end"><b>Vorname(n):</b></td>
                        <td><?= $user->getFirstName() ?></td>
                    </tr>
                    <tr>
                        <td class="text-end"><b>Nachname:</b></td>
                        <td><?= $user->getLastName() ?></td>
                    </tr>
                    <tr>
                        <td class="text-end"><b>Registrierungsdatum:</b></td>
                        <td><?= $user->getRegistrationDate()->format('d.m.Y H:i') ?></td>
                    </tr>
                </table>
                <!--<h4 class="text-center">Kontaktinformationen & soziale Medien</h4>-->
            </div>
        </div>
        <div class="card shadow-sm mb-3">
            <div class="card-header text-center">
                Gruppenmitgliedschaften
            </div>
            <div class="card-body">
                <?= view('group/MembershipListComponent', ['user' => $user]) ?>
            </div>
        </div>
    </div>
</div>