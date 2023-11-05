<?php

use function App\Helpers\getCronLog;
use function App\Helpers\getMails;

?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/">Startseite</a></li>
        <li class="breadcrumb-item"><a href="<?= base_url('/admin') ?>">Administration</a></li>
        <li class="breadcrumb-item active" aria-current="page">
            Debug
        </li>
    </ol>
</nav>

<h1 class="header">Debug</h1>

<h3 class="subheader">E-Mail-Warteschlange</h3>

<b>Nachrichten in der Warteschlange:</b> <?= count(getMails()) ?>
<br><br>

<h3 class="subheader">Protokoll</h3>

<label for="log">
    Protokoll der automatischen Synchronisation zw. Nextcloud und LDAP und der E-Mail-Versandbericht.
</label>
<textarea class="form-control log" id="log" rows="50" readonly>
    <?= getCronLog() ?>
</textarea>