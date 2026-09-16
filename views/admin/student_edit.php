<?php
/**
 * views/admin/student_edit.php
 * Edit Enrolled Student & Applicant Details View
 */
$fullName = trim($application['first_name'] . ' ' . ($application['middle_name'] ? $application['middle_name'] . ' ' : '') . $application['last_name']);
?>

<div class="container-fluid px-0">
    <!-- Breadcrumb & Top Bar -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted small">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/applications?status=Enrolled') ?>" class="text-decoration-none text-muted">Students</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/applications/' . $application['id']) ?>" class="text-decoration-none text-muted"><?= e($fullName) ?></a></li>
                    <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Edit Details</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark mb-1" style="letter-spacing: -0.02em;">Edit Student Details</h1>
            <p class="text-muted small mb-0">Update profile, class placement, student admission credentials, and parent contact info.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= url('admin/applications/' . $application['id']) ?>" class="btn btn-outline-secondary btn-sm px-3 rounded-3 shadow-sm">
                <i class="ti ti-arrow-left me-1"></i> Back to Profile
            </a>
            <a href="<?= url('admin/applications/' . $application['id'] . '/id-card') ?>" target="_blank" class="btn btn-outline-primary btn-sm px-3 rounded-3 shadow-sm">
                <i class="ti ti-id me-1"></i> ID Card
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <form action="<?= url('admin/applications/' . $application['id'] . '/edit') ?>" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <?= csrf_field() ?>

        <!-- 1. Academic & Status Settings -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-circle bg-primary-subtle text-primary" style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="ti ti-school"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 15px;">Academic Placement &amp; Identifiers</h5>
                        <p class="text-muted small mb-0">Class, admission number, status, and portal username.</p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark">Current Class <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 shadow-none" name="class_id" required>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= e($c['id']) ?>" <?= (int) $application['class_id'] === (int) $c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback small">Please select a class.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark">Admission Category / Type</label>
                        <select class="form-select rounded-3 shadow-none" name="admission_type">
                            <?php foreach (['Old Student', 'Direct Admission', 'General', 'Transfer', 'Scholarship'] as $type): ?>
                                <option value="<?= $type ?>" <?= ($application['admission_type'] ?? '') === $type ? 'selected' : '' ?>><?= $type ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark">Student Account Status</label>
                        <select class="form-select rounded-3 shadow-none" name="student_status">
                            <?php foreach (['Active', 'Suspended', 'Graduated', 'Transferred', 'Withdrawn'] as $st): ?>
                                <option value="<?= $st ?>" <?= ($application['student_status'] ?? 'Active') === $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark">Admission / Matric Number</label>
                        <input type="text" class="form-control rounded-3 shadow-none font-monospace" name="admission_number" value="<?= e($application['admission_number'] ?? '') ?>">
                        <div class="form-text text-muted" style="font-size: 11px;">School-assigned registration number.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark">Student Portal Username</label>
                        <input type="text" class="form-control rounded-3 shadow-none font-monospace" name="student_username" value="<?= e($application['student_username'] ?? '') ?>">
                        <div class="form-text text-muted" style="font-size: 11px;">Syncs with student login portal account.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark">Enrollment Date</label>
                        <input type="date" class="form-control rounded-3 shadow-none" name="enrolled_at" value="<?= !empty($application['enrolled_at']) ? date('Y-m-d', strtotime($application['enrolled_at'])) : date('Y-m-d') ?>">
                        <div class="form-text text-muted" style="font-size: 11px;">Date student was officially admitted.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Student Bio & Personal Details -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-circle bg-success-subtle text-success" style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="ti ti-user"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 15px;">Student Personal Bio</h5>
                        <p class="text-muted small mb-0">Demographics, names, gender, and photograph.</p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 shadow-none" name="first_name" value="<?= e($application['first_name'] ?? '') ?>" required>
                        <div class="invalid-feedback small">First name is required.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark">Middle Name</label>
                        <input type="text" class="form-control rounded-3 shadow-none" name="middle_name" value="<?= e($application['middle_name'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark">Last Name / Surname <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 shadow-none" name="last_name" value="<?= e($application['last_name'] ?? '') ?>" required>
                        <div class="invalid-feedback small">Last name is required.</div>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-dark">Gender <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3 shadow-none" name="gender" required>
                            <option value="Male" <?= ($application['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
                            <option value="Female" <?= ($application['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-dark">Date of Birth <span class="text-danger">*</span></label>
                        <input type="date" class="form-control rounded-3 shadow-none" name="date_of_birth" value="<?= e($application['date_of_birth'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-dark">Nationality</label>
                        <input type="text" class="form-control rounded-3 shadow-none" name="nationality" value="<?= e($application['nationality'] ?? 'Nigerian') ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold small text-dark">State of Origin</label>
                        <input type="text" class="form-control rounded-3 shadow-none" name="state_of_origin" value="<?= e($application['state_of_origin'] ?? '') ?>">
                    </div>

                    <div class="col-md-8">
                        <label class="form-label fw-semibold small text-dark">Residential Address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 shadow-none" name="home_address" value="<?= e($application['home_address'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark">Update Passport Photo</label>
                        <div class="d-flex align-items-center gap-3">
                            <?php if (!empty($application['passport_photo'])): ?>
                                <img src="<?= url('uploads/' . $application['passport_photo']) ?>" alt="Passport" style="width: 42px; height: 42px; border-radius: 8px; object-fit: cover; border: 1px solid #e2e8f0;">
                            <?php endif; ?>
                            <input type="file" class="form-control form-control-sm rounded-3 shadow-none" name="passport_photo" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Parent & Guardian Information -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-circle bg-info-subtle text-info" style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="ti ti-users"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 15px;">Parent &amp; Guardian Information</h5>
                        <p class="text-muted small mb-0">Primary contacts for notifications and parent portal credentials.</p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark">Parent / Guardian Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3 shadow-none" name="parent_name" value="<?= e($application['parent_name'] ?? '') ?>" required>
                        <div class="invalid-feedback small">Parent name is required.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark">Parent Phone Number <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control rounded-3 shadow-none" name="parent_phone" value="<?= e($application['parent_phone'] ?? '') ?>" required>
                        <div class="form-text text-muted" style="font-size: 11px;">Syncs with parent portal login &amp; SMS alerts.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark">Parent Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control rounded-3 shadow-none" name="parent_email" value="<?= e($application['parent_email'] ?? '') ?>" required>
                        <div class="form-text text-muted" style="font-size: 11px;">Syncs with parent portal login &amp; email notices.</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark">Father's Name</label>
                        <input type="text" class="form-control rounded-3 shadow-none" name="father_name" value="<?= e($application['father_name'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark">Mother's Name</label>
                        <input type="text" class="form-control rounded-3 shadow-none" name="mother_name" value="<?= e($application['mother_name'] ?? '') ?>">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-dark">Parent Occupation</label>
                        <input type="text" class="form-control rounded-3 shadow-none" name="parent_occupation" value="<?= e($application['parent_occupation'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Student Documents & Uploads -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="icon-circle bg-warning-subtle text-warning" style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                        <i class="ti ti-paperclip"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 15px;">Student Documents &amp; Certificates</h5>
                        <p class="text-muted small mb-0">View current attachments or upload replacement documents (PDF, PNG, JPG, DOC up to 5MB).</p>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <!-- Birth Certificate -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold text-dark small"><i class="ti ti-certificate me-1 text-primary"></i> Birth Certificate</span>
                                <?php if (!empty($application['birth_certificate'])): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 11px;">Uploaded</span>
                                        <a href="<?= url('uploads/' . $application['birth_certificate']) ?>" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 rounded-2" style="font-size: 11px;">
                                            <i class="ti ti-eye me-1"></i> View
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 11px;">Pending</span>
                                <?php endif; ?>
                            </div>
                            <input type="file" class="form-control form-control-sm rounded-3 shadow-none bg-white" name="birth_certificate" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                            <div class="form-text text-muted" style="font-size: 11px;">Upload to add or replace current certificate.</div>
                        </div>
                    </div>

                    <!-- Previous School Result -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold text-dark small"><i class="ti ti-file-text me-1 text-primary"></i> Previous School Result / Transcript</span>
                                <?php if (!empty($application['previous_result'])): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 11px;">Uploaded</span>
                                        <a href="<?= url('uploads/' . $application['previous_result']) ?>" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 rounded-2" style="font-size: 11px;">
                                            <i class="ti ti-eye me-1"></i> View
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 11px;">Pending</span>
                                <?php endif; ?>
                            </div>
                            <input type="file" class="form-control form-control-sm rounded-3 shadow-none bg-white" name="previous_result" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                            <div class="form-text text-muted" style="font-size: 11px;">Upload to add or replace report card / result.</div>
                        </div>
                    </div>

                    <!-- Testimonial -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold text-dark small"><i class="ti ti-award me-1 text-primary"></i> Testimonial</span>
                                <?php if (!empty($application['testimonial'])): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 11px;">Uploaded</span>
                                        <a href="<?= url('uploads/' . $application['testimonial']) ?>" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 rounded-2" style="font-size: 11px;">
                                            <i class="ti ti-eye me-1"></i> View
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 11px;">Pending</span>
                                <?php endif; ?>
                            </div>
                            <input type="file" class="form-control form-control-sm rounded-3 shadow-none bg-white" name="testimonial" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                            <div class="form-text text-muted" style="font-size: 11px;">Upload to add or replace testimonial.</div>
                        </div>
                    </div>

                    <!-- Recommendation Letter -->
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold text-dark small"><i class="ti ti-mail-opened me-1 text-primary"></i> Recommendation Letter</span>
                                <?php if (!empty($application['recommendation_letter'])): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 11px;">Uploaded</span>
                                        <a href="<?= url('uploads/' . $application['recommendation_letter']) ?>" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 rounded-2" style="font-size: 11px;">
                                            <i class="ti ti-eye me-1"></i> View
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size: 11px;">Pending</span>
                                <?php endif; ?>
                            </div>
                            <input type="file" class="form-control form-control-sm rounded-3 shadow-none bg-white" name="recommendation_letter" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
                            <div class="form-text text-muted" style="font-size: 11px;">Upload to add or replace recommendation letter.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Controls Bar -->
        <div class="d-flex justify-content-end align-items-center gap-3 mb-5">
            <a href="<?= url('admin/applications/' . $application['id']) ?>" class="btn btn-light border px-4 py-2 rounded-3 text-muted">Cancel</a>
            <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold rounded-3 shadow-sm">
                <i class="ti ti-device-floppy me-1"></i> Save Changes
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    'use strict';
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>
