<?php

use function App\Helpers\getCurrentUser;

?>
<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('/') ?>">
            <img class="navbar-brand-logo" src="<?= base_url('/') ?>assets/img/banner_small.png"
                 alt="Logo WaldorfConnect">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMobileToggle"
                aria-controls="navbarMobileToggle" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMobileToggle">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0"></ul>
            <ul class="navbar-nav">
                <?php if (($user = getCurrentUser())->isAdmin()): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-gauge"></i> Admin
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <a class="dropdown-item" href="<?= base_url('/admin') ?>">
                                Dashboard
                            </a>
                            <?php if ($user->isAdmin()): ?>
                                <a class="dropdown-item" href="<?= base_url('/admin/debug') ?>">
                                    Debug
                                </a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?= base_url('/admin/users') ?>">
                                Benutzer
                            </a>
                            <?php if ($user->isAdmin()): ?>
                                <a class="dropdown-item" href="<?= base_url('/admin/organisations') ?>">
                                    Organisationen
                                </a>
                            <?php endif; ?>
                            <?php if ($user->isAdmin()): ?>
                                <a class="dropdown-item" href="<?= base_url('/admin/regions') ?>">
                                    Regionen
                                </a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endif; ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-bell"></i> Mitteilungen
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">

                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-user"></i> <?= $user->getName() ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
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