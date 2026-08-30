<style>
    .id-card-wrapper {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
        justify-content: center;
        margin-top: 2rem;
        font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
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
        background: #0f172a;
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
        object-fit: cover;
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
        background: linear-gradient(135deg, #0052cc 0%, #0080ff 100%);
        padding: 5px;
        box-shadow: 0 8px 20px rgba(0, 82, 204, 0.35);
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
        color: #0040a8;
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
        background: #0052cc;
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
        background: #0052cc;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0, 82, 204, 0.25);
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
        background: #0052cc;
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
        background: #0f172a;
        width: 100%;
    }

    @media print {
        .parent-sidebar, .parent-mobile-bar, .no-print, div[style*="text-align:center"] {
            display: none !important;
        }
        .parent-main {
            margin-left: 0 !important;
        }
        .parent-content {
            padding: 0 !important;
        }
        .id-card-wrapper {
            margin-top: 0 !important;
            gap: 20px !important;
        }
        .id-card {
            box-shadow: none !important;
            border: 1px solid #94a3b8 !important;
            page-break-inside: avoid;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center no-print">
    <h1 class="h3 mb-0"><i class="ti ti-id text-primary" style="margin-right:8px"></i>Child Student ID Card</h1>
    <button onclick="window.print()" class="btn btn-primary" style="background:var(--parent-primary, #0052cc); border-color:var(--parent-primary, #0052cc);"><i class="ti ti-printer" style="margin-right:6px"></i>Print ID Card</button>
</div>

<?php
$fullSchoolName = setting('school_name', 'BLUEFIELD INTERNATIONAL SCHOOL');
$nameParts = explode(' ', trim($fullSchoolName), 2);
$mainName = strtoupper($nameParts[0] ?? 'BLUEFIELD');
$subName  = strtoupper($nameParts[1] ?? 'INTERNATIONAL SCHOOL');

// School Logo URL calculation
$logoSetting = setting('school_logo');
$logoUrl = '';
if ($logoSetting) {
    if (str_starts_with($logoSetting, 'http://') || str_starts_with($logoSetting, 'https://')) {
        $logoUrl = $logoSetting;
    } else {
        $cleanLogo = ltrim(str_replace('uploads/', '', $logoSetting), '/');
        $logoUrl = url('uploads/' . $cleanLogo);
    }
}

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
                    <linearGradient id="blueWaveGradPar" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#0052cc" />
                        <stop offset="100%" stop-color="#0066ff" />
                    </linearGradient>
                </defs>
                <rect width="330" height="195" fill="#0f172a" />
                <path d="M 0,0 L 210,0 C 170,70 110,120 0,165 Z" fill="url(#blueWaveGradPar)" />
                <path d="M 0,0 L 140,0 C 240,60 290,130 0,195 Z" fill="url(#blueWaveGradPar)" opacity="0.85" />
                <path d="M 0,195 C 100,135 230,190 330,140 L 330,195 Z" fill="#ffffff" />
            </svg>

            <div class="id-school-branding">
                <div class="id-school-logo-box">
                    <?php if (!empty($logoUrl)): ?>
                        <img src="<?= e($logoUrl) ?>" alt="School Logo" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='block';">
                        <div style="display:none;">
                            <svg width="32" height="32" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M18 2 L33 10.5 V25.5 L18 34 L3 25.5 V10.5 Z" stroke="#00d2ff" stroke-width="3" fill="none" stroke-linejoin="round"/>
                                <path d="M18 9 L27 14.25 V21.75 L18 27 L9 21.75 V14.25 Z" fill="url(#bluefieldLogoGradPar)" />
                                <defs>
                                    <linearGradient id="bluefieldLogoGradPar" x1="9" y1="9" x2="27" y2="27">
                                        <stop offset="0%" stop-color="#00d2ff"/>
                                        <stop offset="100%" stop-color="#0052cc"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        </div>
                    <?php else: ?>
                        <svg width="32" height="32" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 2 L33 10.5 V25.5 L18 34 L3 25.5 V10.5 Z" stroke="#00d2ff" stroke-width="3" fill="none" stroke-linejoin="round"/>
                            <path d="M18 9 L27 14.25 V21.75 L18 27 L9 21.75 V14.25 Z" fill="url(#bluefieldLogoGradParFallback)" />
                            <defs>
                                <linearGradient id="bluefieldLogoGradParFallback" x1="9" y1="9" x2="27" y2="27">
                                    <stop offset="0%" stop-color="#00d2ff"/>
                                    <stop offset="100%" stop-color="#0052cc"/>
                                </linearGradient>
                            </defs>
                        </svg>
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
                    Parent/Guardian: <?= e($student['parent_name'] ?: ($student['emergency_contact_name'] ?? 'Mr Azzan')) ?><br>
                    Phone: <?= e($student['parent_phone'] ?: ($student['emergency_contact_phone'] ?? '07081306993')) ?><br>
                    Address: <?= e($student['home_address'] ?: 'akobo OLORUDA') ?>
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
