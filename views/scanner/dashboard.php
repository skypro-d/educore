<?php
/**
 * views/scanner/dashboard.php
 * Dedicated EduCore Scanner Officer Kiosk & Attendance Terminal
 */
$officerName = e($user['name'] ?? ($user['first_name'] . ' ' . ($user['last_name'] ?? '')));
$stationName = e($assignment['station_name'] ?? 'Main Gate Scanner');
$stationLocation = e($assignment['station_location'] ?? 'Gate Terminal');
$stationCode = e($assignment['station_code'] ?? 'STN-01');
$schoolName = e(setting('school_name', 'EduCore School'));
$schoolLogo = setting('school_logo', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $schoolName ?> — Attendance Scanner Station</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        :root {
            --brand-primary: #1e40af;
            --brand-primary-light: #eff6ff;
            --brand-accent: #0284c7;
            --brand-success: #16a34a;
            --brand-success-light: #f0fdf4;
            --brand-warning: #d97706;
            --brand-warning-light: #fffbeb;
            --brand-danger: #dc2626;
            --brand-danger-light: #fef2f2;
            --bg-page: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --shadow-soft: 0 4px 20px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -1px rgba(15, 23, 42, 0.04);
            --shadow-elevated: 0 20px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
            --radius-lg: 18px;
            --radius-md: 12px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: var(--bg-page);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* ── Header Bar ── */
        .kiosk-header {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 16px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .header-logo {
            width: 44px;
            height: 44px;
            border-radius: var(--radius-md);
            background: linear-gradient(135deg, var(--brand-primary) 0%, #1d4ed8 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 22px;
            box-shadow: 0 4px 10px rgba(30, 64, 175, 0.25);
        }

        .header-titles h1 {
            font-size: 18px;
            font-weight: 800;
            color: var(--text-main);
            margin: 0;
            line-height: 1.2;
            letter-spacing: -0.2px;
        }

        .header-titles .subtitle {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--brand-accent);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .station-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--brand-primary-light);
            border: 1px solid #bfdbfe;
            color: var(--brand-primary);
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
        }

        .station-badge .pulse-dot {
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(34, 197, 94, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0); }
        }

        .officer-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .officer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            color: #475569;
            font-weight: 700;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }

        .btn-logout {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
            padding: 8px 16px;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-logout:hover {
            background: #fee2e2;
            color: #b91c1c;
            border-color: #fca5a5;
        }

        /* ── Main Container ── */
        .kiosk-body {
            flex: 1;
            padding: 24px 28px;
            max-width: 1440px;
            margin: 0 auto;
            width: 100%;
        }

        /* ── Metric Cards ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: var(--shadow-soft);
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-info .stat-label {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-info .stat-value {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-main);
            line-height: 1;
            font-family: 'JetBrains Mono', monospace;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
        }

        .stat-icon.green { background: var(--brand-success-light); color: var(--brand-success); }
        .stat-icon.blue { background: var(--brand-primary-light); color: var(--brand-primary); }
        .stat-icon.amber { background: var(--brand-warning-light); color: var(--brand-warning); }
        .stat-icon.purple { background: #f5f3ff; color: #7c3aed; }

        /* ── Scanning Stage ── */
        .scanner-stage {
            display: grid;
            grid-template-columns: 460px 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }

        .scanner-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 28px;
            box-shadow: var(--shadow-soft);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .scanner-card-header {
            margin-bottom: 20px;
        }

        .scanner-card-header h2 {
            font-size: 17px;
            font-weight: 800;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ── Mode Selector ── */
        .mode-selector {
            display: flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: var(--radius-md);
            gap: 4px;
            margin-bottom: 22px;
        }

        .mode-btn {
            flex: 1;
            border: none;
            background: transparent;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .mode-btn.active[data-mode="auto"] {
            background: #ffffff;
            color: var(--brand-primary);
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .mode-btn.active[data-mode="in"] {
            background: var(--brand-success);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
        }

        .mode-btn.active[data-mode="out"] {
            background: var(--brand-primary);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
        }

        /* ── Scanner Input Box ── */
        .scan-input-wrap {
            position: relative;
            margin-bottom: 18px;
        }

        .scan-input {
            width: 100%;
            padding: 18px 20px 18px 52px;
            font-size: 16px;
            font-weight: 600;
            border: 2px solid #cbd5e1;
            border-radius: var(--radius-md);
            background: #ffffff;
            color: var(--text-main);
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            font-family: 'JetBrains Mono', monospace;
        }

        .scan-input:focus {
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.12);
        }

        .scan-input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 24px;
            color: var(--brand-primary);
            pointer-events: none;
        }

        .scan-status-alert {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: var(--radius-md);
            padding: 16px;
            text-align: center;
            color: #64748b;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .scan-status-alert .status-pulse {
            width: 10px;
            height: 10px;
            background: #3b82f6;
            border-radius: 50%;
            animation: pulse-blue 1.5s infinite;
        }

        @keyframes pulse-blue {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(59, 130, 246, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
        }

        /* ── Large Visual Confirmation Card ── */
        .confirmation-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 32px;
            box-shadow: var(--shadow-soft);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            min-height: 420px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .confirmation-card.ready-state {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .ready-illustration {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #94a3b8;
            margin-bottom: 20px;
            border: 3px dashed #cbd5e1;
            animation: float 4s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        .ready-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 8px;
        }

        .ready-desc {
            font-size: 14px;
            color: var(--text-muted);
            max-width: 360px;
            line-height: 1.5;
        }

        /* Active Result Card */
        .result-display {
            display: none;
            width: 100%;
            max-width: 580px;
            animation: popIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes popIn {
            0% { transform: scale(0.92); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .student-photo-wrap {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto 16px;
        }

        .student-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #ffffff;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .student-avatar-fallback {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: #ffffff;
            font-size: 42px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 4px solid #ffffff;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .result-action-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 22px;
            border-radius: 999px;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .result-action-badge.badge-success { background: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .result-action-badge.badge-primary { background: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd; }
        .result-action-badge.badge-warning { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .result-action-badge.badge-danger { background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }

        .student-name-display {
            font-size: 26px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .student-meta-pills {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .meta-pill {
            background: #f1f5f9;
            padding: 4px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            color: #475569;
            font-family: 'JetBrains Mono', monospace;
        }

        .notification-status-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: #f8fafc;
            border: 1px solid var(--border-color);
            padding: 10px 18px;
            border-radius: var(--radius-md);
            font-size: 13px;
            font-weight: 600;
            color: #475569;
        }

        .notify-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .notify-item i {
            font-size: 16px;
        }

        /* Countdown Reset Bar */
        .reset-progress-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            height: 4px;
            background: var(--brand-primary);
            width: 0%;
            transition: width 0.1s linear;
        }

        /* ── Log Table Card ── */
        .logs-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 24px;
            box-shadow: var(--shadow-soft);
        }

        .logs-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .logs-card-header h3 {
            font-size: 16px;
            font-weight: 800;
            color: var(--text-main);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-tabs {
            display: flex;
            background: #f1f5f9;
            padding: 3px;
            border-radius: 8px;
            gap: 3px;
        }

        .tab-btn {
            border: none;
            background: transparent;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 12.5px;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .tab-btn.active {
            background: #ffffff;
            color: var(--text-main);
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .table-custom {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-custom th {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
            background: #fafafa;
        }

        .table-custom td {
            font-size: 13.5px;
            font-weight: 500;
            color: var(--text-main);
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .table-custom tr:last-child td {
            border-bottom: none;
        }

        .badge-type {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-type.in { background: #dcfce7; color: #166534; }
        .badge-type.out { background: #dbeafe; color: #1e40af; }
        .badge-type.warning { background: #fef3c7; color: #92400e; }
        .badge-type.danger { background: #fee2e2; color: #991b1b; }

        @media (max-width: 992px) {
            .stat-grid { grid-template-columns: repeat(2, 1fr); }
            .scanner-stage { grid-template-columns: 1fr; }
        }
        @media (max-width: 576px) {
            .stat-grid { grid-template-columns: 1fr; }
            .kiosk-header { flex-direction: column; gap: 12px; align-items: flex-start; }
        }
    </style>
</head>
<body>

<!-- Kiosk Header -->
<header class="kiosk-header">
    <div class="header-brand">
        <div class="header-logo">
            <i class="ti ti-qrcode"></i>
        </div>
        <div class="header-titles">
            <h1><?= $schoolName ?></h1>
            <div class="subtitle">
                <i class="ti ti-scan"></i> Scanner Attendance Station
            </div>
        </div>
    </div>

    <div class="station-badge">
        <span class="pulse-dot"></span>
        <span>Station: <strong><?= $stationName ?></strong> (<?= $stationCode ?>)</span>
    </div>

    <div class="officer-profile">
        <div class="officer-avatar" title="<?= $officerName ?>">
            <?= strtoupper(substr($user['first_name'] ?? 'S', 0, 1) . substr($user['last_name'] ?? 'O', 0, 1)) ?>
        </div>
        <div class="d-none d-md-block text-end">
            <div style="font-size: 13.5px; font-weight: 800; color: var(--text-main);"><?= $officerName ?></div>
            <div style="font-size: 11.5px; color: var(--text-muted); font-weight: 600;">Scanner Officer</div>
        </div>
        <a href="<?= url('scanner/logout') ?>" class="btn-logout ms-2" id="logoutBtn">
            <i class="ti ti-logout"></i> <span>Logout</span>
        </a>
    </div>
</header>

<!-- Main Container -->
<main class="kiosk-body">
    <!-- 4 Stats Cards -->
    <section class="stat-grid">
        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Today's Entries</div>
                <div class="stat-value text-success" id="statEntries"><?= number_format((int)$stats['entries_today']) ?></div>
            </div>
            <div class="stat-icon green">
                <i class="ti ti-login"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Today's Exits</div>
                <div class="stat-value text-primary" id="statExits"><?= number_format((int)$stats['exits_today']) ?></div>
            </div>
            <div class="stat-icon blue">
                <i class="ti ti-logout"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Currently On Campus</div>
                <div class="stat-value text-purple" id="statOnCampus"><?= number_format((int)$stats['on_campus']) ?></div>
            </div>
            <div class="stat-icon purple">
                <i class="ti ti-users-group"></i>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-info">
                <div class="stat-label">Total Scans Today</div>
                <div class="stat-value" id="statTotalScans"><?= number_format((int)$stats['total_scans']) ?></div>
            </div>
            <div class="stat-icon amber">
                <i class="ti ti-device-heart-monitor"></i>
            </div>
        </div>
    </section>

    <!-- Main Scanner Stage -->
    <section class="scanner-stage">
        <!-- Left: Input Control & Mode Switcher -->
        <div class="scanner-card">
            <div>
                <div class="scanner-card-header">
                    <h2><i class="ti ti-device-ipad-horizontal text-primary"></i> Scan Student</h2>
                    <p class="text-muted small mb-0">Select mode or leave on AUTO for automatic check-in/out.</p>
                </div>

                <!-- Mode Selector -->
                <div class="mode-selector" id="modeSelector">
                    <button type="button" class="mode-btn active" data-mode="auto" id="modeAutoBtn">
                        <i class="ti ti-bolt"></i> AUTO MODE
                    </button>
                    <button type="button" class="mode-btn" data-mode="in" id="modeInBtn">
                        <i class="ti ti-login"></i> SCAN IN
                    </button>
                    <button type="button" class="mode-btn" data-mode="out" id="modeOutBtn">
                        <i class="ti ti-logout"></i> SCAN OUT
                    </button>
                </div>

                <!-- Live USB Scanner Input -->
                <div class="scan-input-wrap">
                    <i class="ti ti-barcode scan-input-icon"></i>
                    <input type="text"
                           id="scannerInput"
                           class="scan-input"
                           placeholder="Ready for USB scanner..."
                           autocomplete="off"
                           autofocus>
                </div>

                <div class="scan-status-alert">
                    <span class="status-pulse"></span>
                    <span id="scannerStatusText">Ready to Scan — USB Scanner / QR Reader Active</span>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top text-center text-muted small">
                <i class="ti ti-keyboard me-1"></i> Scanner input maintains continuous focus automatically.
            </div>
        </div>

        <!-- Right: Visual Confirmation Card -->
        <div class="confirmation-card ready-state" id="confirmationCard">
            <!-- Ready State Placeholder -->
            <div class="ready-placeholder" id="readyPlaceholder">
                <div class="ready-illustration">
                    <i class="ti ti-scan"></i>
                </div>
                <div class="ready-title">READY TO SCAN</div>
                <div class="ready-desc">
                    Point barcode scanner at student ID card or present QR code to register arrival or departure.
                </div>
            </div>

            <!-- Active Result View (Dynamically Injected) -->
            <div class="result-display" id="resultDisplay">
                <div id="resultBadgeWrap">
                    <span class="result-action-badge badge-success" id="resultBadge">
                        <i class="ti ti-check" id="resultBadgeIcon"></i> <span id="resultBadgeText">ENTRY RECORDED</span>
                    </span>
                </div>

                <div class="student-photo-wrap">
                    <img src="" alt="Student Photo" class="student-photo" id="resultPhoto" style="display:none;">
                    <div class="student-avatar-fallback" id="resultAvatarFallback">
                        <i class="ti ti-user"></i>
                    </div>
                </div>

                <div class="student-name-display" id="resultStudentName">Michael Adebayo</div>

                <div class="student-meta-pills">
                    <span class="meta-pill" id="resultAdmNo"><i class="ti ti-id me-1"></i> EDU/2026/00124</span>
                    <span class="meta-pill" id="resultClass"><i class="ti ti-school me-1"></i> JSS 2A</span>
                    <span class="meta-pill" id="resultTime"><i class="ti ti-clock me-1"></i> 07:42:18 AM</span>
                </div>

                <div class="mb-3">
                    <p class="text-muted fw-semibold mb-0" id="resultMessage">The student has been successfully checked in.</p>
                </div>

                <div class="notification-status-bar" id="notificationStatusBar">
                    <div class="notify-item">
                        <i class="ti ti-message-2 text-success"></i>
                        <span id="smsStatusText">SMS: Sent</span>
                    </div>
                    <span class="text-muted">•</span>
                    <div class="notify-item">
                        <i class="ti ti-mail text-primary"></i>
                        <span id="emailStatusText">Email: Sent</span>
                    </div>
                </div>
            </div>

            <!-- Auto-reset progress indicator -->
            <div class="reset-progress-bar" id="resetProgressBar"></div>
        </div>
    </section>

    <!-- Today's Scan Logs Card -->
    <section class="logs-card">
        <div class="logs-card-header">
            <h3><i class="ti ti-history text-primary"></i> Today's Scan Logs</h3>
            <div class="filter-tabs" id="logFilterTabs">
                <button type="button" class="tab-btn active" data-filter="all">All</button>
                <button type="button" class="tab-btn" data-filter="entry">Entry</button>
                <button type="button" class="tab-btn" data-filter="exit">Exit</button>
                <button type="button" class="tab-btn" data-filter="failed">Failed / Warnings</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table-custom" id="todayLogsTable">
                <thead>
                    <tr>
                        <th style="width: 110px;">Time</th>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Class</th>
                        <th style="width: 130px;">Action</th>
                        <th style="width: 140px;">Status</th>
                        <th>Parent Notification</th>
                    </tr>
                </thead>
                <tbody id="logsTableBody">
                    <?php if (empty($recentLogs)): ?>
                        <tr id="emptyLogsRow">
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="ti ti-inbox" style="font-size:28px; display:block; margin-bottom:6px;"></i>
                                No student scans logged today yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentLogs as $log): ?>
                            <?php
                                $actionCls = 'in';
                                $actionLabel = 'Check-In';
                                if ($log['scan_action'] === 'check_out') {
                                    $actionCls = 'out';
                                    $actionLabel = 'Check-Out';
                                } elseif ($log['status'] === 'warning') {
                                    $actionCls = 'warning';
                                    $actionLabel = 'Duplicate';
                                } elseif ($log['status'] === 'danger') {
                                    $actionCls = 'danger';
                                    $actionLabel = 'Invalid';
                                }
                            ?>
                            <tr>
                                <td style="font-family:'JetBrains Mono', monospace; font-weight:700; color:#475569;">
                                    <?= date('g:i:s A', strtotime($log['scanned_at'])) ?>
                                </td>
                                <td style="font-weight:700; color:var(--text-main);">
                                    <?= e($log['student_name'] ?: 'Unknown Student') ?>
                                </td>
                                <td style="font-family:'JetBrains Mono', monospace; color:#64748b;">
                                    <?= e($log['student_admission_no'] ?: ($log['identifier_scanned'] ?: '—')) ?>
                                </td>
                                <td><?= e($log['class_name'] ?: '—') ?></td>
                                <td>
                                    <span class="badge-type <?= $actionCls ?>"><?= $actionLabel ?></span>
                                </td>
                                <td>
                                    <span class="small fw-semibold text-muted"><?= e($log['response_message'] ?: 'Logged') ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2 small">
                                        <span class="badge bg-light text-muted border"><i class="ti ti-message-2"></i> SMS: <?= e(ucfirst($log['sms_status'] ?? 'None')) ?></span>
                                        <span class="badge bg-light text-muted border"><i class="ti ti-mail"></i> Email: <?= e(ucfirst($log['email_status'] ?? 'None')) ?></span>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const scannerInput = document.getElementById('scannerInput');
    const modeButtons = document.querySelectorAll('.mode-btn');
    const logFilterButtons = document.querySelectorAll('.tab-btn');
    const confirmationCard = document.getElementById('confirmationCard');
    const readyPlaceholder = document.getElementById('readyPlaceholder');
    const resultDisplay = document.getElementById('resultDisplay');
    const resetProgressBar = document.getElementById('resetProgressBar');
    const scannerStatusText = document.getElementById('scannerStatusText');
    const logsTableBody = document.getElementById('logsTableBody');

    let currentMode = 'auto';
    let currentLogFilter = 'all';
    let isProcessing = false;
    let autoResetTimer = null;
    let progressInterval = null;

    // ── Mode Switcher ──
    modeButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            modeButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentMode = btn.dataset.mode;
            scannerStatusText.textContent = `Active Mode: ${currentMode.toUpperCase()} — USB Scanner Ready`;
            refocusInput();
        });
    });

    // ── Continuous Auto-Focus ──
    function refocusInput() {
        if (document.activeElement !== scannerInput) {
            scannerInput.focus();
        }
    }

    // Refocus on click anywhere on screen
    document.addEventListener('click', (e) => {
        if (!e.target.closest('button, a, input, select')) {
            refocusInput();
        }
    });

    // Keep focused
    window.addEventListener('focus', refocusInput);
    setInterval(refocusInput, 2000);

    // ── Scanner Input Handling ──
    let scanBuffer = '';
    let lastKeyTime = Date.now();

    scannerInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const code = scannerInput.value.trim();
            if (code && !isProcessing) {
                processScannedCode(code);
            }
            scannerInput.value = '';
        }
    });

    // ── Process Scan via AJAX ──
    async function processScannedCode(code) {
        isProcessing = true;
        scannerStatusText.textContent = 'Processing scan...';
        clearTimeout(autoResetTimer);
        clearInterval(progressInterval);
        resetProgressBar.style.width = '0%';

        try {
            const res = await fetch('<?= url("scanner/scan") ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    qr_data: code,
                    mode: currentMode
                })
            });

            const data = await res.json();
            displayScanResult(data);
            refreshStats();
            refreshLogs();
        } catch (err) {
            displayScanResult({
                success: false,
                badge_status: 'danger',
                title: 'CONNECTION ERROR',
                message: 'Failed to contact attendance server. Please try again.'
            });
        } finally {
            isProcessing = false;
            scannerInput.value = '';
            refocusInput();
        }
    }

    // ── Display Confirmation UI ──
    function displayScanResult(data) {
        readyPlaceholder.style.display = 'none';
        resultDisplay.style.display = 'block';

        const resultBadge = document.getElementById('resultBadge');
        const resultBadgeText = document.getElementById('resultBadgeText');
        const resultBadgeIcon = document.getElementById('resultBadgeIcon');
        const resultStudentName = document.getElementById('resultStudentName');
        const resultAdmNo = document.getElementById('resultAdmNo');
        const resultClass = document.getElementById('resultClass');
        const resultTime = document.getElementById('resultTime');
        const resultMessage = document.getElementById('resultMessage');
        const resultPhoto = document.getElementById('resultPhoto');
        const resultAvatarFallback = document.getElementById('resultAvatarFallback');
        const smsStatusText = document.getElementById('smsStatusText');
        const emailStatusText = document.getElementById('emailStatusText');

        // Configure Badge Styling
        resultBadge.className = 'result-action-badge';
        const badgeType = data.badge_status || (data.success ? 'success' : 'danger');
        resultBadge.classList.add(`badge-${badgeType}`);
        resultBadgeText.textContent = data.title || (data.success ? '✓ SCAN RECORDED' : '✕ SCAN REJECTED');

        if (badgeType === 'success') {
            resultBadgeIcon.className = 'ti ti-check';
        } else if (badgeType === 'primary') {
            resultBadgeIcon.className = 'ti ti-logout';
        } else if (badgeType === 'warning') {
            resultBadgeIcon.className = 'ti ti-alert-triangle';
        } else {
            resultBadgeIcon.className = 'ti ti-x';
        }

        // Student Data Injection
        if (data.student) {
            resultStudentName.textContent = data.student.name || 'Student';
            resultAdmNo.innerHTML = `<i class="ti ti-id me-1"></i> ${data.student.admission_number || 'N/A'}`;
            resultClass.innerHTML = `<i class="ti ti-school me-1"></i> ${data.student.class_name || 'N/A'}`;
            resultTime.innerHTML = `<i class="ti ti-clock me-1"></i> ${data.time || new Date().toLocaleTimeString()}`;

            if (data.student.photo) {
                resultPhoto.src = data.student.photo;
                resultPhoto.style.display = 'block';
                resultAvatarFallback.style.display = 'none';
            } else {
                resultPhoto.style.display = 'none';
                resultAvatarFallback.style.display = 'flex';
                resultAvatarFallback.innerHTML = `<span>${(data.student.first_name || 'S').charAt(0)}${(data.student.last_name || 'T').charAt(0)}</span>`;
            }
        } else {
            resultStudentName.textContent = 'Invalid / Unrecognized Card';
            resultAdmNo.innerHTML = `<i class="ti ti-id me-1"></i> ${data.raw_code || '—'}`;
            resultClass.innerHTML = `<i class="ti ti-alert-circle me-1"></i> Not Enrolled`;
            resultTime.innerHTML = `<i class="ti ti-clock me-1"></i> ${new Date().toLocaleTimeString()}`;
            resultPhoto.style.display = 'none';
            resultAvatarFallback.style.display = 'flex';
            resultAvatarFallback.innerHTML = `<i class="ti ti-user-x"></i>`;
        }

        resultMessage.textContent = data.message || '';

        // Notifications
        if (data.notifications) {
            smsStatusText.textContent = `SMS: ${data.notifications.sms || 'Sent'}`;
            emailStatusText.textContent = `Email: ${data.notifications.email || 'Sent'}`;
            document.getElementById('notificationStatusBar').style.display = 'flex';
        } else {
            document.getElementById('notificationStatusBar').style.display = data.success ? 'flex' : 'none';
        }

        // Auto-Reset Countdown (3.5 seconds)
        const duration = 3500;
        let elapsed = 0;
        const intervalStep = 50;

        progressInterval = setInterval(() => {
            elapsed += intervalStep;
            const pct = Math.min(100, (elapsed / duration) * 100);
            resetProgressBar.style.width = `${pct}%`;
            if (elapsed >= duration) {
                clearInterval(progressInterval);
                resetToReadyState();
            }
        }, intervalStep);
    }

    // ── Reset to Ready State ──
    function resetToReadyState() {
        clearInterval(progressInterval);
        resetProgressBar.style.width = '0%';
        resultDisplay.style.display = 'none';
        readyPlaceholder.style.display = 'flex';
        scannerStatusText.textContent = `Active Mode: ${currentMode.toUpperCase()} — Ready to Scan`;
        refocusInput();
    }

    // ── Refresh Stats ──
    async function refreshStats() {
        try {
            const res = await fetch('<?= url("scanner/stats") ?>');
            const data = await res.json();
            if (data.success && data.stats) {
                document.getElementById('statEntries').textContent = Number(data.stats.entries_today || 0).toLocaleString();
                document.getElementById('statExits').textContent = Number(data.stats.exits_today || 0).toLocaleString();
                document.getElementById('statOnCampus').textContent = Number(data.stats.on_campus || 0).toLocaleString();
                document.getElementById('statTotalScans').textContent = Number(data.stats.total_scans || 0).toLocaleString();
            }
        } catch (e) {}
    }

    // ── Refresh Logs ──
    async function refreshLogs() {
        try {
            const res = await fetch(`<?= url("scanner/today-logs") ?>?filter=${encodeURIComponent(currentLogFilter)}`);
            const data = await res.json();
            if (data.success && Array.isArray(data.logs)) {
                renderLogsTable(data.logs);
            }
        } catch (e) {}
    }

    function renderLogsTable(logs) {
        if (!logs || logs.length === 0) {
            logsTableBody.innerHTML = `
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        <i class="ti ti-inbox" style="font-size:28px; display:block; margin-bottom:6px;"></i>
                        No student scans matching current filter.
                    </td>
                </tr>`;
            return;
        }

        let html = '';
        logs.forEach(log => {
            let actionCls = 'in';
            let actionLabel = 'Check-In';
            if (log.scan_action === 'check_out') {
                actionCls = 'out';
                actionLabel = 'Check-Out';
            } else if (log.status === 'warning') {
                actionCls = 'warning';
                actionLabel = 'Duplicate';
            } else if (log.status === 'danger') {
                actionCls = 'danger';
                actionLabel = 'Invalid';
            }

            const scanTime = new Date(log.scanned_at).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', second: '2-digit' });

            html += `
                <tr>
                    <td style="font-family:'JetBrains Mono', monospace; font-weight:700; color:#475569;">
                        ${scanTime}
                    </td>
                    <td style="font-weight:700; color:var(--text-main);">
                        ${escapeHtml(log.student_name || 'Unknown Student')}
                    </td>
                    <td style="font-family:'JetBrains Mono', monospace; color:#64748b;">
                        ${escapeHtml(log.student_admission_no || log.identifier_scanned || '—')}
                    </td>
                    <td>${escapeHtml(log.class_name || '—')}</td>
                    <td>
                        <span class="badge-type ${actionCls}">${actionLabel}</span>
                    </td>
                    <td>
                        <span class="small fw-semibold text-muted">${escapeHtml(log.response_message || 'Logged')}</span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center gap-2 small">
                            <span class="badge bg-light text-muted border"><i class="ti ti-message-2"></i> SMS: ${escapeHtml(log.sms_status || 'None')}</span>
                            <span class="badge bg-light text-muted border"><i class="ti ti-mail"></i> Email: ${escapeHtml(log.email_status || 'None')}</span>
                        </div>
                    </td>
                </tr>`;
        });
        logsTableBody.innerHTML = html;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // ── Log Filter Tabs ──
    logFilterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            logFilterButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            currentLogFilter = btn.dataset.filter;
            refreshLogs();
        });
    });

    // Periodic Background Polling for live dashboard (every 10s)
    setInterval(() => {
        refreshStats();
        refreshLogs();
    }, 10000);
});
</script>
</body>
</html>
