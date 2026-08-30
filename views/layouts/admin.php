<!doctype html>
<html lang="en" data-theme="auto" style="<?= e(brand_css()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - <?= e(setting('school_name', APP_NAME)) ?></title>
    <?php if (setting('favicon')): ?><link rel="icon" href="<?= url('uploads/' . setting('favicon')) ?>"><?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body class="admin-shell">
<button class="admin-sidebar-overlay" id="adminSidebarOverlay" type="button" aria-label="Close admin menu"></button>
<aside class="sidebar" id="adminSidebar">
    <?php $current = trim($_GET['route'] ?? 'dashboard', '/'); ?>
    <div class="brand sidebar-logo">
        <div class="icon"><i class="ti ti-school" data-fallback="S"></i></div>
        <span><?= e(setting('school_name', 'Admissions')) ?></span>
        <button class="sidebar-close" id="adminSidebarClose" type="button" aria-label="Close admin menu"><span aria-hidden="true">X</span></button>
    </div>
    <div class="nav-section">Main</div>
    <a class="nav-item <?= $current === 'dashboard' ? 'active' : '' ?>" href="<?= url('admin/dashboard') ?>"><i class="ti ti-layout-dashboard" data-fallback="D"></i> Dashboard</a>
    <a class="nav-item" href="<?= e(school_website_url()) ?>" target="_blank" rel="noopener noreferrer"><i class="ti ti-world" data-fallback="W"></i> View School Website</a>
    <a class="nav-item <?= str_starts_with($current, 'applications') ? 'active' : '' ?>" href="<?= url('admin/applications') ?>"><i class="ti ti-forms" data-fallback="A"></i> Applications</a>
    <a class="nav-item" href="<?= url('admin/applications?status=Enrolled') ?>"><i class="ti ti-user-check" data-fallback="E"></i> Enrolled students</a>
    <a class="nav-item <?= $current === 'interviews' ? 'active' : '' ?>" href="<?= url('admin/interviews') ?>"><i class="ti ti-calendar-event" data-fallback="I"></i> Interviews</a>
    
    <div class="nav-section">Academics &amp; Class</div>
    <a class="nav-item <?= $current === 'subjects' ? 'active' : '' ?>" href="<?= url('admin/subjects') ?>"><i class="ti ti-book" data-fallback="S"></i> Subject Setup</a>
    <a class="nav-item <?= $current === 'results' ? 'active' : '' ?>" href="<?= url('admin/results') ?>"><i class="ti ti-notebook" data-fallback="R"></i> Enter Scores &amp; CA</a>
    <a class="nav-item <?= $current === 'attendance' ? 'active' : '' ?>" href="<?= url('admin/attendance') ?>"><i class="ti ti-calendar-check" data-fallback="At"></i> Daily Attendance</a>
    <a class="nav-item <?= $current === 'attendance-report' ? 'active' : '' ?>" href="<?= url('admin/attendance-report') ?>"><i class="ti ti-file-analytics" data-fallback="Ar"></i> Attendance Report</a>
    <a class="nav-item <?= $current === 'attendance-settings' ? 'active' : '' ?>" href="<?= url('admin/attendance-settings') ?>"><i class="ti ti-settings-automation" data-fallback="As"></i> Attendance Settings</a>
    <a class="nav-item <?= $current === 'promotion' ? 'active' : '' ?>" href="<?= url('admin/promotion') ?>"><i class="ti ti-arrows-double-ne-sw" data-fallback="Pr"></i> Student Promotion</a>

    <div class="nav-section">Gate &amp; Exit Security</div>
    <a class="nav-item <?= $current === 'exit-scanner' ? 'active' : '' ?>" href="<?= url('admin/exit-scanner') ?>"><i class="ti ti-door-exit" data-fallback="Ex"></i> Student Exit Scanner</a>
    <a class="nav-item <?= $current === 'exit-logs' ? 'active' : '' ?>" href="<?= url('admin/exit-logs') ?>"><i class="ti ti-history" data-fallback="El"></i> Exit Logs &amp; Movement</a>
    <a class="nav-item <?= $current === 'gates' ? 'active' : '' ?>" href="<?= url('admin/gates') ?>"><i class="ti ti-barrier-block" data-fallback="Gt"></i> School Gates</a>
    <a class="nav-item <?= $current === 'authorized-pickups' ? 'active' : '' ?>" href="<?= url('admin/authorized-pickups') ?>"><i class="ti ti-user-shield" data-fallback="Pu"></i> Authorized Pickups</a>

    <div class="nav-section">Finance</div>
    <a class="nav-item <?= $current === 'payments' ? 'active' : '' ?>" href="<?= url('admin/payments') ?>"><i class="ti ti-credit-card" data-fallback="Ap"></i> Admission Payments</a>
    <a class="nav-item <?= $current === 'student-fees' ? 'active' : '' ?>" href="<?= url('admin/student-fees') ?>"><i class="ti ti-receipt" data-fallback="Sf"></i> School Term Fees</a>
    <a class="nav-item <?= $current === 'fee-structures' ? 'active' : '' ?>" href="<?= url('admin/fee-structures') ?>"><i class="ti ti-settings-automation" data-fallback="Fs"></i> Fee Structures</a>

    <div class="nav-section">Administration</div>
    <a class="nav-item <?= $current === 'staff' ? 'active' : '' ?>" href="<?= url('admin/staff') ?>"><i class="ti ti-users" data-fallback="St"></i> Staff Directory</a>
    <a class="nav-item <?= $current === 'classes' ? 'active' : '' ?>" href="<?= url('admin/classes') ?>"><i class="ti ti-building" data-fallback="C"></i> Classes &amp; Capacity</a>
    <a class="nav-item <?= $current === 'communications' ? 'active' : '' ?>" href="<?= url('admin/communications') ?>"><i class="ti ti-message-2" data-fallback="Co"></i> Communication Center</a>
    <a class="nav-item <?= $current === 'devices' ? 'active' : '' ?>" href="<?= url('admin/devices') ?>"><i class="ti ti-device-nfc" data-fallback="Dev"></i> POS Scanner Devices</a>
    <a class="nav-item <?= $current === 'updates' ? 'active' : '' ?>" href="<?= url('admin/updates') ?>"><i class="ti ti-cloud-download" data-fallback="Up"></i> Updates &amp; Upgrades</a>

    <div class="nav-section">Facilities (Stubs)</div>
    <a class="nav-item <?= $current === 'library' ? 'active' : '' ?>" href="<?= url('admin/library') ?>"><i class="ti ti-books" data-fallback="L"></i> Library Catalog</a>
    <a class="nav-item <?= $current === 'transport' ? 'active' : '' ?>" href="<?= url('admin/transport') ?>"><i class="ti ti-bus" data-fallback="T"></i> Transport Routes</a>
    <a class="nav-item <?= $current === 'inventory' ? 'active' : '' ?>" href="<?= url('admin/inventory') ?>"><i class="ti ti-package" data-fallback="In"></i> Inventory Stock</a>

    <div class="nav-section">System</div>
    <a class="nav-item <?= $current === 'form-builder' ? 'active' : '' ?>" href="<?= url('admin/form-builder') ?>"><i class="ti ti-adjustments-horizontal" data-fallback="Fb"></i> Form Builder &amp; Portal</a>
    <a class="nav-item <?= $current === 'reports' ? 'active' : '' ?>" href="<?= url('admin/reports') ?>"><i class="ti ti-report" data-fallback="Re"></i> Reports</a>
    <a class="nav-item <?= $current === 'exams' ? 'active' : '' ?>" href="<?= url('admin/exams') ?>"><i class="ti ti-school-bell" data-fallback="Ex"></i> Entrance Exams</a>
    <a class="nav-item <?= $current === 'settings' ? 'active' : '' ?>" href="<?= url('admin/settings') ?>"><i class="ti ti-settings" data-fallback="Se"></i> Settings</a>
    <a class="nav-item <?= $current === 'roles' ? 'active' : '' ?>" href="<?= url('admin/roles') ?>"><i class="ti ti-shield-lock" data-fallback="Ro"></i> Roles</a>
    <a class="nav-item" href="<?= url('admin/logout') ?>"><i class="ti ti-logout" data-fallback="O"></i> Logout</a>
    <button class="btn btn-sm mt-3" id="themeToggle" type="button"><i class="ti ti-moon" data-fallback="T"></i> Theme</button>
</aside>
<main class="admin-main">
    <div class="admin-mobile-bar">
        <button class="admin-menu-toggle" id="adminMenuToggle" type="button" aria-controls="adminSidebar" aria-expanded="false">
            <span class="admin-menu-lines" aria-hidden="true"></span>
            <span>Menu</span>
        </button>
        <span class="admin-mobile-title"><?= e(setting('school_name', 'Admissions')) ?></span>
    </div>
    <?php if (isset($_SESSION['superadmin']['impersonate_school_id'])): ?>
        <div class="alert alert-info d-flex align-items-center justify-content-between mb-3" style="border-left: 5px solid #0284c7; font-size:13px;">
            <div>
                <i class="ti ti-headset me-2" style="font-size:18px;"></i>
                <strong>Remote Support Mode:</strong> SST Support is logged in as this school's administrator.
            </div>
            <a href="<?= url('superadmin/schools/impersonate/stop') ?>" class="btn btn-sm btn-info text-white font-monospace" style="font-size:11px; font-weight:700;">Exit Impersonation &amp; Return to Hub</a>
        </div>
    <?php endif; ?>
    <?php LicenseGuard::renderBanner(); ?>
    <?php foreach (flashes() as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endforeach; ?>
    <?= $content ?>
    <div class="powered-credit admin-powered-credit">Powered by SkySaving Tech Hub</div>
</main>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= url('assets/js/app.js') ?>"></script>
</body>
</html>
