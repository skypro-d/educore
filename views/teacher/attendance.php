<?php
// views/teacher/attendance.php
?>
<script src="https://unpkg.com/html5-qrcode"></script>
<style>
    .att-tabs {
        border-bottom: 2px solid #e2e8f0;
        margin-bottom: 1.5rem;
    }
    .att-tab-btn {
        background: none;
        border: none;
        padding: 10px 20px;
        font-weight: 600;
        color: #64748b;
        border-bottom: 3px solid transparent;
        transition: all 0.2s;
        font-size: 14px;
    }
    .att-tab-btn:hover {
        color: var(--teacher-primary);
    }
    .att-tab-btn.active {
        color: var(--teacher-primary);
        border-bottom-color: var(--teacher-primary);
    }
    .scanner-box {
        max-width: 500px;
        margin: 0 auto;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 20px;
        background: #f8fafc;
        position: relative;
    }
    #reader {
        width: 100%;
        border-radius: 8px;
        overflow: hidden;
    }
    #reader__dashboard_section_csr {
        display: none !important;
    }
    .scan-log {
        max-height: 250px;
        overflow-y: auto;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff;
        padding: 10px;
    }
    .scan-log-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13px;
    }
    .scan-log-item:last-child {
        border-bottom: none;
    }
    .scan-log-item.success {
        border-left: 3px solid #10b981;
        background: #f0fdf4;
    }
    .scan-log-item.error {
        border-left: 3px solid #ef4444;
        background: #fdf2f2;
    }
</style>

<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0" style="border-radius: 12px;">
            <div class="card-body py-4">
                <form method="GET" action="<?= url('teacher/attendance') ?>" class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label for="class_id" class="form-label font-semibold" style="font-weight:600; color:#475569;">Select Class</label>
                        <select name="class_id" id="class_id" class="form-select" onchange="this.form.submit()">
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>" <?= $c['id'] == $classId ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="date" class="form-label font-semibold" style="font-weight:600; color:#475569;">Attendance Date</label>
                        <input type="date" name="date" id="date" class="form-select" value="<?= e($date) ?>" onchange="this.form.submit()">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100" style="background:var(--teacher-primary); border-color:var(--teacher-primary); font-weight:600;">Reload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if ($classId > 0): ?>
    <div class="card shadow-sm border-0" style="border-radius: 12px;">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <div class="att-tabs d-flex">
                <button class="att-tab-btn active" id="tabManualBtn" onclick="switchTab('manual')">
                    <i class="ti ti-checklist" style="margin-right:6px;"></i> Manual Register
                </button>
                <button class="att-tab-btn" id="tabQRBtn" onclick="switchTab('qr')">
                    <i class="ti ti-qrcode" style="margin-right:6px;"></i> Scan QR Code Attendance
                </button>
            </div>
        </div>

        <div class="card-body px-4 pb-4">
            <!-- TAB 1: MANUAL ATTENDANCE -->
            <div id="tabManual">
                <?php if (empty($students)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="ti ti-mood-empty" style="font-size:3rem; margin-bottom:8px; display:block;"></i>
                        No students enrolled in this class.
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?= url('teacher/attendance/save') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="class_id" value="<?= $classId ?>">
                        <input type="hidden" name="date" value="<?= e($date) ?>">

                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light text-uppercase" style="font-size: 11px; font-weight: 700; color: #64748b;">
                                    <tr>
                                        <th class="ps-3">Student</th>
                                        <th>Admission No</th>
                                        <th style="width:280px;">Status</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <?php 
                                            $currStatus = $existing[$student['id']]['status'] ?? 'Present';
                                            $currRemark = $existing[$student['id']]['remark'] ?? '';
                                            $timeIn = $existing[$student['id']]['time_in'] ?? null;
                                        ?>
                                        <tr>
                                            <td class="ps-3 py-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <?php if ($student['passport_photo']): ?>
                                                        <img src="<?= url('uploads/' . $student['passport_photo']) ?>" alt="Photo" style="width:40px; height:40px; border-radius:50%; object-fit:cover;">
                                                    <?php else: ?>
                                                        <div style="width:40px; height:40px; border-radius:50%; background:#e2e8f0; display:flex; align-items:center; justify-content:center; font-weight:700; color:#64748b;">
                                                            <?= strtoupper(substr($student['first_name'], 0, 1)) ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="font-bold text-dark" style="font-weight:600;"><?= e($student['last_name'] . ' ' . $student['first_name']) ?></div>
                                                        <?php if ($timeIn): ?>
                                                            <small class="text-success"><i class="ti ti-clock"></i> Signed-in: <?= date('h:i A', strtotime($timeIn)) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= e($student['admission_number']) ?></td>
                                            <td>
                                                <div class="btn-group w-100" role="group">
                                                    <input type="radio" class="btn-check" name="status[<?= $student['id'] ?>]" id="pres_<?= $student['id'] ?>" value="Present" <?= $currStatus === 'Present' ? 'checked' : '' ?>>
                                                    <label class="btn btn-outline-success btn-sm" for="pres_<?= $student['id'] ?>">Present</label>

                                                    <input type="radio" class="btn-check" name="status[<?= $student['id'] ?>]" id="late_<?= $student['id'] ?>" value="Late" <?= $currStatus === 'Late' ? 'checked' : '' ?>>
                                                    <label class="btn btn-outline-warning btn-sm" for="late_<?= $student['id'] ?>">Late</label>

                                                    <input type="radio" class="btn-check" name="status[<?= $student['id'] ?>]" id="abs_<?= $student['id'] ?>" value="Absent" <?= $currStatus === 'Absent' ? 'checked' : '' ?>>
                                                    <label class="btn btn-outline-danger btn-sm" for="abs_<?= $student['id'] ?>">Absent</label>

                                                    <input type="radio" class="btn-check" name="status[<?= $student['id'] ?>]" id="exc_<?= $student['id'] ?>" value="Excused" <?= $currStatus === 'Excused' ? 'checked' : '' ?>>
                                                    <label class="btn btn-outline-info btn-sm" for="exc_<?= $student['id'] ?>">Excused</label>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" name="remarks[<?= $student['id'] ?>]" class="form-control form-control-sm" value="<?= e($currRemark) ?>" placeholder="Optional note">
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-teal px-4" style="background:#0f766e; color:#fff; font-weight:600;">Save Attendance Register</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>

            <!-- TAB 2: QR CODE SCANNER -->
            <div id="tabQR" style="display:none;">
                <div class="row pt-3">
                    <div class="col-lg-6">
                        <div class="scanner-box shadow-sm mb-4">
                            <div id="reader"></div>
                            <div class="text-center mt-3">
                                <span class="badge bg-dark rounded-pill py-2 px-3" id="scanStatus">Scanner Stopped</span>
                                <div class="mt-2 text-start" id="cameraSelectContainer" style="display:none; max-width:280px; margin: 0 auto 10px auto;">
                                    <label for="cameraSelect" class="form-label mb-1" style="font-size:12px; font-weight:600; color:#475569;">Select Camera:</label>
                                    <select id="cameraSelect" class="form-select form-select-sm" onchange="switchCamera(this.value)">
                                    </select>
                                </div>
                                <div class="mt-3">
                                    <button class="btn btn-success btn-sm me-2" onclick="startScanner()"><i class="ti ti-device-camera"></i> Start Camera</button>
                                    <button class="btn btn-danger btn-sm" onclick="stopScanner()"><i class="ti ti-circle-x"></i> Stop Camera</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <h4 style="font-size:15px; font-weight:700; color:#1e293b; margin-bottom:12px;">Real-Time Scan Log</h4>
                        <div class="scan-log" id="scanLog">
                            <div class="text-center py-4 text-muted" id="emptyLogMsg">
                                Scanned student check-ins will appear here in real time.
                            </div>
                        </div>
                        <div class="alert alert-info mt-3" style="font-size:12px; border-radius:8px;">
                            <i class="ti ti-info-circle"></i> Place the student's ID Card barcode/QR code inside the camera grid. When scanned, parents will receive instant SMS & in-app notifications.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    let html5QrcodeScanner = null;
    let selectedCameraId = null;
    let camerasLoaded = false;
    let scanTimeout = null;

    function switchTab(tab) {
        if (tab === 'manual') {
            document.getElementById('tabManual').style.display = 'block';
            document.getElementById('tabQR').style.display = 'none';
            document.getElementById('tabManualBtn').classList.add('active');
            document.getElementById('tabQRBtn').classList.remove('active');
            stopScanner();
        } else {
            document.getElementById('tabManual').style.display = 'none';
            document.getElementById('tabQR').style.display = 'block';
            document.getElementById('tabManualBtn').classList.remove('active');
            document.getElementById('tabQRBtn').classList.add('active');
        }
    }

    function startScanner() {
        if (html5QrcodeScanner) {
            try {
                html5QrcodeScanner.clear();
            } catch (e) {
                console.warn("Error clearing scanner: ", e);
            }
        }

        document.getElementById('scanStatus').innerText = 'Initializing Camera...';
        document.getElementById('scanStatus').className = 'badge bg-warning text-dark rounded-pill py-2 px-3';

        html5QrcodeScanner = new Html5Qrcode("reader");

        // Prefer selectedCameraId if set by the user, otherwise default to environment (back) camera
        const cameraTarget = selectedCameraId ? selectedCameraId : { facingMode: "environment" };

        html5QrcodeScanner.start(
            cameraTarget,
            {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            },
            (decodedText, decodedResult) => {
                handleScannedQR(decodedText);
            },
            (errorMessage) => {
                // ignore scan error
            }
        ).then(() => {
            document.getElementById('scanStatus').innerText = 'Scanning...';
            document.getElementById('scanStatus').className = 'badge bg-success rounded-pill py-2 px-3';

            // Try to find which device ID is currently active
            let activeDeviceId = selectedCameraId;
            if (!activeDeviceId) {
                try {
                    const settings = html5QrcodeScanner.getRunningTrackSettings();
                    activeDeviceId = settings.deviceId;
                } catch (e) {
                    console.warn("Could not get running track settings: ", e);
                }
            }

            // Once camera is running, get camera list to populate the dropdown selection
            if (!camerasLoaded) {
                Html5Qrcode.getCameras().then(devices => {
                    if (devices && devices.length > 1) {
                        const cameraSelect = document.getElementById('cameraSelect');
                        cameraSelect.innerHTML = '';

                        devices.forEach(device => {
                            const option = document.createElement('option');
                            option.value = device.id;
                            option.text = device.label || `Camera ${cameraSelect.options.length + 1}`;

                            if (activeDeviceId && device.id === activeDeviceId) {
                                option.selected = true;
                                selectedCameraId = device.id;
                            } else if (!activeDeviceId && (device.label.toLowerCase().includes('back') || device.label.toLowerCase().includes('environment'))) {
                                option.selected = true;
                                selectedCameraId = device.id;
                            }

                            cameraSelect.appendChild(option);
                        });

                        // Fallback selection if nothing was pre-selected
                        if (cameraSelect.selectedIndex === -1 && devices.length > 0) {
                            cameraSelect.selectedIndex = 0;
                            selectedCameraId = devices[0].id;
                        }

                        document.getElementById('cameraSelectContainer').style.display = 'block';
                        camerasLoaded = true;
                    }
                }).catch(err => {
                    console.warn("Could not retrieve camera list: ", err);
                });
            }
        }).catch(err => {
            console.error("Error starting camera: ", err);
            document.getElementById('scanStatus').innerText = 'Camera Error';
            document.getElementById('scanStatus').className = 'badge bg-danger rounded-pill py-2 px-3';
        });
    }

    function switchCamera(cameraId) {
        selectedCameraId = cameraId;
        if (scanTimeout) {
            clearTimeout(scanTimeout);
            scanTimeout = null;
        }

        if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
            html5QrcodeScanner.stop().then(() => {
                startScanner();
            }).catch(err => {
                console.error("Error stopping scanner for switch: ", err);
                startScanner();
            });
        } else {
            startScanner();
        }
    }

    function stopScanner() {
        if (scanTimeout) {
            clearTimeout(scanTimeout);
            scanTimeout = null;
        }

        if (html5QrcodeScanner && html5QrcodeScanner.isScanning) {
            html5QrcodeScanner.stop().then(() => {
                try {
                    html5QrcodeScanner.clear();
                } catch (e) {}
                document.getElementById('scanStatus').innerText = 'Scanner Stopped';
                document.getElementById('scanStatus').className = 'badge bg-dark rounded-pill py-2 px-3';
            }).catch(err => {
                console.error("Error stopping scanner: ", err);
            });
        }
    }

    // Audio context for beep sound
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    function playBeep(success = true) {
        try {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            
            osc.frequency.setValueAtTime(success ? 880 : 330, audioCtx.currentTime); // high tone for success, low for failure
            gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
            
            osc.start();
            osc.stop(audioCtx.currentTime + 0.15);
        } catch(e) {}
    }

    function handleScannedQR(qrCode) {
        // Stop scanning briefly to handle processing
        stopScanner();

        // Remove empty log message
        const emptyMsg = document.getElementById('emptyLogMsg');
        if (emptyMsg) emptyMsg.remove();

        const scanLog = document.getElementById('scanLog');

        fetch("<?= url('teacher/attendance/scan-qr') ?>", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ qr_data: qrCode })
        })
        .then(res => res.json())
        .then(data => {
            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            
            if (data.success) {
                playBeep(true);
                const item = document.createElement('div');
                item.className = 'scan-log-item success';
                item.innerHTML = `
                    <div>
                        <strong>${data.student_name}</strong> marked <strong>${data.status}</strong>
                    </div>
                    <div>
                        <span class="badge bg-success">${data.time_in}</span>
                    </div>
                `;
                scanLog.insertBefore(item, scanLog.firstChild);
            } else {
                playBeep(false);
                const item = document.createElement('div');
                item.className = 'scan-log-item error';
                item.innerHTML = `
                    <div>
                        <i class="ti ti-alert-triangle text-danger me-1"></i> Scan Failed: ${data.message}
                    </div>
                    <small class="text-muted">${time}</small>
                `;
                scanLog.insertBefore(item, scanLog.firstChild);
            }

            // Auto-resume scanner after 1.5 seconds delay
            scanTimeout = setTimeout(startScanner, 1500);
        })
        .catch(err => {
            console.error("AJAX Error: ", err);
            playBeep(false);
            const time = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const item = document.createElement('div');
            item.className = 'scan-log-item error';
            item.innerHTML = `
                <div>
                    <i class="ti ti-wifi-off text-danger me-1"></i> Connection error or invalid QR code
                </div>
                <small class="text-muted">${time}</small>
            `;
            scanLog.insertBefore(item, scanLog.firstChild);

            scanTimeout = setTimeout(startScanner, 1500);
        });
    }
</script>
