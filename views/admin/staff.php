<?php
// views/admin/staff.php
?>
<div class="sa-top-bar mb-4">
    <div>
        <h1 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0 0 4px 0;">Staff & Teacher Management</h1>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Configure roles, granular permissions, class and subject assignments, and portal credentials</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-primary" onclick="openAddStaffModal()" style="font-weight:600; font-size:13px; border-radius:8px; display:inline-flex; align-items:center; gap:6px;">
            <i class="ti ti-user-plus" style="font-size:16px;"></i> Add Staff Member
        </button>
    </div>
</div>

<!-- Staff Summary Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3" style="border-radius:12px; background:#fff;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Total Staff</div>
                    <div style="font-size:1.6rem; font-weight:700; color:#1e293b;"><?= count($staff) ?></div>
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
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Active Accounts</div>
                    <div style="font-size:1.6rem; font-weight:700; color:#15803d;">
                        <?= count(array_filter($staff, fn($s) => $s['status'] === 'Active')) ?>
                    </div>
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
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Teaching Faculty</div>
                    <div style="font-size:1.6rem; font-weight:700; color:#0f766e;">
                        <?= count(array_filter($staff, fn($s) => (int)$s['class_count'] > 0 || stripos($s['role'], 'teacher') !== false)) ?>
                    </div>
                </div>
                <div style="width:42px;height:42px;border-radius:10px;background:#ccfbf1;color:#0f766e;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="ti ti-school"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3" style="border-radius:12px; background:#fff;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">On Leave / Inactive</div>
                    <div style="font-size:1.6rem; font-weight:700; color:#b45309;">
                        <?= count(array_filter($staff, fn($s) => $s['status'] !== 'Active')) ?>
                    </div>
                </div>
                <div style="width:42px;height:42px;border-radius:10px;background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;font-size:20px;">
                    <i class="ti ti-user-exclamation"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Staff Directory Card -->
<div class="card border-0 shadow-sm" style="border-radius:14px; overflow:hidden;">
    <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-0">
        <div class="d-flex align-items-center gap-3">
            <h3 class="mb-0" style="font-size:1.15rem; font-weight:700; color:#1e293b;">
                <i class="ti ti-id-badge-2" style="color:var(--brand-primary); margin-right:6px;"></i> Staff Directory
            </h3>
            <span class="badge bg-light text-secondary border py-1 px-2 font-monospace" style="font-size:11px;">Session: <?= e($academicYear) ?></span>
        </div>
        <div class="d-flex gap-2">
            <input type="text" id="staffSearchInput" class="form-control form-control-sm" placeholder="Search staff name, ID, role..." style="width:240px; border-radius:6px;">
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="staffDataTable">
            <thead class="table-light text-uppercase" style="font-size:11px; letter-spacing:0.5px; font-weight:700; color:#64748b;">
                <tr>
                    <th class="ps-4 py-3">Staff Member</th>
                    <th class="py-3">Role & Dept</th>
                    <th class="py-3">Contact</th>
                    <th class="py-3 text-center">Class / Subject Assignments</th>
                    <th class="py-3 text-center">Status</th>
                    <th class="py-3">Portal Login</th>
                    <th class="pe-4 py-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($staff)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="ti ti-users-minus" style="font-size:2.5rem; display:block; margin-bottom:8px; color:#cbd5e1;"></i>
                            No staff members registered yet. Click "Add Staff Member" to register faculty.
                        </td>
                    </tr>
                <?php else: foreach ($staff as $s): ?>
                    <tr>
                        <td class="ps-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <?php if (!empty($s['passport_photo'])): ?>
                                    <img src="<?= url('uploads/' . e($s['passport_photo'])) ?>" alt="Photo" style="width:40px;height:40px;border-radius:10px;object-fit:cover;border:1px solid #e2e8f0;">
                                <?php else: ?>
                                    <div style="width:40px;height:40px;border-radius:10px;background:#f1f5f9;color:#64748b;font-weight:700;display:flex;align-items:center;justify-content:center;font-size:14px;">
                                        <?= strtoupper(substr($s['first_name'], 0, 1) . substr($s['last_name'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <div style="font-weight:700; color:#1e293b; font-size:13.5px;"><?= e($s['first_name'] . ' ' . $s['last_name']) ?></div>
                                    <div style="font-size:11px; color:#64748b; font-family:monospace;"><?= e($s['staff_id']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="py-3">
                            <span class="badge bg-light text-dark border px-2 py-1" style="font-size:11.5px; font-weight:600;">
                                <?= e($s['role_title'] ? ucwords(str_replace('_', ' ', $s['role_title'])) : $s['role']) ?>
                            </span>
                            <?php if (!empty($s['department'])): ?>
                                <div style="font-size:11px; color:#64748b; margin-top:2px;"><?= e($s['department']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="py-3">
                            <div style="font-size:12.5px; color:#334155;"><i class="ti ti-phone" style="font-size:12px;color:#94a3b8;margin-right:3px;"></i><?= e($s['phone']) ?></div>
                            <?php if (!empty($s['email'])): ?>
                                <div style="font-size:11.5px; color:#64748b;"><i class="ti ti-mail" style="font-size:12px;color:#94a3b8;margin-right:3px;"></i><?= e($s['email']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <span class="badge bg-teal-subtle text-teal-emphasis border border-teal-subtle" style="background:#e6fffa; color:#0d9488; padding:5px 8px; font-size:11.5px;">
                                    <?= (int)$s['class_count'] ?> Classes
                                </span>
                                <span class="badge bg-indigo-subtle text-indigo-emphasis border border-indigo-subtle" style="background:#eef2ff; color:#4f46e5; padding:5px 8px; font-size:11.5px;">
                                    <?= (int)$s['subject_count'] ?> Subjects
                                </span>
                            </div>
                        </td>
                        <td class="py-3 text-center">
                            <?php
                            $stBg = ['Active'=>'#dcfce7','On Leave'=>'#fef9c3','Resigned'=>'#f1f5f9','Terminated'=>'#fee2e2'];
                            $stCol = ['Active'=>'#15803d','On Leave'=>'#a16207','Resigned'=>'#475569','Terminated'=>'#b91c1c'];
                            ?>
                            <span style="padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;background:<?= $stBg[$s['status']] ?? '#f3f4f6' ?>;color:<?= $stCol[$s['status']] ?? '#374151' ?>;">
                                <?= e($s['status']) ?>
                            </span>
                        </td>
                        <td class="py-3">
                            <?php if (!empty($s['username'])): ?>
                                <div style="font-size:12px; font-family:monospace; font-weight:600; color:#1e293b;"><?= e($s['username']) ?></div>
                                <div style="font-size:10.5px; color:#94a3b8;">
                                    <?= !empty($s['last_login']) ? 'Last: ' . date('M d, H:i', strtotime($s['last_login'])) : 'Never logged in' ?>
                                </div>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:12px;">No account</span>
                            <?php endif; ?>
                        </td>
                        <td class="pe-4 py-3 text-end">
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius:6px; font-size:12px; font-weight:600;">
                                    Manage
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="font-size:13px; border-radius:8px;">
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" onclick='openEditStaffModal(<?= json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                                            <i class="ti ti-edit text-primary me-2"></i> Edit Profile
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="openAssignmentsModal(<?= $s['id'] ?>, '<?= e($s['first_name'] . ' ' . $s['last_name']) ?>')">
                                            <i class="ti ti-layout-grid-add text-success me-2"></i> Assign Classes & Subjects
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="openPermissionsModal(<?= $s['id'] ?>, '<?= e($s['first_name'] . ' ' . $s['last_name']) ?>')">
                                            <i class="ti ti-shield-lock text-warning me-2"></i> Custom Permissions
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="openStudentsModal(<?= $s['id'] ?>, '<?= e($s['first_name'] . ' ' . $s['last_name']) ?>')">
                                            <i class="ti ti-users text-info me-2"></i> View Assigned Students
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0)" onclick="openActivityModal(<?= $s['id'] ?>, '<?= e($s['first_name'] . ' ' . $s['last_name']) ?>')">
                                            <i class="ti ti-history text-secondary me-2"></i> View Audit Activity
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="<?= url('admin/staff/' . $s['id'] . '/reset-password') ?>" onsubmit="return confirm('Reset password for <?= e($s['first_name'] . ' ' . $s['last_name']) ?>? A temporary password will be issued.');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="dropdown-item text-secondary">
                                                <i class="ti ti-key me-2"></i> Reset Password
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form method="POST" action="<?= url('admin/staff/' . $s['id'] . '/toggle-status') ?>">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="dropdown-item text-secondary">
                                                <i class="ti ti-power me-2"></i> Toggle Active / Inactive
                                            </button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="<?= url('admin/staff/' . $s['id'] . '/delete') ?>" onsubmit="return confirm('Permanently delete <?= e($s['first_name'] . ' ' . $s['last_name']) ?> and all associated assignments?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="ti ti-trash me-2"></i> Delete Staff
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ==================== MODAL 1: ADD / EDIT STAFF ==================== -->
<div class="modal fade" id="staffFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:14px;">
            <div class="modal-header border-0 pb-0 px-4 pt-4">
                <h5 class="modal-title font-weight-bold" id="staffModalTitle" style="font-weight:700; color:#1e293b;">
                    <i class="ti ti-user-plus text-primary me-1"></i> Add Staff Member
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= url('admin/staff') ?>" id="staffModalForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="modalStaffId" value="0">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:12px; font-weight:600;">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" id="modalFirstName" required class="form-control" placeholder="e.g. John">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:12px; font-weight:600;">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" id="modalLastName" required class="form-control" placeholder="e.g. Doe">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:12px; font-weight:600;">Role Designation <span class="text-danger">*</span></label>
                            <select name="role_id" id="modalRoleId" class="form-select" required onchange="handleRoleSelectChange(this)">
                                <?php foreach ($roles as $r): ?>
                                    <option value="<?= $r['id'] ?>" data-name="<?= e($r['name']) ?>"><?= ucwords(str_replace('_', ' ', $r['name'])) ?> — <?= e($r['description'] ?? '') ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="role" id="modalRoleStr" value="Teacher">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:12px; font-weight:600;">Department</label>
                            <input type="text" name="department" id="modalDepartment" class="form-control" placeholder="e.g. Science / Mathematics / Administration">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:12px; font-weight:600;">Contact Phone <span class="text-danger">*</span></label>
                            <input type="text" name="phone" id="modalPhone" required class="form-control" placeholder="e.g. 08012345678">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:12px; font-weight:600;">Email Address</label>
                            <input type="email" name="email" id="modalEmail" class="form-control" placeholder="e.g. teacher@school.edu.ng">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-size:12px; font-weight:600;">Qualification</label>
                            <input type="text" name="qualification" id="modalQualification" class="form-control" placeholder="e.g. B.Sc Mathematics, PGDE">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px; font-weight:600;">Monthly Salary (NGN)</label>
                            <input type="number" name="salary" id="modalSalary" min="0" step="500" class="form-control" placeholder="e.g. 150000">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" style="font-size:12px; font-weight:600;">Status</label>
                            <select name="status" id="modalStatus" class="form-select">
                                <option value="Active">Active</option>
                                <option value="On Leave">On Leave</option>
                                <option value="Resigned">Resigned</option>
                                <option value="Terminated">Terminated</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="ti ti-device-floppy me-1"></i> Save Staff Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL 2: ASSIGN CLASSES & SUBJECTS ==================== -->
<div class="modal fade" id="assignmentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:14px;">
            <div class="modal-header border-0 px-4 pt-4 pb-2">
                <div>
                    <h5 class="modal-title font-weight-bold" style="font-weight:700; color:#1e293b;">
                        <i class="ti ti-layout-grid-add text-success me-1"></i> Class & Subject Assignments
                    </h5>
                    <p class="text-muted mb-0" style="font-size:12px;">Staff Member: <strong id="assignStaffName"></strong></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Add New Assignment Form -->
                <div class="card p-3 mb-4 bg-light border-0" style="border-radius:10px;">
                    <div style="font-size:12px; font-weight:700; color:#334155; margin-bottom:10px;">Assign to a New Class / Subject</div>
                    <form method="POST" id="addAssignmentForm" onsubmit="submitNewAssignment(event)">
                        <?= csrf_field() ?>
                        <input type="hidden" name="assignment_action" value="add">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label" style="font-size:11px; font-weight:600;">Select Class <span class="text-danger">*</span></label>
                                <select name="class_id" id="assignClassSelect" class="form-select form-select-sm" required>
                                    <option value="">-- Choose Class --</option>
                                    <?php foreach ($classes as $c): ?>
                                        <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" style="font-size:11px; font-weight:600;">Subject (Leave empty if Class Teacher only)</label>
                                <select name="subject_id" id="assignSubjectSelect" class="form-select form-select-sm">
                                    <option value="">-- None / Class Teacher Only --</option>
                                    <?php foreach ($subjects as $sb): ?>
                                        <option value="<?= $sb['id'] ?>"><?= e($sb['name']) ?> (<?= e($sb['code'] ?? 'N/A') ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="is_form_teacher" id="assignFormTeacherCheck" value="1">
                                    <label class="form-check-label" for="assignFormTeacherCheck" style="font-size:11.5px; font-weight:600;">
                                        Form Teacher
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-2 text-end">
                                <button type="submit" class="btn btn-sm btn-success w-100" style="font-weight:600;">
                                    <i class="ti ti-plus"></i> Assign
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Current Assignments List -->
                <div style="font-size:12.5px; font-weight:700; color:#1e293b; margin-bottom:10px;">Current Academic Year Assignments</div>
                <div class="table-responsive" style="max-height: 280px; overflow-y:auto;">
                    <table class="table table-sm table-hover align-middle mb-0" id="currentAssignmentsTable">
                        <thead class="table-light text-uppercase" style="font-size:10px; font-weight:700;">
                            <tr>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Role</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody id="currentAssignmentsList">
                            <tr><td colspan="4" class="text-center py-4 text-muted">Loading assignments...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODAL 3: GRANULAR PERMISSIONS ==================== -->
<div class="modal fade" id="permissionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:14px;">
            <div class="modal-header border-0 px-4 pt-4 pb-2">
                <div>
                    <h5 class="modal-title font-weight-bold" style="font-weight:700; color:#1e293b;">
                        <i class="ti ti-shield-lock text-warning me-1"></i> Custom Permission Overrides
                    </h5>
                    <p class="text-muted mb-0" style="font-size:12px;">Staff Member: <strong id="permStaffName"></strong></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="permissionsForm">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 border-0 d-flex align-items-center gap-2 mb-3" style="font-size:12px; border-radius:8px;">
                        <i class="ti ti-info-circle fs-5"></i>
                        <span>Role default permissions are automatically granted. Check or uncheck boxes below to grant additional rights or revoke defaults.</span>
                    </div>

                    <div class="row g-3" id="permissionsListGrid" style="max-height: 380px; overflow-y:auto;">
                        <!-- Injected via JavaScript -->
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm px-4">Save Permissions</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL 4: ASSIGNED STUDENTS ==================== -->
<div class="modal fade" id="studentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:14px;">
            <div class="modal-header border-0 px-4 pt-4 pb-2">
                <div>
                    <h5 class="modal-title font-weight-bold" style="font-weight:700; color:#1e293b;">
                        <i class="ti ti-users text-info me-1"></i> Authorized Students
                    </h5>
                    <p class="text-muted mb-0" style="font-size:12px;">Students accessible by <strong id="studentsStaffName"></strong> based on class assignments</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive" style="max-height: 350px; overflow-y:auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase" style="font-size:10.5px; font-weight:700;">
                            <tr>
                                <th>Student</th>
                                <th>Admission No</th>
                                <th>Class</th>
                                <th>Gender</th>
                            </tr>
                        </thead>
                        <tbody id="studentsListTable">
                            <tr><td colspan="4" class="text-center py-4 text-muted">Loading students...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODAL 5: AUDIT ACTIVITY LOGS ==================== -->
<div class="modal fade" id="activityModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius:14px;">
            <div class="modal-header border-0 px-4 pt-4 pb-2">
                <div>
                    <h5 class="modal-title font-weight-bold" style="font-weight:700; color:#1e293b;">
                        <i class="ti ti-history text-secondary me-1"></i> Staff Activity Audit Logs
                    </h5>
                    <p class="text-muted mb-0" style="font-size:12px;">Audit history for <strong id="activityStaffName"></strong></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive" style="max-height: 350px; overflow-y:auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase" style="font-size:10.5px; font-weight:700;">
                            <tr>
                                <th>Timestamp</th>
                                <th>Action</th>
                                <th>Resource</th>
                                <th>Details</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody id="activityListTable">
                            <tr><td colspan="5" class="text-center py-4 text-muted">Loading audit history...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentAssignStaffId = 0;

function handleRoleSelectChange(select) {
    const selectedOption = select.options[select.selectedIndex];
    const roleName = selectedOption.getAttribute('data-name') || 'Teacher';
    document.getElementById('modalRoleStr').value = roleName;
}

function openAddStaffModal() {
    document.getElementById('staffModalTitle').innerHTML = '<i class="ti ti-user-plus text-primary me-1"></i> Add Staff Member';
    document.getElementById('modalStaffId').value = '0';
    document.getElementById('modalFirstName').value = '';
    document.getElementById('modalLastName').value = '';
    document.getElementById('modalEmail').value = '';
    document.getElementById('modalPhone').value = '';
    document.getElementById('modalDepartment').value = '';
    document.getElementById('modalQualification').value = '';
    document.getElementById('modalSalary').value = '';
    document.getElementById('modalStatus').value = 'Active';
    new bootstrap.Modal(document.getElementById('staffFormModal')).show();
}

function openEditStaffModal(staff) {
    document.getElementById('staffModalTitle').innerHTML = '<i class="ti ti-edit text-primary me-1"></i> Edit Staff Member';
    document.getElementById('modalStaffId').value = staff.id;
    document.getElementById('modalFirstName').value = staff.first_name || '';
    document.getElementById('modalLastName').value = staff.last_name || '';
    document.getElementById('modalEmail').value = staff.email || '';
    document.getElementById('modalPhone').value = staff.phone || '';
    document.getElementById('modalDepartment').value = staff.department || '';
    document.getElementById('modalQualification').value = staff.qualification || '';
    document.getElementById('modalSalary').value = staff.salary || '';
    document.getElementById('modalStatus').value = staff.status || 'Active';
    
    if (staff.role_id) {
        document.getElementById('modalRoleId').value = staff.role_id;
    }
    document.getElementById('modalRoleStr').value = staff.role || 'Teacher';

    new bootstrap.Modal(document.getElementById('staffFormModal')).show();
}

function openAssignmentsModal(staffId, staffName) {
    currentAssignStaffId = staffId;
    document.getElementById('assignStaffName').innerText = staffName;
    document.getElementById('currentAssignmentsList').innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Loading assignments...</td></tr>';
    
    const modal = new bootstrap.Modal(document.getElementById('assignmentsModal'));
    modal.show();

    fetch('<?= url("admin/staff/") ?>' + staffId + '/assignments')
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                document.getElementById('currentAssignmentsList').innerHTML = '<tr><td colspan="4" class="text-danger text-center">Failed to load assignments.</td></tr>';
                return;
            }
            renderAssignmentsTable(data.assignments);
        })
        .catch(err => {
            document.getElementById('currentAssignmentsList').innerHTML = '<tr><td colspan="4" class="text-danger text-center">Error loading assignments.</td></tr>';
        });
}

function renderAssignmentsTable(assignments) {
    const tbody = document.getElementById('currentAssignmentsList');
    if (!assignments || assignments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No classes or subjects assigned yet for this year.</td></tr>';
        return;
    }

    tbody.innerHTML = assignments.map(a => `
        <tr>
            <td class="font-weight-bold" style="font-weight:600;">${escapeHtml(a.class_name)}</td>
            <td>${a.subject_name ? escapeHtml(a.subject_name) + ' (' + escapeHtml(a.subject_code || '') + ')' : '<em class="text-muted">None (Form Teacher Only)</em>'}</td>
            <td>${a.is_form_teacher ? '<span class="badge bg-warning text-dark">Form Teacher</span>' : '<span class="badge bg-light text-secondary border">Subject Teacher</span>'}</td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:11px;" onclick="removeAssignment(${a.id})">
                    <i class="ti ti-x"></i> Remove
                </button>
            </td>
        </tr>
    `).join('');
}

function submitNewAssignment(e) {
    e.preventDefault();
    const form = document.getElementById('addAssignmentForm');
    const formData = new FormData(form);

    fetch('<?= url("admin/staff/") ?>' + currentAssignStaffId + '/assignments', {
        method: 'POST',
        body: formData,
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            // Reload assignments
            fetch('<?= url("admin/staff/") ?>' + currentAssignStaffId + '/assignments')
                .then(r => r.json())
                .then(data => renderAssignmentsTable(data.assignments));
            form.reset();
        } else {
            alert(res.message || 'Failed to add assignment');
        }
    })
    .catch(err => alert('Network error while saving assignment'));
}

function removeAssignment(assignId) {
    if (!confirm('Remove this class/subject assignment?')) return;

    const fd = new FormData();
    fd.append('assignment_action', 'remove');
    fd.append('assignment_id', assignId);
    fd.append('csrf_token', '<?= csrf_token() ?>');

    fetch('<?= url("admin/staff/") ?>' + currentAssignStaffId + '/assignments', {
        method: 'POST',
        body: fd,
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            fetch('<?= url("admin/staff/") ?>' + currentAssignStaffId + '/assignments')
                .then(r => r.json())
                .then(data => renderAssignmentsTable(data.assignments));
        } else {
            alert(res.message || 'Failed to remove assignment');
        }
    });
}

function openPermissionsModal(staffId, staffName) {
    document.getElementById('permStaffName').innerText = staffName;
    document.getElementById('permissionsForm').action = '<?= url("admin/staff/") ?>' + staffId + '/permissions';
    document.getElementById('permissionsListGrid').innerHTML = '<div class="col-12 text-center py-4 text-muted">Loading permissions...</div>';

    new bootstrap.Modal(document.getElementById('permissionsModal')).show();

    fetch('<?= url("admin/staff/") ?>' + staffId + '/permissions')
        .then(r => r.json())
        .then(data => {
            if (!data.success) {
                document.getElementById('permissionsListGrid').innerHTML = '<div class="col-12 text-danger text-center">Failed to load permissions.</div>';
                return;
            }

            const rolePermIds = new Set(data.role_permissions.map(Number));
            const overrides = data.staff_overrides || {};

            let html = '';
            data.all_permissions.forEach(p => {
                const pid = Number(p.id);
                const isRoleDefault = rolePermIds.has(pid);
                const hasOverride = overrides.hasOwnProperty(pid);
                const isChecked = hasOverride ? (Number(overrides[pid]) === 1) : isRoleDefault;

                html += `
                    <div class="col-md-6">
                        <div class="p-2 border rounded d-flex align-items-start gap-2 ${isChecked ? 'bg-light' : ''}">
                            <input class="form-check-input mt-1" type="checkbox" name="permissions[${pid}]" value="1" id="perm_${pid}" ${isChecked ? 'checked' : ''}>
                            <div style="flex:1;">
                                <label class="form-check-label d-block" for="perm_${pid}" style="font-size:12px; font-weight:700; color:#1e293b; cursor:pointer;">
                                    ${escapeHtml(p.name)}
                                    ${isRoleDefault ? '<span class="badge bg-secondary-subtle text-secondary py-0 px-1 border" style="font-size:9.5px;">Role Default</span>' : ''}
                                </label>
                                <small class="text-muted" style="font-size:11px; display:block;">${escapeHtml(p.description || '')}</small>
                            </div>
                        </div>
                    </div>
                `;
            });

            document.getElementById('permissionsListGrid').innerHTML = html;
        });
}

function openStudentsModal(staffId, staffName) {
    document.getElementById('studentsStaffName').innerText = staffName;
    document.getElementById('studentsListTable').innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Loading students...</td></tr>';
    new bootstrap.Modal(document.getElementById('studentsModal')).show();

    fetch('<?= url("admin/staff/") ?>' + staffId + '/students')
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.students || data.students.length === 0) {
                document.getElementById('studentsListTable').innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">No students assigned to this staff member.</td></tr>';
                return;
            }

            document.getElementById('studentsListTable').innerHTML = data.students.map(st => `
                <tr>
                    <td class="font-weight-bold" style="font-weight:600;">${escapeHtml(st.first_name + ' ' + st.last_name)}</td>
                    <td class="font-monospace" style="font-size:11.5px;">${escapeHtml(st.admission_number || 'N/A')}</td>
                    <td><span class="badge bg-light text-dark border">${escapeHtml(st.class_name)}</span></td>
                    <td>${escapeHtml(st.gender)}</td>
                </tr>
            `).join('');
        });
}

function openActivityModal(staffId, staffName) {
    document.getElementById('activityStaffName').innerText = staffName;
    document.getElementById('activityListTable').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Loading audit history...</td></tr>';
    new bootstrap.Modal(document.getElementById('activityModal')).show();

    fetch('<?= url("admin/staff/") ?>' + staffId + '/activity')
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.logs || data.logs.length === 0) {
                document.getElementById('activityListTable').innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">No activity records logged yet.</td></tr>';
                return;
            }

            document.getElementById('activityListTable').innerHTML = data.logs.map(lg => `
                <tr>
                    <td style="font-size:11px; color:#64748b;">${escapeHtml(lg.created_at)}</td>
                    <td><span class="badge bg-primary-subtle text-primary border" style="font-size:10px;">${escapeHtml(lg.action)}</span></td>
                    <td style="font-size:11.5px;">${escapeHtml(lg.resource_type)} #${escapeHtml(lg.resource_id || '')}</td>
                    <td style="font-size:12px;">${escapeHtml(lg.details || '—')}</td>
                    <td class="font-monospace" style="font-size:10px; color:#94a3b8;">${escapeHtml(lg.ip_address || '127.0.0.1')}</td>
                </tr>
            `).join('');
        });
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Live table filter
document.getElementById('staffSearchInput').addEventListener('input', function() {
    const term = this.value.toLowerCase();
    const rows = document.querySelectorAll('#staffDataTable tbody tr');
    rows.forEach(r => {
        const text = r.innerText.toLowerCase();
        r.style.display = text.includes(term) ? '' : 'none';
    });
});
</script>
