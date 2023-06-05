<div class="row gx-4 mt-3 justify-content-center">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <b><?= lang('login.headline') ?></b>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php if ($error = session('error')): ?>
                        <div class="alert alert-danger mb-3">
                            <i class="fas fa-triangle-exclamation"></i> <?= lang($error) ?>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="username" class="form-label"><?= lang('login.fields.name') ?></label>
                        <input class="form-control" id="username" name="username" aria-describedby="usernameHelp"
                               value="<?= session('username') ? session('username') : '' ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label"><?= lang('login.fields.password') ?></label>
                        <input type="password" class="form-control" id="password" name="password"
                               aria-describedby="passwordHelp" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><?= lang('login.buttons.login') ?></button>
                </form>
            </div>
        </div>
    </div>
</div>