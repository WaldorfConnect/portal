<?php

use function App\Helpers\getGroupsByRegionId;
use function App\Helpers\getRegions;
use function App\Helpers\getSchoolsByRegionId;

?>
<main>
    <div class="container login">
        <form method="post">
            <div class="card register">
                <div class="card-header header-plain">
                    <img class="mb-2 navbar-brand-logo" src="<?= base_url('/') ?>/assets/img/banner_small.png"
                         alt="WaldorfConnect Logo">
                    <h1 class="h2">Verifikation</h1>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <b>Konto verifiziert!</b> Dein Konto wurde erfolgreich verifiziert. Die
                        Administratoren deines Bundeslandes bzw. Regionalverbands und die Administratoren der Gruppen,
                        denen du beitreten möchtest, wurden nun benachrichtigt.<br><br>
                        Sobald deine Registrierungsanfrage von einer dieser Personen akzeptiert wird, ist dein Konto
                        einsatzbereit! <br>Wir werden dich hierzu umgehend benachrichtigen.
                    </div>
                </div>
            </div>
        </form>