<?php
// views/teacher/profile.php — Staff Profile Management
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 style="font-size:1.4rem; font-weight:700; color:#0f172a; margin:0 0 4px 0;">Staff Profile</h1>
        <p style="color:#64748b; font-size:0.875rem; margin:0;">Manage your contact details, security credentials, and view active assignments</p>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Profile Card & Update Form -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius:14px; background:#fff;">
            <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                <?php if (!empty($staff['passport_photo'])): ?>
                    <img src="<?= url('uploads/' . e($staff['passport_photo'])) ?>" alt="Photo" style="width:70px;height:70px;border-radius:14px;object-fit:cover;border:2px solid #e2e8f0;">
                <?php else: ?>
                    <div style="width:70px;height:70px;border-radius:14px;background:#f1f5f9;color:#475569;font-weight:700;display:flex;align-items:center;justify-content:center;font-size:24px;">
                        <?= strtoupper(substr($staff['first_name'], 0, 1) . substr($staff['last_name'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <h3 class="h5 fw-bold mb-0" style="color:#0f172a;"><?= e($staff['first_name'] . ' ' . $staff['last_name']) ?></h3>
                    <div style="font-size:12px; color:#64748b;" class="font-monospace"><?= e($staff['staff_id']) ?></div>
                    <span class="badge bg-teal-subtle text-teal-emphasis border border-teal-subtle mt-1" style="background:#e6fffa; color:#0d9488; font-size:11px;">
                        <?= e($staff['role']) ?>
                    </span>
                </div>
            </div>

            <form method="POST" action="<?= url('teacher/profile') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" style="font-size:12px; font-weight:600;">Email Address</label>
                        <input type="email" class="form-control bg-light" value="<?= e($staff['email']) ?>" readonly disabled>
                        <small class="text-muted" style="font-size:10.5px;">Contact admin to update email</small>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" style="font-size:12px; font-weight:600;">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?= e($staff['phone']) ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label" style="font-size:12px; font-weight:600;">Update Passport Photo</label>
                        <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png">
                        <small class="text-muted" style="font-size:10.5px;">Accepted formats: JPG, PNG. Max: 2MB.</small>
                    </div>

                    <div class="col-12 pt-3 border-top">
                        <h6 class="fw-bold mb-2" style="font-size:13px; color:#0f172a;">Change Password</h6>
                        <label class="form-label" style="font-size:12px; font-weight:600;">New Password (leave empty to keep current)</label>
                        <input type="password" name="new_password" class="form-control" placeholder="••••••••" minlength="6">
                    </div>

                    <div class="col-12 pt-2">
                        <button type="submit" class="btn btn-primary px-4" style="font-weight:600; border-radius:8px;">
                            <i class="ti ti-device-floppy me-1"></i> Save Profile Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: Active Assignments & Authorizations -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius:14px; background:#fff;">
            <h4 class="h6 fw-bold mb-3" style="color:#0f172a;">Active Class & Subject Allocations</h4>
            <?php if (empty($assignments)): ?>
                <p class="text-muted text-center py-4 mb-0" style="font-size:12.5px;">No active allocations found for this session.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase" style="font-size:10.5px; font-weight:700; color:#64748b;">
                            <tr>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($assignments as $a): ?>
                                <tr>
                                    <td class="fw-bold" style="font-size:13px; color:#0f172a;"><?= e($a['class_name']) ?></td>
                                    <td style="font-size:12px; color:#475569;"><?= e($a['subject_name'] ?: 'None (Class Teacher)') ?></td>
                                    <td>
                                        <?php if (!empty($a['is_form_teacher'])): ?>
                                            <span class="badge bg-warning text-dark font-weight-bold" style="font-size:10.5px;">Form Teacher</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-secondary border" style="font-size:10.5px;">Subject Teacher</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="mt-4 pt-3 border-top">
                <h6 class="fw-bold mb-1" style="font-size:13px; color:#0f172a;">Security & Access Information</h6>
                <div class="text-muted" style="font-size:12px; line-height:1.6;">
                    <div>Username: <strong class="font-monospace text-dark"><?= e($staff['username']) ?></strong></div>
                    <div>Last Login: <strong><?= !empty($staff['last_login']) ? date('M d, Y h:i A', strtotime($staff['last_login'])) : 'First session' ?></strong></div>
                    <div>Department: <strong><?= e($staff['department'] ?: 'Academic') ?></strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
