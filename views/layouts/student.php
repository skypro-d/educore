<!doctype html>
<html lang="en" style="<?= e(brand_css()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Portal — <?= e(setting('school_name', APP_NAME)) ?></title>
    <?php if (setting('favicon')): ?><link rel="icon" href="<?= url('uploads/' . setting('favicon')) ?>"><?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
    <style>
        :root {
            --student-primary: var(--brand-primary, #0b3d91);
            --student-sidebar: var(--brand-sidebar, #061a40);
            --student-accent: var(--brand-secondary, #f4b942);
        }
        body { font-family: 'Inter', sans-serif; background: #f0f4f8; }
        .student-shell { display: flex; min-height: 100vh; }
        .student-sidebar {
            width: 260px; min-height: 100vh; background: var(--student-sidebar);
            color: #c8d6e5; display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; z-index: 100;
            transition: transform .3s;
        }
        .student-sidebar .brand {
            padding: 24px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,.1);
            display: flex; align-items: center; gap: 12px;
        }
        .student-sidebar .brand .logo-icon {
            width: 42px; height: 42px; border-radius: 10px;
            background: var(--student-accent); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 700; flex-shrink: 0;
        }
        .student-sidebar .brand .school-name { font-size: 13px; font-weight: 600; color: #fff; line-height: 1.3; }
        .student-sidebar .brand .portal-label { font-size: 11px; color: rgba(255,255,255,.5); }
        .student-nav { padding: 16px 0; flex: 1; }
        .student-nav .nav-label { font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: rgba(255,255,255,.35); padding: 12px 20px 4px; }
        .student-nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 20px; color: rgba(255,255,255,.7);
            text-decoration: none; font-size: 13.5px; font-weight: 500;
            border-left: 3px solid transparent; transition: all .2s;
        }
        .student-nav a:hover, .student-nav a.active {
            background: rgba(255,255,255,.08);
            color: #fff; border-left-color: var(--student-accent);
        }
        .student-nav a i { font-size: 17px; width: 20px; }
        .student-sidebar-footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.1); }
        .student-sidebar-footer .student-name { font-size: 12px; color: #fff; font-weight: 600; }
        .student-sidebar-footer .student-role { font-size: 11px; color: rgba(255,255,255,.45); }

        .student-main { margin-left: 260px; flex: 1; min-width: 0; }
        .student-topbar {
            background: #fff; border-bottom: 1px solid #e8eef4;
            padding: 0 28px; height: 60px; display: flex;
            align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .student-topbar .page-title { font-size: 16px; font-weight: 600; color: #1a2535; }
        .student-content { padding: 28px; }

        .student-mobile-bar {
            display: none; background: var(--student-sidebar);
            padding: 12px 16px; align-items: center; justify-content: space-between;
        }
        .student-menu-toggle { background: none; border: none; color: #fff; font-size: 22px; cursor: pointer; }

        @media (max-width: 768px) {
            .student-sidebar { transform: translateX(-100%); }
            .student-sidebar.open { transform: translateX(0); }
            .student-main { margin-left: 0; }
            .student-mobile-bar { display: flex; }
            .student-content { padding: 16px; }
        }
    </style>
</head>
<body>
<?php $studentSession = $_SESSION['student'] ?? null; ?>
<?php $currentRoute  = trim($_GET['route'] ?? 'dashboard', '/'); ?>

<div class="student-shell">
<?php if ($studentSession): ?>
<aside class="student-sidebar" id="studentSidebar">
    <div class="brand">
        <?php $logoUrl = school_logo_url(); ?>
        <?php if ($logoUrl): ?>
            <img src="<?= e($logoUrl) ?>" alt="Logo" style="width:42px;height:42px;border-radius:10px;object-fit:contain;background:#fff;padding:2px;" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
            <div class="logo-icon" style="display:none;"><?= strtoupper(substr(setting('school_name', 'S'), 0, 1)) ?></div>
        <?php else: ?>
            <div class="logo-icon"><?= strtoupper(substr(setting('school_name', 'S'), 0, 1)) ?></div>
        <?php endif; ?>
        <div>
            <div class="school-name"><?= e(setting('school_name', 'School')) ?></div>
            <div class="portal-label">Student Portal</div>
        </div>
    </div>
    <nav class="student-nav">
        <div class="nav-label">General</div>
        <a class="<?= $currentRoute === 'dashboard' ? 'active' : '' ?>" href="<?= url('student/dashboard') ?>"><i class="ti ti-layout-dashboard"></i> Dashboard</a>
        <a class="<?= $currentRoute === 'timetable' ? 'active' : '' ?>" href="<?= url('student/timetable') ?>"><i class="ti ti-calendar"></i> Class Timetable</a>
        <a class="<?= $currentRoute === 'id-card' ? 'active' : '' ?>" href="<?= url('student/id-card') ?>"><i class="ti ti-id"></i> Student ID Card</a>
        
        <div class="nav-label">Alerts</div>
        <a class="<?= $currentRoute === 'notifications' ? 'active' : '' ?>" href="<?= url('student/notifications') ?>"><i class="ti ti-bell"></i> Notifications</a>
        
        <div class="nav-label">Session</div>
        <a href="<?= url('student/logout') ?>"><i class="ti ti-logout"></i> Logout</a>
    </nav>
    <div class="student-sidebar-footer">
        <div class="student-name"><i class="ti ti-user-circle" style="margin-right:5px"></i><?= e($studentSession['name']) ?></div>
        <div class="student-role"><?= e($studentSession['admission_no'] ?? '') ?></div>
    </div>
</aside>
<?php endif; ?>

<div class="student-main">
    <?php if ($studentSession): ?>
    <div class="student-mobile-bar">
        <button class="student-menu-toggle" id="studentMenuToggle" type="button"><i class="ti ti-menu-2"></i></button>
        <span style="color:#fff;font-weight:600;font-size:14px"><?= e(setting('school_name', 'Student Portal')) ?></span>
        <a href="<?= url('student/logout') ?>" style="color:rgba(255,255,255,.7);font-size:13px">Logout</a>
    </div>
    <?php endif; ?>

    <?php foreach (flashes() as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>" style="margin:12px 28px 0;border-radius:8px;">
            <?= e($flash['message']) ?>
        </div>
    <?php endforeach; ?>

    <div class="student-content">
        <?= $content ?>
    </div>
    <div style="text-align:center;padding:20px;font-size:12px;color:#aaa;">Powered by SkySaving Tech Hub</div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('studentMenuToggle')?.addEventListener('click', () => {
    document.getElementById('studentSidebar')?.classList.toggle('open');
});
</script>
</body>
</html>
