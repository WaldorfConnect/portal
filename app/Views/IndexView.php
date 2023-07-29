<?php

use function App\Helpers\getCurrentUser;

?>
<h1 class="header">Willkommen</h1>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3">
    <div class="col mb-4">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                Dienste
            </div>
            <div class="card-body">
                <div class="d-grid gap-2 mx-auto">
                    <a class="btn btn-primary btn-lg" href="https://cloud.waldorfconnect.de/">
                        <i class="fas fa-cloud fa-3x"></i><br>Cloud
                    </a>
                    <a class="btn btn-primary btn-lg disabled" href="https://chat.waldorfconnect.de/">
                        <i class="fas fa-message fa-3x"></i><br>Chat
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col mb-4">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                Gruppen
            </div>
            <div class="card-body">
                <ul class="list-group">
                </ul>
            </div>
            <div class="card-footer footer-plain">
                <div class="mt-3 text-center">
                    <a class="btn btn-block btn-outline-primary" href="/groups">
                        Alle Gruppen anzeigen
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col mb-4">
        <div class="card shadow-sm">
            <div class="card-header text-center">
                Schule
            </div>
            <div class="card-body text-center">
                <h5><?= getCurrentUser()->getSchool()->getName() ?></h5>
            </div>
            <div class="card-footer footer-plain">
                <div class="text-center">
                    <a class="btn btn-block btn-outline-primary" href="/schools">
                        Alle Schulen anzeigen
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>