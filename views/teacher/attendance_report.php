<?php
// views/teacher/attendance_report.php
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('teacher/attendance') ?>" class="text-decoration-none text-muted" style="font-size:12px; font-weight:600;">
            ← Back to Attendance Register
        </a>
        <h1 class="mt-1" style="font-size:1.4rem; font-weight:700; color:#0f172a; margin:0 0 4px 0;">Attendance Monthly Reports</h1>
        <p style="color:#64748b; font-size:0.875rem; margin:0;">Monthly summary of presence, late arrivals, and absences for your classes</p>
    </div>
</div>

<div class="card border-0 shadow-sm p-3 mb-4" style="border-radius:14px; background:#fff;">
    <form method="GET" action="<?= url('teacher/attendance/report') ?>" class="row g-2 align-items-center">
        <div class="col-md-5">
            <label class="form-label" style="font-size:11px; font-weight:600; color:#64748b;">Class</label>
            <select name="class_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (int)$c['id'] === $classId ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-5">
            <label class="form-label" style="font-size:11px; font-weight:600; color:#64748b;">Month</label>
            <input type="month" name="month" class="form-control form-control-sm" value="<?= e($month) ?>" onchange="this.form.submit()">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-sm btn-primary w-100" style="font-weight:600;">Filter Report</button>
        </div>
    </form>
</div>

<div class="card border-0 shadow-sm p-4" style="border-radius:14px; background:#fff;">
    <h4 class="h6 fw-bold mb-3" style="color:#0f172a;">Daily Attendance Breakdown (<?= date('F Y', strtotime($month . '-01')) ?>)</h4>
    <?php if (empty($records)): ?>
        <p class="text-muted text-center py-4 mb-0">No attendance records found for this class and month.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase" style="font-size:11px; font-weight:700; color:#64748b;">
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Student Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $rec): ?>
                        <tr>
                            <td class="fw-bold" style="font-size:13px; color:#0f172a;"><?= date('D, M d, Y', strtotime($rec['date'])) ?></td>
                            <td>
                                <?php
                                $bgs = ['Present' => '#dcfce7', 'Late' => '#fef3c7', 'Absent' => '#fee2e2', 'Excused' => '#e0f2fe'];
                                $cols = ['Present' => '#15803d', 'Late' => '#d97706', 'Absent' => '#dc2626', 'Excused' => '#0284c7'];
                                ?>
                                <span style="background:<?= $bgs[$rec['status']] ?? '#f1f5f9' ?>; color:<?= $cols[$rec['status']] ?? '#334155' ?>; padding:3px 8px; border-radius:12px; font-size:11px; font-weight:700;">
                                    <?= e($rec['status']) ?>
                                </span>
                            </td>
                            <td class="font-monospace fw-bold"><?= (int)$rec['count'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
