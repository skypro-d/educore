<?php
// views/admin/staff_attendance.php
?>
<div class="sa-top-bar mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;">
            <i class="ti ti-clock-check text-primary me-2"></i> Staff Attendance &amp; Arrival Tracking
        </h1>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">
            Real-time faculty arrival logs, QR scan monitoring, departure tracking, and attendance management
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= url('admin/attendance-scanner') ?>" class="btn btn-outline-primary" style="font-weight:600; font-size:13px; border-radius:8px; display:inline-flex; align-items:center; gap:6px;">
            <i class="ti ti-qrcode" style="font-size:16px;"></i> Open Live Scanner Kiosk
        </a>
        <a href="<?= url('admin/staff-attendance-report') ?>" class="btn btn-primary" style="font-weight:600; font-size:13px; border-radius:8px; display:inline-flex; align-items:center; gap:6px;">
            <i class="ti ti-file-analytics" style="font-size:16px;"></i> Monthly Attendance Report
        </a>
    </div>
</div>

<!-- Date Filter & Department Selector Bar -->
<div class="card border-0 shadow-sm p-3 mb-4" style="border-radius:14px; background:#ffffff;">
    <form method="GET" action="<?= url('admin/staff-attendance') ?>" class="row g-2 align-items-center">
        <div class="col-md-3 col-sm-6">
            <label class="form-label" style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Attendance Date</label>
            <input type="date" name="date" class="form-control form-control-sm" value="<?= e($date) ?>" onchange="this.form.submit()" style="border-radius:8px; font-weight:600;">
        </div>
        <div class="col-md-3 col-sm-6">
            <label class="form-label" style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Department Filter</label>
            <select name="department" class="form-select form-select-sm" onchange="this.form.submit()" style="border-radius:8px; font-weight:600;">
                <option value="">-- All Departments --</option>
                <?php foreach ($departments as $dept): if (!empty($dept)): ?>
                    <option value="<?= e($dept) ?>" <?= ($departmentFilter === $dept) ? 'selected' : '' ?>><?= e($dept) ?></option>
                <?php endif; endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 col-sm-8">
            <label class="form-label" style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;">Quick Search</label>
            <input type="text" id="staffAttendanceSearch" class="form-control form-control-sm" placeholder="Search staff by name, role, ID..." style="border-radius:8px;">
        </div>
        <div class="col-md-2 col-sm-4 text-end d-flex align-items-end" style="padding-top:22px;">
            <a href="<?= url('admin/staff-attendance?date=' . date('Y-m-d')) ?>" class="btn btn-sm btn-light border w-100" style="border-radius:8px; font-weight:600;">
                <i class="ti ti-calendar-event me-1"></i> Today
            </a>
        </div>
    </form>
</div>

<!-- Summary Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3" style="border-radius:12px; background:#fff;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Total Staff</div>
                    <div style="font-size:1.6rem; font-weight:700; color:#1e293b;"><?= $stats['total_staff'] ?></div>
                </div>
                <div style="width:42px;height:42px;border-radius:10px;background:#e0f2fe;color:#0284c7;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="ti ti-users"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3" style="border-radius:12px; background:#fff;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Arrived / On Duty</div>
                    <div style="font-size:1.6rem; font-weight:700; color:#15803d;"><?= $stats['present_count'] ?></div>
                </div>
                <div style="width:42px;height:42px;border-radius:10px;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="ti ti-user-check"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3" style="border-radius:12px; background:#fff;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Late Arrivals</div>
                    <div style="font-size:1.6rem; font-weight:700; color:#b45309;"><?= $stats['late_count'] ?></div>
                </div>
                <div style="width:42px;height:42px;border-radius:10px;background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="ti ti-clock-exclamation"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3" style="border-radius:12px; background:#fff;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Not Checked In / Absent</div>
                    <div style="font-size:1.6rem; font-weight:700; color:#dc2626;"><?= $stats['absent_count'] ?></div>
                </div>
                <div style="width:42px;height:42px;border-radius:10px;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="ti ti-user-x"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Staff Attendance Table -->
<div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden; background:#fff;">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-0">
        <div class="d-flex align-items-center gap-3">
            <h3 class="mb-0" style="font-size:1.15rem; font-weight:700; color:#1e293b;">
                <i class="ti ti-calendar-time text-primary me-2"></i> Attendance Log for <?= date('l, F j, Y', strtotime($date)) ?>
            </h3>
            <?php if ($date === date('Y-m-d')): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size:11px; font-weight:700;">
                    <i class="ti ti-point-filled"></i> Today Live
                </span>
            <?php endif; ?>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="staffAttendanceTable">
            <thead class="table-light text-uppercase" style="font-size:11px; letter-spacing:0.5px; font-weight:700; color:#64748b;">
                <tr>
                    <th class="ps-4 py-3">Staff Member</th>
                    <th class="py-3">Role &amp; Department</th>
                    <th class="py-3 text-center">Arrival Time (Time In)</th>
                    <th class="py-3 text-center">Departure (Time Out)</th>
                    <th class="py-3 text-center">Status</th>
                    <th class="py-3 text-center">Method</th>
                    <th class="pe-4 py-3 text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($staffList)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="ti ti-users-minus" style="font-size:2.5rem; display:block; margin-bottom:8px; color:#cbd5e1;"></i>
                            No staff records found for this criteria.
                        </td>
                    </tr>
                <?php else: foreach ($staffList as $st): ?>
                    <?php
                    $att = $existingMap[$st['id']] ?? null;
                    $status = $att['status'] ?? 'Not Recorded';
                    $timeIn = !empty($att['time_in']) ? date('g:i A', strtotime($att['time_in'])) : '—';
                    $timeOut = !empty($att['time_out']) ? date('g:i A', strtotime($att['time_out'])) : '—';
                    $method = $att['scan_method'] ?? '—';
                    
                    $statusBg = '#f1f5f9';
                    $statusColor = '#475569';
                    if ($status === 'Present') { $statusBg = '#dcfce7'; $statusColor = '#15803d'; }
                    elseif ($status === 'Late') { $statusBg = '#fef3c7'; $statusColor = '#b45309'; }
                    elseif ($status === 'Absent') { $statusBg = '#fee2e2'; $statusColor = '#dc2626'; }
                    elseif ($status === 'On Leave') { $statusBg = '#e0e7ff'; $statusColor = '#4338ca'; }
                    ?>
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <?php if (!empty($st['passport_photo'])): ?>
                                    <img src="<?= url('uploads/' . e($st['passport_photo'])) ?>" alt="Photo" style="width:38px;height:38px;border-radius:10px;object-fit:cover;border:1px solid #e2e8f0;">
                                <?php else: ?>
                                    <div style="width:38px;height:38px;border-radius:10px;background:#f1f5f9;color:#64748b;font-weight:700;display:flex;align-items:center;justify-content:center;font-size:13px;">
                                        <?= strtoupper(substr($st['first_name'], 0, 1) . substr($st['last_name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div style="font-weight:700; color:#1e293b; font-size:13.5px;"><?= e($st['first_name'] . ' ' . $st['last_name']) ?></div>
                                    <div style="font-size:11px; color:#64748b; font-family:monospace;"><?= e($st['staff_id']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-light text-dark border px-2 py-1" style="font-size:11px; font-weight:600;">
                                <?= e(!empty($st['role_title']) ? ucwords(str_replace('_', ' ', $st['role_title'])) : $st['role']) ?>
                            </span>
                            <?php if (!empty($st['department'])): ?>
                                <div style="font-size:11px; color:#64748b; margin-top:2px;"><?= e($st['department']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 text-center">
                            <?php if (!empty($att['time_in'])): ?>
                                <span class="badge bg-light text-dark border px-2 py-1 font-monospace" style="font-size:12px; font-weight:700;">
                                    <i class="ti ti-clock text-success me-1"></i><?= $timeIn ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:12px;">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 text-center">
                            <?php if (!empty($att['time_out'])): ?>
                                <span class="badge bg-light text-dark border px-2 py-1 font-monospace" style="font-size:12px; font-weight:700;">
                                    <i class="ti ti-door-exit text-primary me-1"></i><?= $timeOut ?>
                                </span>
                            <?php elseif (!empty($att['time_in'])): ?>
                                <span class="badge bg-teal-subtle text-teal-emphasis border border-teal-subtle px-2 py-1" style="font-size:11px; font-weight:600;">
                                    On Campus
                                </span>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:12px;">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 text-center">
                            <span style="padding:4px 10px; border-radius:20px; font-size:11.5px; font-weight:700; background:<?= $statusBg ?>; color:<?= $statusColor ?>;">
                                <?= e($status) ?>
                            </span>
                        </td>
                        <td class="py-3 text-center">
                            <?php if ($method === 'qr_usb' || $method === 'kiosk'): ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size:10.5px;">Kiosk QR</span>
                            <?php elseif ($method === 'qr_web'): ?>
                                <span class="badge bg-info-subtle text-info border border-info-subtle" style="font-size:10.5px;">Mobile QR</span>
                            <?php elseif ($method === 'manual'): ?>
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle" style="font-size:10.5px;">Manual</span>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:11px;">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <div class="d-inline-flex gap-1">
                                <button type="button" class="btn btn-sm btn-light border" onclick="openManualMarkModal(<?= (int)$st['id'] ?>, '<?= e($st['first_name'] . ' ' . $st['last_name']) ?>', '<?= e($status) ?>', '<?= e($att['time_in'] ?? '') ?>', '<?= e($att['time_out'] ?? '') ?>', '<?= e($att['remark'] ?? '') ?>')" style="font-size:12px; font-weight:600; border-radius:6px;" title="Manual Entry / Edit">
                                    <i class="ti ti-edit text-primary"></i> Edit
                                </button>
                                <a href="<?= url('admin/staff/' . $st['id'] . '/id-card') ?>" target="_blank" class="btn btn-sm btn-light border" style="font-size:12px; font-weight:600; border-radius:6px;" title="Print Staff ID Card">
                                    <i class="ti ti-id-badge-2 text-secondary"></i> ID Card
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ==================== MANUAL ATTENDANCE MODAL ==================== -->
<div class="modal fade" id="manualMarkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:14px;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title font-weight-bold" style="font-weight:700; color:#1e293b;">
                    <i class="ti ti-clock-edit text-primary me-1"></i> Update Staff Attendance
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= url('admin/staff-attendance/save') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="date" value="<?= e($date) ?>">
                <input type="hidden" name="staff_id" id="modalMarkStaffId" value="0">
                <div class="modal-body p-4">
                    <p class="text-muted mb-3" style="font-size:13px;">
                        Staff Member: <strong id="modalMarkStaffName" class="text-dark"></strong><br>
                        Date: <strong><?= date('M d, Y', strtotime($date)) ?></strong>
                    </p>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label" style="font-size:12px; font-weight:600;">Attendance Status <span class="text-danger">*</span></label>
                            <select name="status" id="modalMarkStatus" class="form-select" required>
                                <option value="Present">Present (On Time)</option>
                                <option value="Late">Late (Arrival after threshold)</option>
                                <option value="Absent">Absent</option>
                                <option value="On Leave">On Leave</option>
                                <option value="Holiday">Holiday</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px; font-weight:600;">Arrival Time (Time In)</label>
                            <input type="time" name="time_in" id="modalMarkTimeIn" class="form-control" value="<?= date('H:i') ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label" style="font-size:12px; font-weight:600;">Departure Time (Time Out)</label>
                            <input type="time" name="time_out" id="modalMarkTimeOut" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label" style="font-size:12px; font-weight:600;">Remark / Reason</label>
                            <input type="text" name="remark" id="modalMarkRemark" class="form-control" placeholder="e.g. Official Duty, Sick Leave, Permission granted">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="ti ti-device-floppy me-1"></i> Save Attendance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openManualMarkModal(staffId, staffName, status, timeIn, timeOut, remark) {
    document.getElementById('modalMarkStaffId').value = staffId;
    document.getElementById('modalMarkStaffName').innerText = staffName;
    document.getElementById('modalMarkStatus').value = (status === 'Not Recorded') ? 'Present' : status;
    document.getElementById('modalMarkTimeIn').value = timeIn || '<?= date('H:i') ?>';
    document.getElementById('modalMarkTimeOut').value = timeOut || '';
    document.getElementById('modalMarkRemark').value = remark || '';
    new bootstrap.Modal(document.getElementById('manualMarkModal')).show();
}

// Live search filter
document.getElementById('staffAttendanceSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    const rows = document.querySelectorAll('#staffAttendanceTable tbody tr');
    rows.forEach(r => {
        const text = r.innerText.toLowerCase();
        r.style.display = text.includes(q) ? '' : 'none';
    });
});
</script>
