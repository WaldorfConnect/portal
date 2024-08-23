<?php

use function App\Helpers\getGroups;

?>
<div id="osm-map"></div>

<script>
    let element = document.getElementById('osm-map');
    element.style = 'height:<?= $height ?>px;';
    let map = L.map(element);
    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    <?php foreach (getGroups() as $group): ?>
    <?php if (!$group->getLatitude() || !$group->getLongitude()): continue; endif; ?>
    L.marker([<?= $group->getLatitude()?>, <?= $group->getLongitude()?>]).bindPopup('<b><?= addslashes($group->getUrl()) ?></b><p><?= addslashes($group->getAddress()) ?></p>').addTo(map);
    <?php if(isset($targetGroup) && $targetGroup->getId() == $group->getId()): ?>
    map.setView(L.latLng(<?= $group->getLatitude()?>, <?= $group->getLongitude()?>), 18);
    <?php endif; ?>
    <?php endforeach; ?>

    <?php if(!isset($targetGroup)): ?>
    map.setView(L.latLng(<?= getenv('map.defaultLatitude') ?>, <?= getenv('map.defaultLongitude') ?>), 5);
    <?php endif; ?>
</script>