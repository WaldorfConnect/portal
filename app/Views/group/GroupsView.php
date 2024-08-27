<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            Gruppen
        </li>
    </ol>
</nav>

<h1 class="header">Alle Gruppen</h1>

<?php use function App\Helpers\countMembers;
use function App\Helpers\getParentGroupsByRegionId;
use function App\Helpers\getRegions;

foreach (getRegions() as $region): ?>
    <?php $groups = getParentGroupsByRegionId($region->getId()) ?>
    <?php if (empty($groups)): continue; endif; ?>

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
                    <?php foreach ($groups as $group): ?>
                        <li>
                            <a class="me-2" href="<?= base_url('group/' . $group->getId()) ?>">
                                <?= $group->getName() ?>
                            </a>
                            <?php if (($count = countMembers($group->getId())) == 0): ?>
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
<?php endforeach; ?>