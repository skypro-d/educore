<?php
/**
 * views/admin/id_card.php
 * Official Student ID Card — Full Dynamic Theming & Print System
 */
$fullSchoolName = setting('school_name', 'BLUEFIELD INTERNATIONAL SCHOOL');
$nameParts = explode(' ', trim($fullSchoolName), 2);
$mainName = strtoupper($nameParts[0] ?? 'BLUEFIELD');
$subName  = strtoupper($nameParts[1] ?? 'INTERNATIONAL SCHOOL');

// School Logo URL calculation
$logoUrl = school_logo_url() ?: '';

// Dynamic ID Card Colors
$idPrimaryColor   = setting('id_card_primary_color', setting('primary_color', '#0b3d91'));
$idSecondaryColor = setting('id_card_secondary_color', setting('secondary_color', '#1e40af'));
$idHeaderBg       = setting('id_card_header_bg', '#0f172a');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Student ID Card — <?= e($student['first_name'] . ' ' . $student['last_name']) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --id-primary: <?= e($idPrimaryColor) ?>;
            --id-secondary: <?= e($idSecondaryColor) ?>;
            --id-header-bg: <?= e($idHeaderBg) ?>;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
            background: #f1f5f9;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 20px 60px;
            color: #0f172a;
        }
        
        /* Top Action & Customizer Bar */
        .no-print {
            margin-bottom: 24px;
            width: 100%;
            max-width: 820px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .action-bar-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .action-bar-left, .action-bar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--id-primary);
            color: white;
            padding: 10px 22px;
            border-radius: 10px;
            font-weight: 700;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transition: all 0.2s ease;
        }
        .btn-action:hover {
            filter: brightness(0.92);
            transform: translateY(-1px);
        }
        .btn-action-back {
            background: white;
            color: #334155;
            border: 1px solid #cbd5e1;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05);
        }
        .btn-action-back:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        /* Color Customization Panel */
        .color-palette-toolbar {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 18px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }

        .color-toolbar-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
        }

        .color-inputs-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .color-input-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f8fafc;
            padding: 4px 8px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
        }

        .color-swatch-picker {
            width: 28px;
            height: 28px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            background: transparent;
            padding: 0;
        }

        .preset-chips-container {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .preset-chip-btn {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 2px solid #ffffff;
            box-shadow: 0 0 0 1px #cbd5e1;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
            outline: none;
        }
        .preset-chip-btn:hover {
            transform: scale(1.2);
            box-shadow: 0 0 0 2px #0f172a;
        }

        .btn-save-theme {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #0f172a;
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-save-theme:hover {
            background: #1e293b;
        }

        /* ID Card Container */
        .id-card-wrapper {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .id-card {
            width: 330px;
            height: 530px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 12px 30px -5px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            position: relative;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
        }

        /* Front Card Header */
        .id-card-header-bg {
            position: relative;
            width: 100%;
            height: 195px;
            background: var(--id-header-bg);
            overflow: hidden;
        }

        .id-header-wave {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }

        .id-school-branding {
            position: absolute;
            top: 14px;
            left: 14px;
            right: 14px;
            z-index: 3;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .id-school-logo-box {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 22px;
            flex-shrink: 0;
            overflow: hidden;
        }
        .id-school-logo-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: #ffffff;
            padding: 2px;
            display: block;
        }

        .id-school-title-text {
            color: #ffffff;
            line-height: 1.15;
        }
        .id-school-main-name {
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #ffffff;
        }
        .id-school-sub-name {
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.85);
        }

        /* Photo Frame */
        .id-photo-wrapper {
            position: absolute;
            top: 65px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 4;
        }

        .id-photo-outer-ring {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--id-primary) 0%, var(--id-secondary) 100%);
            padding: 5px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.25);
        }

        .id-photo-inner-ring {
            width: 100%;
            height: 100%;
            background: #ffffff;
            border-radius: 50%;
            padding: 4px;
            overflow: hidden;
        }

        .id-photo-img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 64px;
        }

        /* Front Body */
        .id-card-front-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 18px 14px;
            background: #ffffff;
            text-align: center;
        }

        .id-student-name {
            font-size: 19px;
            font-weight: 900;
            color: var(--id-primary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            line-height: 1.2;
        }

        .id-student-subtitle {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .id-divider-line {
            width: 65%;
            height: 2px;
            background: var(--id-primary);
            margin: 8px 0 14px;
            border-radius: 2px;
        }

        /* Info Grid */
        .id-info-grid {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .id-info-row {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f8fafc;
            border-radius: 12px;
            padding: 8px 12px;
            border: 1px solid #f1f5f9;
        }

        .id-info-badge {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--id-primary);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }

        .id-info-cols {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            text-align: left;
        }

        .id-info-cell {
            display: flex;
            flex-direction: column;
        }

        .id-info-lbl {
            font-size: 8.5px;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.2;
        }

        .id-info-val {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.3;
        }

        /* Back Card Specifics */
        .id-card-back-topbar {
            height: 14px;
            background: var(--id-primary);
            width: 100%;
        }

        .id-card-back-body {
            flex: 1;
            padding: 18px 20px;
            display: flex;
            flex-direction: column;
            background: #ffffff;
            color: #334155;
            text-align: left;
        }

        .id-back-rules {
            font-size: 10.5px;
            line-height: 1.5;
            color: #334155;
            margin-bottom: 14px;
        }

        .id-back-rules ol {
            padding-left: 0;
            list-style: none;
            margin: 0;
        }

        .id-back-rules li {
            margin-bottom: 6px;
        }

        .id-emergency-box {
            background: #f8fafc;
            border: 1.5px dashed #cbd5e1;
            border-radius: 12px;
            padding: 10px 14px;
            margin-bottom: 14px;
        }

        .id-emergency-title {
            font-size: 10px;
            font-weight: 800;
            color: #dc2626;
            text-transform: uppercase;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 6px;
        }

        .id-emergency-details {
            font-size: 10.5px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.4;
        }

        .id-qr-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 12px;
        }

        .id-qr-note {
            font-size: 9.5px;
            font-style: italic;
            color: #475569;
            margin-bottom: 6px;
            text-align: center;
        }

        .id-qr-box {
            background: #ffffff;
            border-radius: 12px;
            padding: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .id-qr-box img {
            width: 95px;
            height: 95px;
            display: block;
        }

        .id-sig-section {
            text-align: center;
            margin-top: auto;
            margin-bottom: 6px;
        }

        .id-sig-line {
            width: 140px;
            border-top: 1px solid #94a3b8;
            margin: 0 auto 4px;
        }

        .id-sig-text {
            font-size: 11px;
            font-style: italic;
            color: #334155;
        }

        .id-card-back-footer {
            height: 22px;
            background: var(--id-header-bg);
            width: 100%;
        }

        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .id-card-wrapper {
                gap: 20px !important;
                margin-top: 0 !important;
            }
            .id-card {
                box-shadow: none !important;
                border: 1px solid #94a3b8 !important;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <!-- Executive Customization & Print Bar -->
    <div class="no-print">
        <div class="action-bar-row">
            <div class="action-bar-left">
                <a href="<?= url('admin/applications/' . $student['id']) ?>" class="btn-action btn-action-back"><i class="ti ti-arrow-left"></i> Back to Student Profile</a>
                <button onclick="window.print()" class="btn-action"><i class="ti ti-printer"></i> Print ID Card</button>
            </div>
            <div class="action-bar-right">
                <span id="saveStatusToast" style="display:none; font-size:13px; font-weight:700; color:#059669;"><i class="ti ti-circle-check-filled"></i> Saved as Default</span>
                <button type="button" id="saveIdColorBtn" class="btn-save-theme">
                    <i class="ti ti-device-floppy"></i> Save Color as School Default
                </button>
            </div>
        </div>

        <div class="color-palette-toolbar">
            <div class="color-toolbar-title">
                <i class="ti ti-palette" style="color:var(--id-primary); font-size:18px;"></i>
                <span>ID Card Color Theme:</span>
            </div>

            <div class="color-inputs-wrap">
                <div class="color-input-badge" title="Primary Header & Accent Color">
                    <span>Primary:</span>
                    <input type="color" id="primaryColorPicker" value="<?= e($idPrimaryColor) ?>" class="color-swatch-picker">
                </div>
                <div class="color-input-badge" title="Gradient Highlight Wave">
                    <span>Accent:</span>
                    <input type="color" id="secondaryColorPicker" value="<?= e($idSecondaryColor) ?>" class="color-swatch-picker">
                </div>
                <div class="color-input-badge" title="Header Dark/Background Base">
                    <span>Base:</span>
                    <input type="color" id="headerBgPicker" value="<?= e($idHeaderBg) ?>" class="color-swatch-picker">
                </div>
            </div>

            <div class="preset-chips-container" title="Quick Color Palettes">
                <button type="button" class="preset-chip-btn" style="background:#0b3d91;" data-primary="#0b3d91" data-secondary="#1e40af" data-header="#0f172a" title="Navy Blue"></button>
                <button type="button" class="preset-chip-btn" style="background:#047857;" data-primary="#047857" data-secondary="#10b981" data-header="#064e3b" title="Emerald Green"></button>
                <button type="button" class="preset-chip-btn" style="background:#6b21a8;" data-primary="#6b21a8" data-secondary="#9333ea" data-header="#3b0764" title="Royal Purple"></button>
                <button type="button" class="preset-chip-btn" style="background:#991b1b;" data-primary="#991b1b" data-secondary="#dc2626" data-header="#450a0a" title="Crimson Red"></button>
                <button type="button" class="preset-chip-btn" style="background:#b45309;" data-primary="#b45309" data-secondary="#f59e0b" data-header="#451a03" title="Gold Amber"></button>
                <button type="button" class="preset-chip-btn" style="background:#0f766e;" data-primary="#0f766e" data-secondary="#06b6d4" data-header="#134e4a" title="Deep Teal"></button>
                <button type="button" class="preset-chip-btn" style="background:#334155;" data-primary="#334155" data-secondary="#64748b" data-header="#0f172a" title="Slate Onyx"></button>
            </div>
        </div>
    </div>

    <?php
    // QR Code Website Link Generation
    $token = !empty($student['qr_data']) ? $student['qr_data'] : 'ATTENDANCE-STD-' . $student['id'];
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    $siteScanUrl = $scheme . '://' . $host . $baseUrl . '/?route=attendance/scan&token=' . urlencode($token);

    $qrLocalDir  = UPLOAD_PATH . 'qrcodes/';
    $safeHost    = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $host);
    $qrFileName  = 'std_url_' . $student['id'] . '_' . $safeHost . '.png';
    $qrLocalFile = $qrLocalDir . $qrFileName;
    $qrSrc       = url('uploads/qrcodes/' . $qrFileName);
    $hasQr       = false;

    try {
        if (!is_dir($qrLocalDir)) {
            @mkdir($qrLocalDir, 0755, true);
        }
        if (extension_loaded('gd') && function_exists('imagecreate')) {
            require_once __DIR__ . '/../../config/phpqrcode.php';
            @QRcode::png($siteScanUrl, $qrLocalFile, 'L', 6, 2);
            if (file_exists($qrLocalFile)) {
                $hasQr = true;
            }
        }
    } catch (Throwable $e) {
        error_log("Failed to generate site link QR code for student " . $student['id'] . ": " . $e->getMessage());
    }

    if (!$hasQr) {
        $qrSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($siteScanUrl);
        $hasQr = true;
    }
    ?>

    <div class="id-card-wrapper">
        <!-- FRONT SIDE -->
        <div class="id-card">
            <div class="id-card-header-bg">
                <svg class="id-header-wave" viewBox="0 0 330 195" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="blueWaveGradAdmin" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop id="waveStop1" offset="0%" stop-color="<?= e($idPrimaryColor) ?>" />
                            <stop id="waveStop2" offset="100%" stop-color="<?= e($idSecondaryColor) ?>" />
                        </linearGradient>
                    </defs>
                    <rect id="waveHeaderRect" width="330" height="195" fill="<?= e($idHeaderBg) ?>" />
                    <path d="M 0,0 L 210,0 C 170,70 110,120 0,165 Z" fill="url(#blueWaveGradAdmin)" />
                    <path d="M 0,0 L 140,0 C 240,60 290,130 0,195 Z" fill="url(#blueWaveGradAdmin)" opacity="0.85" />
                    <path d="M 0,195 C 100,135 230,190 330,140 L 330,195 Z" fill="#ffffff" />
                </svg>

                <div class="id-school-branding">
                    <div class="id-school-logo-box">
                        <?php if (!empty($logoUrl)): ?>
                            <img src="<?= e($logoUrl) ?>" alt="School Logo" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='block';">
                            <div style="display:none;">
                                <i class="ti ti-school" style="font-size:24px;"></i>
                            </div>
                        <?php else: ?>
                            <i class="ti ti-school" style="font-size:24px;"></i>
                        <?php endif; ?>
                    </div>
                    <div class="id-school-title-text">
                        <div class="id-school-main-name"><?= e($mainName) ?></div>
                        <div class="id-school-sub-name"><?= e($subName) ?></div>
                    </div>
                </div>

                <div class="id-photo-wrapper">
                    <div class="id-photo-outer-ring">
                        <div class="id-photo-inner-ring">
                            <?php if (!empty($student['passport_photo'])): ?>
                                <img class="id-photo-img" src="<?= url('uploads/' . $student['passport_photo']) ?>" alt="Student Photo">
                            <?php else: ?>
                                <div class="id-photo-img">
                                    <i class="ti ti-user"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="id-card-front-body">
                <h2 class="id-student-name"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h2>
                <div class="id-student-subtitle">STUDENT ID CARD</div>
                <div class="id-divider-line"></div>
                
                <div class="id-info-grid">
                    <div class="id-info-row">
                        <div class="id-info-badge">
                            <i class="ti ti-address-book"></i>
                        </div>
                        <div class="id-info-cols">
                            <div class="id-info-cell">
                                <span class="id-info-lbl">STUDENT ID</span>
                                <span class="id-info-val"><?= e($student['admission_number'] ?: ($student['application_number'] ?? 'ADM-2026-00001')) ?></span>
                            </div>
                            <div class="id-info-cell">
                                <span class="id-info-lbl">CLASS</span>
                                <span class="id-info-val"><?= e($student['class_name'] ?: 'JSS 1') ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="id-info-row">
                        <div class="id-info-badge">
                            <i class="ti ti-droplet-filled"></i>
                        </div>
                        <div class="id-info-cols">
                            <div class="id-info-cell">
                                <span class="id-info-lbl">BLOOD GROUP</span>
                                <span class="id-info-val"><?= e($student['blood_group'] ?: 'A') ?></span>
                            </div>
                            <div class="id-info-cell">
                                <span class="id-info-lbl">ACADEMIC YEAR</span>
                                <span class="id-info-val"><?= e(setting('academic_year', '2024/2025')) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="id-info-row">
                        <div class="id-info-badge">
                            <i class="ti ti-calendar-event"></i>
                        </div>
                        <div class="id-info-cols">
                            <div class="id-info-cell" style="grid-column: span 2;">
                                <span class="id-info-lbl">VALID</span>
                                <span class="id-info-val">JUL <?= date('Y') ?> – JUL <?= date('Y', strtotime('+1 year')) ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BACK SIDE -->
        <div class="id-card">
            <div class="id-card-back-topbar"></div>
            <div class="id-card-back-body">
                <div class="id-back-rules">
                    <ol>
                        <li>1. This card is the property of <?= e($fullSchoolName) ?> and must be worn at all times while on school premises.</li>
                        <li>2. It is non-transferable and must be surrendered upon graduation, transfer or withdrawal.</li>
                        <li>3. Report loss immediately to the administration office. Replacement fee applies.</li>
                    </ol>
                </div>

                <div class="id-emergency-box">
                    <div class="id-emergency-title">
                        <i class="ti ti-phone-call"></i> IN CASE OF EMERGENCY
                    </div>
                    <div class="id-emergency-details">
                        Parent/Guardian: <?= e($student['parent_name'] ?: ($student['emergency_contact_name'] ?? 'Parent / Guardian')) ?><br>
                        Phone: <?= e($student['parent_phone'] ?: ($student['emergency_contact_phone'] ?? '08000000000')) ?><br>
                        Address: <?= e($student['home_address'] ?: 'School Residential District') ?>
                    </div>
                </div>

                <div class="id-qr-section">
                    <div class="id-qr-note">Scan the QR code to sign in your attendance</div>
                    <div class="id-qr-box">
                         <?php if (!empty($hasQr)): ?>
                             <img src="<?= $qrSrc ?>" alt="Attendance QR Code Website Link">
                         <?php else: ?>
                             <div style="width:95px; height:95px; border:1px solid #cbd5e1; border-radius:8px; background:#f8fafc; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#64748b; font-size:9px; text-align:center; padding:4px;">
                                 <i class="ti ti-qrcode" style="font-size:32px; color:#94a3b8; margin-bottom:2px;"></i>
                                 <span>QR READY</span>
                             </div>
                         <?php endif; ?>
                    </div>
                </div>

                <div class="id-sig-section">
                    <div class="id-sig-line"></div>
                    <div class="id-sig-text">Principal signature</div>
                </div>
            </div>
            <div class="id-card-back-footer"></div>
        </div>
    </div>

    <script>
    (function() {
        const root = document.documentElement;
        const primaryPicker = document.getElementById('primaryColorPicker');
        const secondaryPicker = document.getElementById('secondaryColorPicker');
        const headerBgPicker = document.getElementById('headerBgPicker');
        const stop1 = document.getElementById('waveStop1');
        const stop2 = document.getElementById('waveStop2');
        const rect = document.getElementById('waveHeaderRect');
        const saveBtn = document.getElementById('saveIdColorBtn');
        const toast = document.getElementById('saveStatusToast');

        function applyTheme(primary, secondary, headerBg) {
            root.style.setProperty('--id-primary', primary);
            root.style.setProperty('--id-secondary', secondary);
            root.style.setProperty('--id-header-bg', headerBg);
            if (stop1) stop1.setAttribute('stop-color', primary);
            if (stop2) stop2.setAttribute('stop-color', secondary);
            if (rect) rect.setAttribute('fill', headerBg);
            if (primaryPicker) primaryPicker.value = primary;
            if (secondaryPicker) secondaryPicker.value = secondary;
            if (headerBgPicker) headerBgPicker.value = headerBg;
        }

        if (primaryPicker) {
            primaryPicker.addEventListener('input', function() {
                applyTheme(this.value, secondaryPicker.value, headerBgPicker.value);
            });
        }
        if (secondaryPicker) {
            secondaryPicker.addEventListener('input', function() {
                applyTheme(primaryPicker.value, this.value, headerBgPicker.value);
            });
        }
        if (headerBgPicker) {
            headerBgPicker.addEventListener('input', function() {
                applyTheme(primaryPicker.value, secondaryPicker.value, this.value);
            });
        }

        document.querySelectorAll('.preset-chip-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                applyTheme(this.dataset.primary, this.dataset.secondary, this.dataset.header);
            });
        });

        if (saveBtn) {
            saveBtn.addEventListener('click', async function() {
                const origHtml = saveBtn.innerHTML;
                saveBtn.disabled = true;
                saveBtn.innerHTML = '<i class="ti ti-loader-2 ti-spin"></i> Saving...';

                try {
                    const formData = new FormData();
                    formData.append('csrf_token', '<?= csrf_token() ?>');
                    formData.append('id_card_primary_color', primaryPicker.value);
                    formData.append('id_card_secondary_color', secondaryPicker.value);
                    formData.append('id_card_header_bg', headerBgPicker.value);

                    const res = await fetch('<?= url('admin/settings/save-id-card-color') ?>', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();

                    if (data.success) {
                        toast.style.display = 'inline-flex';
                        setTimeout(() => { toast.style.display = 'none'; }, 3500);
                    } else {
                        alert(data.message || 'Error saving ID card color.');
                    }
                } catch (e) {
                    alert('Network error while saving color.');
                } finally {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = origHtml;
                }
            });
        }
    })();
    </script>

</body>
</html>
