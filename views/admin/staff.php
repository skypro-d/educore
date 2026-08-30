<div class="sa-top-bar">
    <div>
        <h1>Staff & Teacher Management</h1>
        <p>Register school teachers, administrators, and configure staff roles and profiles</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:400px 1fr;gap:20px;align-items:start;">
    <!-- Add/Edit Staff -->
    <div class="sa-card">
        <div class="sa-card-title" id="formTitle"><i class="ti ti-plus"></i> Add Staff Member</div>
        <form method="POST" action="<?= url('admin/staff') ?>" id="staffForm">
            <?= csrf_field() ?>
            <input type="hidden" name="id" id="staffId" value="0">
            
            <div class="mb-3">
                <label class="form-label" style="font-size:12px;font-weight:600;">First Name <span style="color:#dc2626;">*</span></label>
                <input type="text" name="first_name" id="staffFirstName" required class="form-control form-control-sm" placeholder="e.g. John">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12px;font-weight:600;">Last Name <span style="color:#dc2626;">*</span></label>
                <input type="text" name="last_name" id="staffLastName" required class="form-control form-control-sm" placeholder="e.g. Doe">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12px;font-weight:600;">Role Designation</label>
                <select name="role" id="staffRole" class="form-select form-select-sm" required>
                    <option value="Teacher">Teacher</option>
                    <option value="Form Teacher">Form Teacher</option>
                    <option value="Head of Department">Head of Department</option>
                    <option value="Principal">Principal</option>
                    <option value="Vice Principal">Vice Principal</option>
                    <option value="Bursar">Bursar / Accountant</option>
                    <option value="Librarian">Librarian</option>
                    <option value="Driver">Driver</option>
                    <option value="Security">Security</option>
                    <option value="Other">Other Staff</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12px;font-weight:600;">Contact Phone <span style="color:#dc2626;">*</span></label>
                <input type="text" name="phone" id="staffPhone" required class="form-control form-control-sm" placeholder="e.g. +234...">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12px;font-weight:600;">Email Address</label>
                <input type="email" name="email" id="staffEmail" class="form-control form-control-sm" placeholder="e.g. teacher@school.com">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12px;font-weight:600;">Academic Qualification</label>
                <input type="text" name="qualification" id="staffQualification" class="form-control form-control-sm" placeholder="e.g. B.Ed English / B.Sc Maths">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12px;font-weight:600;">Monthly Salary (NGN)</label>
                <input type="number" name="salary" id="staffSalary" min="0" step="1000" class="form-control form-control-sm" placeholder="e.g. 120000">
            </div>
            <div class="mb-3">
                <label class="form-label" style="font-size:12px;font-weight:600;">Status</label>
                <select name="status" id="staffStatus" class="form-select form-select-sm" required>
                    <option value="Active">Active</option>
                    <option value="On Leave">On Leave</option>
                    <option value="Resigned">Resigned</option>
                    <option value="Terminated">Terminated</option>
                </select>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="button" class="sa-btn" id="btnCancelEdit" style="display:none;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;flex:1;justify-content:center;">Cancel</button>
                <button type="submit" class="sa-btn sa-btn-primary" style="flex:2;justify-content:center;"><i class="ti ti-device-floppy"></i> Save Profile</button>
            </div>
        </form>
    </div>

    <!-- Staff List -->
    <div class="sa-card">
        <div class="sa-card-title"><i class="ti ti-users"></i> Staff Directory</div>
        <div class="table-responsive">
            <table class="app-table" id="staffTable">
                <thead>
                    <tr>
                        <th>Staff ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Salary</th>
                        <th style="width:120px;text-align:center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($staff): foreach ($staff as $s): ?>
                    <tr>
                        <td style="font-weight:600;color:#0b3d91;"><?= e($s['staff_id']) ?></td>
                        <td>
                            <strong style="color:#1e293b;"><?= e($s['first_name'].' '.$s['last_name']) ?></strong>
                            <div style="font-size:11px;color:#64748b;"><?= e($s['email'] ?: 'No email') ?></div>
                        </td>
                        <td><span style="font-size:12px;font-weight:500;"><?= e($s['role']) ?></span></td>
                        <td style="color:#475569;"><?= e($s['phone']) ?></td>
                        <td>
                            <?php
                            $stBg = ['Active'=>'#dcfce7','On Leave'=>'#fef9ec','Resigned'=>'#f1f5f9','Terminated'=>'#fee2e2'];
                            $stCol = ['Active'=>'#15803d','On Leave'=>'#d97706','Resigned'=>'#475569','Terminated'=>'#b91c1c'];
                            ?>
                            <span style="padding:2px 8px;border-radius:12px;font-size:11px;font-weight:700;background:<?= $stBg[$s['status']] ?? '#f3f4f6' ?>;color:<?= $stCol[$s['status']] ?? '#374151' ?>;">
                                <?= e($s['status']) ?>
                            </span>
                        </td>
                        <td style="font-weight:600;color:#0b3d91;">
                            <?= $s['salary'] ? '₦' . number_format((float)$s['salary']) : '—' ?>
                        </td>
                        <td style="text-align:center;display:flex;gap:6px;justify-content:center;">
                            <button class="sa-btn btn-edit-staff" style="font-size:11px;padding:4px 8px;"
                                    data-id="<?= $s['id'] ?>"
                                    data-fname="<?= e($s['first_name']) ?>"
                                    data-lname="<?= e($s['last_name']) ?>"
                                    data-role="<?= e($s['role']) ?>"
                                    data-phone="<?= e($s['phone']) ?>"
                                    data-email="<?= e($s['email'] ?: '') ?>"
                                    data-qualification="<?= e($s['qualification'] ?: '') ?>"
                                    data-salary="<?= e($s['salary'] ?: '') ?>"
                                    data-status="<?= e($s['status']) ?>">
                                <i class="ti ti-edit"></i>
                            </button>
                            <form method="POST" action="<?= url('admin/staff/'.$s['id'].'/delete') ?>" onsubmit="return confirm('Delete this staff profile?');" style="display:inline;">
                                <?= csrf_field() ?>
                                <button class="sa-btn" style="font-size:11px;padding:4px 8px;color:#dc2626;"><i class="ti ti-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="7" style="text-align:center;color:#94a3b8;padding:30px;">No staff registered yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.btn-edit-staff');
    const formTitle = document.getElementById('formTitle');
    const staffId = document.getElementById('staffId');
    const staffFname = document.getElementById('staffFirstName');
    const staffLname = document.getElementById('staffLastName');
    const staffRole = document.getElementById('staffRole');
    const staffPhone = document.getElementById('staffPhone');
    const staffEmail = document.getElementById('staffEmail');
    const staffQual = document.getElementById('staffQualification');
    const staffSalary = document.getElementById('staffSalary');
    const staffStatus = document.getElementById('staffStatus');
    const btnCancel = document.getElementById('btnCancelEdit');

    editButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            formTitle.innerHTML = '<i class="ti ti-edit"></i> Edit Staff Profile';
            staffId.value = this.getAttribute('data-id');
            staffFname.value = this.getAttribute('data-fname');
            staffLname.value = this.getAttribute('data-lname');
            staffRole.value = this.getAttribute('data-role');
            staffPhone.value = this.getAttribute('data-phone');
            staffEmail.value = this.getAttribute('data-email');
            staffQual.value = this.getAttribute('data-qualification');
            staffSalary.value = this.getAttribute('data-salary');
            staffStatus.value = this.getAttribute('data-status');
            btnCancel.style.display = 'block';
        });
    });

    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            document.getElementById('staffForm').reset();
            staffId.value = '0';
            formTitle.innerHTML = '<i class="ti ti-plus"></i> Add Staff Member';
            this.style.display = 'none';
        });
    }
});
</script>
