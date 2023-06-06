<nav class="navbar navbar-expand-lg navbar-mst
 bg-dark">
    <div class="container">
        <a class="navbar-brand" href="<?= base_url('/') ?>">
            <img src="<?= base_url('/') ?>/assets/img/banner.png" width="15%" class="d-inline-block align-top"
                 alt="">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMobileToggle"
                aria-controls="navbarMobileToggle" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMobileToggle">
            <?php if ($user = \App\Helpers\getCurrentUser()): ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0"></ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user"></i> <?= $user->displayName ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="<?= base_url('logout') ?>"><i
                                            class="fas fa-sign-out-alt"></i> <?= lang('menu.self.logout') ?></a></li>
                        </ul>
                    </li>
                </ul>
            <?php else: ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0"></ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('login') ?>"><i
                                    class="fas fa-sign-in-alt"></i> <?= lang('menu.self.login') ?></a></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container px-4 mt-4">