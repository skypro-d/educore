<!doctype html>
<html lang="en" style="<?= e(brand_css()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher Portal — <?= e(setting('school_name', APP_NAME)) ?></title>
    <?php if (setting('favicon')): ?><link rel="icon" href="<?= url('uploads/' . setting('favicon')) ?>"><?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
    <style>
        :root {
            --teacher-primary: var(--brand-primary, #0f766e);
            --teacher-sidebar: var(--brand-sidebar, #111827);
            --teacher-accent: var(--brand-secondary, #14b8a6);
        }
        body { font-family: 'Inter', sans-serif; background: #f0f4f8; }
        .teacher-shell { display: flex; min-height: 100vh; }
        .teacher-sidebar {
            width: 260px; min-height: 100vh; background: var(--teacher-sidebar);
            color: #c8d6e5; display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; z-index: 100;
            transition: transform .3s;
        }
        .teacher-sidebar .brand {
            padding: 24px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,.1);
            display: flex; align-items: center; gap: 12px;
        }
        .teacher-sidebar .brand .logo-icon {
            width: 42px; height: 42px; border-radius: 10px;
            background: var(--teacher-accent); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 700; flex-shrink: 0;
        }
        .teacher-sidebar .brand .school-name { font-size: 13px; font-weight: 600; color: #fff; line-height: 1.3; }
        .teacher-sidebar .brand .portal-label { font-size: 11px; color: rgba(255,255,255,.5); }
        .teacher-nav { padding: 16px 0; flex: 1; }
        .teacher-nav .nav-label { font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: rgba(255,255,255,.35); padding: 12px 20px 4px; }
        .teacher-nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 20px; color: rgba(255,255,255,.7);
            text-decoration: none; font-size: 13.5px; font-weight: 500;
            border-left: 3px solid transparent; transition: all .2s;
        }
        .teacher-nav a:hover, .teacher-nav a.active {
            background: rgba(255,255,255,.08);
            color: #fff; border-left-color: var(--teacher-accent);
        }
        .teacher-nav a i { font-size: 17px; width: 20px; }
        .teacher-sidebar-footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.1); }
        .teacher-sidebar-footer .teacher-name { font-size: 12px; color: #fff; font-weight: 600; }
        .teacher-sidebar-footer .teacher-role { font-size: 11px; color: rgba(255,255,255,.45); }

        .teacher-main { margin-left: 260px; flex: 1; min-width: 0; }
        .teacher-topbar {
            background: #fff; border-bottom: 1px solid #e8eef4;
            padding: 0 28px; height: 60px; display: flex;
            align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .teacher-topbar .page-title { font-size: 16px; font-weight: 600; color: #1a2535; }
        .teacher-content { padding: 28px; }

        .teacher-mobile-bar {
            display: none; background: var(--teacher-sidebar);
            padding: 12px 16px; align-items: center; justify-content: space-between;
        }
        .teacher-menu-toggle { background: none; border: none; color: #fff; font-size: 22px; cursor: pointer; }

        @media (max-width: 768px) {
            .teacher-sidebar { transform: translateX(-100%); }
            .teacher-sidebar.open { transform: translateX(0); }
            .teacher-main { margin-left: 0; }
            .teacher-mobile-bar { display: flex; }
            .teacher-content { padding: 16px; }
        }
    </style>
</head>
<body>
<?php $teacherSession = $_SESSION['teacher'] ?? null; ?>
<?php $currentRoute  = trim($_GET['route'] ?? 'dashboard', '/'); ?>

<div class="teacher-shell">
<?php if ($teacherSession): ?>
<aside class="teacher-sidebar" id="teacherSidebar">
    <div class="brand">
        <?php if (setting('school_logo')): ?>
            <img src="<?= url('uploads/' . setting('school_logo')) ?>" alt="Logo" style="width:42px;height:42px;border-radius:10px;object-fit:cover;">
        <?php else: ?>
            <div class="logo-icon"><?= strtoupper(substr(setting('school_name', 'S'), 0, 1)) ?></div>
        <?php endif; ?>
        <div>
            <div class="school-name"><?= e(setting('school_name', 'School')) ?></div>
            <div class="portal-label">Teacher Portal</div>
        </div>
    </div>
    <nav class="teacher-nav">
        <div class="nav-label">General</div>
        <a class="<?= $currentRoute === 'dashboard' ? 'active' : '' ?>" href="<?= url('teacher/dashboard') ?>"><i class="ti ti-layout-dashboard"></i> Dashboard</a>
        <a class="<?= $currentRoute === 'class-list' ? 'active' : '' ?>" href="<?= url('teacher/class-list') ?>"><i class="ti ti-users"></i> Class Assignments</a>
        
        <div class="nav-label">Academics</div>
        <a class="<?= $currentRoute === 'attendance' ? 'active' : '' ?>" href="<?= url('teacher/attendance') ?>"><i class="ti ti-calendar-check"></i> Take Attendance</a>
        <a class="<?= $currentRoute === 'results' ? 'active' : '' ?>" href="<?= url('teacher/results') ?>"><i class="ti ti-report-analytics"></i> Manage Results</a>
        
        <div class="nav-label">Session</div>
        <a href="<?= url('teacher/logout') ?>"><i class="ti ti-logout"></i> Logout</a>
    </nav>
    <div class="teacher-sidebar-footer">
        <div class="teacher-name"><i class="ti ti-user-circle" style="margin-right:5px"></i><?= e($teacherSession['name']) ?></div>
        <div class="teacher-role"><?= e($teacherSession['role'] ?? 'Teacher') ?> (<?= e($teacherSession['staff_id'] ?? '') ?>)</div>
    </div>
</aside>
<?php endif; ?>

<div class="teacher-main">
    <?php if ($teacherSession): ?>
    <div class="teacher-mobile-bar">
        <button class="teacher-menu-toggle" id="teacherMenuToggle" type="button"><i class="ti ti-menu-2"></i></button>
        <span style="color:#fff;font-weight:600;font-size:14px"><?= e(setting('school_name', 'Teacher Portal')) ?></span>
        <a href="<?= url('teacher/logout') ?>" style="color:rgba(255,255,255,.7);font-size:13px">Logout</a>
    </div>
    <?php endif; ?>

    <?php foreach (flashes() as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>" style="margin:12px 28px 0;border-radius:8px;">
            <?= e($flash['message']) ?>
        </div>
    <?php endforeach; ?>

    <div class="teacher-content">
        <?= $content ?>
    </div>
    <div style="text-align:center;padding:20px;font-size:12px;color:#aaa;">Powered by SkySaving Tech Hub</div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('teacherMenuToggle')?.addEventListener('click', () => {
    document.getElementById('teacherSidebar')?.classList.toggle('open');
});
</script>
</body>
</html>
