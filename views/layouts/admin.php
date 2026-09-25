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
    <link href="<?= url('assets/css/style.css?v=' . time()) ?>" rel="stylesheet">
</head>
<body class="admin-shell">
<button class="admin-sidebar-overlay" id="adminSidebarOverlay" type="button" aria-label="Close admin menu"></button>
<aside class="sidebar" id="adminSidebar">
    <?php 
    $current = trim($_GET['route'] ?? 'dashboard', '/');
    $logoUrl = school_logo_url();

    $updateNotification = null;
    if (file_exists(__DIR__ . '/../../updater/UpdateChecker.php')) {
        require_once __DIR__ . '/../../updater/UpdateChecker.php';
        try {
            $updateNotification = UpdateChecker::check(false);
        } catch (Throwable $e) {
            $updateNotification = null;
        }
    }
    $isUpdateAvailable = !empty($updateNotification['update_available']);
    ?>
    <div class="brand sidebar-logo" style="display:flex;align-items:center;gap:10px;margin-bottom:1.5rem;min-width:0;">
        <?php if ($logoUrl): ?>
            <img src="<?= e($logoUrl) ?>" alt="Logo" class="sidebar-logo-img" style="width:40px !important;height:40px !important;max-width:40px !important;max-height:40px !important;object-fit:contain !important;border-radius:8px !important;background:#fff !important;padding:2px !important;border:1px solid #e2e8f0 !important;flex-shrink:0 !important;display:block !important;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <div class="icon" style="display:none;width:40px;height:40px;border-radius:8px;background:var(--brand-primary);align-items:center;justify-content:center;color:#fff;font-size:18px;flex-shrink:0;"><i class="ti ti-school" data-fallback="S"></i></div>
        <?php else: ?>
            <div class="icon" style="width:40px;height:40px;border-radius:8px;background:var(--brand-primary);display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;flex-shrink:0;"><i class="ti ti-school" data-fallback="S"></i></div>
        <?php endif; ?>
        <div class="sidebar-brand-info" style="display:flex;flex-direction:column;min-width:0;flex:1;overflow:hidden;">
            <span class="sidebar-brand-name" title="<?= e(setting('school_name', 'EduCore Portal')) ?>" style="font-size:13px;font-weight:700;color:#0f172a;line-height:1.25;word-break:break-word;overflow-wrap:anywhere;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= e(setting('school_name', 'EduCore Portal')) ?></span>
            <span class="sidebar-brand-badge" style="font-size:10px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-top:1px;">Admin Portal</span>
        </div>
        <button class="sidebar-close" id="adminSidebarClose" type="button" aria-label="Close admin menu"><span aria-hidden="true">X</span></button>
    </div>
    <div class="nav-section">Main</div>
    <a class="nav-item <?= $current === 'dashboard' ? 'active' : '' ?>" href="<?= url('admin/dashboard') ?>"><i class="ti ti-layout-dashboard" data-fallback="D"></i> Dashboard</a>
    <a class="nav-item" href="<?= e(school_website_url()) ?>" target="_blank" rel="noopener noreferrer"><i class="ti ti-world" data-fallback="W"></i> View School Website</a>
    <a class="nav-item <?= str_starts_with($current, 'applications') && ($_GET['status'] ?? '') !== 'Enrolled' ? 'active' : '' ?>" href="<?= url('admin/applications') ?>"><i class="ti ti-forms" data-fallback="A"></i> Applications</a>
    <a class="nav-item <?= ($current === 'applications' && ($_GET['status'] ?? '') === 'Enrolled') ? 'active' : '' ?>" href="<?= url('admin/applications?status=Enrolled') ?>"><i class="ti ti-user-check" data-fallback="E"></i> Enrolled students</a>
    <a class="nav-item <?= str_starts_with($current, 'students') || $current === 'enrol-student' ? 'active' : '' ?>" href="<?= url('admin/students/enrol') ?>"><i class="ti ti-user-plus" data-fallback="+"></i> Direct Enrollment</a>
    <a class="nav-item <?= $current === 'interviews' ? 'active' : '' ?>" href="<?= url('admin/interviews') ?>"><i class="ti ti-calendar-event" data-fallback="I"></i> Interviews</a>
    
    <div class="nav-section">Academics &amp; Class</div>
    <a class="nav-item <?= $current === 'subjects' ? 'active' : '' ?>" href="<?= url('admin/subjects') ?>"><i class="ti ti-book" data-fallback="S"></i> Subject Setup</a>
    <a class="nav-item <?= $current === 'timetable' || $current === 'timetables' ? 'active' : '' ?>" href="<?= url('admin/timetable') ?>"><i class="ti ti-calendar-time" data-fallback="Tt"></i> Class Timetable</a>
    <a class="nav-item <?= $current === 'results' ? 'active' : '' ?>" href="<?= url('admin/results') ?>"><i class="ti ti-notebook" data-fallback="R"></i> Enter Scores &amp; CA</a>
    <a class="nav-item <?= $current === 'attendance' ? 'active' : '' ?>" href="<?= url('admin/attendance') ?>"><i class="ti ti-calendar-check" data-fallback="At"></i> Daily Attendance</a>
    <a class="nav-item <?= $current === 'staff-attendance' || $current === 'staff-attendance-report' ? 'active' : '' ?>" href="<?= url('admin/staff-attendance') ?>"><i class="ti ti-clock-check" data-fallback="Sa"></i> Staff Attendance</a>
    <a class="nav-item <?= $current === 'attendance-scanner' ? 'active' : '' ?>" href="<?= url('admin/attendance-scanner') ?>"><i class="ti ti-qrcode" data-fallback="Sc"></i> Attendance Scanner</a>
    <a class="nav-item <?= $current === 'scanner-assignments' ? 'active' : '' ?>" href="<?= url('admin/scanner-assignments') ?>"><i class="ti ti-scan" data-fallback="Sa"></i> Scanner Assignments</a>
    <a class="nav-item <?= $current === 'scanner-logs' ? 'active' : '' ?>" href="<?= url('admin/scanner-logs') ?>"><i class="ti ti-history" data-fallback="Sl"></i> Scanner Activity Logs</a>
    <a class="nav-item <?= $current === 'attendance-report' ? 'active' : '' ?>" href="<?= url('admin/attendance-report') ?>"><i class="ti ti-file-analytics" data-fallback="Ar"></i> Attendance Report</a>
    <a class="nav-item <?= $current === 'attendance-settings' ? 'active' : '' ?>" href="<?= url('admin/attendance-settings') ?>"><i class="ti ti-settings-automation" data-fallback="As"></i> Attendance Settings</a>
    <a class="nav-item <?= $current === 'attendance-notification-logs' ? 'active' : '' ?>" href="<?= url('admin/attendance-notification-logs') ?>"><i class="ti ti-bell-ringing" data-fallback="Nl"></i> Notification Logs</a>
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
    <a class="nav-item <?= $current === 'updates' ? 'active' : '' ?>" href="<?= url('admin/updates') ?>">
        <i class="ti ti-cloud-download" data-fallback="Up"></i> Updates &amp; Upgrades
        <?php if ($isUpdateAvailable): ?>
            <span class="badge bg-danger rounded-pill ms-auto shadow-sm" style="font-size: 10px; padding: 3px 8px; font-weight:700;">
                <i class="ti ti-sparkles me-1"></i>v<?= e($updateNotification['latest_version'] ?? 'New') ?>
            </span>
        <?php endif; ?>
    </a>

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
    <?php if ($isUpdateAvailable && $current !== 'updates'): ?>
        <div class="alert alert-primary border-0 shadow-sm rounded-4 d-flex flex-wrap align-items-center justify-content-between p-3 mb-4" style="background: linear-gradient(135deg, #0b3d91 0%, #1e40af 100%); color: #fff; border-left: 5px solid #f59e0b !important;">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                    <i class="ti ti-sparkles text-warning"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-warning text-dark fw-bold px-2 py-1" style="font-size: 11px;">Update Available</span>
                        <strong style="font-size: 15px; letter-spacing: 0.2px;">EduCore Software Release v<?= e($updateNotification['latest_version'] ?? '') ?> is Ready</strong>
                    </div>
                    <div class="small mt-1" style="color: rgba(255,255,255,0.9); font-size: 12.5px; line-height: 1.4;">
                        Your system is currently running <strong>v<?= e($updateNotification['current_version'] ?? (defined('EDUCORE_VERSION') ? EDUCORE_VERSION : '1.0.0')) ?></strong>. 
                        <?= e(!empty($updateNotification['release_notes']) ? mb_strimwidth($updateNotification['release_notes'], 0, 130, '...') : 'New features, security updates, and performance optimizations are ready to install.') ?>
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
                <a href="<?= url('admin/updates') ?>" class="btn btn-warning btn-sm px-3 py-2 fw-bold shadow-sm rounded-3" style="color: #0f172a; font-size: 12.5px;">
                    <i class="ti ti-cloud-download me-1"></i> Review &amp; Install Update
                </a>
            </div>
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
