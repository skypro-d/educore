<?php
$school = setting('school_name', 'Westfield Academy');
$pipeline = [
    ['Applications received', (int) $stats['total'], '#378ADD'],
    ['Document verified', max(0, (int) $stats['total'] - (int) $stats['rejected']), '#185FA5'],
    ['Under review', (int) $stats['pending'], '#7F77DD'],
    ['Interviews scheduled', count(array_filter($recent, fn($r) => $r['status'] === 'Interview Scheduled')), '#534AB7'],
    ['Decisions made', (int) $stats['approved'] + (int) $stats['rejected'], '#1D9E75'],
    ['Enrollment confirmed', (int) ($stats['enrolled'] ?? 0), '#0F6E56'],
];
$maxPipeline = max(1, (int) $stats['total']);
$gradeColors = ['#378ADD', '#7F77DD', '#1D9E75', '#EF9F27', '#D85A30', '#0F6E56'];
$activityIcons = [
    'login' => 'ti-login',
    'application_approved' => 'ti-circle-check',
    'application_rejected' => 'ti-x',
    'application_enrolled' => 'ti-user-check'
];
?>

<div class="sa-top-bar">
    <div>
        <h1><?= e($school) ?> Dashboard</h1>
        <p>Session: <?= e(setting('academic_year', '2024/2025')) ?> &nbsp;.&nbsp; Term: <?= e(setting('current_term', 'First')) ?> &nbsp;.&nbsp; Quick Overview</p>
    </div>
    <div class="sa-top-actions">
        <span class="badge-session" style="background:#16a34a;color:#fff;"><i class="ti ti-wifi"></i> Portal Live</span>
        <a class="sa-btn" href="<?= url('admin/settings') ?>"><i class="ti ti-settings"></i> Settings</a>
        <a class="sa-btn sa-btn-primary" href="<?= url('admin/student-fees') ?>"><i class="ti ti-plus"></i> Record Payment</a>
    </div>
</div>

<!-- Consolidated Metrics -->
<div class="sa-metrics" style="grid-template-columns: repeat(6, 1fr); gap: 15px; margin-bottom: 25px;">
    <div class="sa-metric-card" style="padding: 15px;">
        <div class="label" style="font-size: 11px;"><i class="ti ti-users" style="color:#0b3d91;"></i> Total Students</div>
        <div class="value" style="font-size: 20px; font-weight: 800; margin-top: 5px;"><?= number_format($totalStudents) ?></div>
        <div class="sub" style="font-size: 10px;">Active enrolled</div>
    </div>
    <div class="sa-metric-card" style="padding: 15px;">
        <div class="label" style="font-size: 11px;"><i class="ti ti-school" style="color:#16a34a;"></i> Staff Registry</div>
        <div class="value" style="font-size: 20px; font-weight: 800; margin-top: 5px;"><?= number_format($totalStaff) ?></div>
        <div class="sub" style="font-size: 10px;">Teachers &amp; admin</div>
    </div>
    <div class="sa-metric-card" style="padding: 15px;">
        <div class="label" style="font-size: 11px;"><i class="ti ti-building" style="color:#d97706;"></i> Total Classes</div>
        <div class="value" style="font-size: 20px; font-weight: 800; margin-top: 5px;"><?= number_format($totalClasses) ?></div>
        <div class="sub" style="font-size: 10px;">Configured rooms</div>
    </div>
    <div class="sa-metric-card" style="padding: 15px;">
        <div class="label" style="font-size: 11px;"><i class="ti ti-credit-card" style="color:#2563eb;"></i> Admission Rev</div>
        <div class="value" style="font-size: 20px; font-weight: 800; margin-top: 5px; color:#2563eb;">₦<?= number_format((float)$stats['revenue']) ?></div>
        <div class="sub" style="font-size: 10px;">Form &amp; acceptance</div>
    </div>
    <div class="sa-metric-card" style="padding: 15px;">
        <div class="label" style="font-size: 11px;"><i class="ti ti-wallet" style="color:#16a34a;"></i> Term Fees Paid</div>
        <div class="value" style="font-size: 20px; font-weight: 800; margin-top: 5px; color:#16a34a;">₦<?= number_format($termFeesCollected) ?></div>
        <div class="sub" style="font-size: 10px;">Tuition collected</div>
    </div>
    <div class="sa-metric-card" style="padding: 15px;">
        <div class="label" style="font-size: 11px;"><i class="ti ti-alert-circle" style="color:#dc2626;"></i> Outstanding Fees</div>
        <div class="value" style="font-size: 20px; font-weight: 800; margin-top: 5px; color:#dc2626;">₦<?= number_format($termFeesOutstanding) ?></div>
        <div class="sub" style="font-size: 10px;">Pending balances</div>
    </div>
</div>

<div class="sa-grid2">
    <!-- Chart Column -->
    <div class="sa-card">
        <div class="sa-card-title"><i class="ti ti-chart-bar"></i> Monthly Admission Intake</div>
        <div class="chart-legend">
            <span><span class="dot" style="background:#B5D4F4;"></span>Applications</span>
        </div>
        <div style="position:relative;width:100%;height:220px;">
            <canvas id="monthChart" data-labels='<?= e(json_encode(array_column($byMonth, 'label'))) ?>' data-values='<?= e(json_encode(array_column($byMonth, 'total'))) ?>'></canvas>
        </div>
    </div>
    
    <!-- Application Pipeline -->
    <div class="sa-card">
        <div class="sa-card-title"><i class="ti ti-git-branch"></i> Admission Funnel Pipeline</div>
        <div class="stage-list">
            <?php foreach ($pipeline as $stage): ?>
                <div class="stage-row">
                    <span class="stage-label" style="font-size: 12.5px;"><?= e($stage[0]) ?></span>
                    <div class="stage-bar-wrap" style="height: 10px;"><div class="stage-bar" style="width:<?= e((string) min(100, round($stage[1] / $maxPipeline * 100))) ?>%;background:<?= e($stage[2]) ?>;"></div></div>
                    <span class="stage-count" style="font-size: 13px; font-weight:700;"><?= number_format($stage[1]) ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="sa-grid3" style="margin-top: 25px; gap: 20px;">
    <!-- Recent Fee Payments log -->
    <div class="sa-card">
        <div class="sa-card-title" style="justify-content:space-between;">
            <span><i class="ti ti-receipt"></i> Recent Fee Payments</span>
            <a href="<?= url('admin/student-fees') ?>" style="font-size: 11px; text-decoration:none;">View All</a>
        </div>
        <div style="max-height: 280px; overflow-y:auto; font-size: 12.5px;">
            <?php if ($recentFees): foreach ($recentFees as $rf):
                $stB = $rf['payment_status'] === 'Paid' ? '#dcfce7' : '#fee2e2';
                $stC = $rf['payment_status'] === 'Paid' ? '#15803d' : '#b91c1c';
            ?>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f1f5f9;">
                    <div>
                        <strong style="color:#334155;"><?= e($rf['first_name'].' '.$rf['last_name']) ?></strong>
                        <div style="font-size:10px; color:#94a3b8;"><?= e($rf['fee_name']) ?></div>
                    </div>
                    <div style="text-align:right;">
                        <span style="font-weight:700; color:#16a34a;">₦<?= number_format((float)$rf['amount_paid']) ?></span>
                        <div style="font-size:9px; color:#94a3b8;"><?= date('M j, Y', strtotime($rf['payment_date'] ?: $rf['created_at'])) ?></div>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <p style="color:#94a3b8; text-align:center; padding: 30px 0;">No school fee payments recorded.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Attendance Activity -->
    <div class="sa-card">
        <div class="sa-card-title" style="justify-content:space-between;">
            <span><i class="ti ti-calendar-check"></i> Daily Attendance Alerts</span>
            <a href="<?= url('admin/attendance') ?>" style="font-size: 11px; text-decoration:none;">Mark List</a>
        </div>
        <div style="max-height: 280px; overflow-y:auto; font-size: 12.5px;">
            <?php if ($recentAttendance): foreach ($recentAttendance as $ra):
                $attColor = ['Present'=>'#15803d','Absent'=>'#b91c1c','Late'=>'#d97706','Excused'=>'#475569'][$ra['status']] ?? '#374151';
                $attBg = ['Present'=>'#dcfce7','Absent'=>'#fee2e2','Late'=>'#fef9ec','Excused'=>'#f1f5f9'][$ra['status']] ?? '#f3f4f6';
            ?>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f1f5f9;">
                    <div>
                        <strong style="color:#334155;"><?= e($ra['first_name'].' '.$ra['last_name']) ?></strong>
                        <div style="font-size:10px; color:#94a3b8;"><?= e($ra['class_name']) ?> &nbsp;.&nbsp; <?= date('M j, Y', strtotime($ra['date'])) ?></div>
                    </div>
                    <span style="padding:2px 8px; border-radius:12px; font-size:10px; font-weight:700; background:<?= $attBg ?>; color:<?= $attColor ?>;">
                        <?= e($ra['status']) ?>
                    </span>
                </div>
            <?php endforeach; else: ?>
                <p style="color:#94a3b8; text-align:center; padding: 30px 0;">No attendance records entered today.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Campus Gate & Student Movement Today -->
    <div class="sa-card">
        <div class="sa-card-title" style="justify-content:space-between;">
            <span><i class="ti ti-door-exit"></i> Gate &amp; Student Movement Today</span>
            <a href="<?= url('admin/exit-scanner') ?>" class="badge bg-primary text-white text-decoration-none px-2 py-1" style="font-size:10px;">
                <i class="ti ti-scan"></i> Scanner
            </a>
        </div>
        <div class="p-2" style="font-size:12.5px;">
            <div class="d-flex justify-content-between align-items-center p-2 mb-2 rounded" style="background:#f0fdf4; border:1px solid #bbf7d0;">
                <div>
                    <span class="text-success fw-bold d-block">Currently on Campus</span>
                    <span class="text-muted" style="font-size:11px;">Checked in minus departures</span>
                </div>
                <h4 class="mb-0 fw-bold text-success"><?= number_format($studentsInSchool ?? 0) ?></h4>
            </div>

            <div class="row g-2 mb-2 text-center">
                <div class="col-6">
                    <div class="p-2 rounded bg-light border">
                        <span class="text-muted d-block" style="font-size:10px; text-transform:uppercase;">Total Departures</span>
                        <strong class="text-dark fs-6"><?= number_format($studentsExitedToday ?? 0) ?></strong>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-2 rounded bg-light border">
                        <span class="text-muted d-block" style="font-size:10px; text-transform:uppercase;">Early Exits</span>
                        <strong class="<?= ($earlyExitsToday ?? 0) > 0 ? 'text-warning' : 'text-dark' ?> fs-6"><?= number_format($earlyExitsToday ?? 0) ?></strong>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between pt-1">
                <a href="<?= url('admin/exit-logs') ?>" class="text-primary small text-decoration-none fw-semibold">
                    <i class="ti ti-history me-1"></i>Audit Exit Logs &rarr;
                </a>
                <a href="<?= url('admin/gates') ?>" class="text-secondary small text-decoration-none">
                    <i class="ti ti-barrier-block me-1"></i>Gates (<?= count($recentAttendance ? [1] : [1]) ?>)
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Students Admission Registry -->
<div class="sa-card" style="margin-top: 25px;">
    <div class="sa-card-title"><i class="ti ti-users"></i> Recent Admission &amp; Enrollment Registry</div>
    <div class="filter-row">
        <input class="search-box" type="text" placeholder="Search by name or ID..." id="saSearchBox">
        <select id="saStatusFilter">
            <option value="">All statuses</option>
            <?php foreach (['Submitted','Under Review','Approved','Enrolled','Rejected'] as $status): ?><option><?= e($status) ?></option><?php endforeach; ?>
        </select>
        <a class="sa-btn ms-auto" href="<?= url('admin/export') ?>"><i class="ti ti-download"></i> Export CSV</a>
    </div>
    <div class="table-responsive">
        <table class="app-table" id="saAppTable">
            <thead><tr><th style="width:220px">Student</th><th style="width:130px">App ID</th><th style="width:120px">Class</th><th style="width:130px">Applied</th><th style="width:140px">Status</th><th style="width:90px">Action</th></tr></thead>
            <tbody>
                <?php foreach ($recent as $i => $row): $initials = strtoupper(substr($row['first_name'], 0, 1) . substr($row['last_name'], 0, 1)); ?>
                    <tr data-status="<?= e($row['status']) ?>">
                        <td><div style="display:flex;align-items:center;gap:8px;"><div class="avatar av-<?= e(['blue','teal','purple','coral','pink','amber'][$i % 6]) ?>"><?= e($initials) ?></div><?= e($row['first_name'] . ' ' . $row['last_name']) ?></div></td>
                        <td style="color:#888"><?= e($row['application_number']) ?></td>
                        <td><?= e($row['class_name']) ?></td>
                        <td style="color:#888"><?= e(date('M j, Y', strtotime($row['created_at']))) ?></td>
                        <td><span class="status-pill <?= e('s-' . strtolower(str_replace(' ', '-', $row['status']))) ?>"><?= e($row['status']) ?></span></td>
                        <td>
                            <div style="display:flex; gap:4px;">
                                <a class="sa-btn" style="font-size:11px;padding:4px 8px;" href="<?= url('admin/applications/' . $row['id']) ?>"><i class="ti ti-eye"></i></a>
                                <?php if ($row['status'] === 'Enrolled'): ?>
                                    <a class="sa-btn sa-btn-primary" style="font-size:11px;padding:4px 8px;" href="<?= url('admin/applications/' . $row['id'] . '/id-card') ?>"><i class="ti ti-id"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
