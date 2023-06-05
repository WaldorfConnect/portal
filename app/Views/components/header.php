<!DOCTYPE html>
<html lang="<?= service('request')->getLocale(); ?>" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <title><?= lang('app.name.full') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="<?= lang('app.description') ?>"/>
    <meta name="referrer" content="no-referrer">

    <meta property="og:url" content="<?= base_url('/') ?>"/>
    <meta property="og:title" content="<?= lang('app.name.full') ?>"/>
    <meta property="og:description" content="<?= lang('app.description') ?>"/>
    <meta property="og:image" content="<?= base_url('/') ?>/assets/img/logo.png"/>
    <meta property="og:type" content="website"/>
    <meta property="og:locale" content="<?= service('request')->getLocale(); ?>"/>

    <link href="<?= base_url('/') ?>/assets/css/style.css" rel="stylesheet">
    <link href="<?= base_url('/') ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('/') ?>/assets/img/logo.png" rel="icon">
    <link href="<?= base_url('/') ?>/assets/css/fontawesome.min.css" rel="stylesheet">
    <link href="<?= base_url('/') ?>/assets/css/bootstrap-table.min.css" rel="stylesheet">
    <link href="<?= base_url('/') ?>/assets/css/summernote-bs5.min.css" rel="stylesheet">

    <script src="<?= base_url('/') ?>/assets/js/jquery.min.js"></script>
    <script src="<?= base_url('/') ?>/assets/js/popper.min.js"></script>
    <script src="<?= base_url('/') ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('/') ?>/assets/js/bootstrap-table.min.js"></script>
    <script src="<?= base_url('/') ?>/assets/js/bootstrap-table-cookies.min.js" type="application/javascript"></script>
    <script src="<?= base_url('/') ?>/assets/js/bootstrap-table-locale-all.min.js"></script>
    <script src="<?= base_url('/') ?>/assets/js/summernote-bs5.min.js"></script>
</head>

<body>