<div class="row gx-4 mt-3 justify-content-center">
    <div class="col-lg-12">
        <?php if ($error = session('error')): ?>
            <div class="alert alert-danger mb-3">
                <i class="fas fa-triangle-exclamation"></i> <?= lang($error) ?>
            </div>
        <?php endif; ?>
    </div>
</div>