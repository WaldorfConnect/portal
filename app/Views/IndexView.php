<div class="row mt-3 justify-content-center">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-ticket"></i> <?= lang('index.voucher.title') ?>
                </div>
            </div>
            <div class="card-body">
                <pre><?= print_r(\App\Helpers\getCurrentUser()->groups[0]->members) ?></pre>
            </div>
        </div>
    </div>
</div>