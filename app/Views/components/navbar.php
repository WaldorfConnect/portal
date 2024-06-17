<?php

use function App\Helpers\countUnreadNotificationsByUserId;
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
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?= base_url('/admin/users') ?>">
                                Benutzer
                            </a>
                            <a class="dropdown-item" href="<?= base_url('/admin/organisations') ?>">
                                Organisationen
                            </a>
                            <a class="dropdown-item" href="<?= base_url('/admin/regions') ?>">
                                Regionen
                            </a>
                        </div>
                    </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= base_url('notifications') ?>">
                        <i class="fa fa-bell position-relative">
                            <?php if (($count = countUnreadNotificationsByUserId($user->getId())) > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $count ?>
                                    <span class="visually-hidden">offene Benachrichtigungen</span>
                                </span>
                            <?php endif; ?>
                        </i> Benachrichtigungen
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-user"></i> <?= $user->getName() ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                        <a class="dropdown-item" href="<?= base_url('user/profile') ?>">
                            <i class="fas fa-user-edit"></i> Profil bearbeiten
                        </a>
                        <a class="dropdown-item" href="<?= base_url('user/settings') ?>">
                            <i class="fas fa-cogs"></i> Einstellungen
                        </a>
                        <a class="dropdown-item" href="<?= base_url('user/security') ?>">
                            <i class="fas fa-shield"></i> Sicherheit
                        </a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= base_url('logout') ?>">
                            <i class="fas fa-sign-out"></i> Abmelden
                        </a>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="helpDropdown" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-question-circle"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="helpDropdown">
                        <a class="dropdown-item" href="https://wiki.waldorfconnect.de/" target="_blank">
                            <i class="fas fa-book-open"></i> Dokumentation
                        </a>
                        <a class="dropdown-item" href="https://waldorfconnect.de/support" target="_blank">
                            <i class="fas fa-headset"></i> Support
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main>
    <div class="container">

    <?php if ($errors = session('error')): ?>
        <div class="col-md-12">
            <div class="alert alert-danger">
                <?php if (is_array($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <?= esc($error) ?><br>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?= esc($errors) ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($success = session('success')): ?>
        <div class="col-md-12">
            <div class="alert alert-success">
                <?= esc($success) ?>
            </div>
        </div>
    <?php endif; ?>