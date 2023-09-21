<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            Administration
        </li>
    </ol>
</nav>

<h1 class="header">Administration</h1>

<?php $user = \App\Helpers\getCurrentUser() ?>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3">
    <div class="col-md-4">
        <div class="card text-bg-danger mb-3">
            <div class="card-header text-center">Deine Rolle</div>
            <div class="card-body">
                <h5 class="card-title text-center"><?= $user->getRole()->displayName() ?></h5>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-secondary mb-3">
            <div class="card-header text-center">Nutzer gesamt</div>
            <div class="card-body">
                <h5 class="card-title text-center">100</h5>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-bg-secondary mb-3">
            <div class="card-header text-center">Nutzer im Zuständigkeitsbereich</div>
            <div class="card-body">
                <h5 class="card-title text-center">4</h5>
            </div>
        </div>
    </div>
</div>
