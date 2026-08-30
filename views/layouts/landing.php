<?php $schoolName = setting('school_name', 'Westfield Academy'); ?>
<!doctype html>
<html lang="en" style="<?= e(brand_css()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($schoolName) ?> - Admissions <?= date('Y') ?>/<?= (int) date('y') + 1 ?></title>
    <?php if (setting('favicon')): ?><link rel="icon" href="<?= url('uploads/' . setting('favicon')) ?>"><?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400;1,700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body class="landing-body">
    <div class="landing-cursor" id="landingCursor"></div>
    <div class="landing-cursor-ring" id="landingCursorRing"></div>
    <?= $content ?>
    <div class="powered-credit landing-powered-credit">Powered by SkySaving Tech Hub</div>
    <script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>
