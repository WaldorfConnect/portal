<?php

use function App\Helpers\getCurrentUser;

?>
<!DOCTYPE html>
<html lang="<?= service('request')->getLocale(); ?>">
<head>
    <meta charset="utf-8">
    <title>Portal &ndash; WaldorfConnect</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Wir vernetzen Waldorfschüler*innen!"/>
    <meta name="referrer" content="no-referrer">

    <meta property="og:url" content="<?= base_url('/') ?>"/>
    <meta property="og:title" content="Portal &ndash; WaldorfConnect"/>
    <meta property="og:description" content="Wir vernetzen Waldorfschüler*innen!"/>
    <meta property="og:image" content="<?= base_url('/') ?>assets/img/banner_small.png"/>
    <meta property="og:type" content="website"/>
    <meta property="og:locale" content="<?= service('request')->getLocale(); ?>"/>

    <link href="<?= base_url('/') ?>assets/css/style.css" rel="stylesheet">
    <link href="<?= base_url('/') ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('/') ?>assets/img/logo.png" rel="icon">
    <link href="<?= base_url('/') ?>assets/css/fontawesome.min.css" rel="stylesheet">
    <link href="<?= base_url('/') ?>assets/css/bootstrap-table.min.css" rel="stylesheet">
    <link href="<?= base_url('/') ?>assets/css/summernote-bs5.min.css" rel="stylesheet">

    <script src="<?= base_url('/') ?>assets/js/jquery.min.js"></script>
    <script src="<?= base_url('/') ?>assets/js/popper.min.js"></script>
    <script src="<?= base_url('/') ?>assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('/') ?>assets/js/bootstrap-table.min.js"></script>
    <script src="<?= base_url('/') ?>assets/js/bootstrap-table-cookies.min.js" type="application/javascript"></script>
    <script src="<?= base_url('/') ?>assets/js/bootstrap-table-locale-all.min.js"></script>
    <script src="<?= base_url('/') ?>assets/js/summernote-bs5.min.js"></script>

    <!-- Matomo -->
    <script>
        var _paq = window._paq = window._paq || [];
        /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function () {
            var u = "//matomo.elektronisch.dev/";
            _paq.push(['setTrackerUrl', u + 'matomo.php']);
            _paq.push(['setSiteId', '8']);

            <?php if($user = getCurrentUser()): ?>
            _paq.push(['setUserId', '<?= $user->getUsername() ?>'])
            <?php endif; ?>

            var d = document, g = d.createElement('script'), s = d.getElementsByTagName('script')[0];
            g.async = true;
            g.src = u + 'matomo.js';
            s.parentNode.insertBefore(g, s);
        })();
    </script>
    <!-- End Matomo Code -->
</head>

<body>