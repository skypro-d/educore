<?php
/**
 * views/admin/attendance_scanner.php
 * High-Speed Hands-Free Attendance Terminal for HIPPOINT X7-1000 USB Scanner
 */
$pageTitle = 'Attendance Scanner Terminal';
?>

<div class="row g-3 mb-4 align-items-center">
    <div class="col-12 col-md-7">
        <div class="d-flex align-items-center gap-2">
            <h3 class="fw-bold mb-0" style="color: #0f172a; font-size: 1.6rem; letter-spacing: -0.02em;">
                <i class="ti ti-scan text-primary me-2"></i>EduCore Attendance Terminal
            </h3>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small fw-semibold">
                <i class="ti ti-usb me-1"></i>HIPPOINT X7-1000
            </span>
        </div>
        <p class="text-muted mb-0 small mt-1">Automatic high-speed IN/OUT attendance scanning with duplicate protection and parent SMS/Email dispatch.</p>
    </div>
    <div class="col-12 col-md-5 text-md-end d-flex gap-2 justify-content-md-end align-items-center flex-wrap">
        <div class="d-flex align-items-center bg-white px-3 py-2 rounded-3 border shadow-sm">
            <i class="ti ti-clock text-primary me-2"></i>
            <span id="liveClock" class="fw-bold text-dark font-monospace" style="font-size: 1rem;">--:--:-- --</span>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-3 shadow-sm" id="btnAudioToggle" onclick="toggleAudio()">
            <i class="ti ti-volume text-success me-1" id="soundIcon"></i> Sound: <span id="soundText" class="fw-semibold">ON</span>
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#testQrModal">
            <i class="ti ti-qrcode me-1"></i> Scan Test Student QR
        </button>
        <a href="<?= url('admin/attendance') ?>" class="btn btn-outline-primary btn-sm rounded-3 shadow-sm">
            <i class="ti ti-table me-1"></i> Daily Sheet
        </a>
    </div>
</div>

<!-- TOP METRIC CARDS -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted small fw-medium">Active Enrolled</span>
                <span class="p-2 rounded-circle bg-light text-secondary"><i class="ti ti-users"></i></span>
            </div>
            <div class="h3 fw-bold mb-0 text-dark" id="statTotalStudents"><?= number_format((int)$stats['total_students']) ?></div>
            <div class="text-muted" style="font-size: 11px;">Students registered</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted small fw-medium">Present Today (IN)</span>
                <span class="p-2 rounded-circle bg-success-subtle text-success"><i class="ti ti-login"></i></span>
            </div>
            <div class="h3 fw-bold mb-0 text-success" id="statCheckedIn"><?= number_format((int)$stats['checked_in']) ?></div>
            <div class="text-muted" style="font-size: 11px;"><span id="statLateCount"><?= (int)$stats['late_count'] ?></span> marked late arrival</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted small fw-medium">Departed (OUT)</span>
                <span class="p-2 rounded-circle bg-primary-subtle text-primary"><i class="ti ti-logout"></i></span>
            </div>
            <div class="h3 fw-bold mb-0 text-primary" id="statCheckedOut"><?= number_format((int)$stats['checked_out']) ?></div>
            <div class="text-muted" style="font-size: 11px;">Recorded checkout scans</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted small fw-medium">Currently on Campus</span>
                <span class="p-2 rounded-circle bg-warning-subtle text-warning"><i class="ti ti-building-community"></i></span>
            </div>
            <div class="h3 fw-bold mb-0 text-dark" id="statOnCampus"><?= number_format((int)$stats['on_campus']) ?></div>
            <div class="text-muted" style="font-size: 11px;">Still inside school gates</div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- MAIN SCANNER TERMINAL COLUMN -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <!-- TERMINAL HEADER -->
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="scanner-dot pulse-green" id="scannerStatusDot"></span>
                    <span class="fw-bold text-dark small" id="scannerStatusText">SCANNER READY</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-secondary border px-2 py-1 small">
                        <i class="ti ti-shield-check text-success me-1"></i><?= $debounceMins ?>m Debounce Protection
                    </span>
                </div>
            </div>

            <div class="card-body p-4 text-center">
                <!-- HIDDEN SCANNER INPUT (Always focused for USB HID wedge) -->
                <input type="text" id="scannerInput" autocomplete="off" autofocus
                       style="position: fixed; opacity: 0; width: 1px; height: 1px; top: 0; left: 0; z-index: -1;">

                <!-- SCAN TARGET AREA (IDLE STATE) -->
                <div id="scanTargetBox" class="scan-target-box p-4 rounded-4 mb-4" onclick="focusScannerInput()">
                    <div class="scanner-beam-animation"></div>
                    <div class="scan-icon-wrap mx-auto mb-3">
                        <i class="ti ti-qrcode fs-1 text-primary"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1" id="scanPromptTitle">SCAN STUDENT QR CODE</h4>
                    <p class="text-muted small mb-0" id="scanPromptDesc">
                        Present EduCore ID card or mobile QR to the <strong>HIPPOINT X7-1000</strong> scanner.
                    </p>
                    <div class="mt-3">
                        <span class="badge bg-light text-muted border px-3 py-1 font-monospace" style="font-size: 11px;">
                            <i class="ti ti-point-filled text-success me-1"></i>Listening to USB HID input...
                        </span>
                    </div>
                </div>

                <!-- SCAN RESULT CARD (Revealed upon scan) -->
                <div id="scanResultCard" class="card border border-2 shadow-sm rounded-4 p-4 text-center" style="display: none; transition: all 0.3s ease;">
                    <!-- Result Status Badge -->
                    <div class="mb-3">
                        <span id="resultBadge" class="badge bg-success-subtle text-success fs-6 fw-bold px-4 py-2 rounded-pill shadow-xs">
                            <i class="ti ti-check-circle me-1"></i> <span id="resultBadgeText">CHECK-IN RECORDED</span>
                        </span>
                    </div>

                    <!-- Student Avatar & Details -->
                    <div class="d-flex flex-column align-items-center mb-3">
                        <div id="resultAvatarWrap" class="avatar-lg rounded-circle overflow-hidden shadow-sm border border-3 mb-2" style="width: 90px; height: 90px; background: #f8fafc;">
                            <img id="resultPhoto" src="" alt="Student Photo" class="w-100 h-100 object-fit-cover" style="display: none;">
                            <div id="resultInitials" class="w-100 h-100 d-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold fs-2">--</div>
                        </div>
                        <h4 class="fw-bold text-dark mb-1" id="resultStudentName">Student Name</h4>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary text-white fw-semibold px-2 py-1" id="resultClassName">Class</span>
                            <span class="badge bg-light text-secondary border px-2 py-1 font-monospace" id="resultAdmNo">ADM-0000</span>
                        </div>
                    </div>

                    <!-- Time & Alert Summary Box -->
                    <div class="bg-light p-3 rounded-3 mb-3 text-start small border">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted"><i class="ti ti-clock me-1"></i>Scan Time:</span>
                            <strong class="text-dark font-monospace fs-6" id="resultScanTime">--:-- --</strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="ti ti-info-circle me-1"></i>Status Message:</span>
                            <span class="fw-semibold text-dark text-end" id="resultMessageText">--</span>
                        </div>
                    </div>

                    <!-- Countdown Auto-Reset Progress Bar -->
                    <div class="progress" style="height: 5px; background: #e2e8f0;">
                        <div id="resetProgressBar" class="progress-bar bg-primary" role="progressbar" style="width: 100%; transition: width 0.1s linear;"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2" style="font-size: 11px;">
                        <span class="text-muted">Returning to Ready state automatically...</span>
                        <button type="button" class="btn btn-link p-0 text-muted small text-decoration-none" onclick="resetToReadyNow()">Ready Now</button>
                    </div>
                </div>

                <!-- MANUAL TEST INPUT ACCORDION (For development or manual fallbacks) -->
                <div class="mt-4 pt-3 border-top text-start">
                    <button class="btn btn-link text-muted p-0 small text-decoration-none d-flex align-items-center" type="button" data-bs-toggle="collapse" data-bs-target="#manualTestCollapse">
                        <i class="ti ti-keyboard me-1"></i> Manual Token Entry / Diagnostic Override
                        <i class="ti ti-chevron-down ms-1"></i>
                    </button>
                    <div class="collapse mt-2" id="manualTestCollapse">
                        <div class="input-group input-group-sm">
                            <input type="text" id="manualInput" class="form-control" placeholder="Paste QR URL, token, or Admission No...">
                            <button class="btn btn-primary" type="button" onclick="submitManualScan()">Submit Test Scan</button>
                        </div>
                        <div class="text-muted mt-1" style="font-size: 11px;">
                            Simulates scanner keystroke stream. Test with: student ID number, admission number, or full scanned QR URL.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RECENT SCAN STREAM COLUMN -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="ti ti-activity text-primary me-2"></i>Live Scan Stream
                </h6>
                <span class="badge bg-light text-muted border" id="recentScansCount">
                    <?= count($recentScans) ?> scan(s) today
                </span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" id="recentScansList" style="max-height: 540px; overflow-y: auto;">
                    <?php if (empty($recentScans)): ?>
                        <div class="p-4 text-center text-muted" id="noScansNotice">
                            <i class="ti ti-scan text-muted mb-2 fs-2 d-block opacity-50"></i>
                            <div class="small fw-semibold">No scans recorded yet today.</div>
                            <div style="font-size: 11px;">Scanned students will appear here in real-time.</div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentScans as $scan):
                            $studentName = trim($scan['first_name'] . ' ' . $scan['last_name']);
                            $isOut = !empty($scan['time_out']);
                            $displayTime = $isOut ? date('g:i A', strtotime($scan['time_out'])) : date('g:i A', strtotime($scan['time_in']));
                            $initials = strtoupper(substr($scan['first_name'] ?? 'S', 0, 1) . substr($scan['last_name'] ?? '', 0, 1));
                        ?>
                            <div class="list-group-item px-4 py-3 d-flex align-items-center justify-content-between border-bottom">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar rounded-circle overflow-hidden flex-shrink-0" style="width: 42px; height: 42px; background: #f1f5f9;">
                                        <?php if (!empty($scan['passport_photo'])): ?>
                                            <img src="<?= url('uploads/' . $scan['passport_photo']) ?>" class="w-100 h-100 object-fit-cover" alt="<?= e($studentName) ?>">
                                        <?php else: ?>
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold" style="font-size: 13px;">
                                                <?= $initials ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small mb-0"><?= e($studentName) ?></div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            <span class="badge bg-light text-secondary border px-1"><?= e($scan['class_name'] ?: 'N/A') ?></span>
                                            <span class="ms-1 font-monospace"><?= e($scan['admission_number'] ?: $scan['application_number']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold small text-dark font-monospace"><?= $displayTime ?></div>
                                    <?php if ($isOut): ?>
                                        <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-0" style="font-size: 10px;">OUT</span>
                                    <?php elseif ($scan['status'] === 'Late'): ?>
                                        <span class="badge bg-warning-subtle text-warning fw-semibold px-2 py-0" style="font-size: 10px;">IN (LATE)</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success fw-semibold px-2 py-0" style="font-size: 10px;">IN</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
</div>

<!-- TEST STUDENT QR MODAL -->
<div class="modal fade" id="testQrModal" tabindex="-1" aria-labelledby="testQrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom py-3 px-4">
                <h5 class="modal-title fw-bold text-dark" id="testQrModalLabel">
                    <i class="ti ti-qrcode text-primary me-2"></i>Live Test Student QR Codes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <p class="text-muted small mb-4">
                    Point your <strong>HIPPOINT X7-1000</strong> scanner directly at any QR code on this screen.
                    The scanner will beep, dismiss this modal automatically, and record attendance in real-time.
                </p>

                <div class="row g-3 justify-content-center">
                    <?php if (!empty($sampleStudents)): ?>
                        <?php foreach ($sampleStudents as $idx => $st):
                            $sName = trim($st['first_name'] . ' ' . $st['last_name']);
                            $qrVal = !empty($st['qr_data']) ? $st['qr_data'] : ($st['admission_number'] ?: 'ATTENDANCE-STD-' . $st['id']);
                            $qrImgSrc = 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&margin=4&data=' . urlencode($qrVal);
                        ?>
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="card border rounded-3 p-3 text-center bg-light h-100 shadow-2xs">
                                    <div class="bg-white p-2 rounded-3 border mb-2 mx-auto d-inline-block shadow-xs">
                                        <img src="<?= $qrImgSrc ?>" alt="QR for <?= e($sName) ?>" style="width: 130px; height: 130px; display: block;">
                                    </div>
                                    <div class="fw-bold text-dark small text-truncate" title="<?= e($sName) ?>"><?= e($sName) ?></div>
                                    <div class="text-muted mb-1" style="font-size: 11px;"><?= e($st['class_name'] ?: 'Enrolled') ?></div>
                                    <span class="badge bg-white text-secondary border font-monospace" style="font-size: 10px;">
                                        <?= e($st['admission_number'] ?: $qrVal) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-muted py-4 small">No active enrolled students found in system.</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer bg-light border-top py-2 px-4 d-flex justify-content-between">
                <span class="text-muted small" style="font-size: 11px;">
                    <i class="ti ti-device-laptop me-1"></i>Tip: Keep your monitor at normal brightness and hold scanner 15–20cm away.
                </span>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
/* KIOSK SCANNER STYLING */
.scan-target-box {
    background: #f8fafc;
    border: 2.5px dashed #cbd5e1;
    position: relative;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.25s ease;
}
.scan-target-box:hover, .scan-target-box.focus-active {
    border-color: #3b82f6;
    background: #eff6ff;
}
.scan-icon-wrap {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}
.scanner-beam-animation {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, transparent, #3b82f6, transparent);
    animation: scannerBeam 2.4s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    pointer-events: none;
    opacity: 0.7;
}
@keyframes scannerBeam {
    0% { top: 0; opacity: 0; }
    15% { opacity: 1; }
    85% { opacity: 1; }
    100% { top: 100%; opacity: 0; }
}
.scanner-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    display: inline-block;
}
.pulse-green {
    background: #10b981;
    box-shadow: 0 0 0 rgba(16, 185, 129, 0.4);
    animation: pulseDot 1.8s infinite;
}
.pulse-yellow {
    background: #f59e0b;
    box-shadow: 0 0 0 rgba(245, 158, 11, 0.4);
    animation: pulseDot 1s infinite;
}
@keyframes pulseDot {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}
.bg-primary-subtle { background-color: rgba(59, 130, 246, 0.12) !important; color: #2563eb !important; }
.bg-success-subtle { background-color: rgba(16, 185, 129, 0.12) !important; color: #059669 !important; }
.bg-warning-subtle { background-color: rgba(245, 158, 11, 0.12) !important; color: #d97706 !important; }
.bg-danger-subtle { background-color: rgba(239, 68, 68, 0.12) !important; color: #dc2626 !important; }
</style>

<script>
/**
 * EduCore HIPPOINT X7-1000 USB HID Attendance Client Engine
 */
let audioEnabled = true;
let isProcessingScan = false;
let autoResetTimer = null;
let progressInterval = null;
const AUTO_RESET_MS = <?= (int)$autoResetSeconds * 1000 ?>;

// Web Audio API Synthesizer (Zero-latency offline audio feedback)
let audioCtx = null;
function getAudioContext() {
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    }
    if (audioCtx.state === 'suspended') {
        audioCtx.resume();
    }
    return audioCtx;
}

function playTone(type) {
    if (!audioEnabled) return;
    try {
        const ctx = getAudioContext();
        const now = ctx.currentTime;
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);

        if (type === 'check_in') {
            // High happy double chime: 880Hz -> 1320Hz
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, now);
            osc.frequency.setValueAtTime(1320, now + 0.08);
            gain.gain.setValueAtTime(0.3, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.28);
            osc.start(now);
            osc.stop(now + 0.28);
        } else if (type === 'check_out') {
            // Warm pleasant chord: 523Hz -> 659Hz
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(523.25, now);
            osc.frequency.setValueAtTime(659.25, now + 0.1);
            gain.gain.setValueAtTime(0.35, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.35);
            osc.start(now);
            osc.stop(now + 0.35);
        } else if (type === 'duplicate') {
            // Warning double boop: 380Hz
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(380, now);
            osc.frequency.setValueAtTime(0, now + 0.08);
            osc.frequency.setValueAtTime(380, now + 0.12);
            gain.gain.setValueAtTime(0.25, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.3);
            osc.start(now);
            osc.stop(now + 0.3);
        } else {
            // Low buzz for error/not found: 200Hz
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(200, now);
            gain.gain.setValueAtTime(0.4, now);
            gain.gain.exponentialRampToValueAtTime(0.001, now + 0.4);
            osc.start(now);
            osc.stop(now + 0.4);
        }
    } catch (e) {
        console.warn('Audio tone could not play:', e);
    }
}

function toggleAudio() {
    audioEnabled = !audioEnabled;
    document.getElementById('soundIcon').className = audioEnabled ? 'ti ti-volume text-success me-1' : 'ti ti-volume-off text-danger me-1';
    document.getElementById('soundText').innerText = audioEnabled ? 'ON' : 'OFF';
}

// Digital Clock
function updateClock() {
    const now = new Date();
    document.getElementById('liveClock').innerText = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
}
setInterval(updateClock, 1000);
updateClock();

// Focus Management
function focusScannerInput() {
    const input = document.getElementById('scannerInput');
    if (input) {
        input.focus();
        document.getElementById('scanTargetBox').classList.add('focus-active');
    }
}
window.addEventListener('click', function(e) {
    // If not clicking an interactive form input, re-focus the scanner input
    const tag = e.target.tagName;
    if (tag !== 'INPUT' && tag !== 'BUTTON' && tag !== 'A' && tag !== 'SELECT' && tag !== 'TEXTAREA') {
        focusScannerInput();
    }
});
document.addEventListener('DOMContentLoaded', focusScannerInput);

// Dual-Layer USB HID Barcode Listener
let usbBuffer = '';
let lastKeyTime = Date.now();
let usbBurstTimeout = null;

function processIncomingBarcode(code) {
    code = (code || '').trim();
    if (code.length >= 2) {
        executeScan(code);
    }
}

// 1. Direct listener on focused input element
const scanInputEl = document.getElementById('scannerInput');
if (scanInputEl) {
    scanInputEl.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(usbBurstTimeout);
            const val = this.value.trim() || usbBuffer.trim();
            this.value = '';
            usbBuffer = '';
            processIncomingBarcode(val);
        }
    });

    scanInputEl.addEventListener('input', function() {
        clearTimeout(usbBurstTimeout);
        // If scanner dumps without Enter key, auto-dispatch after 120ms idle
        usbBurstTimeout = setTimeout(() => {
            const val = scanInputEl.value.trim();
            if (val.length >= 4) {
                scanInputEl.value = '';
                usbBuffer = '';
                processIncomingBarcode(val);
            }
        }, 120);
    });
}

// 2. Global window keystroke interceptor (in case input momentarily lost focus)
window.addEventListener('keydown', function(e) {
    // Ignore keys typed into manual diagnostic input
    if (e.target.id === 'manualInput') {
        return;
    }

    const now = Date.now();
    // USB HID scanners dump characters within 10-35ms intervals
    if (now - lastKeyTime > 350) {
        usbBuffer = '';
    }
    lastKeyTime = now;

    if (e.key === 'Enter') {
        clearTimeout(usbBurstTimeout);
        const scannedCode = (scanInputEl ? scanInputEl.value.trim() : '') || usbBuffer.trim();
        if (scannedCode.length >= 2) {
            e.preventDefault();
            if (scanInputEl) scanInputEl.value = '';
            usbBuffer = '';
            processIncomingBarcode(scannedCode);
        }
    } else if (e.key.length === 1) {
        usbBuffer += e.key;
        clearTimeout(usbBurstTimeout);
        // Fallback auto-trigger if scanner suffix is disabled
        usbBurstTimeout = setTimeout(() => {
            const scannedCode = (scanInputEl ? scanInputEl.value.trim() : '') || usbBuffer.trim();
            if (scannedCode.length >= 4) {
                if (scanInputEl) scanInputEl.value = '';
                usbBuffer = '';
                processIncomingBarcode(scannedCode);
            }
        }, 130);
    }
});

// Manual Form Submission
function submitManualScan() {
    const val = document.getElementById('manualInput').value.trim();
    if (val) {
        executeScan(val);
        document.getElementById('manualInput').value = '';
    }
}

// Core Execution
function executeScan(qrPayload) {
    if (isProcessingScan) return;
    isProcessingScan = true;

    clearTimeout(usbBurstTimeout);
    usbBuffer = '';
    if (scanInputEl) scanInputEl.value = '';

    clearTimeout(autoResetTimer);
    clearInterval(progressInterval);

    // Visual feedback: Scanner Busy
    const dot = document.getElementById('scannerStatusDot');
    dot.className = 'scanner-dot pulse-yellow';
    document.getElementById('scannerStatusText').innerText = 'IDENTIFYING STUDENT...';

    // Auto-dismiss test QR modal if open so result card is visible
    const modalEl = document.getElementById('testQrModal');
    if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
    }

    fetch('<?= url("admin/attendance-scanner/scan") ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ qr_data: qrPayload })
    })
    .then(res => res.json())
    .then(data => {
        handleScanResult(data);
    })
    .catch(err => {
        console.error('Scan error:', err);
        playTone('error');
        showScanResultCard({
            badge_status: 'danger',
            title: 'NETWORK / SYSTEM ERROR',
            message: 'Could not connect to EduCore attendance server. Please verify connection.',
            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true }),
            student: {
                name: 'Communication Failure',
                class_name: 'Error',
                admission_number: 'N/A'
            }
        });
        startAutoResetTimer();
    });
}

function handleScanResult(data) {
    if (data.action === 'check_in') {
        playTone('check_in');
        showScanResultCard(data);
        prependRecentScan(data, 'IN');
        refreshLiveStats();
    } else if (data.action === 'check_out') {
        playTone('check_out');
        showScanResultCard(data);
        prependRecentScan(data, 'OUT');
        refreshLiveStats();
    } else if (data.action === 'duplicate_in' || data.action === 'duplicate_out') {
        playTone('duplicate');
        showScanResultCard(data);
    } else {
        playTone('error');
        showScanResultCard(data);
    }

    startAutoResetTimer();
}

function showScanResultCard(data) {
    const targetBox = document.getElementById('scanTargetBox');
    const resultCard = document.getElementById('scanResultCard');

    targetBox.style.display = 'none';
    resultCard.style.display = 'block';

    // Status Badge
    const badge = document.getElementById('resultBadge');
    const badgeText = document.getElementById('resultBadgeText');
    badge.className = `badge bg-${data.badge_status || 'secondary'}-subtle text-${data.badge_status || 'secondary'} fs-6 fw-bold px-4 py-2 rounded-pill shadow-xs`;
    badgeText.innerText = data.title || (data.success ? 'ATTENDANCE LOGGED' : 'SCAN ALERT');

    // Student Info
    const s = data.student || {};
    document.getElementById('resultStudentName').innerText = s.name || 'Unknown Student';
    document.getElementById('resultClassName').innerText = s.class_name || 'N/A';
    document.getElementById('resultAdmNo').innerText = s.admission_number || 'N/A';
    document.getElementById('resultScanTime').innerText = data.time || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
    document.getElementById('resultMessageText').innerText = data.message || '';

    // Photo or Initials
    const photoImg = document.getElementById('resultPhoto');
    const initialsDiv = document.getElementById('resultInitials');
    if (s.photo) {
        photoImg.src = s.photo;
        photoImg.style.display = 'block';
        initialsDiv.style.display = 'none';
    } else {
        photoImg.style.display = 'none';
        initialsDiv.style.display = 'flex';
        const initials = (s.name || 'ST').split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase();
        initialsDiv.innerText = initials || 'ST';
    }

    // Border highlight
    resultCard.className = `card border border-2 border-${data.badge_status || 'secondary'} shadow-sm rounded-4 p-4 text-center`;
}

function startAutoResetTimer() {
    const progressBar = document.getElementById('resetProgressBar');
    progressBar.style.width = '100%';
    const startTime = Date.now();

    clearInterval(progressInterval);
    progressInterval = setInterval(() => {
        const elapsed = Date.now() - startTime;
        const remainingPct = Math.max(0, 100 - (elapsed / AUTO_RESET_MS) * 100);
        progressBar.style.width = remainingPct + '%';

        if (remainingPct <= 0) {
            clearInterval(progressInterval);
            resetToReadyNow();
        }
    }, 40);
}

function resetToReadyNow() {
    clearInterval(progressInterval);
    clearTimeout(autoResetTimer);

    document.getElementById('scanResultCard').style.display = 'none';
    document.getElementById('scanTargetBox').style.display = 'block';

    const dot = document.getElementById('scannerStatusDot');
    dot.className = 'scanner-dot pulse-green';
    document.getElementById('scannerStatusText').innerText = 'SCANNER READY';

    isProcessingScan = false;
    focusScannerInput();
}

function prependRecentScan(data, type) {
    const list = document.getElementById('recentScansList');
    const notice = document.getElementById('noScansNotice');
    if (notice) notice.remove();

    const s = data.student || {};
    const initials = (s.name || 'ST').split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase();
    const isOut = (type === 'OUT');

    const item = document.createElement('div');
    item.className = 'list-group-item px-4 py-3 d-flex align-items-center justify-content-between border-bottom bg-success-subtle';
    item.style.transition = 'background 1.5s ease';

    const photoHtml = s.photo
        ? `<img src="${s.photo}" class="w-100 h-100 object-fit-cover" alt="${s.name}">`
        : `<div class="w-100 h-100 d-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold" style="font-size: 13px;">${initials}</div>`;

    const badgeHtml = isOut
        ? `<span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-0" style="font-size: 10px;">OUT</span>`
        : `<span class="badge bg-success-subtle text-success fw-semibold px-2 py-0" style="font-size: 10px;">IN</span>`;

    item.innerHTML = `
        <div class="d-flex align-items-center gap-3">
            <div class="avatar rounded-circle overflow-hidden flex-shrink-0" style="width: 42px; height: 42px; background: #f1f5f9;">
                ${photoHtml}
            </div>
            <div>
                <div class="fw-bold text-dark small mb-0">${s.name || 'Student'}</div>
                <div class="text-muted" style="font-size: 11px;">
                    <span class="badge bg-light text-secondary border px-1">${s.class_name || 'N/A'}</span>
                    <span class="ms-1 font-monospace">${s.admission_number || 'N/A'}</span>
                </div>
            </div>
        </div>
        <div class="text-end">
            <div class="fw-bold small text-dark font-monospace">${data.time}</div>
            ${badgeHtml}
        </div>
    `;

    list.insertBefore(item, list.firstChild);
    setTimeout(() => {
        item.classList.remove('bg-success-subtle');
    }, 1200);

    const countBadge = document.getElementById('recentScansCount');
    if (countBadge) {
        const count = list.querySelectorAll('.list-group-item').length;
        countBadge.innerText = `${count} scan(s) today`;
    }
}

function refreshLiveStats() {
    fetch('<?= url("admin/attendance-scanner/recent") ?>')
        .then(res => res.json())
        .then(data => {
            if (data.success && data.stats) {
                document.getElementById('statTotalStudents').innerText = data.stats.total_students;
                document.getElementById('statCheckedIn').innerText = data.stats.checked_in;
                document.getElementById('statCheckedOut').innerText = data.stats.checked_out;
                document.getElementById('statOnCampus').innerText = data.stats.on_campus;
                document.getElementById('statLateCount').innerText = data.stats.late_count;
            }
        })
        .catch(err => console.warn('Stat poll error:', err));
}

// Background poll for stats every 30 seconds
setInterval(refreshLiveStats, 30000);
</script>
