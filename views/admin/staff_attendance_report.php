<?php
// views/admin/staff_attendance_report.php
?>
<div class="sa-top-bar mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;">
            <i class="ti ti-file-analytics text-primary me-2"></i> Staff Attendance Monthly Report
        </h1>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">
            Comprehensive monthly faculty punctuality rates, present days, late arrivals, and absence analytics
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('admin/staff-attendance') ?>" class="btn btn-outline-secondary" style="font-weight:600; font-size:13px; border-radius:8px; display:inline-flex; align-items:center; gap:6px;">
            <i class="ti ti-arrow-left"></i> Back to Daily Log
        </a>
        <button onclick="window.print()" class="btn btn-primary" style="font-weight:600; font-size:13px; border-radius:8px; display:inline-flex; align-items:center; gap:6px;">
            <i class="ti ti-printer"></i> Print Report
        </button>
    </div>
</div>

<!-- Month & Year Filter Selector -->
<div class="card border-0 shadow-sm p-3 mb-4 no-print" style="border-radius:14px; background:#ffffff;">
    <form method="GET" action="<?= url('admin/staff-attendance-report') ?>" class="row g-2 align-items-center">
        <div class="col-md-4">
            <label class="form-label" style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Select Month</label>
            <select name="month" class="form-select form-select-sm" onchange="this.form.submit()" style="border-radius:8px; font-weight:600;">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= ($selectedMonth === $m) ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Select Year</label>
            <select name="year" class="form-select form-select-sm" onchange="this.form.submit()" style="border-radius:8px; font-weight:600;">
                <?php for ($y = date('Y'); $y >= date('Y') - 4; $y--): ?>
                    <option value="<?= $y ?>" <?= ($selectedYear === $y) ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label" style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Department Filter</label>
            <select name="department" class="form-select form-select-sm" onchange="this.form.submit()" style="border-radius:8px; font-weight:600;">
                <option value="">-- All Departments --</option>
                <?php foreach ($departments as $dept): if (!empty($dept)): ?>
                    <option value="<?= e($dept) ?>" <?= ($departmentFilter === $dept) ? 'selected' : '' ?>><?= e($dept) ?></option>
                <?php endif; endforeach; ?>
            </select>
        </div>
    </form>
</div>

<!-- Main Staff Report Table -->
<div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden; background:#fff;">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-0">
        <h3 class="mb-0" style="font-size:1.15rem; font-weight:700; color:#1e293b;">
            <i class="ti ti-calendar-stats text-primary me-2"></i> Attendance Summary: <?= date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) ?>
        </h3>
        <span class="badge bg-light text-secondary border py-1 px-2 font-monospace" style="font-size:11px;">
            Total Staff Tracked: <?= count($report) ?>
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase" style="font-size:11px; letter-spacing:0.5px; font-weight:700; color:#64748b;">
                <tr>
                    <th class="ps-4 py-3">Staff Member</th>
                    <th class="py-3">Role &amp; Department</th>
                    <th class="py-3 text-center text-success">Present (On Time)</th>
                    <th class="py-3 text-center text-warning">Late Arrivals</th>
                    <th class="py-3 text-center text-danger">Absent</th>
                    <th class="py-3 text-center text-info">On Leave</th>
                    <th class="py-3 text-center">Total Days</th>
                    <th class="pe-4 py-3 text-end">Punctuality %</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($report)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="ti ti-calendar-off" style="font-size:2.5rem; display:block; margin-bottom:8px; color:#cbd5e1;"></i>
                            No attendance records recorded for <?= date('F Y', mktime(0, 0, 0, $selectedMonth, 1, $selectedYear)) ?>.
                        </td>
                    </tr>
                <?php else: foreach ($report as $r): ?>
                    <?php
                    $totalLogged = (int)$r['present_count'] + (int)$r['late_count'] + (int)$r['absent_count'] + (int)$r['leave_count'];
                    $totalAttended = (int)$r['present_count'] + (int)$r['late_count'];
                    $punctualityRate = ($totalAttended > 0) ? round(((int)$r['present_count'] / $totalAttended) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <?php if (!empty($r['passport_photo'])): ?>
                                    <img src="<?= url('uploads/' . e($r['passport_photo'])) ?>" alt="Photo" style="width:36px;height:36px;border-radius:8px;object-fit:cover;border:1px solid #e2e8f0;">
                                <?php else: ?>
                                    <div style="width:36px;height:36px;border-radius:8px;background:#f1f5f9;color:#64748b;font-weight:700;display:flex;align-items:center;justify-content:center;font-size:12px;">
                                        <?= strtoupper(substr($r['first_name'], 0, 1) . substr($r['last_name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div style="font-weight:700; color:#1e293b; font-size:13.5px;"><?= e($r['first_name'] . ' ' . $r['last_name']) ?></div>
                                    <div style="font-size:11px; color:#64748b; font-family:monospace;"><?= e($r['staff_id']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-light text-dark border px-2 py-1" style="font-size:11px; font-weight:600;">
                                <?= e(!empty($r['role_title']) ? ucwords(str_replace('_', ' ', $r['role_title'])) : $r['role']) ?>
                            </span>
                            <?php if (!empty($r['department'])): ?>
                                <div style="font-size:11px; color:#64748b; margin-top:2px;"><?= e($r['department']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 font-monospace" style="font-size:12px; font-weight:700;">
                                <?= (int)$r['present_count'] ?>
                            </span>
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 font-monospace" style="font-size:12px; font-weight:700;">
                                <?= (int)$r['late_count'] ?>
                            </span>
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 font-monospace" style="font-size:12px; font-weight:700;">
                                <?= (int)$r['absent_count'] ?>
                            </span>
                        </td>
                        <td class="py-3 text-center">
                            <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 font-monospace" style="font-size:12px; font-weight:700;">
                                <?= (int)$r['leave_count'] ?>
                            </span>
                        </td>
                        <td class="py-3 text-center font-monospace" style="font-weight:700; color:#1e293b;">
                            <?= $totalAttended ?>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                <div class="progress" style="width: 70px; height: 6px; border-radius: 4px; background:#e2e8f0;">
                                    <div class="progress-bar <?= ($punctualityRate >= 80) ? 'bg-success' : (($punctualityRate >= 50) ? 'bg-warning' : 'bg-danger') ?>" style="width: <?= $punctualityRate ?>%;"></div>
                                </div>
                                <span style="font-size:12px; font-weight:700; color:#1e293b;"><?= $punctualityRate ?>%</span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
@media print {
    body { background: white !important; padding: 0 !important; }
    .no-print, .sidebar, .sa-top-bar a, .sa-top-bar button { display: none !important; }
    .admin-main { margin-left: 0 !important; padding: 0 !important; }
    .card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; }
}
</style>
