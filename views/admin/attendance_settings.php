<?php
/**
 * views/admin/attendance_settings.php
 * Attendance Settings — Time Rules, SMS Configuration, Auto-Absent, SMS Logs
 */
$activeTab = $_GET['tab'] ?? 'time-rules';
?>

<style>
.att-settings-tabs { display:flex; gap:0; border-bottom:2px solid var(--brand-primary,#0b3d91); margin-bottom:24px; flex-wrap:wrap; }
.att-settings-tab  { padding:10px 20px; font-size:13px; font-weight:600; cursor:pointer; border:none; background:transparent;
    color:#6b7280; border-bottom:3px solid transparent; margin-bottom:-2px; transition:.2s; display:flex; align-items:center; gap:6px; }
.att-settings-tab.active  { color:var(--brand-primary,#0b3d91); border-bottom-color:var(--brand-primary,#0b3d91); background:#f0f4ff; }
.att-settings-tab:hover:not(.active) { color:#374151; background:#f9fafb; }
.att-tab-panel { display:none; }
.att-tab-panel.active { display:block; }

.time-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(220px,1fr)); gap:20px; }
.time-card  { background:#f8faff; border:1.5px solid #e0e7ff; border-radius:12px; padding:20px; }
.time-card label { font-size:11px; font-weight:700; color:#6366f1; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:6px; }
.time-card input[type="time"] { width:100%; padding:10px 14px; border:1.5px solid #c7d2fe; border-radius:8px;
    font-size:18px; font-weight:700; color:#1e1b4b; background:#fff; outline:none; }
.time-card input[type="time"]:focus { border-color:var(--brand-primary,#0b3d91); box-shadow:0 0 0 3px rgba(99,102,241,.15); }
.time-card .tc-hint { font-size:11px; color:#9ca3af; margin-top:5px; }

.status-badge-p { background:#dcfce7; color:#15803d; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.status-badge-l { background:#fef9c3; color:#a16207; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.status-badge-d { background:#fee2e2; color:#b91c1c; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.status-badge-s { background:#dbeafe; color:#1d4ed8; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.status-badge-f { background:#fce7f3; color:#be185d; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }

.sms-toggle { display:flex; align-items:center; gap:14px; background:#f8faff; border:1.5px solid #e0e7ff; border-radius:12px; padding:16px 20px; margin-bottom:14px; }
.sms-toggle .toggle-label { flex:1; }
.sms-toggle .toggle-label strong { display:block; font-size:13px; color:#1f2937; }
.sms-toggle .toggle-label span   { font-size:11px; color:#9ca3af; }
.sms-switch { position:relative; width:46px; height:24px; flex-shrink:0; }
.sms-switch input { opacity:0; width:0; height:0; }
.sms-switch .slider { position:absolute; inset:0; background:#d1d5db; border-radius:24px; cursor:pointer; transition:.3s; }
.sms-switch .slider::before { content:''; position:absolute; width:18px; height:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; }
.sms-switch input:checked + .slider { background:var(--brand-primary,#0b3d91); }
.sms-switch input:checked + .slider::before { transform:translateX(22px); }

.log-badge-sent   { background:#d1fae5; color:#065f46; }
.log-badge-failed { background:#fee2e2; color:#991b1b; }
.log-badge-pending{ background:#fef9c3; color:#92400e; }
.log-type-checkin { background:#dbeafe; color:#1e40af; }
.log-type-absent  { background:#fee2e2; color:#9f1239; }
.log-type-test    { background:#f3e8ff; color:#6b21a8; }
.log-type-bulk    { background:#fef3c7; color:#92400e; }
.log-badge, .log-type { display:inline-block; padding:2px 8px; border-radius:20px; font-size:10px; font-weight:700; }

.preview-box { background:#1e293b; color:#94a3b8; border-radius:12px; padding:20px; font-family:monospace; font-size:12px; line-height:1.8; white-space:pre-wrap; }
.preview-box .highlight { color:#38bdf8; }
.preview-box .token { color:#86efac; }
</style>

<div class="sa-top-bar">
    <div>
        <h1>Attendance Settings</h1>
        <p>Configure time rules, SMS notifications, auto-absent processing, and view SMS logs</p>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="att-settings-tabs" id="attTabs">
    <button class="att-settings-tab <?= $activeTab==='time-rules' ? 'active' : '' ?>" data-tab="time-rules" type="button">
        <i class="ti ti-clock"></i> Time Rules
    </button>
    <button class="att-settings-tab <?= $activeTab==='sms' ? 'active' : '' ?>" data-tab="sms" type="button">
        <i class="ti ti-message-dots"></i> SMS Configuration
    </button>
    <button class="att-settings-tab <?= $activeTab==='auto-absent' ? 'active' : '' ?>" data-tab="auto-absent" type="button">
        <i class="ti ti-user-x"></i> Auto-Absent
    </button>
    <button class="att-settings-tab <?= $activeTab==='exit' ? 'active' : '' ?>" data-tab="exit" type="button">
        <i class="ti ti-door-exit"></i> Exit &amp; Dismissal
    </button>
    <button class="att-settings-tab <?= $activeTab==='logs' ? 'active' : '' ?>" data-tab="logs" type="button">
        <i class="ti ti-list-details"></i> SMS Logs
        <?php
            try {
                $logCount = (int) Database::connect()->query("SELECT COUNT(*) FROM sms_logs")->fetchColumn();
            } catch (Throwable) { $logCount = 0; }
        ?>
        <?php if ($logCount > 0): ?>
            <span style="background:var(--brand-primary,#0b3d91);color:#fff;border-radius:20px;font-size:10px;padding:1px 7px;"><?= $logCount ?></span>
        <?php endif; ?>
    </button>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- TAB 1: TIME RULES                                       -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="att-tab-panel <?= $activeTab==='time-rules' ? 'active' : '' ?>" id="tab-time-rules">

    <form method="POST" action="<?= url('admin/attendance-settings') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="section" value="time-rules">

        <div class="sa-card" style="margin-bottom:20px;">
            <div class="sa-card-title"><i class="ti ti-clock-hour-4"></i> Daily Attendance Time Window</div>
            <p style="color:#6b7280;font-size:13px;margin-bottom:20px;">
                Configure when students can check in and what status they receive based on their arrival time.
            </p>

            <div class="time-grid">
                <div class="time-card">
                    <label>🔓 School Gate Opens</label>
                    <input type="time" name="settings[attendance_open_time]"
                           value="<?= e($map['attendance_open_time'] ?? '07:00') ?>"
                           id="inp-open">
                    <div class="tc-hint">Students can start scanning from this time</div>
                </div>
                <div class="time-card" style="border-color:#d1fae5;background:#f0fdf4;">
                    <label style="color:#15803d;">✅ On-Time Until</label>
                    <input type="time" name="settings[attendance_ontime_until]"
                           value="<?= e($map['attendance_ontime_until'] ?? '07:30') ?>"
                           id="inp-ontime" style="border-color:#86efac;color:#15803d;">
                    <div class="tc-hint">Students arriving by this time are marked <strong>Present</strong></div>
                </div>
                <div class="time-card" style="border-color:#fde68a;background:#fefce8;">
                    <label style="color:#a16207;">⏰ Late Arrival From</label>
                    <input type="time" name="settings[attendance_late_from]"
                           value="<?= e($map['attendance_late_from'] ?? '07:31') ?>"
                           id="inp-late" style="border-color:#fcd34d;color:#a16207;">
                    <div class="tc-hint">Students arriving after on-time deadline are marked <strong>Late</strong></div>
                </div>
                <div class="time-card" style="border-color:#fecaca;background:#fef2f2;">
                    <label style="color:#b91c1c;">🔒 Attendance Closes</label>
                    <input type="time" name="settings[attendance_close_time]"
                           value="<?= e($map['attendance_close_time'] ?? '09:00') ?>"
                           id="inp-close" style="border-color:#fca5a5;color:#b91c1c;">
                    <div class="tc-hint">No check-ins accepted after this time (scans are denied)</div>
                </div>
                <div class="time-card">
                    <label>🏫 School Dismissal (Optional)</label>
                    <input type="time" name="settings[school_close_time]"
                           value="<?= e($map['school_close_time'] ?? '14:30') ?>">
                    <div class="tc-hint">For reference only — does not affect attendance recording</div>
                </div>
            </div>
        </div>

        <!-- Live Status Preview -->
        <div class="sa-card" style="margin-bottom:20px;">
            <div class="sa-card-title"><i class="ti ti-eye"></i> Status Preview</div>
            <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">See how the system will classify a student based on your configured times.</p>
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;margin-bottom:16px;">
                <label style="font-size:13px;font-weight:600;">Test a Check-In Time:</label>
                <input type="time" id="previewTime" value="07:15" style="padding:8px 14px;border:1.5px solid #d1d5db;border-radius:8px;font-size:14px;font-weight:700;">
                <span id="previewResult" class="status-badge-p">Present</span>
            </div>
            <div style="display:flex;gap:24px;flex-wrap:wrap;font-size:13px;">
                <div style="display:flex;align-items:center;gap:8px;"><span class="status-badge-p">Present</span> Before or at on-time deadline</div>
                <div style="display:flex;align-items:center;gap:8px;"><span class="status-badge-l">Late</span> After on-time, before attendance closes</div>
                <div style="display:flex;align-items:center;gap:8px;"><span class="status-badge-d">Denied</span> After attendance window closes</div>
            </div>
        </div>

        <div style="display:flex;gap:12px;">
            <button type="submit" class="sa-btn sa-btn-primary"><i class="ti ti-device-floppy"></i> Save Time Rules</button>
        </div>
    </form>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- TAB 2: SMS CONFIGURATION                                -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="att-tab-panel <?= $activeTab==='sms' ? 'active' : '' ?>" id="tab-sms">

    <form method="POST" action="<?= url('admin/attendance-settings') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="section" value="sms">

        <!-- SMS Toggles -->
        <div class="sa-card" style="margin-bottom:20px;">
            <div class="sa-card-title"><i class="ti ti-toggle-right"></i> SMS Feature Toggles</div>

            <div class="sms-toggle">
                <label class="sms-switch">
                    <input type="checkbox" name="settings[attendance_sms_enabled]" value="1"
                           <?= ($map['attendance_sms_enabled'] ?? '1') === '1' ? 'checked' : '' ?> id="masterSmsSwitch">
                    <span class="slider"></span>
                </label>
                <div class="toggle-label">
                    <strong>Master SMS Switch</strong>
                    <span>When disabled, no SMS will be sent from the attendance system</span>
                </div>
                <span id="masterSmsLabel" style="font-size:12px;font-weight:700;color:<?= ($map['attendance_sms_enabled'] ?? '1') === '1' ? '#15803d' : '#dc2626' ?>;">
                    <?= ($map['attendance_sms_enabled'] ?? '1') === '1' ? 'ENABLED' : 'DISABLED' ?>
                </span>
            </div>

            <div id="smsSubToggles" style="<?= ($map['attendance_sms_enabled'] ?? '1') === '0' ? 'opacity:.4;pointer-events:none;' : '' ?>">
                <div class="sms-toggle">
                    <label class="sms-switch">
                        <input type="checkbox" name="settings[checkin_sms_enabled]" value="1"
                               <?= ($map['checkin_sms_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                    <div class="toggle-label">
                        <strong>✅ Check-In SMS (Arrival Notification)</strong>
                        <span>Sends SMS to parent when student successfully checks in (Present or Late)</span>
                    </div>
                </div>

                <div class="sms-toggle">
                    <label class="sms-switch">
                        <input type="checkbox" name="settings[absent_sms_enabled]" value="1"
                               <?= ($map['absent_sms_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span class="slider"></span>
                    </label>
                    <div class="toggle-label">
                        <strong>❌ Absent SMS (Absence Alert)</strong>
                        <span>Sends SMS to parent when student is marked or auto-processed as Absent</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gateway Config -->
        <div class="sa-card" style="margin-bottom:20px;">
            <div class="sa-card-title"><i class="ti ti-settings-2"></i> SMS Gateway Configuration</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">SMS Gateway</label>
                    <select class="form-select" name="settings[sms_gateway]" id="gatewaySelect">
                        <?php foreach (['bulksms' => '📨 BulkSMSNigeria (Recommended)', 'termii' => '📡 Termii SMS API', 'stub' => '🧪 Log Stub (Development)'] as $val => $lbl): ?>
                            <option value="<?= e($val) ?>" <?= ($map['sms_gateway'] ?? 'bulksms') === $val ? 'selected' : '' ?>><?= e($lbl) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text" id="gatewayNote">
                        BulkSMSNigeria active: The school enters their own API Token and Sender ID below.
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">API Key</label>
                    <input class="form-control" type="password" autocomplete="new-password"
                           name="settings[sms_api_key]"
                           value="<?= e($map['sms_api_key'] ?? $map['termii_api_key'] ?? '') ?>"
                           placeholder="Paste your SMS gateway API key here">
                    <div class="form-text">Get your API key from your gateway dashboard</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Sender ID / Name</label>
                    <input class="form-control" name="settings[sms_sender_id]"
                           value="<?= e($map['sms_sender_id'] ?? $map['termii_sender_id'] ?? 'EduCore') ?>"
                           placeholder="e.g. EduCore, YourSchool"
                           maxlength="11">
                    <div class="form-text">Max 11 characters. Must be pre-approved by your gateway.</div>
                </div>
            </div>
        </div>

        <!-- SMS Message Previews -->
        <div class="sa-card" style="margin-bottom:20px;">
            <div class="sa-card-title"><i class="ti ti-message-2"></i> SMS Message Templates</div>
            <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">These are the exact messages parents will receive. They are automatically filled with real student data.</p>

            <div class="row g-3">
                <div class="col-md-6">
                    <div style="font-size:12px;font-weight:700;color:#15803d;margin-bottom:8px;">✅ CHECK-IN MESSAGE (Present / Late)</div>
                    <div class="preview-box">EduCore Alert

Dear Parent,
Your child, <span class="token">{StudentName}</span>, has successfully
arrived at <span class="token">{SchoolName}</span>.

Date: <span class="highlight">Mon, Jun 30 2026</span>
Time In: <span class="highlight">7:25 AM</span>
Status: <span class="highlight">Present</span>

Thank you.</div>
                </div>
                <div class="col-md-6">
                    <div style="font-size:12px;font-weight:700;color:#b91c1c;margin-bottom:8px;">❌ ABSENT MESSAGE</div>
                    <div class="preview-box">EduCore Alert

Dear Parent,
Your child, <span class="token">{StudentName}</span>, has not been
marked present at <span class="token">{SchoolName}</span> today and is
currently recorded as Absent.

Date: <span class="highlight">Mon, Jun 30 2026</span>

If you believe this is an error, please
contact the school.

Thank you.</div>
                </div>
            </div>
        </div>

        <button type="submit" class="sa-btn sa-btn-primary"><i class="ti ti-device-floppy"></i> Save SMS Settings</button>
    </form>

    <!-- Test SMS -->
    <div class="sa-card" style="margin-top:20px;">
        <div class="sa-card-title"><i class="ti ti-test-pipe"></i> Test SMS Delivery</div>
        <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">Send a test SMS to verify your gateway configuration is working correctly.</p>
        <form method="POST" action="<?= url('admin/attendance-settings/test-sms') ?>" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <?= csrf_field() ?>
            <div>
                <label class="form-label">Phone Number</label>
                <input class="form-control" type="tel" name="test_phone"
                       placeholder="+2348012345678" style="min-width:220px;">
            </div>
            <button type="submit" class="sa-btn" style="background:#7c3aed;color:#fff;">
                <i class="ti ti-send"></i> Send Test SMS
            </button>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- TAB 3: AUTO-ABSENT                                      -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="att-tab-panel <?= $activeTab==='auto-absent' ? 'active' : '' ?>" id="tab-auto-absent">

    <form method="POST" action="<?= url('admin/attendance-settings') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="section" value="auto-absent">

        <div class="sa-card" style="margin-bottom:20px;">
            <div class="sa-card-title"><i class="ti ti-user-x"></i> Auto-Absent Processing</div>

            <div class="sms-toggle" style="margin-bottom:20px;">
                <label class="sms-switch">
                    <input type="checkbox" name="settings[auto_absent_enabled]" value="1"
                           <?= ($map['auto_absent_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <span class="slider"></span>
                </label>
                <div class="toggle-label">
                    <strong>Enable Auto-Absent Processing</strong>
                    <span>When enabled, students with no check-in will be automatically marked Absent after the attendance window closes</span>
                </div>
            </div>

            <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:12px;padding:20px;margin-bottom:20px;">
                <div style="font-size:13px;font-weight:700;color:#15803d;margin-bottom:8px;">🔄 How Auto-Absent Works</div>
                <ol style="font-size:13px;color:#374151;margin:0;padding-left:20px;line-height:2;">
                    <li>After attendance closes at <strong><?= AttendanceRules::format($map['attendance_close_time'] ?? '09:00') ?></strong>, the system identifies students with no check-in record</li>
                    <li>Each unrecorded student is automatically marked <strong>Absent</strong></li>
                    <li>If Absent SMS is enabled, parents receive an immediate notification</li>
                    <li>Records are marked to prevent duplicate SMS notifications</li>
                </ol>
            </div>

            <div style="background:#fef9c3;border:1.5px solid #fde68a;border-radius:12px;padding:16px;margin-bottom:20px;">
                <div style="font-size:12px;font-weight:700;color:#92400e;">⚙️ How to Schedule Automatic Processing</div>
                <div style="font-size:12px;color:#78350f;margin-top:8px;line-height:1.8;">
                    <strong>Windows Task Scheduler:</strong> Create a task that runs daily after attendance closes:<br>
                    <code style="background:#fef3c7;padding:2px 8px;border-radius:4px;">php <?= e(str_replace('\\', '/', realpath(__DIR__ . '/../../'))) ?>/cron/auto_absent.php</code><br><br>
                    <strong>Or use the manual "Run Now" button below</strong> to process today's absences at any time.
                </div>
            </div>

            <button type="submit" class="sa-btn sa-btn-primary"><i class="ti ti-device-floppy"></i> Save Auto-Absent Setting</button>
        </div>
    </form>

    <!-- Manual Run -->
    <div class="sa-card">
        <div class="sa-card-title"><i class="ti ti-player-play"></i> Manual Absent Processing</div>
        <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">
            Run absent processing manually for any date. Only students with <strong>no attendance record</strong> will be marked.
            Students already recorded (Present, Late, Excused) will be unaffected.
        </p>

        <?php if ($lastAutoAbsent): ?>
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#15803d;">
                <i class="ti ti-check"></i> <strong>Last auto-absent run:</strong>
                <?= e(date('D, M j Y g:i A', strtotime($lastAutoAbsent))) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= url('admin/attendance-settings/run-auto-absent') ?>" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <?= csrf_field() ?>
            <div>
                <label class="form-label">Date to Process</label>
                <input type="date" class="form-control" name="date" value="<?= date('Y-m-d') ?>" style="width:180px;">
            </div>
            <button type="submit" class="sa-btn" style="background:#dc2626;color:#fff;"
                    onclick="return confirm('This will mark all students with no check-in as Absent and send SMS notifications. Continue?')">
                <i class="ti ti-player-play"></i> Run Auto-Absent Now
            </button>
        </form>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- TAB 4: STUDENT EXIT & DISMISSAL SETTINGS                -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="att-tab-panel <?= $activeTab==='exit' ? 'active' : '' ?>" id="tab-exit">

    <form method="POST" action="<?= url('admin/attendance-settings') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="section" value="exit">

        <!-- General Exit & Dismissal Rules -->
        <div class="sa-card" style="margin-bottom:20px;">
            <div class="sa-card-title"><i class="ti ti-door-exit"></i> Student Exit &amp; Dismissal Rules</div>
            <p style="color:#6b7280;font-size:13px;margin-bottom:20px;">
                Control gate checkout operations, early departure verification, and pickup safety protocols.
            </p>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="time-card h-100">
                        <label>🔔 Official Dismissal Time</label>
                        <input type="time" name="settings[exit_normal_time]"
                               value="<?= e($map['exit_normal_time'] ?? ($map['school_close_time'] ?? '14:30')) ?>"
                               id="inp-exit-time">
                        <div class="tc-hint">Students exiting before this time are flagged as <strong>Early Exit</strong> and require reason confirmation.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="sms-toggle">
                        <div class="toggle-label">
                            <strong>Enable Student Exit Tracking</strong>
                            <span>Allow gate staff to verify student exits using EduCore ID cards</span>
                        </div>
                        <label class="sms-switch">
                            <input type="checkbox" name="settings[exit_tracking_enabled]" value="1"
                                   <?= ($map['exit_tracking_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Operational Security Toggles -->
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="sms-toggle">
                        <div class="toggle-label">
                            <strong>Require Check-in Before Exit</strong>
                            <span>Only permit students who have a check-in attendance record today to exit</span>
                        </div>
                        <label class="sms-switch">
                            <input type="checkbox" name="settings[exit_require_entry_record]" value="1"
                                   <?= ($map['exit_require_entry_record'] ?? '0') === '1' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="sms-toggle">
                        <div class="toggle-label">
                            <strong>Prompt Pickup Guardian Selection</strong>
                            <span>Require gate staff to verify authorized pickup person for all exits</span>
                        </div>
                        <label class="sms-switch">
                            <input type="checkbox" name="settings[exit_require_pickup_verification]" value="1"
                                   <?= ($map['exit_require_pickup_verification'] ?? '0') === '1' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="sms-toggle">
                        <div class="toggle-label">
                            <strong>Allow Manual Gate Override</strong>
                            <span>Permit authorized gatekeepers to manually look up and log student exits</span>
                        </div>
                        <label class="sms-switch">
                            <input type="checkbox" name="settings[exit_allow_manual]" value="1"
                                   <?= ($map['exit_allow_manual'] ?? '1') === '1' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Exit SMS Automation & Templates -->
        <div class="sa-card" style="margin-bottom:20px;">
            <div class="sa-card-title"><i class="ti ti-message-share"></i> Parent Exit SMS Notifications</div>
            <p style="color:#6b7280;font-size:13px;margin-bottom:20px;">
                Automatically dispatch instant SMS notifications to parents and guardians as soon as students scan out at the gate.
            </p>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="sms-toggle">
                        <div class="toggle-label">
                            <strong>Normal Dismissal SMS</strong>
                            <span>Send SMS when student exits at scheduled dismissal time</span>
                        </div>
                        <label class="sms-switch">
                            <input type="checkbox" name="settings[exit_sms_enabled]" value="1"
                                   <?= ($map['exit_sms_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="sms-toggle">
                        <div class="toggle-label">
                            <strong>Early Exit SMS Alerts</strong>
                            <span>Send high-priority SMS alert with reason for early departures</span>
                        </div>
                        <label class="sms-switch">
                            <input type="checkbox" name="settings[early_exit_sms_enabled]" value="1"
                                   <?= ($map['early_exit_sms_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                            <span class="slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Normal Dismissal SMS Template</label>
                    <textarea name="settings[exit_sms_template_normal]" class="form-control" rows="3" style="font-size:13px;"><?= e($map['exit_sms_template_normal'] ?? 'Dear Parent, your child {student_name} has safely departed {school_name} at {exit_time} on {exit_date}.') ?></textarea>
                    <div class="text-muted small mt-1">Available placeholders: <code>{student_name}</code>, <code>{school_name}</code>, <code>{exit_time}</code>, <code>{exit_date}</code>, <code>{class_name}</code></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold small">Early Departure SMS Template</label>
                    <textarea name="settings[exit_sms_template_early]" class="form-control" rows="3" style="font-size:13px;"><?= e($map['exit_sms_template_early'] ?? 'ALERT: {student_name} departed {school_name} early at {exit_time}. Reason: {reason}. Pickup: {pickup_person}.') ?></textarea>
                    <div class="text-muted small mt-1">Available placeholders: <code>{student_name}</code>, <code>{reason}</code>, <code>{pickup_person}</code>, <code>{exit_time}</code>, <code>{school_name}</code></div>
                </div>
            </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:20px;">
            <button type="submit" class="sa-btn sa-btn-primary">
                <i class="ti ti-device-floppy"></i> Save Exit &amp; Dismissal Settings
            </button>
        </div>
    </form>
</div>

<!-- ═══════════════════════════════════════════════════════ -->
<!-- TAB 5: SMS LOGS                                         -->
<!-- ═══════════════════════════════════════════════════════ -->
<div class="att-tab-panel <?= $activeTab==='logs' ? 'active' : '' ?>" id="tab-logs">

    <!-- Filters -->
    <div class="sa-card" style="margin-bottom:20px;">
        <div class="sa-card-title"><i class="ti ti-filter"></i> Filter SMS Logs</div>
        <form method="GET" action="<?= url('admin/attendance-settings') ?>" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <input type="hidden" name="route" value="attendance-settings">
            <input type="hidden" name="tab" value="logs">
            <div>
                <label class="form-label">Type</label>
                <select class="form-select form-select-sm" name="sms_type" style="width:140px;">
                    <option value="">All Types</option>
                    <?php foreach (['checkin' => 'Check-In', 'exit' => 'Student Exit', 'absent' => 'Absent', 'bulk' => 'Bulk', 'test' => 'Test', 'general' => 'General'] as $v => $l): ?>
                        <option value="<?= e($v) ?>" <?= $smsFilter === $v ? 'selected' : '' ?>><?= e($l) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select class="form-select form-select-sm" name="sms_status" style="width:120px;">
                    <option value="">All</option>
                    <option value="sent" <?= $smsStatus === 'sent' ? 'selected' : '' ?>>Sent</option>
                    <option value="failed" <?= $smsStatus === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="pending" <?= $smsStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
            <div>
                <label class="form-label">Date</label>
                <input type="date" class="form-control form-control-sm" name="sms_date" value="<?= e($smsDate) ?>" style="width:160px;">
            </div>
            <div>
                <label class="form-label">Search Phone/Name</label>
                <input type="text" class="form-control form-control-sm" name="sms_search" value="<?= e($smsSearch) ?>" placeholder="Search…" style="width:180px;">
            </div>
            <button type="submit" class="sa-btn sa-btn-primary" style="height:32px;">Filter</button>
            <a href="<?= url('admin/attendance-settings?tab=logs') ?>" class="sa-btn" style="height:32px;">Clear</a>
        </form>
    </div>

    <!-- Log Table -->
    <div class="sa-card">
        <div class="sa-card-title" style="justify-content:space-between;">
            <span><i class="ti ti-messages"></i> SMS Log — <?= count($smsLogs) ?> records</span>
        </div>
        <div class="table-responsive">
            <table class="app-table" id="smsLogTable">
                <thead>
                    <tr>
                        <th style="width:60px;">#</th>
                        <th>Recipient</th>
                        <th style="width:90px;">Type</th>
                        <th style="width:80px;">Status</th>
                        <th>Message Preview</th>
                        <th style="width:160px;">Date &amp; Time</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($smsLogs)): ?>
                        <tr>
                            <td colspan="7" style="text-align:center;padding:40px;color:#9ca3af;">
                                <i class="ti ti-messages" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                                No SMS logs found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($smsLogs as $log): ?>
                            <?php
                                $typeCls = match ($log['sms_type'] ?? 'general') {
                                    'checkin' => 'log-type-checkin',
                                    'absent'  => 'log-type-absent',
                                    'test'    => 'log-type-test',
                                    'bulk'    => 'log-type-bulk',
                                    default   => '',
                                };
                                $statusCls = match ($log['status']) {
                                    'sent'    => 'log-badge-sent',
                                    'failed'  => 'log-badge-failed',
                                    default   => 'log-badge-pending',
                                };
                            ?>
                            <tr>
                                <td style="color:#9ca3af;font-size:12px;"><?= e($log['id']) ?></td>
                                <td>
                                    <div style="font-weight:600;font-size:13px;"><?= e($log['recipient_phone']) ?></div>
                                    <?php if ($log['recipient_name']): ?>
                                        <div style="font-size:11px;color:#9ca3af;"><?= e($log['recipient_name']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="log-type <?= $typeCls ?>"><?= e(ucfirst($log['sms_type'] ?? 'general')) ?></span></td>
                                <td><span class="log-badge <?= $statusCls ?>"><?= e(ucfirst($log['status'])) ?></span></td>
                                <td>
                                    <div style="font-size:12px;color:#4b5563;max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                                         title="<?= e($log['message']) ?>">
                                        <?= e(mb_substr($log['message'], 0, 80)) ?>…
                                    </div>
                                </td>
                                <td style="font-size:12px;color:#6b7280;">
                                    <?= $log['sent_at'] ? e(date('M j, Y g:i A', strtotime($log['sent_at']))) : '—' ?>
                                </td>
                                <td>
                                    <form method="POST" action="<?= url('admin/attendance-settings/sms-log/' . $log['id'] . '/delete') ?>"
                                          onsubmit="return confirm('Delete this log entry?')">
                                        <?= csrf_field() ?>
                                        <button class="sa-btn" style="padding:3px 8px;font-size:11px;color:#dc2626;" type="submit">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// ── Tab switching ─────────────────────────────────────────────────────────────
(function() {
    const tabs   = document.querySelectorAll('.att-settings-tab');
    const panels = document.querySelectorAll('.att-tab-panel');

    function activateTab(target) {
        if (!target) return;
        tabs.forEach(t => t.classList.toggle('active', t.dataset.tab === target));
        panels.forEach(p => p.classList.toggle('active', p.id === 'tab-' + target));
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.tab;
            activateTab(target);
            try {
                const url = new URL(window.location.href);
                url.searchParams.set('tab', target);
                history.replaceState(null, '', url.toString());
            } catch (e) {}
        });
    });

    // Hash-based tab activation (e.g. from redirects)
    const hash = location.hash.replace('#tab-', '').replace('#', '');
    if (hash && document.getElementById('tab-' + hash)) {
        activateTab(hash);
    }
})();

// ── Master SMS toggle ─────────────────────────────────────────────────────────
const masterSwitch = document.getElementById('masterSmsSwitch');
const masterLabel  = document.getElementById('masterSmsLabel');
const subToggles   = document.getElementById('smsSubToggles');

if (masterSwitch) {
    masterSwitch.addEventListener('change', () => {
        const on = masterSwitch.checked;
        masterLabel.textContent = on ? 'ENABLED' : 'DISABLED';
        masterLabel.style.color = on ? '#15803d' : '#dc2626';
        subToggles.style.opacity = on ? '1' : '0.4';
        subToggles.style.pointerEvents = on ? 'auto' : 'none';
    });
}

// ── Gateway selector note ─────────────────────────────────────────────────────
const gw   = document.getElementById('gatewaySelect');
const note = document.getElementById('gatewayNote');
if (gw && note) {
    gw.addEventListener('change', () => {
        const notes = {
            stub:    'Stub mode: SMS messages are written to PHP error_log only. No real SMS sent.',
            termii:  'Termii API: ensure your API key is valid and the Sender ID is approved at termii.com.',
            bulksms: 'BulkSMSNigeria: enter your API token and approved sender name.',
        };
        note.textContent = notes[gw.value] || '';
    });
}

// ── Status preview ────────────────────────────────────────────────────────────
function toMins(t) {
    const [h, m] = t.split(':').map(Number);
    return h * 60 + m;
}
function calcStatus(testTime, open, ontime, close) {
    const t = toMins(testTime), o = toMins(ontime), c = toMins(close);
    if (t > c)  return 'Denied';
    if (t <= o) return 'Present';
    return 'Late';
}

const previewInput  = document.getElementById('previewTime');
const previewResult = document.getElementById('previewResult');
const inpOntime     = document.getElementById('inp-ontime');
const inpClose      = document.getElementById('inp-close');

function updatePreview() {
    const status = calcStatus(
        previewInput?.value  || '07:15',
        document.getElementById('inp-open')?.value || '07:00',
        inpOntime?.value || '07:30',
        inpClose?.value  || '09:00'
    );
    const cls   = {Present:'status-badge-p', Late:'status-badge-l', Denied:'status-badge-d'};
    if (previewResult) {
        previewResult.textContent  = status;
        previewResult.className    = cls[status] || 'status-badge-p';
    }
}

[previewInput, inpOntime, inpClose, document.getElementById('inp-open')].forEach(el => {
    el?.addEventListener('change', updatePreview);
    el?.addEventListener('input',  updatePreview);
});
updatePreview();
</script>
