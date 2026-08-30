<?php $schoolName = setting('school_name', 'Bluefield International School'); ?>
<!doctype html>
<html lang="en" data-theme="auto" style="<?= e(brand_css()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($schoolName) ?> - Admission Portal</title>
    <?php if (setting('favicon')): ?><link rel="icon" href="<?= url('uploads/' . setting('favicon')) ?>"><?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark app-nav sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= url() ?>">
            <?php if (setting('school_logo')): ?><img class="nav-logo" src="<?= url('uploads/' . setting('school_logo')) ?>" alt="<?= e($schoolName) ?>"><?php else: ?><span class="logo-mark">S</span><?php endif; ?> <?= e($schoolName) ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="<?= url('apply') ?>">Apply</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('track') ?>">Track</a></li>
                <li class="nav-item"><button class="btn btn-sm btn-outline-light ms-lg-3" id="themeToggle" type="button">Theme</button></li>
            </ul>
        </div>
    </div>
</nav>
<main>
    <div class="container mt-3">
        <?php foreach (flashes() as $flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>
    </div>
    <?= $content ?>
</main>
<footer class="footer-band py-4 mt-5">
    <div class="container d-flex flex-column flex-md-row justify-content-between gap-2">
        <span>&copy; <?= date('Y') ?> <?= e($schoolName) ?></span>
        <span><?= e(setting('school_phone', '+234 800 000 0000')) ?> | <?= e(setting('school_email', 'info@school.test')) ?></span>
    </div>
    <div class="powered-credit">Powered by SkySaving Tech Hub</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>
