<div class="sa-top-bar">
    <div><h1>Attendance Report</h1><p>Monthly attendance summary by class</p></div>
    <div class="sa-top-actions">
        <a class="sa-btn" href="<?= url('admin/attendance') ?>"><i class="ti ti-clipboard-list"></i> Mark Attendance</a>
    </div>
</div>

<div class="sa-card" style="margin-bottom:20px;">
    <div class="sa-card-title"><i class="ti ti-filter"></i> Filter Report</div>
    <form method="GET" action="<?= url('admin/attendance-report') ?>" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="route" value="attendance-report">
        <div>
            <label style="font-size:12px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">CLASS</label>
            <select name="class_id" class="form-select form-select-sm" style="min-width:150px;">
                <option value="">Select class…</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (int)$c['id'] === $classId ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size:12px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">MONTH</label>
            <select name="month" class="form-select form-select-sm">
                <?php foreach ($months as $m => $mName): ?>
                    <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>><?= e($mName) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size:12px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">YEAR</label>
            <input type="number" name="year" value="<?= $year ?>" min="2020" max="<?= date('Y')+1 ?>" class="form-control form-control-sm" style="width:90px;">
        </div>
        <button type="submit" class="sa-btn sa-btn-primary" style="height:32px;">Generate</button>
    </form>
</div>

<?php if ($report): ?>
<div class="sa-card">
    <div class="sa-card-title" style="justify-content:space-between;">
        <span><i class="ti ti-file-analytics"></i> Report — <?= date('F Y', mktime(0,0,0,$month,1,$year)) ?></span>
        <a href="<?= url('admin/attendance-report?route=attendance-report&class_id='.$classId.'&month='.$month.'&year='.$year.'&export=1') ?>" class="sa-btn"><i class="ti ti-download"></i> Export CSV</a>
    </div>
    <div class="table-responsive">
    <table class="app-table" id="attReportTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th style="text-align:center;">Present</th>
                <th style="text-align:center;">Absent</th>
                <th style="text-align:center;">Late</th>
                <th style="text-align:center;">Excused</th>
                <th style="text-align:center;">Total Days</th>
                <th style="text-align:center;">Attendance %</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($report as $i => $row):
                $pct = $row['total_days'] ? round($row['present'] / $row['total_days'] * 100) : 0;
                $pctColor = $pct >= 80 ? '#16a34a' : ($pct >= 60 ? '#d97706' : '#dc2626');
                $pctBg    = $pct >= 80 ? '#f0fdf4' : ($pct >= 60 ? '#fef9ec' : '#fee2e2');
            ?>
            <tr>
                <td style="color:#9ca3af;font-size:12px;"><?= $i+1 ?></td>
                <td><?= e($row['first_name'].' '.$row['last_name']) ?><br><span style="font-size:11px;color:#9ca3af;"><?= e($row['application_number']) ?></span></td>
                <td style="text-align:center;font-weight:700;color:#16a34a;"><?= $row['present'] ?></td>
                <td style="text-align:center;font-weight:700;color:#dc2626;"><?= $row['absent'] ?></td>
                <td style="text-align:center;font-weight:700;color:#d97706;"><?= $row['late'] ?></td>
                <td style="text-align:center;font-weight:700;color:#7c3aed;"><?= $row['excused'] ?></td>
                <td style="text-align:center;color:#6b7280;"><?= $row['total_days'] ?></td>
                <td style="text-align:center;">
                    <span style="padding:3px 10px;border-radius:20px;background:<?= $pctBg ?>;color:<?= $pctColor ?>;font-size:12px;font-weight:700;"><?= $pct ?>%</span>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php elseif ($classId): ?>
    <div class="sa-card" style="text-align:center;padding:48px;"><p style="color:#9ca3af;">No attendance records for the selected period.</p></div>
<?php else: ?>
    <div class="sa-card" style="text-align:center;padding:48px;"><p style="color:#9ca3af;">Select a class and month above to generate the report.</p></div>
<?php endif; ?>
