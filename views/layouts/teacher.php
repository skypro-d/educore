<!doctype html>
<html lang="en" style="<?= e(brand_css()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle ?? 'Staff Portal') ?> — <?= e(setting('school_name', APP_NAME)) ?></title>
    <?php if (setting('favicon')): ?><link rel="icon" href="<?= url('uploads/' . setting('favicon')) ?>"><?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
    <style>
        :root {
            --teacher-primary: var(--brand-primary, #0f766e);
            --teacher-sidebar: var(--brand-sidebar, #0f172a);
            --teacher-accent: var(--brand-secondary, #14b8a6);
            --teacher-bg: #f8fafc;
        }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--teacher-bg); color: #1e293b; margin: 0; }
        .teacher-shell { display: flex; min-height: 100vh; }
        .teacher-sidebar {
            width: 260px; min-height: 100vh; background: var(--teacher-sidebar);
            color: #94a3b8; display: flex; flex-direction: column;
            position: fixed; top: 0; left: 0; z-index: 1000;
            transition: transform .25s ease-in-out;
            box-shadow: 2px 0 12px rgba(0,0,0,0.05);
        }
        .teacher-sidebar .brand {
            padding: 22px 20px 18px;
            border-bottom: 1px solid rgba(255,255,255,.07);
            display: flex; align-items: center; gap: 12px;
        }
        .teacher-sidebar .brand .logo-icon {
            width: 40px; height: 40px; border-radius: 10px;
            background: linear-gradient(135deg, var(--teacher-accent), #0d9488);
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-size: 18px; font-weight: 700; flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(20, 184, 166, 0.25);
        }
        .teacher-sidebar .brand .school-name { font-size: 13.5px; font-weight: 700; color: #fff; line-height: 1.3; }
        .teacher-sidebar .brand .portal-label { font-size: 11px; color: var(--teacher-accent); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .teacher-nav { padding: 16px 0; flex: 1; overflow-y: auto; }
        .teacher-nav .nav-label { font-size: 10.5px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase; color: rgba(255,255,255,.3); padding: 14px 20px 6px; }
        .teacher-nav a {
            display: flex; align-items: center; gap: 11px;
            padding: 9.5px 20px; color: rgba(255,255,255,.65);
            text-decoration: none; font-size: 13.5px; font-weight: 500;
            border-left: 3px solid transparent; transition: all .15s ease;
        }
        .teacher-nav a:hover {
            background: rgba(255,255,255,.05);
            color: #fff; border-left-color: var(--teacher-accent);
        }
        .teacher-nav a.active {
            background: rgba(20, 184, 166, 0.12);
            color: #fff; border-left-color: var(--teacher-accent);
            font-weight: 600;
        }
        .teacher-nav a i { font-size: 18px; width: 22px; text-align: center; }
        .teacher-sidebar-footer {
            padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.07);
            background: rgba(0,0,0,0.15);
        }
        .teacher-sidebar-footer .teacher-name { font-size: 13px; color: #fff; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .teacher-sidebar-footer .teacher-role { font-size: 11px; color: var(--teacher-accent); font-weight: 600; }

        .teacher-main { margin-left: 260px; flex: 1; min-width: 0; display: flex; flex-direction: column; }
        .teacher-topbar {
            background: #fff; border-bottom: 1px solid #e2e8f0;
            padding: 0 28px; height: 64px; display: flex;
            align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 500;
        }
        .teacher-topbar .page-title { font-size: 16px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px; }
        .teacher-topbar .topbar-meta { display: flex; align-items: center; gap: 14px; }
        .teacher-content { padding: 28px; flex: 1; }

        .teacher-mobile-bar {
            display: none; background: var(--teacher-sidebar);
            padding: 12px 18px; align-items: center; justify-content: space-between;
        }
        .teacher-menu-toggle { background: none; border: none; color: #fff; font-size: 24px; cursor: pointer; padding: 0; }

        .t-card {
            background: #fff; border-radius: 14px; border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            padding: 24px;
        }

        @media (max-width: 992px) {
            .teacher-sidebar { transform: translateX(-100%); }
            .teacher-sidebar.open { transform: translateX(0); }
            .teacher-main { margin-left: 0; }
            .teacher-mobile-bar { display: flex; }
            .teacher-topbar { display: none; }
            .teacher-content { padding: 18px; }
        }
    </style>
</head>
<body>
<?php
$teacherSession = $_SESSION['teacher'] ?? null;
$currentRoute   = trim($_GET['route'] ?? 'dashboard', '/');
$currentRoute   = preg_replace('#^teacher/?#', '', $currentRoute);
?>

<div class="teacher-shell">
<?php if ($teacherSession): ?>
<div class="teacher-mobile-bar">
    <div class="d-flex align-items-center gap-2">
        <button class="teacher-menu-toggle" id="menuToggleBtn" aria-label="Toggle Navigation"><i class="ti ti-menu-2"></i></button>
        <span style="font-weight:700; color:#fff; font-size:14px;"><?= e(setting('school_name', APP_NAME)) ?></span>
    </div>
    <span class="badge bg-teal-subtle text-teal-emphasis" style="background:#e6fffa; color:#0d9488; font-size:11px;">Staff Portal</span>
</div>

<aside class="teacher-sidebar" id="teacherSidebar">
    <div class="brand">
        <?php $logoUrl = school_logo_url(); ?>
        <?php if ($logoUrl): ?>
            <img src="<?= e($logoUrl) ?>" alt="Logo" style="width:40px;height:40px;border-radius:10px;object-fit:contain;background:#fff;padding:2px;" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
            <div class="logo-icon" style="display:none;"><?= strtoupper(substr(setting('school_name', 'S'), 0, 1)) ?></div>
        <?php else: ?>
            <div class="logo-icon"><?= strtoupper(substr(setting('school_name', 'S'), 0, 1)) ?></div>
        <?php endif; ?>
        <div style="min-width:0;">
            <div class="school-name text-truncate"><?= e(setting('school_name', 'EduCore School')) ?></div>
            <div class="portal-label">Staff / Teacher Portal</div>
        </div>
    </div>
    <nav class="teacher-nav">
        <div class="nav-label">Main</div>
        <a class="<?= ($currentRoute === '' || $currentRoute === 'dashboard') ? 'active' : '' ?>" href="<?= url('teacher/dashboard') ?>">
            <i class="ti ti-layout-dashboard"></i> Dashboard
        </a>

        <?php if (staff_can('classes.view')): ?>
            <a class="<?= (str_starts_with($currentRoute, 'classes') || $currentRoute === 'class-list') ? 'active' : '' ?>" href="<?= url('teacher/classes') ?>">
                <i class="ti ti-chalkboard"></i> My Classes
            </a>
        <?php endif; ?>

        <?php if (staff_can('students.view')): ?>
            <a class="<?= str_starts_with($currentRoute, 'students') ? 'active' : '' ?>" href="<?= url('teacher/students') ?>">
                <i class="ti ti-users"></i> My Students
            </a>
        <?php endif; ?>

        <div class="nav-label">Academics</div>
        <?php if (staff_can('attendance.view') || staff_can('attendance.mark')): ?>
            <a class="<?= str_starts_with($currentRoute, 'attendance') ? 'active' : '' ?>" href="<?= url('teacher/attendance') ?>">
                <i class="ti ti-calendar-check"></i> Attendance & QR
            </a>
        <?php endif; ?>

        <?php if (staff_can('results.view') || staff_can('results.enter')): ?>
            <a class="<?= str_starts_with($currentRoute, 'results') ? 'active' : '' ?>" href="<?= url('teacher/results') ?>">
                <i class="ti ti-report-analytics"></i> Results & Grades
            </a>
        <?php endif; ?>

        <?php if (staff_can('assignments.view') || staff_can('assignments.create')): ?>
            <a class="<?= str_starts_with($currentRoute, 'assignments') ? 'active' : '' ?>" href="<?= url('teacher/assignments') ?>">
                <i class="ti ti-notebook"></i> Assignments
            </a>
        <?php endif; ?>

        <?php if (staff_can('timetable.view')): ?>
            <a class="<?= $currentRoute === 'timetable' ? 'active' : '' ?>" href="<?= url('teacher/timetable') ?>">
                <i class="ti ti-calendar-time"></i> Timetable
            </a>
        <?php endif; ?>

        <div class="nav-label">Communication</div>
        <?php if (staff_can('messages.view') || staff_can('messages.send')): ?>
            <a class="<?= $currentRoute === 'messages' ? 'active' : '' ?>" href="<?= url('teacher/messages') ?>">
                <i class="ti ti-message-dots"></i> Parent Messages
            </a>
        <?php endif; ?>

        <?php if (staff_can('announcements.view')): ?>
            <a class="<?= $currentRoute === 'announcements' ? 'active' : '' ?>" href="<?= url('teacher/announcements') ?>">
                <i class="ti ti-speakerphone"></i> Announcements
            </a>
        <?php endif; ?>

        <div class="nav-label">Account</div>
        <a class="<?= $currentRoute === 'profile' ? 'active' : '' ?>" href="<?= url('teacher/profile') ?>">
            <i class="ti ti-user-cog"></i> My Profile
        </a>
        <a href="<?= url('teacher/logout') ?>" style="color:#f87171;">
            <i class="ti ti-logout"></i> Logout
        </a>
    </nav>
    <div class="teacher-sidebar-footer">
        <div class="d-flex align-items-center gap-2">
            <?php if (!empty($teacherSession['photo'])): ?>
                <img src="<?= url('uploads/' . e($teacherSession['photo'])) ?>" alt="Photo" style="width:34px;height:34px;border-radius:8px;object-fit:cover;border:1px solid rgba(255,255,255,0.2);">
            <?php else: ?>
                <div style="width:34px;height:34px;border-radius:8px;background:var(--teacher-accent);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;">
                    <?= strtoupper(substr($teacherSession['name'] ?? 'T', 0, 1)) ?>
                </div>
            <?php endif; ?>
            <div style="min-width:0; flex:1;">
                <div class="teacher-name"><?= e($teacherSession['name'] ?? 'Staff Member') ?></div>
                <div class="teacher-role"><?= e($teacherSession['role_title'] ?? ucwords(str_replace('_', ' ', $teacherSession['role'] ?? 'Teacher'))) ?> (<?= e($teacherSession['staff_id'] ?? '') ?>)</div>
            </div>
        </div>
    </div>
</aside>
<?php endif; ?>

<main class="teacher-main">
    <?php if ($teacherSession): ?>
    <header class="teacher-topbar">
        <div class="page-title">
            <i class="ti ti-dashboard text-muted"></i>
            <span>Staff Portal</span>
            <span class="text-muted" style="font-weight:400; font-size:13px;">| Academic Session: <strong><?= e(current_academic_year()) ?></strong> (<?= e(current_term()) ?> Term)</span>
        </div>
        <div class="topbar-meta">
            <a href="<?= url('teacher/profile') ?>" class="text-decoration-none d-flex align-items-center gap-2 text-dark">
                <span style="font-size:13px; font-weight:600;"><?= e($teacherSession['name'] ?? '') ?></span>
                <span class="badge bg-teal-subtle text-teal-emphasis border border-teal-subtle py-1 px-2" style="background:#e6fffa; color:#0d9488; font-size:11px; font-weight:700;">
                    <?= e($teacherSession['role_title'] ?? ucwords(str_replace('_', ' ', $teacherSession['role'] ?? 'Teacher'))) ?>
                </span>
            </a>
        </div>
    </header>
    <?php endif; ?>

    <div class="teacher-content">
        <?php foreach (flashes() as $flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" style="border-radius:10px;">
                <?= $flash['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach; ?>

        <?= $content ?>
    </div>
</main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('menuToggleBtn');
    const sidebar = document.getElementById('teacherSidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('open');
        });
        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    }
});
</script>
</body>
</html>
