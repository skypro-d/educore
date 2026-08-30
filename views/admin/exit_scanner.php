<?php
// views/admin/exit_scanner.php
?>
<script src="https://unpkg.com/html5-qrcode"></script>

<div class="row g-3 mb-4 align-items-center">
    <div class="col-12 col-md-6">
        <h3 class="fw-bold mb-1" style="color: #0f172a; font-size: 1.5rem; letter-spacing: -0.02em;">
            <i class="ti ti-door-exit text-primary me-2"></i>Student Exit Scanner
        </h3>
        <p class="text-muted mb-0 small">Scan EduCore student ID cards at school gates to verify exit and trigger automatic parent SMS alerts.</p>
    </div>
    <div class="col-12 col-md-6 text-md-end d-flex gap-2 justify-content-md-end align-items-center flex-wrap">
        <div class="d-flex align-items-center bg-white px-3 py-2 rounded-3 border shadow-sm">
            <i class="ti ti-clock text-primary me-2"></i>
            <span class="small text-muted me-1">Scheduled Dismissal:</span>
            <span class="badge bg-primary-subtle text-primary fw-bold"><?= date('g:i A', strtotime($normalCloseTime)) ?></span>
        </div>
        <div class="d-flex align-items-center bg-white px-3 py-2 rounded-3 border shadow-sm">
            <span class="small text-muted me-2">Current Gate:</span>
            <select id="activeGateSelect" class="form-select form-select-sm fw-semibold border-0 bg-light" style="width: auto; min-width: 150px;">
                <option value="">-- Select Gate --</option>
                <?php foreach ($gates as $g): ?>
                    <option value="<?= $g['id'] ?>"><?= e($g['gate_name']) ?> (<?= e($g['gate_code'] ?: 'Gate') ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <a href="<?= url('admin/exit-logs') ?>" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="ti ti-history me-1"></i> Exit Logs
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Scanner / Verification Column -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex gap-2 nav-pills" id="scannerModeTabs">
                    <button class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold active" id="tabCameraMode" onclick="setScannerMode('camera')">
                        <i class="ti ti-camera me-1"></i> Camera Scanner
                    </button>
                    <button class="btn btn-sm btn-light rounded-pill px-3 py-1 fw-semibold text-muted" id="tabUsbMode" onclick="setScannerMode('usb')">
                        <i class="ti ti-scan me-1"></i> USB / Laser Reader
                    </button>
                    <button class="btn btn-sm btn-light rounded-pill px-3 py-1 fw-semibold text-muted" id="tabManualMode" onclick="setScannerMode('manual')">
                        <i class="ti ti-search me-1"></i> Manual Search
                    </button>
                </div>
                <span id="scanStatusBadge" class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1">
                    <i class="ti ti-point-filled"></i> Ready
                </span>
            </div>

            <div class="card-body p-4">
                <!-- CAMERA SCANNER VIEW -->
                <div id="cameraModeContainer">
                    <div class="scanner-container text-center position-relative p-3 rounded-4" style="background: #0f172a; min-height: 320px; display: flex; flex-direction: column; justify-content: center; align-items: center;">
                        <div id="reader" style="width: 100%; max-width: 440px; border-radius: 12px; overflow: hidden;"></div>
                        <div id="cameraPlaceholder" class="text-white py-5">
                            <i class="ti ti-camera-selfie text-white-50" style="font-size: 3rem;"></i>
                            <h6 class="mt-3 text-white fw-bold">Camera is starting...</h6>
                            <p class="text-white-50 small mb-3">Position student QR code inside the camera frame.</p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2" id="cameraControls">
                        <div class="d-flex align-items-center gap-2">
                            <i class="ti ti-device-cctv text-muted"></i>
                            <select id="cameraSelect" class="form-select form-select-sm" style="max-width: 220px;" onchange="switchCamera(this.value)">
                                <option value="">Default Camera</option>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="restartCamera()">
                                <i class="ti ti-refresh me-1"></i> Restart
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btnAudioToggle" onclick="toggleAudioFeedback()">
                                <i class="ti ti-volume text-success me-1"></i> Sound: <span id="soundLabel">ON</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- USB SCANNER VIEW -->
                <div id="usbModeContainer" style="display: none;">
                    <div class="text-center py-5 px-4 rounded-4" style="background: #f8fafc; border: 2px dashed #cbd5e1;">
                        <div class="mb-3">
                            <span class="d-inline-flex p-3 rounded-circle bg-primary-subtle text-primary">
                                <i class="ti ti-barcode" style="font-size: 2.5rem;"></i>
                            </span>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">USB / Laser Scanner Active</h5>
                        <p class="text-muted small mb-4" style="max-width: 400px; margin: 0 auto;">
                            Aim your handheld or desktop barcode scanner at the student's EduCore ID card. Scans are captured automatically.
                        </p>
                        <div class="position-relative" style="max-width: 420px; margin: 0 auto;">
                            <input type="text" id="usbScannerInput" class="form-control form-control-lg text-center fw-bold shadow-sm"
                                   placeholder="Ready for scan..." autocomplete="off" autofocus>
                            <button class="btn btn-primary position-absolute top-50 end-0 translate-middle-y me-2 btn-sm rounded-pill px-3"
                                    type="button" onclick="submitUsbScan()">Verify</button>
                        </div>
                        <div class="mt-3 text-muted small">
                            <i class="ti ti-keyboard me-1"></i> Fast keystroke mode active. You do not need to click this box.
                        </div>
                    </div>
                </div>

                <!-- MANUAL SEARCH VIEW -->
                <div id="manualModeContainer" style="display: none;">
                    <div class="p-3 rounded-4 bg-light mb-3">
                        <label class="form-label small fw-bold text-secondary">Search Student by Name or Admission / Application No</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="ti ti-search text-muted"></i></span>
                            <input type="text" id="manualSearchInput" class="form-control" placeholder="Type student name or ID..." oninput="searchStudents(this.value)">
                        </div>
                    </div>
                    <div id="manualSearchResults" class="list-group list-group-flush rounded-3 border" style="max-height: 280px; overflow-y: auto; display: none;"></div>
                </div>
            </div>
        </div>

        <!-- Scan Feedback Notification Area -->
        <div id="scanResultBox" style="display: none;"></div>
    </div>

    <!-- Student Verification Panel & Live Stream -->
    <div class="col-12 col-lg-5">
        <!-- Active Verification Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" id="studentVerificationCard" style="display: none;">
            <div class="card-header py-3 px-4 bg-white border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark small text-uppercase letter-spacing-1">
                    <i class="ti ti-id-badge-2 text-primary me-1"></i> Student Verification
                </span>
                <span id="exitTypeBadge" class="badge bg-success-subtle text-success fw-bold px-3 py-1 rounded-pill">
                    Normal Exit
                </span>
            </div>
            <div class="card-body p-4 text-center" id="studentCardBody">
                <!-- Injected via JavaScript -->
            </div>
            <!-- Early Exit & Pickup Confirmation Actions -->
            <div class="card-footer bg-light p-3 border-top" id="confirmationFooter" style="display: none;">
                <form id="confirmExitForm" onsubmit="submitExitConfirmation(event)">
                    <input type="hidden" id="confStudentId" name="student_id">
                    <input type="hidden" id="confExitType" name="exit_type" value="normal">
                    
                    <div id="earlyExitSection" class="mb-3 text-start" style="display: none;">
                        <div class="alert alert-warning py-2 px-3 small d-flex align-items-center mb-2 rounded-3">
                            <i class="ti ti-alert-triangle me-2 fs-5"></i>
                            <div><strong>Early Exit Warning:</strong> Departure is before scheduled dismissal.</div>
                        </div>
                        <label class="form-label small fw-bold text-dark mb-1">Reason for Early Exit <span class="text-danger">*</span></label>
                        <select id="confExitReason" class="form-select form-select-sm mb-2" required>
                            <option value="Parent Request / Pickup">Parent Request / Pickup</option>
                            <option value="Medical / Feeling Unwell">Medical / Feeling Unwell</option>
                            <option value="Official School Permit / Event">Official School Permit / Event</option>
                            <option value="Scheduled Appointment">Scheduled Appointment</option>
                            <option value="Disciplinary Departure">Disciplinary Departure</option>
                            <option value="Other Authorized Reason">Other Authorized Reason</option>
                        </select>
                        <input type="text" id="confExitNotes" class="form-control form-select-sm" placeholder="Optional notes / remark...">
                    </div>

                    <div id="pickupSection" class="mb-3 text-start">
                        <label class="form-label small fw-bold text-dark mb-1">Authorized Person Collecting Student</label>
                        <div id="authorizedPickupsList" class="mb-2"></div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="ti ti-user"></i></span>
                            <input type="text" id="confPickupCustomName" class="form-control" placeholder="Or enter collector's name...">
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary w-50 btn-sm" onclick="cancelVerification()">
                            <i class="ti ti-x me-1"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success w-50 btn-sm fw-bold" id="btnConfirmSubmit">
                            <i class="ti ti-check me-1"></i> Confirm &amp; Log Exit
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Recent Exits Stream -->
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="ti ti-activity text-primary me-2"></i>Today's Recent Gate Exits
                </h6>
                <span class="badge bg-light text-muted small" id="recentExitsCount"><?= count($recentExits) ?> exits</span>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush" id="recentExitsList" style="max-height: 480px; overflow-y: auto;">
                    <?php if (empty($recentExits)): ?>
                        <div class="text-center py-5 text-muted small" id="noExitsNotice">
                            <i class="ti ti-door-exit d-block mb-2 fs-3 text-secondary opacity-50"></i>
                            No student exits recorded today yet.
                        </div>
                    <?php else: ?>
                        <?php foreach ($recentExits as $re): ?>
                            <div class="list-group-item px-3 py-3 border-bottom d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar avatar-md rounded-circle overflow-hidden flex-shrink-0" style="width: 42px; height: 42px; background: #e2e8f0;">
                                        <?php if ($re['passport_photo']): ?>
                                            <img src="<?= url('uploads/' . $re['passport_photo']) ?>" class="w-100 h-100 object-fit-cover">
                                        <?php else: ?>
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold">
                                                <?= strtoupper(substr($re['first_name'], 0, 1) . substr($re['last_name'], 0, 1)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small mb-0"><?= e($re['first_name'] . ' ' . $re['last_name']) ?></div>
                                        <div class="text-muted" style="font-size: 11px;">
                                            <span class="badge bg-light text-secondary border px-1"><?= e($re['class_name'] ?: 'General') ?></span>
                                            <span class="ms-1"><?= e($re['admission_number'] ?: $re['application_number']) ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold small text-dark"><?= date('g:i A', strtotime($re['exit_time'])) ?></div>
                                    <span class="badge <?= $re['exit_type'] === 'early' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' ?>" style="font-size: 10px;">
                                        <?= ucfirst($re['exit_type']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let html5QrcodeScanner = null;
let isScanning = false;
let currentScannerMode = 'camera';
let audioFeedbackEnabled = true;
let scanCooldown = false;
let autoResetTimer = null;

// Initialize Audio Context for synthetic audio tones
const audioCtx = new (window.AudioContext || window.webkitAudioContext)();

function playTone(type) {
    if (!audioFeedbackEnabled || !audioCtx) return;
    if (audioCtx.state === 'suspended') {
        audioCtx.resume();
    }
    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();
    osc.connect(gain);
    gain.connect(audioCtx.destination);

    const now = audioCtx.currentTime;

    if (type === 'success') {
        // High crisp chime
        osc.type = 'sine';
        osc.frequency.setValueAtTime(587.33, now); // D5
        osc.frequency.exponentialRampToValueAtTime(880, now + 0.15); // A5
        gain.gain.setValueAtTime(0.2, now);
        gain.gain.exponentialRampToValueAtTime(0.01, now + 0.35);
        osc.start(now);
        osc.stop(now + 0.35);
    } else if (type === 'warning') {
        // Double alert tone for early exit
        osc.type = 'triangle';
        osc.frequency.setValueAtTime(440, now);
        osc.frequency.setValueAtTime(370, now + 0.1);
        gain.gain.setValueAtTime(0.2, now);
        gain.gain.exponentialRampToValueAtTime(0.01, now + 0.25);
        osc.start(now);
        osc.stop(now + 0.25);
    } else if (type === 'error') {
        // Low error buzz
        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(160, now);
        osc.frequency.linearRampToValueAtTime(110, now + 0.3);
        gain.gain.setValueAtTime(0.25, now);
        gain.gain.exponentialRampToValueAtTime(0.01, now + 0.35);
        osc.start(now);
        osc.stop(now + 0.35);
    }
}

function toggleAudioFeedback() {
    audioFeedbackEnabled = !audioFeedbackEnabled;
    document.getElementById('soundLabel').innerText = audioFeedbackEnabled ? 'ON' : 'OFF';
    document.getElementById('btnAudioToggle').querySelector('i').className = audioFeedbackEnabled ? 'ti ti-volume text-success me-1' : 'ti ti-volume-off text-muted me-1';
}

// Gate Preference
const gateSelect = document.getElementById('activeGateSelect');
const savedGate = localStorage.getItem('educore_active_gate_id');
if (savedGate && gateSelect.querySelector(`option[value="${savedGate}"]`)) {
    gateSelect.value = savedGate;
}
gateSelect.addEventListener('change', function() {
    localStorage.setItem('educore_active_gate_id', this.value);
});

// Mode Switching
function setScannerMode(mode) {
    currentScannerMode = mode;
    document.getElementById('tabCameraMode').className = mode === 'camera' ? 'btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold active' : 'btn btn-sm btn-light rounded-pill px-3 py-1 fw-semibold text-muted';
    document.getElementById('tabUsbMode').className = mode === 'usb' ? 'btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold active' : 'btn btn-sm btn-light rounded-pill px-3 py-1 fw-semibold text-muted';
    document.getElementById('tabManualMode').className = mode === 'manual' ? 'btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold active' : 'btn btn-sm btn-light rounded-pill px-3 py-1 fw-semibold text-muted';

    document.getElementById('cameraModeContainer').style.display = mode === 'camera' ? 'block' : 'none';
    document.getElementById('usbModeContainer').style.display = mode === 'usb' ? 'block' : 'none';
    document.getElementById('manualModeContainer').style.display = mode === 'manual' ? 'block' : 'none';

    if (mode === 'camera') {
        startCamera();
    } else {
        stopCamera();
    }

    if (mode === 'usb') {
        setTimeout(() => document.getElementById('usbScannerInput').focus(), 150);
    }
}

// Camera Scanner Setup
function startCamera() {
    if (html5QrcodeScanner && isScanning) return;
    
    document.getElementById('cameraPlaceholder').style.display = 'block';
    const cameraSelect = document.getElementById('cameraSelect');
    const selectedCameraId = cameraSelect.value || undefined;

    html5QrcodeScanner = new Html5Qrcode("reader");
    const config = { fps: 12, qrbox: { width: 250, height: 250 } };

    const cameraConfig = selectedCameraId ? { deviceId: { exact: selectedCameraId } } : { facingMode: "environment" };

    html5QrcodeScanner.start(
        cameraConfig,
        config,
        (decodedText) => {
            onCodeScanned(decodedText, 'qr_camera');
        },
        (errorMessage) => {
            // passive scan loop
        }
    ).then(() => {
        isScanning = true;
        document.getElementById('cameraPlaceholder').style.display = 'none';
        document.getElementById('scanStatusBadge').innerHTML = '<i class="ti ti-point-filled text-success"></i> Active Scanning';
        document.getElementById('scanStatusBadge').className = 'badge bg-success-subtle text-success rounded-pill px-3 py-1';

        // Load camera devices
        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length > 0) {
                cameraSelect.innerHTML = '';
                devices.forEach((dev, idx) => {
                    const opt = document.createElement('option');
                    opt.value = dev.id;
                    opt.text = dev.label || `Camera ${idx + 1}`;
                    if (selectedCameraId && dev.id === selectedCameraId) opt.selected = true;
                    cameraSelect.appendChild(opt);
                });
            }
        }).catch(err => console.warn(err));
    }).catch(err => {
        console.error("Camera startup error: ", err);
        document.getElementById('scanStatusBadge').innerHTML = '<i class="ti ti-alert-circle text-danger"></i> Camera Offline';
        document.getElementById('scanStatusBadge').className = 'badge bg-danger-subtle text-danger rounded-pill px-3 py-1';
    });
}

function stopCamera() {
    if (html5QrcodeScanner && isScanning) {
        html5QrcodeScanner.stop().then(() => {
            isScanning = false;
        }).catch(err => console.warn(err));
    }
}

function restartCamera() {
    stopCamera();
    setTimeout(startCamera, 300);
}

function switchCamera(cameraId) {
    if (html5QrcodeScanner && isScanning) {
        html5QrcodeScanner.stop().then(() => {
            startCamera();
        });
    }
}

// Global USB Barcode Keystroke Listener
let usbBuffer = '';
let lastKeystrokeTime = Date.now();

window.addEventListener('keydown', function(e) {
    // If inside a text input or textarea (not the scanner input), allow standard typing
    const activeEl = document.activeElement;
    if (activeEl && activeEl.tagName === 'INPUT' && activeEl.id !== 'usbScannerInput' && activeEl.id !== 'manualSearchInput') {
        return;
    }

    const now = Date.now();
    if (now - lastKeystrokeTime > 200) {
        usbBuffer = '';
    }
    lastKeystrokeTime = now;

    if (e.key === 'Enter') {
        if (usbBuffer.length > 3) {
            e.preventDefault();
            onCodeScanned(usbBuffer, 'qr_usb');
            usbBuffer = '';
        }
    } else if (e.key.length === 1) {
        usbBuffer += e.key;
    }
});

function submitUsbScan() {
    const val = document.getElementById('usbScannerInput').value.trim();
    if (val) {
        onCodeScanned(val, 'qr_usb');
        document.getElementById('usbScannerInput').value = '';
    }
}

// Main Scan Processing Engine
function onCodeScanned(code, method = 'qr_camera') {
    if (scanCooldown) return;
    scanCooldown = true;
    clearTimeout(autoResetTimer);

    const gateId = document.getElementById('activeGateSelect').value;

    // Visual feedback
    document.getElementById('scanStatusBadge').innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verifying...';
    document.getElementById('scanStatusBadge').className = 'badge bg-warning-subtle text-warning rounded-pill px-3 py-1';

    fetch('<?= url("admin/exit-scanner/scan") ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            qr_data: code,
            gate_id: gateId,
            scan_method: method
        })
    })
    .then(res => res.json())
    .then(data => {
        handleScanResponse(data);
    })
    .catch(err => {
        console.error("Scan fetch error: ", err);
        showResultNotification('danger', 'Network / Server connection error. Please retry.');
        playTone('error');
        resumeScanning(2000);
    });
}

function handleScanResponse(data) {
    if (data.status === 'already_exited') {
        playTone('error');
        renderAlreadyExitedCard(data);
        showResultNotification('warning', data.message);
        resumeScanning(4000);
    } else if (data.status === 'confirm_required') {
        playTone('warning');
        renderConfirmationCard(data);
        showResultNotification('info', `Early Exit / Verification prompt for ${data.student.name}`);
        // Do not auto resume while waiting for confirmation
    } else if (data.status === 'success') {
        playTone('success');
        renderSuccessCard(data);
        showResultNotification('success', `Exit Verified: ${data.student.name} (${data.exit_type.toUpperCase()})`);
        prependRecentExit(data);
        resumeScanning(4000);
    } else {
        playTone('error');
        showResultNotification('danger', data.message || 'Verification failed.');
        resumeScanning(2500);
    }
}

function renderConfirmationCard(data) {
    const s = data.student;
    const isEarly = data.is_early;

    document.getElementById('studentVerificationCard').style.display = 'block';
    document.getElementById('confirmationFooter').style.display = 'block';
    document.getElementById('confStudentId').value = s.id;
    document.getElementById('confExitType').value = isEarly ? 'early' : 'normal';

    document.getElementById('exitTypeBadge').className = isEarly ? 'badge bg-warning-subtle text-warning fw-bold px-3 py-1 rounded-pill' : 'badge bg-success-subtle text-success fw-bold px-3 py-1 rounded-pill';
    document.getElementById('exitTypeBadge').innerText = isEarly ? 'Early Departure' : 'Normal Exit';

    // Show/hide early section
    document.getElementById('earlyExitSection').style.display = isEarly ? 'block' : 'none';

    // Render Authorized Pickups
    const pickupsContainer = document.getElementById('authorizedPickupsList');
    pickupsContainer.innerHTML = '';
    if (data.authorized_pickups && data.authorized_pickups.length > 0) {
        data.authorized_pickups.forEach(p => {
            const row = document.createElement('div');
            row.className = 'form-check p-2 border rounded-3 mb-1 bg-white';
            row.innerHTML = `
                <input class="form-check-input" type="radio" name="selected_pickup" id="pickup_${p.id}" value="${p.id}" data-name="${p.name}">
                <label class="form-check-label small d-flex justify-content-between align-items-center w-100" for="pickup_${p.id}">
                    <span class="fw-bold">${p.name} <span class="text-muted fw-normal">(${p.relationship})</span></span>
                    <span class="badge bg-light text-muted">${p.phone}</span>
                </label>
            `;
            pickupsContainer.appendChild(row);
        });
    } else {
        pickupsContainer.innerHTML = '<div class="text-muted small mb-2">No pre-registered pickup persons found for this student.</div>';
    }

    const photoHtml = s.photo ? `<img src="${s.photo}" class="w-100 h-100 object-fit-cover">` : `<div class="w-100 h-100 d-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold fs-3">${s.first_name[0]}${s.last_name[0]}</div>`;

    document.getElementById('studentCardBody').innerHTML = `
        <div class="avatar mx-auto mb-3 rounded-circle overflow-hidden shadow-sm" style="width: 84px; height: 84px; background: #f1f5f9;">
            ${photoHtml}
        </div>
        <h5 class="fw-bold text-dark mb-1">${s.name}</h5>
        <div class="mb-3">
            <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1">${s.class_name}</span>
            <span class="badge bg-light text-secondary border px-2 py-1 ms-1">${s.admission_number}</span>
        </div>
        <div class="bg-light p-3 rounded-3 text-start small">
            <div class="d-flex justify-content-between mb-1">
                <span class="text-muted">Parent / Guardian:</span>
                <span class="fw-semibold text-dark">${s.parent_name}</span>
            </div>
            <div class="d-flex justify-content-between">
                <span class="text-muted">Parent Contact:</span>
                <span class="fw-semibold text-dark">${s.parent_phone}</span>
            </div>
        </div>
    `;
}

function submitExitConfirmation(e) {
    e.preventDefault();
    const btn = document.getElementById('btnConfirmSubmit');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Logging...';

    const studentId = document.getElementById('confStudentId').value;
    const exitType = document.getElementById('confExitType').value;
    const gateId = document.getElementById('activeGateSelect').value;
    const exitReason = document.getElementById('confExitReason').value;
    const exitNotes = document.getElementById('confExitNotes').value;

    let pickupId = null;
    let pickupName = document.getElementById('confPickupCustomName').value.trim();

    const selectedRadio = document.querySelector('input[name="selected_pickup"]:checked');
    if (selectedRadio) {
        pickupId = selectedRadio.value;
        if (!pickupName) {
            pickupName = selectedRadio.dataset.name;
        }
    }

    fetch('<?= url("admin/exit-scanner/confirm") ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            student_id: studentId,
            gate_id: gateId,
            exit_type: exitType,
            exit_reason: exitReason,
            exit_notes: exitNotes,
            pickup_person_id: pickupId,
            pickup_person_name: pickupName,
            scan_method: currentScannerMode === 'camera' ? 'qr_camera' : 'qr_usb'
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-check me-1"></i> Confirm &amp; Log Exit';
        if (data.success) {
            playTone('success');
            renderSuccessCard(data);
            showResultNotification('success', data.message);
            prependRecentExit(data);
            resumeScanning(4000);
        } else {
            playTone('error');
            showResultNotification('danger', data.message || 'Confirmation failed.');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-check me-1"></i> Confirm &amp; Log Exit';
        console.error(err);
        showResultNotification('danger', 'Network error confirming exit.');
    });
}

function cancelVerification() {
    document.getElementById('studentVerificationCard').style.display = 'none';
    resumeScanning(500);
}

function renderSuccessCard(data) {
    const s = data.student;
    document.getElementById('studentVerificationCard').style.display = 'block';
    document.getElementById('confirmationFooter').style.display = 'none';

    document.getElementById('exitTypeBadge').className = 'badge bg-success-subtle text-success fw-bold px-3 py-1 rounded-pill';
    document.getElementById('exitTypeBadge').innerText = 'Exit Confirmed';

    const photoHtml = s.photo ? `<img src="${s.photo}" class="w-100 h-100 object-fit-cover">` : `<div class="w-100 h-100 d-flex align-items-center justify-content-center bg-success-subtle text-success fw-bold fs-3">✓</div>`;

    document.getElementById('studentCardBody').innerHTML = `
        <div class="avatar mx-auto mb-3 rounded-circle overflow-hidden shadow-sm border border-3 border-success" style="width: 84px; height: 84px; background: #f1f5f9;">
            ${photoHtml}
        </div>
        <h5 class="fw-bold text-dark mb-1">${s.name}</h5>
        <div class="mb-3">
            <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1">${s.class_name}</span>
            <span class="badge bg-light text-secondary border px-2 py-1 ms-1">${s.admission_number}</span>
        </div>
        <div class="alert alert-success py-2 px-3 small rounded-3 mb-3">
            <i class="ti ti-check-circle me-1"></i> Exit recorded at <strong>${data.exit_time}</strong> via <strong>${data.gate_name}</strong>.
            <div class="mt-1" style="font-size: 11px;">Parent SMS: <strong>${data.sms_status}</strong></div>
        </div>
        <div class="progress" style="height: 4px;">
            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" style="width: 100%;"></div>
        </div>
        <div class="text-muted small mt-2" style="font-size: 11px;">Ready for next student in a moment...</div>
    `;
}

function renderAlreadyExitedCard(data) {
    const s = data.student;
    document.getElementById('studentVerificationCard').style.display = 'block';
    document.getElementById('confirmationFooter').style.display = 'none';

    document.getElementById('exitTypeBadge').className = 'badge bg-danger-subtle text-danger fw-bold px-3 py-1 rounded-pill';
    document.getElementById('exitTypeBadge').innerText = 'Duplicate Scan Alert';

    const photoHtml = s.photo ? `<img src="${s.photo}" class="w-100 h-100 object-fit-cover">` : `<div class="w-100 h-100 d-flex align-items-center justify-content-center bg-danger-subtle text-danger fw-bold fs-3">!</div>`;

    document.getElementById('studentCardBody').innerHTML = `
        <div class="avatar mx-auto mb-3 rounded-circle overflow-hidden shadow-sm border border-3 border-danger" style="width: 84px; height: 84px; background: #fdf2f2;">
            ${photoHtml}
        </div>
        <h5 class="fw-bold text-danger mb-1">${s.name}</h5>
        <div class="mb-3">
            <span class="badge bg-light text-secondary border px-2 py-1">${s.class_name}</span>
            <span class="badge bg-light text-secondary border px-2 py-1 ms-1">${s.admission_number}</span>
        </div>
        <div class="alert alert-danger py-2 px-3 small rounded-3 text-start">
            <div class="fw-bold mb-1"><i class="ti ti-ban me-1"></i> Already Checked Out Today!</div>
            <div style="font-size: 12px;"><strong>Exited At:</strong> ${s.exit_time} (${s.gate_name})</div>
            <div style="font-size: 12px;"><strong>Exit Status:</strong> ${s.exit_type}</div>
            <div style="font-size: 12px;"><strong>Parent Phone:</strong> ${s.parent_phone}</div>
        </div>
    `;
}

function prependRecentExit(data) {
    const list = document.getElementById('recentExitsList');
    const notice = document.getElementById('noExitsNotice');
    if (notice) notice.remove();

    const s = data.student;
    const item = document.createElement('div');
    item.className = 'list-group-item px-3 py-3 border-bottom d-flex align-items-center justify-content-between bg-success-subtle';
    item.style.transition = 'background 1s ease';

    const photoHtml = s.photo ? `<img src="${s.photo}" class="w-100 h-100 object-fit-cover">` : `<div class="w-100 h-100 d-flex align-items-center justify-content-center bg-primary-subtle text-primary fw-bold">${s.name.substring(0, 2).toUpperCase()}</div>`;

    item.innerHTML = `
        <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-md rounded-circle overflow-hidden flex-shrink-0" style="width: 42px; height: 42px; background: #e2e8f0;">
                ${photoHtml}
            </div>
            <div>
                <div class="fw-bold text-dark small mb-0">${s.name}</div>
                <div class="text-muted" style="font-size: 11px;">
                    <span class="badge bg-light text-secondary border px-1">${s.class_name}</span>
                    <span class="ms-1">${s.admission_number}</span>
                </div>
            </div>
        </div>
        <div class="text-end">
            <div class="fw-bold small text-dark">${data.exit_time}</div>
            <span class="badge ${data.exit_type === 'early' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success'}" style="font-size: 10px;">
                ${data.exit_type.toUpperCase()}
            </span>
        </div>
    `;

    list.insertBefore(item, list.firstChild);
    setTimeout(() => {
        item.classList.remove('bg-success-subtle');
    }, 1500);

    const countBadge = document.getElementById('recentExitsCount');
    if (countBadge) {
        const curr = parseInt(countBadge.innerText) || 0;
        countBadge.innerText = `${curr + 1} exits`;
    }
}

function showResultNotification(type, message) {
    const box = document.getElementById('scanResultBox');
    box.style.display = 'block';
    box.innerHTML = `
        <div class="alert alert-${type} alert-dismissible fade show shadow-sm rounded-3 py-2 px-3 small d-flex align-items-center mb-0">
            <i class="ti ti-${type === 'success' ? 'check' : (type === 'warning' ? 'alert-triangle' : 'alert-circle')} me-2 fs-5"></i>
            <div>${message}</div>
            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
        </div>
    `;
}

function resumeScanning(delay = 2500) {
    autoResetTimer = setTimeout(() => {
        scanCooldown = false;
        document.getElementById('scanStatusBadge').innerHTML = '<i class="ti ti-point-filled text-success"></i> Ready';
        document.getElementById('scanStatusBadge').className = 'badge bg-success-subtle text-success rounded-pill px-3 py-1';
        if (currentScannerMode === 'usb') {
            document.getElementById('usbScannerInput').focus();
        }
    }, delay);
}

// Manual Student Lookup
let searchTimer = null;
function searchStudents(query) {
    clearTimeout(searchTimer);
    const container = document.getElementById('manualSearchResults');
    if (query.trim().length < 2) {
        container.style.display = 'none';
        return;
    }

    searchTimer = setTimeout(() => {
        fetch('<?= url("admin/exit-scanner/student-lookup") ?>?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                container.innerHTML = '';
                if (data.results && data.results.length > 0) {
                    data.results.forEach(std => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3';
                        btn.innerHTML = `
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar rounded-circle overflow-hidden" style="width: 32px; height: 32px; background: #e2e8f0;">
                                    ${std.photo ? `<img src="${std.photo}" class="w-100 h-100 object-fit-cover">` : `<div class="w-100 h-100 d-flex align-items-center justify-content-center fw-bold small">${std.name[0]}</div>`}
                                </div>
                                <div>
                                    <div class="fw-bold small text-dark mb-0">${std.name}</div>
                                    <div class="text-muted" style="font-size: 11px;">${std.admission_number} &bull; ${std.class_name}</div>
                                </div>
                            </div>
                            <span class="btn btn-outline-primary btn-sm rounded-pill py-0 px-2" style="font-size: 11px;">Select</span>
                        `;
                        btn.onclick = () => {
                            container.style.display = 'none';
                            document.getElementById('manualSearchInput').value = '';
                            onCodeScanned(std.admission_number, 'manual');
                        };
                        container.appendChild(btn);
                    });
                    container.style.display = 'block';
                } else {
                    container.innerHTML = '<div class="p-3 text-muted text-center small">No enrolled students found.</div>';
                    container.style.display = 'block';
                }
            });
    }, 250);
}

// Start Camera on initial load
document.addEventListener('DOMContentLoaded', function() {
    startCamera();
});
</script>
