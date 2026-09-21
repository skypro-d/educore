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

        .parent-sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(6, 26, 64, 0.65);
            backdrop-filter: blur(4px);
            z-index: 99;
        }

        .parent-mobile-bar {
            display: none; background: var(--parent-sidebar);
            padding: 12px 16px; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 50;
        }
        .parent-menu-toggle {
            background: rgba(255,255,255,.1);
            border: 1px solid rgba(255,255,255,.2);
            color: #fff;
            border-radius: 8px;
            width: 36px; height: 36px;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; cursor: pointer;
        }

        @media (max-width: 768px) {
            .parent-shell { flex-direction: column; }
            .parent-sidebar {
                transform: translateX(-100%);
                z-index: 1000;
                box-shadow: none;
            }
            .parent-sidebar.open {
                transform: translateX(0);
                box-shadow: 8px 0 25px rgba(0,0,0,0.4);
            }
            .parent-sidebar-overlay.open { display: block; }
            .parent-main { margin-left: 0; width: 100%; }
            .parent-mobile-bar { display: flex; }
            .parent-content { padding: 16px; }
        }
    </style>
</head>
<body>
<?php 
$parentSession = $_SESSION['parent'] ?? null; 
$currentRoute  = trim($_GET['route'] ?? 'dashboard', '/'); 
$allChildren   = $parentSession ? parent_linked_children() : [];
$activeChildId = (int) ($parentSession['applicant_id'] ?? 0);
?>

<div class="parent-sidebar-overlay" id="parentSidebarOverlay"></div>

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
        <div style="min-width:0; flex:1;">
            <div class="school-name text-truncate"><?= e(setting('school_name', 'School')) ?></div>
            <div class="portal-label">Parent Portal</div>
        </div>
        <button type="button" class="btn-close btn-close-white d-md-none ms-auto" id="parentSidebarClose" aria-label="Close menu" style="font-size:11px;"></button>
    </div>

    <?php if (count($allChildren) > 1): ?>
    <!-- Multi-Child Switcher in Sidebar -->
    <div style="padding: 12px 16px; border-bottom: 1px solid rgba(255,255,255,0.08);">
        <label style="font-size: 10px; font-weight: 700; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.8px; display: block; margin-bottom: 6px;">Active Child</label>
        <div class="dropdown">
            <button class="btn btn-sm w-100 text-start d-flex align-items-center justify-content-between" type="button" data-bs-toggle="dropdown" style="background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; font-size: 12.5px; padding: 6px 10px;">
                <span class="text-truncate me-1"><i class="ti ti-user me-1 text-warning"></i><?= e($parentSession['name']) ?></span>
                <i class="ti ti-chevron-down" style="font-size: 14px;"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-dark shadow w-100" style="font-size: 12.5px; border-radius: 8px; background: #0f2757; border: 1px solid rgba(255,255,255,0.1);">
                <li class="dropdown-header text-uppercase" style="font-size: 10px; color: rgba(255,255,255,0.5);">Switch Student</li>
                <?php foreach ($allChildren as $child): ?>
                    <li>
                        <a class="dropdown-item py-2 d-flex align-items-center justify-content-between <?= (int)$child['id'] === $activeChildId ? 'active bg-primary' : '' ?>" href="<?= url('parent/switch-child?id=' . $child['id']) ?>">
                            <div>
                                <div class="fw-bold"><?= e($child['first_name'] . ' ' . $child['last_name']) ?></div>
                                <div style="font-size: 11px; opacity: 0.75;"><?= e($child['class_name'] ?: 'Enrolled') ?> &bull; <?= e($child['admission_number'] ?: $child['application_number']) ?></div>
                            </div>
                            <?php if ((int)$child['id'] === $activeChildId): ?>
                                <i class="ti ti-check text-warning ms-2"></i>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <nav class="parent-nav">
        <div class="nav-label">Overview</div>
        <a class="<?= $currentRoute === 'dashboard' ? 'active' : '' ?>" href="<?= url('parent/dashboard') ?>"><i class="ti ti-layout-dashboard"></i> Dashboard</a>
        <a class="<?= $currentRoute === 'child' ? 'active' : '' ?>" href="<?= url('parent/child') ?>"><i class="ti ti-user"></i> Child Profile <?= count($allChildren) > 1 ? '<span class="badge bg-warning text-dark ms-auto" style="font-size:10px;">'.count($allChildren).'</span>' : '' ?></a>
        <div class="nav-label">Academics</div>
        <a class="<?= $currentRoute === 'results' ? 'active' : '' ?>" href="<?= url('parent/results') ?>"><i class="ti ti-report-analytics"></i> Results</a>
        <a class="<?= $currentRoute === 'attendance' ? 'active' : '' ?>" href="<?= url('parent/attendance') ?>"><i class="ti ti-calendar-check"></i> Attendance</a>
        <a class="<?= $currentRoute === 'timetable' ? 'active' : '' ?>" href="<?= url('parent/timetable') ?>"><i class="ti ti-calendar"></i> Child Timetable</a>
        
        <div class="nav-label">Finance</div>
        <a class="<?= $currentRoute === 'fees' ? 'active' : '' ?>" href="<?= url('parent/fees') ?>"><i class="ti ti-wallet"></i> School Fees</a>
        <a class="<?= $currentRoute === 'payment-history' ? 'active' : '' ?>" href="<?= url('parent/payment-history') ?>"><i class="ti ti-receipt"></i> Payment History</a>
        
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

    <?php if ($parentSession && count($allChildren) > 1): ?>
    <div class="bg-white border-bottom px-4 py-2 d-none d-md-flex align-items-center justify-content-between" style="font-size: 13px;">
        <div class="d-flex align-items-center gap-2 text-muted">
            <i class="ti ti-users text-primary"></i>
            <span>Viewing Student: <strong class="text-dark"><?= e($parentSession['name']) ?></strong> (<?= e($parentSession['app_number']) ?>)</span>
        </div>
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-primary dropdown-toggle py-1 px-3 fw-semibold rounded-pill" type="button" data-bs-toggle="dropdown" style="font-size: 12px;">
                <i class="ti ti-switch-horizontal me-1"></i> Switch Child (<?= count($allChildren) ?>)
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: 12.5px; border-radius: 10px;">
                <li class="dropdown-header text-uppercase" style="font-size: 10px;">Select Child Profile</li>
                <?php foreach ($allChildren as $child): ?>
                    <li>
                        <a class="dropdown-item py-2 d-flex align-items-center justify-content-between <?= (int)$child['id'] === $activeChildId ? 'active' : '' ?>" href="<?= url('parent/switch-child?id=' . $child['id']) ?>">
                            <div>
                                <div class="fw-bold"><?= e($child['first_name'] . ' ' . $child['last_name']) ?></div>
                                <div class="small opacity-75"><?= e($child['class_name'] ?: 'Enrolled') ?> &bull; <?= e($child['admission_number'] ?: $child['application_number']) ?></div>
                            </div>
                            <?php if ((int)$child['id'] === $activeChildId): ?>
                                <i class="ti ti-check ms-3"></i>
                            <?php endif; ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
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
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('parentMenuToggle');
    const sidebar = document.getElementById('parentSidebar');
    const overlay = document.getElementById('parentSidebarOverlay');
    const closeBtn = document.getElementById('parentSidebarClose');

    function openSidebar() {
        sidebar?.classList.add('open');
        overlay?.classList.add('open');
    }
    function closeSidebar() {
        sidebar?.classList.remove('open');
        overlay?.classList.remove('open');
    }

    if (toggle) {
        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            if (sidebar?.classList.contains('open')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
    }
    if (overlay) overlay.addEventListener('click', closeSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
});
</script>
</body>
</html>
