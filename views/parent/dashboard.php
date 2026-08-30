<?php
// views/parent/dashboard.php
$school      = setting('school_name', 'School');
$studentName = $student['first_name'] . ' ' . $student['last_name'];
$classN      = $student['class_name'] ?? '—';
$presentCnt  = $attendance['Present'] ?? 0;
$absentCnt   = $attendance['Absent'] ?? 0;
$lateCnt     = $attendance['Late'] ?? 0;
$totalDays   = $presentCnt + $absentCnt + $lateCnt + ($attendance['Excused'] ?? 0);
$attPct      = $totalDays ? round((($presentCnt + ($lateCnt * 0.5)) / $totalDays) * 100) : 100;
?>
<style>
    /* Premium Style Additions */
    .st-progress-bar {
        height: 8px;
        background: #e2e8f0;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 8px;
        margin-bottom: 4px;
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
        grid-template-columns: repeat(auto-fill, minmax(28px, 1fr));
        gap: 6px;
        margin-top: 10px;
    }
    .cal-day {
        aspect-ratio: 1;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.7rem;
        transition: transform 0.2s;
    }
    .cal-day:hover { transform: scale(1.1); }
    .cal-day.present { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
    .cal-day.absent { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
    .cal-day.late { background: #fef9ec; color: #d97706; border: 1px solid #fef3c7; }
    .cal-day.excused { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
    .cal-day.none { background: #f1f5f9; color: #94a3b8; }

    /* Timetable preview */
    .time-slot {
        display: flex;
        justify-content: space-between;
        padding: 8px 12px;
        background: #f8fafc;
        border-left: 3px solid var(--parent-primary);
        border-radius: 0 6px 6px 0;
        font-size: 12.5px;
        margin-bottom: 8px;
    }
    
    /* Notification row */
    .notif-row {
        padding: 10px 15px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 8px;
        font-size: 12.5px;
    }
    .notif-row.unread {
        background: #eff6ff;
        border-color: #bfdbfe;
    }
</style>

<div class="parent-topbar">
    <div class="page-title">Dashboard</div>
    <div class="topbar-actions">
        <span style="font-size:13px;color:#6b7280;"><i class="ti ti-calendar" style="margin-right:4px;"></i><?= date('D, M j Y') ?></span>
    </div>
</div>

<div class="parent-content">

    <!-- Welcome Banner -->
    <div style="background:linear-gradient(135deg,#0b3d91 0%,#1a6dd8 100%);border-radius:16px;padding:28px;color:#fff;margin-bottom:24px;position:relative;overflow:hidden; box-shadow: 0 10px 30px rgba(11, 61, 145, 0.15);">
        <div style="position:absolute;top:-30px;right:-30px;width:150px;height:150px;border-radius:50%;background:rgba(255,255,255,.07);"></div>
        <div style="position:absolute;bottom:-50px;right:60px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.05);"></div>
        <p style="font-size:13px;opacity:.8;margin:0 0 4px;">Welcome back, Parent</p>
        <h2 style="font-size:22px;font-weight:700;margin:0 0 8px;"><?= e($_SESSION['parent']['name']) ?></h2>
        <p style="font-size:13px;opacity:.75;margin:0;"><i class="ti ti-user" style="margin-right:5px;"></i><?= e($studentName) ?> &nbsp;·&nbsp; <i class="ti ti-building" style="margin-right:5px;"></i><?= e($classN) ?> &nbsp;·&nbsp; Admission No: <strong><?= e($student['admission_number']) ?></strong></p>
    </div>

    <!-- Today's Campus Movement Status -->
    <div style="background:#fff;border-radius:14px;padding:16px 20px;border:1px solid #e8eef4;margin-bottom:24px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px;box-shadow:0 4px 18px rgba(0,0,0,0.02);">
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:42px;height:42px;border-radius:10px;background:#f0f9ff;display:flex;align-items:center;justify-content:center;color:#0284c7;">
                <i class="ti ti-shield-check" style="font-size:22px;"></i>
            </div>
            <div>
                <strong style="color:#0f172a;font-size:14px;display:block;">Today's Campus Movement Status</strong>
                <span style="font-size:12px;color:#64748b;">
                    <?php if ($todayExit): ?>
                        <span class="text-success fw-bold"><i class="ti ti-door-exit"></i> Departed School</span> at <?= date('g:i A', strtotime($todayExit['exit_time'])) ?> via <?= e($todayExit['gate_name'] ?: 'Gate') ?>
                        <?= $todayExit['pickup_person_name'] ? '(Collector: ' . e($todayExit['pickup_person_name']) . ')' : '' ?>
                    <?php elseif ($todayAttendance): ?>
                        <span class="text-primary fw-bold"><i class="ti ti-building"></i> Currently on Campus</span> (Checked in at <?= date('g:i A', strtotime($todayAttendance['time_in'])) ?>)
                    <?php else: ?>
                        <span class="text-muted">No gate check-in or exit recorded yet today.</span>
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <div>
            <a href="<?= url('parent/attendance') ?>" class="btn btn-sm btn-outline-primary rounded-3" style="font-size:12px;">
                <i class="ti ti-history me-1"></i> Full Movement Log
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:24px;">
        <div style="background:#fff;border-radius:14px;padding:20px;border:1px solid #e8eef4;box-shadow:0 4px 18px rgba(0,0,0,0.02);">
            <div style="display:flex;align-items:center;gap:14px;margin-bottom:8px;">
                <div style="width:40px;height:40px;border-radius:10px;background:#eef4ff;display:flex;align-items:center;justify-content:center;"><i class="ti ti-calendar-check" style="font-size:20px;color:#0b3d91;"></i></div>
                <div>
                    <div style="font-size:20px;font-weight:700;color:#1a2535;"><?= $attPct ?>%</div>
                    <div style="font-size:11px;color:#6b7280;">Attendance rate</div>
                </div>
            </div>
            <div class="st-progress-bar">
                <div class="st-progress-fill" style="width: <?= $attPct ?>%;"></div>
            </div>
        </div>
        
        <div style="background:#fff;border-radius:14px;padding:20px;border:1px solid #e8eef4;display:flex;align-items:center;gap:14px;box-shadow:0 4px 18px rgba(0,0,0,0.02);">
            <div style="width:44px;height:44px;border-radius:10px;background:<?= $feeBalance > 0 ? '#fff5f0' : '#f0fdf4' ?>;display:flex;align-items:center;justify-content:center;"><i class="ti ti-receipt" style="font-size:22px;color:<?= $feeBalance > 0 ? '#e84c4c' : '#16a34a' ?>;"></i></div>
            <div>
                <div style="font-size:18px;font-weight:700;color:<?= $feeBalance > 0 ? '#e84c4c' : '#16a34a' ?>;">&#8358;<?= number_format($feeBalance) ?></div>
                <div style="font-size:12px;color:#6b7280;"><?= $feeBalance > 0 ? 'Outstanding Balance' : 'Fees Up to Date' ?></div>
            </div>
        </div>
        
        <div style="background:#fff;border-radius:14px;padding:20px;border:1px solid #e8eef4;display:flex;align-items:center;gap:14px;box-shadow:0 4px 18px rgba(0,0,0,0.02);">
            <div style="width:44px;height:44px;border-radius:10px;background:#fef9ec;display:flex;align-items:center;justify-content:center;"><i class="ti ti-speakerphone" style="font-size:22px;color:#d97706;"></i></div>
            <div>
                <div style="font-size:22px;font-weight:700;color:#1a2535;"><?= count($announcements) ?></div>
                <div style="font-size:12px;color:#6b7280;">Active Announcements</div>
            </div>
        </div>
        
        <div style="background:#fff;border-radius:14px;padding:20px;border:1px solid #e8eef4;display:flex;align-items:center;gap:14px;box-shadow:0 4px 18px rgba(0,0,0,0.02);">
            <div style="width:44px;height:44px;border-radius:10px;background:#f5f0ff;display:flex;align-items:center;justify-content:center;"><i class="ti ti-report-analytics" style="font-size:22px;color:#7c3aed;"></i></div>
            <div>
                <div style="font-size:22px;font-weight:700;color:#1a2535;"><?= count($results) ?></div>
                <div style="font-size:12px;color:#6b7280;">Academic Subjects</div>
            </div>
        </div>
    </div>

    <!-- Attendance Calendar Grid & Timetable today -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:20px;margin-bottom:24px;">
        <!-- Calendar Grid -->
        <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;padding:20px;box-shadow:0 4px 18px rgba(0,0,0,0.02);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <span style="font-weight:600;color:#1a2535;font-size:14px;"><i class="ti ti-calendar-event" style="margin-right:8px;color:#22c55e;"></i>Attendance Grid (<?= date('F') ?>)</span>
                <span class="text-muted" style="font-size:11px;">✓ Present | ✗ Absent | Late</span>
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
                    $classStr = 'none';
                    if ($status === 'present') { $classStr = 'present'; $label = '✓'; }
                    elseif ($status === 'absent') { $classStr = 'absent'; $label = '✗'; }
                    elseif ($status === 'late') { $classStr = 'late'; $label = 'L'; }
                    elseif ($status === 'excused') { $classStr = 'excused'; $label = 'E'; }
                ?>
                    <div class="cal-day <?= $classStr ?>" title="Day <?= $d ?>: <?= ucfirst($status) ?>">
                        <?= $label ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Timetable Today Preview -->
        <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;padding:20px;box-shadow:0 4px 18px rgba(0,0,0,0.02);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <span style="font-weight:600;color:#1a2535;font-size:14px;"><i class="ti ti-calendar" style="margin-right:8px;color:var(--parent-primary);"></i>Child Schedule Today</span>
                <a href="<?= url('parent/timetable') ?>" style="font-size:12px;color:var(--parent-primary);text-decoration:none;">Full timetable →</a>
            </div>
            
            <?php if (empty($timetableToday)): ?>
                <p style="text-align:center;color:#9ca3af;padding:24px;font-size:13px;margin:0;">No classes scheduled for today.</p>
            <?php else: ?>
                <div style="max-height:180px; overflow-y:auto;">
                    <?php foreach ($timetableToday as $t): ?>
                        <div class="time-slot">
                            <div>
                                <strong style="color:#1e293b;"><?= e($t['subject_name']) ?></strong>
                                <div style="font-size:11px;color:#64748b;margin-top:2px;">Teacher: <?= e($t['first_name'] . ' ' . $t['last_name']) ?></div>
                            </div>
                            <div style="text-align:right;font-weight:600;color:var(--parent-accent);">
                                <?= date('h:i A', strtotime($t['start_time'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:20px;margin-bottom:24px;">
        <!-- Recent Results -->
        <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,0.02);">
            <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
                <span style="font-weight:600;color:#1a2535;font-size:14px;"><i class="ti ti-report-analytics" style="margin-right:8px;color:#0b3d91;"></i>Recent Child Grades</span>
                <a href="<?= url('parent/results') ?>" style="font-size:12px;color:#0b3d91;text-decoration:none;">View all →</a>
            </div>
            <div style="padding:8px 0;">
                <?php if ($results): foreach ($results as $r): ?>
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 20px;border-bottom:1px solid #f9fafb;">
                        <span style="font-size:13px;color:#374151;"><?= e($r['subject_name']) ?></span>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <span style="font-size:14px;font-weight:700;color:#1a2535;"><?= number_format((float)($r['total'] ?? 0), 1) ?></span>
                            <span style="font-size:11px;padding:2px 7px;border-radius:20px;background:<?= ($r['grade'] ?? 'F9') < 'D' ? '#dcfce7' : (($r['grade'] ?? 'F9') < 'F' ? '#fef9c3' : '#fee2e2') ?>;color:<?= ($r['grade'] ?? 'F9') < 'D' ? '#16a34a' : (($r['grade'] ?? 'F9') < 'F' ? '#b45309' : '#dc2626') ?>;"><?= e($r['grade'] ?? '—') ?></span>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <p style="text-align:center;color:#9ca3af;padding:24px;font-size:13px;">No results recorded yet</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Notifications Preview -->
        <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;overflow:hidden;box-shadow:0 4px 18px rgba(0,0,0,0.02);">
            <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
                <span style="font-weight:600;color:#1a2535;font-size:14px;"><i class="ti ti-bell" style="margin-right:8px;color:#3b82f6;"></i>Recent Notifications</span>
                <a href="<?= url('parent/notifications') ?>" style="font-size:12px;color:#0b3d91;text-decoration:none;">View all →</a>
            </div>
            <div style="padding:12px 20px;">
                <?php if ($notifications): foreach ($notifications as $n): ?>
                    <div class="notif-row <?= !$n['is_read'] ? 'unread' : '' ?>">
                        <div style="font-weight:600; color:#1e293b;"><?= e($n['title']) ?></div>
                        <div style="color:#475569; font-size:12px; margin-top:2px;"><?= e(substr($n['message'], 0, 70)) ?><?= strlen($n['message']) > 70 ? '...' : '' ?></div>
                        <small style="color:#94a3b8; font-size:10px; margin-top:4px; display:block;"><i class="ti ti-clock"></i> <?= date('M d, h:i A', strtotime($n['created_at'])) ?></small>
                    </div>
                <?php endforeach; else: ?>
                    <p style="text-align:center;color:#9ca3af;padding:12px;font-size:13px;margin:0;">No recent notifications.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;padding:20px;box-shadow:0 4px 18px rgba(0,0,0,0.02);">
        <div style="font-weight:600;color:#1a2535;font-size:14px;margin-bottom:16px;"><i class="ti ti-bolt" style="margin-right:8px;color:#0b3d91;"></i>Quick Actions</div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <a href="<?= url('parent/attendance') ?>" style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:#eef4ff;color:#0b3d91;border-radius:10px;text-decoration:none;font-size:13px;font-weight:500;"><i class="ti ti-calendar-check" style="font-size:17px;"></i>View Attendance</a>
            <a href="<?= url('parent/results') ?>" style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:#f5f0ff;color:#7c3aed;border-radius:10px;text-decoration:none;font-size:13px;font-weight:500;"><i class="ti ti-report-analytics" style="font-size:17px;"></i>View Results</a>
            <a href="<?= url('parent/fees') ?>" style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:#f0fdf4;color:#16a34a;border-radius:10px;text-decoration:none;font-size:13px;font-weight:500;"><i class="ti ti-receipt" style="font-size:17px;"></i>Pay School Fees</a>
            <a href="<?= url('parent/timetable') ?>" style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:#fef9ec;color:#d97706;border-radius:10px;text-decoration:none;font-size:13px;font-weight:500;"><i class="ti ti-calendar" style="font-size:17px;"></i>Child Timetable</a>
            <a href="<?= url('parent/id-card') ?>" style="display:flex;align-items:center;gap:8px;padding:10px 16px;background:#e2f8f5;color:#0d9488;border-radius:10px;text-decoration:none;font-size:13px;font-weight:500;"><i class="ti ti-id" style="font-size:17px;"></i>Child ID Card</a>
        </div>
    </div>

</div>
