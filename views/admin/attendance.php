<?php $currentClass = $classId; $terms = ['First','Second','Third']; ?>
<div class="sa-top-bar">
    <div><h1>Attendance Management</h1><p>Mark daily class attendance and send alerts to parents</p></div>
    <div class="sa-top-actions">
        <a class="sa-btn" href="<?= url('admin/attendance-report') ?>"><i class="ti ti-file-analytics"></i> Monthly Report</a>
    </div>
</div>

<div class="sa-card" style="margin-bottom:20px;">
    <div class="sa-card-title"><i class="ti ti-filter"></i> Select Class & Date</div>
    <form method="GET" action="<?= url('admin/attendance') ?>" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="route" value="attendance">
        <div>
            <label style="font-size:12px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">CLASS</label>
            <select name="class_id" class="form-select form-select-sm" style="min-width:160px;">
                <option value="">Select class…</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (int)$c['id'] === $currentClass ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label style="font-size:12px;font-weight:600;color:#6b7280;display:block;margin-bottom:4px;">DATE</label>
            <input type="date" name="date" value="<?= e($date) ?>" class="form-control form-control-sm" style="width:160px;">
        </div>
        <button type="submit" class="sa-btn sa-btn-primary" style="height:32px;">Load Students</button>
    </form>
</div>

<?php if ($students): ?>
<form method="POST" action="<?= url('admin/attendance') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="class_id" value="<?= $currentClass ?>">
    <input type="hidden" name="date" value="<?= e($date) ?>">

    <div class="sa-card">
        <div class="sa-card-title" style="justify-content:space-between;">
            <span><i class="ti ti-clipboard-list"></i> Attendance — <?= date('D, M j Y', strtotime($date)) ?></span>
            <div style="display:flex;gap:8px;">
                <button type="button" onclick="markAll('Present')" class="sa-btn" style="font-size:11px;padding:4px 10px;background:#f0fdf4;color:#16a34a;">✓ All Present</button>
                <button type="button" onclick="markAll('Absent')" class="sa-btn" style="font-size:11px;padding:4px 10px;background:#fee2e2;color:#dc2626;">✗ All Absent</button>
            </div>
        </div>
        <div class="table-responsive">
        <table class="app-table">
            <thead>
                <tr>
                    <th style="width:50px;">#</th>
                    <th>Student</th>
                    <th style="width:100px;">Reg No.</th>
                    <th style="width:320px;">Status</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $i => $s):
                    $existingStatus = $existing[$s['id']]['status'] ?? 'Present';
                    $existingRemark = $existing[$s['id']]['remark'] ?? '';
                    $initials = strtoupper(substr($s['first_name'],0,1).substr($s['last_name'],0,1));
                    $avColors = ['blue','teal','purple','coral','pink','amber'];
                ?>
                <tr>
                    <td style="color:#9ca3af;font-size:12px;"><?= $i+1 ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <?php if ($s['passport_photo']): ?>
                                <img src="<?= url('uploads/'.$s['passport_photo']) ?>" style="width:30px;height:30px;border-radius:50%;object-fit:cover;">
                            <?php else: ?>
                                <div class="avatar av-<?= $avColors[$i%6] ?>" style="width:30px;height:30px;font-size:11px;"><?= $initials ?></div>
                            <?php endif; ?>
                            <?= e($s['first_name'] . ' ' . $s['last_name']) ?>
                        </div>
                    </td>
                    <td style="color:#888;font-size:12px;"><?= e($s['application_number']) ?></td>
                    <td>
                        <div style="display:flex;gap:6px;" class="att-buttons" data-id="<?= $s['id'] ?>">
                            <?php foreach (['Present','Absent','Late','Excused'] as $statusOpt):
                                $btnColors = ['Present'=>['#f0fdf4','#16a34a','#dcfce7'],'Absent'=>['#fee2e2','#dc2626','#fca5a5'],'Late'=>['#fef9ec','#d97706','#fde68a'],'Excused'=>['#f5f0ff','#7c3aed','#ddd6fe']];
                                [$nbg,$nc,$sc] = $btnColors[$statusOpt];
                                $active = $existingStatus === $statusOpt;
                            ?>
                            <label style="cursor:pointer;">
                                <input type="radio" name="status[<?= $s['id'] ?>]" value="<?= $statusOpt ?>" <?= $active ? 'checked' : '' ?> style="display:none;" class="att-radio">
                                <span class="att-btn" style="display:inline-block;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:600;background:<?= $active ? $sc : '#f3f4f6' ?>;color:<?= $active ? $nc : '#9ca3af' ?>;border:1.5px solid <?= $active ? $nc : 'transparent' ?>;transition:all .15s;cursor:pointer;"><?= $statusOpt ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                    </td>
                    <td>
                        <input type="text" name="remark[<?= $s['id'] ?>]" value="<?= e($existingRemark) ?>" placeholder="Optional remark…" class="form-control form-control-sm" style="min-width:180px;font-size:12px;">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <div style="padding:16px 0 0;display:flex;gap:10px;">
            <button type="submit" class="sa-btn sa-btn-primary"><i class="ti ti-device-floppy"></i> Save Attendance</button>
        </div>
    </div>
</form>

<script>
function markAll(status) {
    document.querySelectorAll('.att-radio').forEach(r => {
        if (r.value === status) {
            r.checked = true;
            r.dispatchEvent(new Event('change'));
        }
    });
    updateButtons();
}
document.querySelectorAll('.att-radio').forEach(radio => {
    radio.addEventListener('change', updateButtons);
});
function updateButtons() {
    document.querySelectorAll('.att-buttons').forEach(group => {
        const checked = group.querySelector('input:checked');
        group.querySelectorAll('.att-btn').forEach(btn => {
            const radio = btn.previousElementSibling;
            const colors = {Present:['#dcfce7','#16a34a'],Absent:['#fca5a5','#dc2626'],Late:['#fde68a','#d97706'],Excused:['#ddd6fe','#7c3aed']};
            const isActive = radio === checked;
            const [bg,col] = colors[radio.value] || ['#f3f4f6','#9ca3af'];
            btn.style.background = isActive ? bg : '#f3f4f6';
            btn.style.color = isActive ? col : '#9ca3af';
            btn.style.border = `1.5px solid ${isActive ? col : 'transparent'}`;
        });
    });
}
</script>
<?php elseif ($currentClass): ?>
<div class="sa-card" style="text-align:center;padding:48px;">
    <i class="ti ti-users" style="font-size:40px;color:#d1d5db;display:block;margin-bottom:12px;"></i>
    <p style="color:#9ca3af;">No enrolled students found in this class.</p>
</div>
<?php else: ?>
<div class="sa-card" style="text-align:center;padding:48px;">
    <i class="ti ti-clipboard-list" style="font-size:40px;color:#d1d5db;display:block;margin-bottom:12px;"></i>
    <p style="color:#9ca3af;">Select a class and date above to mark attendance.</p>
</div>
<?php endif; ?>
