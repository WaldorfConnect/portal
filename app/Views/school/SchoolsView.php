<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            Schulen
        </li>
    </ol>
</nav>

<h1 class="header">Alle Schulen</h1>

<?php if ($success = session('error')): ?>
    <div class="col-md-12">
        <div class="alert alert-danger">
            <?= $success ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($success = session('success')): ?>
    <div class="col-md-12">
        <div class="alert alert-success">
            <?= $success ?>
        </div>
    </div>
<?php endif; ?>

<?php use function App\Helpers\countUsersBySchoolId;
use function App\Helpers\getRegions;
use function App\Helpers\getSchoolsByRegionId;

foreach (getRegions() as $region): ?>
    <?php $schools = getSchoolsByRegionId($region->getId()) ?>
    <?php if (empty($schools)): continue; endif; ?>

    <div class="accordion accordion-flush" id="region<?= $region->getId() ?>">
        <div class="accordion-item">
            <h2 class="accordion-header" id="regionhead<?= $region->getId() ?>">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#regioncollapse<?= $region->getId() ?>"
                        aria-expanded="true" aria-controls="regioncollapse<?= $region->getId() ?>">
                    <?= $region->getName() ?>&nbsp;
                </button>
            </h2>
            <div id="regioncollapse<?= $region->getId() ?>" class="accordion-collapse collapse"
                 aria-labelledby="regionhead<?= $region->getId() ?>"
                 data-bs-parent="#region<?= $region->getId() ?>">
                <div class="accordion-body">
                    <ul>
                        <?php foreach ($schools as $school): ?>
                            <li>
                                <a class="me-2" href="<?= base_url('school/' . $school->getId()) ?>">
                                    <?= $school->getName() ?>
                                </a>
                                <?php if (($count = countUsersBySchoolId($school->getId())) == 0): ?>
                                    <span class="badge bg-danger">
                                Keine Mitglieder
                            </span>
                                <?php elseif ($count == 1): ?>
                                    <span class="badge bg-success">
                                ein Mitglied
                            </span>
                                <?php else: ?>
                                    <span class="badge bg-success">
                                <?= $count ?> Mitglieder
                            </span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>




