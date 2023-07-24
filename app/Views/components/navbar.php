<?php

use function App\Helpers\getCurrentUser;

?>
<nav class="navbar navbar-expand-md navbar-light navbar-expand-lg bg-white border-bottom fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('/') ?>">
            <img class="navbar-brand-logo" src="<?= base_url('/') ?>/assets/img/banner.svg" alt="Logo WaldorfConnect">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMobileToggle"
                aria-controls="navbarMobileToggle" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMobileToggle">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0"></ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <?= getCurrentUser()->getName() ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="<?= base_url('user/profile') ?>">
                            Profil bearbeiten
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('logout') ?>">
                            Abmelden
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main>
    <div class="container">