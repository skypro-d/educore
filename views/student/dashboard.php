<?php
$fullName = trim($student['first_name'] . ' ' . ($student['middle_name'] ?? '') . ' ' . $student['last_name']);
?>
<style>
    /* Card design */
    .st-banner {
        background: linear-gradient(135deg, var(--student-primary), var(--brand-dashboard, #1056c2));
        color: #fff;
        padding: 2.5rem;
        border-radius: var(--radius);
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(11, 61, 145, 0.15);
    }
    .st-banner h1 { font-size: 2rem; font-weight: 700; margin: 0 0 0.5rem; }
    .st-banner p { font-size: 1.05rem; opacity: 0.9; margin: 0; }
    .st-banner .badge-class {
        background: rgba(255,255,255,0.15);
        padding: 0.4rem 1rem;
        border-radius: 2rem;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
        margin-top: 1rem;
        border: 1px solid rgba(255,255,255,0.25);
    }
    
    .st-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .st-card {
        background: #fff;
        border-radius: var(--radius);
        padding: 1.5rem;
        box-shadow: 0 4px 18px rgba(0,0,0,0.03);
        border: 1px solid #e8eef4;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .st-card .card-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
    .st-card .card-title { font-size: 0.82rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
    .st-card .card-icon { font-size: 1.6rem; color: var(--student-primary); }
    .st-card .card-value { font-size: 1.8rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem; }
    
    /* Progress bar */
    .st-progress-container { margin-top: 0.5rem; }
    .st-progress-bar {
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        margin-bottom: 0.3rem;
    }
    .st-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #22c55e, #4ade80);
        border-radius: 4px;
        transition: width 1s ease-in-out;
    }
    
    /* Attendance Grid Calendar */
    .cal-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(32px, 1fr));
        gap: 8px;
        margin-top: 1rem;
    }
    .cal-day {
        aspect-ratio: 1;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.72rem;
        transition: transform 0.2s;
    }
    .cal-day:hover { transform: scale(1.1); }
    .cal-day.present { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .cal-day.absent { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .cal-day.late { background: #fef9ec; color: #d97706; border: 1px solid #fef3c7; }
    .cal-day.excused { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .cal-day.none { background: #f1f5f9; color: #94a3b8; }

    /* Student Profile Panel */
    .profile-photo {
        width: 100px; height: 100px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin: 0 auto 1.25rem;
        display: block;
    }
    .profile-photo-placeholder {
        width: 100px; height: 100px;
        border-radius: 50%;
        background: var(--student-accent);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; font-weight: 700;
        border: 4px solid #fff;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        margin: 0 auto 1.25rem;
    }

    .info-list { display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1rem; }
    .info-item { display: flex; justify-content: space-between; font-size: 13.5px; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem; }
    .info-item span:first-child { color: #64748b; font-weight: 500; }
    .info-item span:last-child { color: #1e293b; font-weight: 600; }

    /* Ticker styles */
    .notif-item { display: flex; gap: 10px; align-items: flex-start; padding: 10px 12px; border-radius: 8px; margin-bottom: 8px; background: #f8fafc; border: 1px solid #e2e8f0; }
    .notif-item.unread { background: #eff6ff; border-color: #bfdbfe; }
    .notif-icon { color: var(--student-primary); font-size: 1.1rem; flex-shrink: 0; }
    .notif-content { font-size: 12.5px; line-height: 1.4; color: #334155; }
    .notif-time { font-size: 10.5px; color: #94a3b8; margin-top: 3px; }
</style>

<!-- Banner -->
<div class="st-banner">
    <div style="z-index: 5; position: relative;">
        <p>Welcome back,</p>
        <h1><?= e($fullName) ?></h1>
        <p>Admission No: <strong><?= e($student['admission_number']) ?></strong></p>
        <span class="badge-class"><i class="ti ti-school" style="margin-right:6px"></i><?= e($student['class_name']) ?></span>
    </div>
</div>

<!-- Grid Cards -->
<div class="st-grid">
    <!-- Card 1: Attendance Rate -->
    <div class="st-card">
        <div class="card-head">
            <span class="card-title">Attendance Rate</span>
            <i class="ti ti-calendar-check card-icon" style="color:#22c55e"></i>
        </div>
        <div>
            <div class="card-value"><?= $attendanceRate ?>%</div>
            <div class="st-progress-container">
                <div class="st-progress-bar">
                    <div class="st-progress-fill" style="width: <?= $attendanceRate ?>%;"></div>
                </div>
                <div class="d-flex justify-content-between" style="font-size:11px;color:#64748b;">
                    <span>Term Target: 90%</span>
                    <span>Excellent</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Fees Status -->
    <div class="st-card">
        <div class="card-head">
            <span class="card-title">School Fees</span>
            <i class="ti ti-receipt card-icon" style="color:<?= $feeBalance > 0 ? '#ef4444' : '#22c55e' ?>"></i>
        </div>
        <div>
            <div class="card-value">&#8358;<?= number_format($feeBalance) ?></div>
            <p style="font-size:12px;margin:0;" class="<?= $feeBalance > 0 ? 'text-danger' : 'text-success' ?>">
                <strong><?= $feeBalance > 0 ? 'Outstanding Balance' : 'Fully Cleared ✓' ?></strong>
            </p>
        </div>
    </div>

    <!-- Card 3: Class Info -->
    <div class="st-card">
        <div class="card-head">
            <span class="card-title">Current Class</span>
            <i class="ti ti-school card-icon"></i>
        </div>
        <div>
            <div class="card-value"><?= e($student['class_name']) ?></div>
            <p style="font-size:12px;margin:0;color:#64748b;">Academic Session: <?= e(current_academic_year()) ?></p>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Left Column: Attendance & Results -->
    <div class="col-lg-8">
        <!-- Monthly Attendance Calendar Grid -->
        <div class="st-card mb-4" style="justify-content:flex-start;">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h3 style="font-size:15px;font-weight:700;margin:0;"><i class="ti ti-calendar-event text-success" style="margin-right:8px"></i>Attendance Calendar (<?= date('F Y') ?>)</h3>
                <span class="text-muted" style="font-size:12px">✓ Present | ✗ Absent | Late</span>
            </div>
            
            <div class="cal-grid">
                <?php
                $daysInMonth = (int) date('t');
                $attendanceMap = [];
                foreach ($calendar as $att) {
                    $dayNum = (int) date('j', strtotime($att['date']));
                    $attendanceMap[$dayNum] = strtolower($att['status']);
                }
                
                for ($d = 1; $d <= $daysInMonth; $d++):
                    $status = $attendanceMap[$d] ?? 'none';
                    $label = $d;
                    $statusIcon = $d;
                    if ($status === 'present') { $statusIcon = '✓'; }
                    elseif ($status === 'absent') { $statusIcon = '✗'; }
                    elseif ($status === 'late') { $statusIcon = 'L'; }
                    elseif ($status === 'excused') { $statusIcon = 'E'; }
                ?>
                    <div class="cal-day <?= $status ?>" title="Day <?= $d ?>: <?= ucfirst($status) ?>">
                        <?= $statusIcon ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Academic Performance Table -->
        <div class="st-card" style="justify-content:flex-start;">
            <h3 style="font-size:15px;font-weight:700;margin-bottom:1rem;"><i class="ti ti-report-analytics text-primary" style="margin-right:8px"></i>Latest Result Scores</h3>
            <div class="table-responsive">
                <table class="table table-sm align-middle" style="font-size:13px">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Term</th>
                            <th style="text-align:center">Test (40)</th>
                            <th style="text-align:center">Exam (60)</th>
                            <th style="text-align:center">Total (100)</th>
                            <th style="text-align:center">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($results): foreach ($results as $row): ?>
                        <tr>
                            <td style="font-weight:600;color:#1e293b;"><?= e($row['subject_name']) ?></td>
                            <td><?= e($row['term']) ?></td>
                            <td style="text-align:center"><?= (float) ($row['ca1'] ?? 0) + (float) ($row['ca2'] ?? 0) + (float) ($row['assignment'] ?? 0) + (float) ($row['mid_term'] ?? 0) ?></td>
                            <td style="text-align:center"><?= (float) ($row['exam'] ?? 0) ?></td>
                            <td style="text-align:center;font-weight:700;color:var(--student-primary);"><?= (float) ($row['total'] ?? 0) ?></td>
                            <td style="text-align:center"><span class="badge bg-secondary"><?= e($row['grade'] ?: '—') ?></span></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="6" style="text-align:center;padding:1.5rem;color:#94a3b8;">No academic records published for this term yet.</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Profile details & Timetable & Notifications -->
    <div class="col-lg-4">
        <!-- Quick Profile Card -->
        <div class="st-card mb-4" style="justify-content:flex-start;">
            <?php if (!empty($student['passport_photo'])): ?>
                <img class="profile-photo" src="<?= url('uploads/' . $student['passport_photo']) ?>" alt="<?= e($fullName) ?>">
            <?php else: ?>
                <div class="profile-photo-placeholder"><?= strtoupper(substr($student['first_name'], 0, 1)) ?></div>
            <?php endif; ?>
            <h3 style="text-align:center;font-size:16px;font-weight:700;margin:0 0 10px;"><?= e($fullName) ?></h3>
            <p style="text-align:center;font-size:12px;color:#64748b;margin:0 0 15px;">Student Profile Summary</p>
            
            <div class="info-list">
                <div class="info-item"><span>Admission Number</span><span><?= e($student['admission_number']) ?></span></div>
                <div class="info-item"><span>Date of Birth</span><span><?= e($student['date_of_birth']) ?></span></div>
                <div class="info-item"><span>Gender</span><span><?= e($student['gender']) ?></span></div>
                <div class="info-item"><span>Blood Group</span><span><?= e($student['blood_group'] ?: '—') ?></span></div>
                <div class="info-item"><span>Status</span><span><span class="badge bg-success" style="font-size:10px"><?= e($student['student_status'] ?? 'Active') ?></span></span></div>
            </div>
            <div style="margin-top:1.5rem;display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <a class="btn btn-sm btn-primary" href="<?= url('student/id-card') ?>"><i class="ti ti-id" style="margin-right:6px"></i>View ID</a>
                <button class="btn btn-sm btn-outline-secondary" onclick="window.print()"><i class="ti ti-printer" style="margin-right:6px"></i>Print Slip</button>
            </div>
        </div>

        <!-- Today's Timetable -->
        <div class="st-card mb-4" style="justify-content:flex-start;">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:1rem;"><i class="ti ti-clock text-warning" style="margin-right:8px"></i>Today's Class Schedule</h3>
            <?php if ($timetableToday): foreach ($timetableToday as $slot): ?>
                <div style="display:flex;justify-content:between;align-items:center;border-bottom:1px solid #f1f5f9;padding:8px 0;">
                    <div>
                        <strong style="font-size:13px;color:#1e293b;"><?= e($slot['subject_name']) ?></strong>
                        <div style="font-size:11px;color:#64748b;"><i class="ti ti-user" style="margin-right:4px"></i><?= e($slot['first_name'] . ' ' . $slot['last_name']) ?></div>
                    </div>
                    <div style="text-align:right;">
                        <span style="font-size:11px;font-weight:600;background:#f1f5f9;padding:2px 8px;border-radius:12px;color:#334155;">
                            <?= date('h:i A', strtotime($slot['start_time'])) ?> - <?= date('h:i A', strtotime($slot['end_time'])) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <p style="font-size:12px;color:#94a3b8;margin:0;padding:10px 0;">No classes scheduled for today.</p>
            <?php endif; ?>
        </div>

        <!-- Announcements / Notifications -->
        <div class="st-card" style="justify-content:flex-start;">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:1rem;"><i class="ti ti-bell text-danger" style="margin-right:8px"></i>In-App Notifications</h3>
            <?php if ($notifications): foreach ($notifications as $notif): ?>
                <div class="notif-item <?= $notif['is_read'] ? '' : 'unread' ?>">
                    <i class="ti ti-info-circle notif-icon"></i>
                    <div class="notif-content">
                        <strong><?= e($notif['title']) ?></strong><br>
                        <?= e($notif['message']) ?>
                        <div class="notif-time"><?= date('j M, h:i A', strtotime($notif['created_at'])) ?></div>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <p style="font-size:12px;color:#94a3b8;margin:0;">No new notifications received.</p>
            <?php endif; ?>
            <a href="<?= url('student/notifications') ?>" style="display:block;text-align:center;font-size:12px;font-weight:600;margin-top:10px;text-decoration:none;color:var(--student-primary);">View All Alerts</a>
        </div>
    </div>
</div>
