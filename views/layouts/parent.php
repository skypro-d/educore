<!doctype html>
<html lang="en" style="<?= e(brand_css()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Parent Portal — <?= e(setting('school_name', APP_NAME)) ?></title>
    <?php if (setting('favicon')): ?><link rel="icon" href="<?= url('uploads/' . setting('favicon')) ?>"><?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
    <style>
        :root {
            --parent-primary: var(--brand-primary, #0b3d91);
            --parent-sidebar: var(--brand-sidebar, #061a40);
            --parent-accent: var(--brand-secondary, #f4b942);
        }
        body { font-family: 'Inter', sans-serif; background: #f0f4f8; }
        .parent-shell { display: flex; min-height: 100vh; }
        .parent-sidebar {
            width: 260px; min-height: 100vh; background: var(--parent-sidebar);
            color: #c8d6e5; display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; z-index: 100;
            transition: transform .3s;
        }
        .parent-sidebar .brand {
            padding: 24px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,.1);
            display: flex; align-items: center; gap: 12px;
        }
        .parent-sidebar .brand .logo-icon {
            width: 42px; height: 42px; border-radius: 10px;
            background: var(--parent-accent); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 700; flex-shrink: 0;
        }
        .parent-sidebar .brand .school-name { font-size: 13px; font-weight: 600; color: #fff; line-height: 1.3; }
        .parent-sidebar .brand .portal-label { font-size: 11px; color: rgba(255,255,255,.5); }
        .parent-nav { padding: 16px 0; flex: 1; }
        .parent-nav .nav-label { font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: rgba(255,255,255,.35); padding: 12px 20px 4px; }
        .parent-nav a {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 20px; color: rgba(255,255,255,.7);
            text-decoration: none; font-size: 13.5px; font-weight: 500;
            border-left: 3px solid transparent; transition: all .2s;
        }
        .parent-nav a:hover, .parent-nav a.active {
            background: rgba(255,255,255,.08);
            color: #fff; border-left-color: var(--parent-accent);
        }
        .parent-nav a i { font-size: 17px; width: 20px; }
        .parent-sidebar-footer { padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.1); }
        .parent-sidebar-footer .parent-name { font-size: 12px; color: #fff; font-weight: 600; }
        .parent-sidebar-footer .parent-role { font-size: 11px; color: rgba(255,255,255,.45); }

        .parent-main { margin-left: 260px; flex: 1; min-width: 0; }
        .parent-topbar {
            background: #fff; border-bottom: 1px solid #e8eef4;
            padding: 0 28px; height: 60px; display: flex;
            align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .parent-topbar .page-title { font-size: 16px; font-weight: 600; color: #1a2535; }
        .parent-topbar .topbar-actions { display: flex; align-items: center; gap: 12px; }
        .parent-content { padding: 28px; }

        .parent-mobile-bar {
            display: none; background: var(--parent-sidebar);
            padding: 12px 16px; align-items: center; justify-content: space-between;
        }
        .parent-menu-toggle { background: none; border: none; color: #fff; font-size: 22px; cursor: pointer; }

        @media (max-width: 768px) {
            .parent-sidebar { transform: translateX(-100%); }
            .parent-sidebar.open { transform: translateX(0); }
            .parent-main { margin-left: 0; }
            .parent-mobile-bar { display: flex; }
            .parent-content { padding: 16px; }
        }
    </style>
</head>
<body>
<?php $parentSession = $_SESSION['parent'] ?? null; ?>
<?php $currentRoute  = trim($_GET['route'] ?? 'dashboard', '/'); ?>

<div class="parent-shell">
<?php if ($parentSession): ?>
<aside class="parent-sidebar" id="parentSidebar">
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
            <div class="portal-label">Parent Portal</div>
        </div>
    </div>
    <nav class="parent-nav">
        <div class="nav-label">Overview</div>
        <a class="<?= $currentRoute === 'dashboard' ? 'active' : '' ?>" href="<?= url('parent/dashboard') ?>"><i class="ti ti-layout-dashboard"></i> Dashboard</a>
        <a class="<?= $currentRoute === 'child' ? 'active' : '' ?>" href="<?= url('parent/child') ?>"><i class="ti ti-user"></i> Child Profile</a>
        <div class="nav-label">Academics</div>
        <a class="<?= $currentRoute === 'results' ? 'active' : '' ?>" href="<?= url('parent/results') ?>"><i class="ti ti-report-analytics"></i> Results</a>
        <a class="<?= $currentRoute === 'attendance' ? 'active' : '' ?>" href="<?= url('parent/attendance') ?>"><i class="ti ti-calendar-check"></i> Attendance</a>
        <a class="<?= $currentRoute === 'timetable' ? 'active' : '' ?>" href="<?= url('parent/timetable') ?>"><i class="ti ti-calendar"></i> Child Timetable</a>
        <a class="<?= $currentRoute === 'id-card' ? 'active' : '' ?>" href="<?= url('parent/id-card') ?>"><i class="ti ti-id"></i> Child ID Card</a>
        
        <div class="nav-label">Finance</div>
        <a class="<?= $currentRoute === 'fees' ? 'active' : '' ?>" href="<?= url('parent/fees') ?>"><i class="ti ti-receipt"></i> School Fees</a>
        
        <div class="nav-label">Communication</div>
        <a class="<?= $currentRoute === 'notifications' ? 'active' : '' ?>" href="<?= url('parent/notifications') ?>"><i class="ti ti-bell"></i> Notifications</a>
        <a class="<?= $currentRoute === 'announcements' ? 'active' : '' ?>" href="<?= url('parent/announcements') ?>"><i class="ti ti-speakerphone"></i> Announcements</a>
        <a href="<?= url('parent/logout') ?>"><i class="ti ti-logout"></i> Logout</a>
    </nav>
    <div class="parent-sidebar-footer">
        <div class="parent-name"><i class="ti ti-user-circle" style="margin-right:5px"></i><?= e($parentSession['name']) ?></div>
        <div class="parent-role"><?= e($parentSession['app_number'] ?? '') ?></div>
    </div>
</aside>
<?php endif; ?>

<div class="parent-main">
    <?php if ($parentSession): ?>
    <div class="parent-mobile-bar">
        <button class="parent-menu-toggle" id="parentMenuToggle" type="button"><i class="ti ti-menu-2"></i></button>
        <span style="color:#fff;font-weight:600;font-size:14px"><?= e(setting('school_name', 'Parent Portal')) ?></span>
        <a href="<?= url('parent/logout') ?>" style="color:rgba(255,255,255,.7);font-size:13px">Logout</a>
    </div>
    <?php endif; ?>

    <?php foreach (flashes() as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>" style="margin:12px 28px 0;border-radius:8px;">
            <?= e($flash['message']) ?>
        </div>
    <?php endforeach; ?>

    <?= $content ?>
    <div style="text-align:center;padding:20px;font-size:12px;color:#aaa;">Powered by SkySaving Tech Hub</div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('parentMenuToggle')?.addEventListener('click', () => {
    document.getElementById('parentSidebar')?.classList.toggle('open');
});
</script>
</body>
</html>
