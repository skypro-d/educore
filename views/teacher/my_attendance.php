<?php
// views/teacher/my_attendance.php
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 style="font-size: 1.4rem; font-weight: 700; color: #0f172a; margin: 0 0 4px 0;">
            <i class="ti ti-clock-check text-teal me-2" style="color:#0d9488;"></i> My Attendance &amp; Arrival Logs
        </h2>
        <p style="color: #64748b; font-size: 0.88rem; margin: 0;">
            Your personal check-in arrival times, departures, and monthly attendance records
        </p>
    </div>
</div>

<!-- Month & Year Filter -->
<div class="card border-0 shadow-sm p-3 mb-4" style="border-radius:14px; background:#ffffff;">
    <form method="GET" action="<?= url('teacher/my-attendance') ?>" class="row g-2 align-items-center">
        <div class="col-md-5">
            <label class="form-label" style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Month</label>
            <select name="month" class="form-select form-select-sm" onchange="this.form.submit()" style="border-radius:8px; font-weight:600;">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= ($selectedMonth === $m) ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label" style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Year</label>
            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()" style="border-radius:8px; font-weight:600;">
                <?php for ($y = date('Y'); $y >= date('Y') - 3; $y--): ?>
                    <option value="<?= $y ?>" <?= ($selectedYear === $y) ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-2 text-end d-flex align-items-end" style="padding-top:22px;">
            <a href="<?= url('teacher/my-attendance?month=' . date('n') . '&year=' . date('Y')) ?>" class="btn btn-sm btn-light border w-100" style="border-radius:8px; font-weight:600;">
                Current Month
            </a>
        </div>
    </form>
</div>

<!-- Personal Summary Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3" style="border-radius:12px; background:#fff;">
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Days Present (On Time)</div>
            <div style="font-size:1.6rem; font-weight:700; color:#15803d;"><?= (int)$stats['present'] ?></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3" style="border-radius:12px; background:#fff;">
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Late Arrivals</div>
            <div style="font-size:1.6rem; font-weight:700; color:#b45309;"><?= (int)$stats['late'] ?></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3" style="border-radius:12px; background:#fff;">
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Absent Days</div>
            <div style="font-size:1.6rem; font-weight:700; color:#dc2626;"><?= (int)$stats['absent'] ?></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3" style="border-radius:12px; background:#fff;">
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Punctuality Rate</div>
            <div style="font-size:1.6rem; font-weight:700; color:#0d9488;"><?= $stats['punctuality'] ?>%</div>
        </div>
    </div>
</div>

<!-- Personal Attendance Log Table -->
<div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden; background:#fff;">
    <div class="card-header bg-white py-3 px-4 border-0">
        <h3 class="mb-0" style="font-size:1.1rem; font-weight:700; color:#1e293b;">
            Daily Check-In &amp; Check-Out Log (<?= date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) ?>)
        </h3>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase" style="font-size:11px; letter-spacing:0.5px; font-weight:700; color:#64748b;">
                <tr>
                    <th class="ps-4 py-3">Date</th>
                    <th class="py-3 text-center">Arrival Time (Time In)</th>
                    <th class="py-3 text-center">Departure (Time Out)</th>
                    <th class="py-3 text-center">Status</th>
                    <th class="py-3 text-center">Method</th>
                    <th class="pe-4 py-3">Remark</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="ti ti-calendar-event" style="font-size:2.2rem; display:block; margin-bottom:8px; color:#cbd5e1;"></i>
                            No attendance logs recorded for this month yet.
                        </td>
                    </tr>
                <?php else: foreach ($logs as $log): ?>
                    <?php
                    $statusBg = '#f1f5f9';
                    $statusColor = '#475569';
                    if ($log['status'] === 'Present') { $statusBg = '#dcfce7'; $statusColor = '#15803d'; }
                    elseif ($log['status'] === 'Late') { $statusBg = '#fef3c7'; $statusColor = '#b45309'; }
                    elseif ($log['status'] === 'Absent') { $statusBg = '#fee2e2'; $statusColor = '#dc2626'; }
                    elseif ($log['status'] === 'On Leave') { $statusBg = '#e0e7ff'; $statusColor = '#4338ca'; }
                    ?>
                    <tr>
                        <td class="ps-4 py-3 font-monospace" style="font-weight:700; color:#1e293b;">
                            <?= date('D, M j, Y', strtotime($log['date'])) ?>
                        </td>
                        <td class="py-3 text-center">
                            <?php if (!empty($log['time_in'])): ?>
                                <span class="badge bg-light text-dark border px-2 py-1 font-monospace" style="font-size:12px; font-weight:700;">
                                    <i class="ti ti-clock text-success me-1"></i><?= date('g:i A', strtotime($log['time_in'])) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 text-center">
                            <?php if (!empty($log['time_out'])): ?>
                                <span class="badge bg-light text-dark border px-2 py-1 font-monospace" style="font-size:12px; font-weight:700;">
                                    <i class="ti ti-door-exit text-primary me-1"></i><?= date('g:i A', strtotime($log['time_out'])) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 text-center">
                            <span style="padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; background:<?= $statusBg ?>; color:<?= $statusColor ?>;">
                                <?= e($log['status']) ?>
                            </span>
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-light text-secondary border" style="font-size:10.5px;">
                                <?= e(ucwords(str_replace('_', ' ', $log['scan_method'] ?: 'Manual'))) ?>
                            </span>
                        </td>
                        <td class="pe-4 py-3 text-muted" style="font-size:12px;">
                            <?= e($log['remark'] ?: '—') ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>
