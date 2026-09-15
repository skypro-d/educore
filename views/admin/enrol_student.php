<?php
/**
 * Direct Student Enrollment & Old Students Onboarding View
 * Fintech-inspired clean administrative interface.
 */
$csrfToken = csrf_token();
?>

<div class="container-fluid px-0">
    <!-- Top Bar & Breadcrumb -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted small">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/applications?status=Enrolled') ?>" class="text-decoration-none text-muted">Students</a></li>
                    <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Direct Enrollment</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark mb-1" style="letter-spacing: -0.02em;">Direct Student Enrollment</h1>
            <p class="text-muted small mb-0">Directly onboard continuing/old students or register new students without the public admission portal.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="<?= url('admin/students/sample-csv') ?>" class="btn btn-outline-secondary btn-sm px-3 shadow-sm rounded-3">
                <i class="ti ti-download me-1"></i> Sample CSV
            </a>
            <a href="<?= url('admin/applications?status=Enrolled') ?>" class="btn btn-outline-primary btn-sm px-3 shadow-sm rounded-3">
                <i class="ti ti-users me-1"></i> Enrolled Students
            </a>
        </div>
    </div>

    <!-- Success Credentials Hero Card -->
    <?php if (!empty($successResult)): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); border-left: 5px solid #16a34a !important;">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                            <i class="ti ti-circle-check"></i>
                        </div>
                        <div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 mb-1" style="font-size: 11px;">Portal Activated</span>
                            <h4 class="fw-bold mb-0 text-dark"><?= e($successResult['student_name']) ?> Successfully Enrolled</h4>
                            <div class="text-muted small mt-1">
                                Admission No: <strong class="text-dark"><?= e($successResult['admission_number']) ?></strong> &bull; 
                                Class: <strong class="text-dark"><?= e($successResult['class_name']) ?></strong> &bull; 
                                Category: <span class="badge bg-light text-dark border"><?= e($successResult['admission_type']) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-dark rounded-3 px-3 shadow-sm" onclick="printCredentialsSlip()">
                            <i class="ti ti-printer me-1"></i> Print Slip
                        </button>
                        <a href="<?= url('admin/applications/' . $successResult['applicant_id']) ?>" class="btn btn-sm btn-primary rounded-3 px-3 shadow-sm">
                            <i class="ti ti-user me-1"></i> View Profile
                        </a>
                        <a href="<?= url('admin/applications/' . $successResult['applicant_id'] . '/id-card') ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-3 px-3 shadow-sm">
                            <i class="ti ti-id me-1"></i> ID Card
                        </a>
                        <a href="<?= url('admin/students/enrol') ?>" class="btn btn-sm btn-light border rounded-3 px-3">
                            <i class="ti ti-plus me-1"></i> Enrol Another
                        </a>
                    </div>
                </div>

                <!-- Credentials Cards Grid -->
                <div class="row g-3 mt-1" id="printableCredentialsArea">
                    <!-- Student Portal Box -->
                    <div class="col-md-6">
                        <div class="p-3 bg-white rounded-3 border shadow-sm h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ti ti-school text-primary" style="font-size: 18px;"></i>
                                    <span class="fw-bold text-dark" style="font-size: 13px;">STUDENT PORTAL LOGIN</span>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 10px;">Student Access</span>
                            </div>
                            <div class="small">
                                <div class="row mb-1">
                                    <span class="col-sm-4 text-muted">Portal URL:</span>
                                    <span class="col-sm-8 text-break fw-mono"><a href="<?= url('student/login') ?>" target="_blank" class="text-decoration-none"><?= url('student/login') ?></a></span>
                                </div>
                                <div class="row mb-1">
                                    <span class="col-sm-4 text-muted">Username / ID:</span>
                                    <span class="col-sm-8 fw-bold text-dark font-monospace" id="valStudentUser"><?= e($successResult['student_username']) ?></span>
                                </div>
                                <div class="row mb-0">
                                    <span class="col-sm-4 text-muted">Temporary Pass:</span>
                                    <span class="col-sm-8 text-dark font-monospace fw-bold" id="valStudentPass"><?= e($successResult['student_password']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Parent Portal Box -->
                    <div class="col-md-6">
                        <div class="p-3 bg-white rounded-3 border shadow-sm h-100">
                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="ti ti-users text-success" style="font-size: 18px;"></i>
                                    <span class="fw-bold text-dark" style="font-size: 13px;">PARENT PORTAL LOGIN</span>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;">Parent Access</span>
                            </div>
                            <div class="small">
                                <div class="row mb-1">
                                    <span class="col-sm-4 text-muted">Portal URL:</span>
                                    <span class="col-sm-8 text-break fw-mono"><a href="<?= url('parent/login') ?>" target="_blank" class="text-decoration-none"><?= url('parent/login') ?></a></span>
                                </div>
                                <div class="row mb-1">
                                    <span class="col-sm-4 text-muted">Email / Username:</span>
                                    <span class="col-sm-8 fw-bold text-dark font-monospace" id="valParentUser"><?= e($successResult['parent_email']) ?></span>
                                </div>
                                <div class="row mb-0">
                                    <span class="col-sm-4 text-muted">Temporary Pass:</span>
                                    <span class="col-sm-8 text-dark font-monospace fw-bold" id="valParentPass"><?= e($successResult['parent_password']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 text-end">
                    <button type="button" class="btn btn-sm btn-link text-decoration-none text-muted" onclick="copyAllCredentials()">
                        <i class="ti ti-copy me-1"></i> Copy All Credentials to Clipboard
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Import Errors Notice -->
    <?php if (!empty($importErrors)): ?>
        <div class="alert alert-warning border-0 shadow-sm rounded-4 mb-4">
            <h6 class="fw-bold mb-2"><i class="ti ti-alert-triangle me-1 text-warning"></i> Import Notice / Skipped Rows</h6>
            <ul class="mb-0 small ps-3">
                <?php foreach (array_slice($importErrors, 0, 8) as $err): ?>
                    <li><?= e($err) ?></li>
                <?php endforeach; ?>
                <?php if (count($importErrors) > 8): ?>
                    <li>...and <?= count($importErrors) - 8 ?> more warnings.</li>
                <?php endif; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills custom-pills mb-4 p-1 bg-white border rounded-3 shadow-sm d-inline-flex" id="enrolTabs" role="tablist" style="width: auto;">
        <li class="nav-item" role="presentation">
            <button class="nav-link active px-4 py-2 rounded-3 fw-semibold small" id="single-tab" data-bs-toggle="pill" data-bs-target="#single-pane" type="button" role="tab" aria-controls="single-pane" aria-selected="true">
                <i class="ti ti-user-plus me-1"></i> Single Student Enrollment
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link px-4 py-2 rounded-3 fw-semibold small" id="bulk-tab" data-bs-toggle="pill" data-bs-target="#bulk-pane" type="button" role="tab" aria-controls="bulk-pane" aria-selected="false">
                <i class="ti ti-file-spreadsheet me-1"></i> Batch CSV Import (Old Students)
            </button>
        </li>
    </ul>

    <!-- Tabs Content Container -->
    <div class="tab-content" id="enrolTabsContent">

        <!-- ================================================================= -->
        <!-- TAB 1: SINGLE STUDENT DIRECT ENROLLMENT                           -->
        <!-- ================================================================= -->
        <div class="tab-pane fade show active" id="single-pane" role="tabpanel" aria-labelledby="single-tab" tabindex="0">
            <form action="<?= url('admin/students/enrol') ?>" method="POST" enctype="multipart/form-data" id="directEnrolForm" class="needs-validation" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="mode" value="single">

                <!-- 1. Academic & Placement Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-circle bg-primary-subtle text-primary">
                                <i class="ti ti-building-bank"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark" style="font-size: 15px;">Academic Placement &amp; Identification</h5>
                                <p class="text-muted small mb-0">Assign class, admission category, and existing registration IDs.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-dark">Target Class <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3 shadow-none" name="class_id" required>
                                    <option value="">-- Choose Class --</option>
                                    <?php foreach ($classes as $c): ?>
                                        <option value="<?= e($c['id']) ?>"><?= e($c['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback small">Please choose a class.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-dark">Admission Category</label>
                                <select class="form-select rounded-3 shadow-none" name="admission_type">
                                    <option value="Old Student" selected>Old Student / Continuing</option>
                                    <option value="Direct Admission">Direct Admission (New)</option>
                                    <option value="Transfer">Transfer Student</option>
                                    <option value="Scholarship">Scholarship / Special</option>
                                    <option value="General">General</option>
                                </select>
                                <div class="form-text text-muted" style="font-size: 11px;">Identifies cohort origin.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-dark">Enrollment Date</label>
                                <input type="date" class="form-control rounded-3 shadow-none" name="enrolled_at" value="<?= date('Y-m-d') ?>">
                                <div class="form-text text-muted" style="font-size: 11px;">Backdate if registering continuing students.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">
                                    Existing Admission / School Number
                                    <span class="badge bg-secondary-subtle text-secondary border ms-1" style="font-size: 10px;">Optional</span>
                                </label>
                                <input type="text" class="form-control rounded-3 shadow-none font-monospace" name="admission_number" placeholder="e.g. SCH/2022/045 or OLD-102">
                                <div class="form-text text-muted" style="font-size: 11px;">
                                    <i class="ti ti-info-circle me-1"></i> If the student already has a school ID, enter it here. Otherwise, leave blank to auto-generate.
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-dark">
                                    Student Portal Username
                                    <span class="badge bg-secondary-subtle text-secondary border ms-1" style="font-size: 10px;">Optional</span>
                                </label>
                                <input type="text" class="form-control rounded-3 shadow-none font-monospace" name="student_username" placeholder="e.g. SCH-STD-2023-045">
                                <div class="form-text text-muted" style="font-size: 11px;">
                                    <i class="ti ti-info-circle me-1"></i> Leave empty to automatically generate from the standard format.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Student Bio Information Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-circle bg-success-subtle text-success">
                                <i class="ti ti-user"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark" style="font-size: 15px;">Student Personal Bio</h5>
                                <p class="text-muted small mb-0">Basic demographic and identity details.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-dark">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3 shadow-none" name="first_name" required placeholder="e.g. David">
                                <div class="invalid-feedback small">First name is required.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-dark">Middle Name</label>
                                <input type="text" class="form-control rounded-3 shadow-none" name="middle_name" placeholder="e.g. Babatunde">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-dark">Last Name / Surname <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3 shadow-none" name="last_name" required placeholder="e.g. Adeyemi">
                                <div class="invalid-feedback small">Last name is required.</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold small text-dark">Gender <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3 shadow-none" name="gender" required>
                                    <option value="">-- Choose --</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                                <div class="invalid-feedback small">Please select gender.</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold small text-dark">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control rounded-3 shadow-none" name="date_of_birth" required value="2015-05-15">
                                <div class="invalid-feedback small">Date of birth is required.</div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold small text-dark">Nationality</label>
                                <input type="text" class="form-control rounded-3 shadow-none" name="nationality" value="Nigerian">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold small text-dark">State of Origin</label>
                                <input type="text" class="form-control rounded-3 shadow-none" name="state_of_origin" placeholder="e.g. Lagos">
                            </div>

                            <div class="col-md-8">
                                <label class="form-label fw-semibold small text-dark">Residential Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3 shadow-none" name="home_address" required placeholder="Full home / street address">
                                <div class="invalid-feedback small">Residential address is required.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-dark">Passport Photograph</label>
                                <input type="file" class="form-control rounded-3 shadow-none" name="passport_photo" accept="image/*" id="passportPhotoInput">
                                <div class="form-text text-muted" style="font-size: 11px;">JPG, PNG, or WEBP (Max 2MB).</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. Parent & Guardian Contact Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-circle bg-info-subtle text-info">
                                <i class="ti ti-users"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark" style="font-size: 15px;">Parent &amp; Guardian Information</h5>
                                <p class="text-muted small mb-0">Contact details for SMS notifications, email alerts, and parent portal access.</p>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-dark">Parent / Guardian Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3 shadow-none" name="parent_name" required placeholder="e.g. Chief Victor Adeyemi">
                                <div class="invalid-feedback small">Parent name is required.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-dark">Parent Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control rounded-3 shadow-none" name="parent_phone" required placeholder="e.g. 08012345678">
                                <div class="form-text text-muted" style="font-size: 11px;">Used for attendance SMS &amp; quick login.</div>
                                <div class="invalid-feedback small">Parent phone number is required.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-dark">Parent Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control rounded-3 shadow-none" name="parent_email" required placeholder="e.g. parent@example.com">
                                <div class="form-text text-muted" style="font-size: 11px;">Receives portal access credentials.</div>
                                <div class="invalid-feedback small">Valid parent email is required.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-dark">Father's Name (Optional)</label>
                                <input type="text" class="form-control rounded-3 shadow-none" name="father_name" placeholder="e.g. Victor Adeyemi">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-dark">Mother's Name (Optional)</label>
                                <input type="text" class="form-control rounded-3 shadow-none" name="mother_name" placeholder="e.g. Funmilayo Adeyemi">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-dark">Parent Occupation (Optional)</label>
                                <input type="text" class="form-control rounded-3 shadow-none" name="parent_occupation" placeholder="e.g. Civil Engineer">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Health & Medical Background (Collapsible) -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center cursor-pointer" data-bs-toggle="collapse" data-bs-target="#healthCollapse" aria-expanded="false" style="cursor: pointer;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="icon-circle bg-danger-subtle text-danger">
                                <i class="ti ti-heartbeat"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-0 text-dark" style="font-size: 15px;">Health &amp; Medical Background</h5>
                                <p class="text-muted small mb-0">Blood group, allergies, and special requirements (Optional).</p>
                            </div>
                        </div>
                        <i class="ti ti-chevron-down text-muted"></i>
                    </div>
                    <div class="collapse" id="healthCollapse">
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-dark">Blood Group</label>
                                    <select class="form-select rounded-3 shadow-none" name="blood_group">
                                        <option value="">-- Choose --</option>
                                        <option value="A+">A+</option>
                                        <option value="A-">A-</option>
                                        <option value="B+">B+</option>
                                        <option value="B-">B-</option>
                                        <option value="O+">O+</option>
                                        <option value="O-">O-</option>
                                        <option value="AB+">AB+</option>
                                        <option value="AB-">AB-</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small text-dark">Known Allergies</label>
                                    <input type="text" class="form-control rounded-3 shadow-none" name="allergies" placeholder="e.g. Peanuts, Penicillin, Dust">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold small text-dark">Special Needs / Care Notes</label>
                                    <input type="text" class="form-control rounded-3 shadow-none" name="special_needs" placeholder="e.g. Needs front seating for vision">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Emergency Contact Person</label>
                                    <input type="text" class="form-control rounded-3 shadow-none" name="emergency_name" placeholder="e.g. Uncle Segun">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-dark">Emergency Contact Phone</label>
                                    <input type="tel" class="form-control rounded-3 shadow-none" name="emergency_phone" placeholder="e.g. 08098765432">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. Credentials & Communication Bar -->
                <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Portal Activation &amp; Notifications</h6>
                                <p class="text-muted small mb-0">EduCore automatically provisions secure student and parent portals upon saving.</p>
                            </div>
                            <div class="d-flex flex-wrap align-items-center gap-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="send_email_credentials" id="sendEmailCreds" value="1" checked>
                                    <label class="form-check-label small fw-semibold text-dark" for="sendEmailCreds">Send Email to Parent</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="send_sms_credentials" id="sendSmsCreds" value="1" checked>
                                    <label class="form-check-label small fw-semibold text-dark" for="sendSmsCreds">Send SMS to Parent</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Submit Button -->
                <div class="d-flex justify-content-end align-items-center gap-3 mb-5">
                    <a href="<?= url('admin/applications?status=Enrolled') ?>" class="btn btn-light border px-4 py-2 rounded-3 text-muted">Cancel</a>
                    <button type="submit" class="btn btn-primary px-5 py-2 fw-semibold rounded-3 shadow-sm" id="btnSubmitEnrol">
                        <i class="ti ti-user-check me-1"></i> Complete Enrollment &amp; Generate Portal Access
                    </button>
                </div>
            </form>
        </div>

        <!-- ================================================================= -->
        <!-- TAB 2: BATCH CSV MIGRATION                                       -->
        <!-- ================================================================= -->
        <div class="tab-pane fade" id="bulk-pane" role="tabpanel" aria-labelledby="bulk-tab" tabindex="0">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-white border-bottom py-3 px-4">
                            <div class="d-flex align-items-center gap-2">
                                <div class="icon-circle bg-info-subtle text-info">
                                    <i class="ti ti-file-upload"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark" style="font-size: 15px;">Batch Onboard Old Students via Spreadsheet</h5>
                                    <p class="text-muted small mb-0">Upload multiple continuing students at once using our standardized CSV template.</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <!-- Instructions -->
                            <div class="alert alert-light border rounded-3 p-3 mb-4">
                                <div class="d-flex align-items-start gap-3">
                                    <i class="ti ti-bulb text-warning" style="font-size: 24px; margin-top: 2px;"></i>
                                    <div class="small">
                                        <strong class="text-dark d-block mb-1">Quick Migration Guide:</strong>
                                        <ol class="ps-3 mb-0 text-muted">
                                            <li>Download our pre-structured template: <a href="<?= url('admin/students/sample-csv') ?>" class="fw-bold text-primary text-decoration-none"><i class="ti ti-download"></i> educore_old_students_template.csv</a>.</li>
                                            <li>Ensure the <code>class_name</code> column matches the exact name of an existing class in your school.</li>
                                            <li>If an old student already has an admission number, place it in the <code>admission_number</code> column; otherwise leave it blank to auto-generate.</li>
                                            <li>Upload the filled CSV file below to enroll all students simultaneously.</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>

                            <!-- Upload Form -->
                            <form action="<?= url('admin/students/enrol') ?>" method="POST" enctype="multipart/form-data">
                                <?= csrf_field() ?>
                                <input type="hidden" name="mode" value="bulk_csv">

                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-dark small">Select CSV File (.csv) <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control rounded-3 p-3 shadow-none" name="csv_file" accept=".csv" required>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-dark small">Default Fallback Class (Optional)</label>
                                        <select class="form-select rounded-3 shadow-none" name="default_class_id">
                                            <option value="">-- No default fallback --</option>
                                            <?php foreach ($classes as $c): ?>
                                                <option value="<?= e($c['id']) ?>"><?= e($c['name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text text-muted" style="font-size: 11px;">Applied if a CSV row leaves class blank.</div>
                                    </div>

                                    <div class="col-md-6 d-flex align-items-center pt-3">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" name="send_bulk_notices" id="sendBulkNotices" value="1">
                                            <label class="form-check-label small fw-semibold text-dark" for="sendBulkNotices">
                                                Dispatch SMS &amp; Email to all parents during import
                                            </label>
                                            <div class="form-text text-muted" style="font-size: 11px;">Recommended OFF for large migrations to conserve SMS balance.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                    <a href="<?= url('admin/students/sample-csv') ?>" class="btn btn-outline-secondary rounded-3 px-3 small">
                                        <i class="ti ti-download me-1"></i> Download Template (.CSV)
                                    </a>
                                    <button type="submit" class="btn btn-primary rounded-3 px-4 py-2 fw-semibold shadow-sm">
                                        <i class="ti ti-upload me-1"></i> Upload &amp; Enroll All Students
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.icon-circle {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.custom-pills .nav-link {
    color: #64748b;
    border: 1px solid transparent;
    transition: all 0.2s ease;
}
.custom-pills .nav-link.active {
    background-color: var(--brand-primary, #0b3d91);
    color: #ffffff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.06);
}
@media print {
    body * {
        visibility: hidden;
    }
    #printableCredentialsArea, #printableCredentialsArea * {
        visibility: visible;
    }
    #printableCredentialsArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
}
</style>

<script>
// Copy credentials utility
function copyAllCredentials() {
    const stdUser = document.getElementById('valStudentUser')?.innerText || '';
    const stdPass = document.getElementById('valStudentPass')?.innerText || '';
    const parUser = document.getElementById('valParentUser')?.innerText || '';
    const parPass = document.getElementById('valParentPass')?.innerText || '';

    const text = `--- EDUCORE PORTAL ACCESS CREDENTIALS ---\n\n` +
                 `STUDENT PORTAL:\nUsername: ${stdUser}\nPassword: ${stdPass}\nURL: <?= url('student/login') ?>\n\n` +
                 `PARENT PORTAL:\nUsername: ${parUser}\nPassword: ${parPass}\nURL: <?= url('parent/login') ?>\n`;

    navigator.clipboard.writeText(text).then(() => {
        if (window.Swal) {
            Swal.fire({
                icon: 'success',
                title: 'Copied!',
                text: 'Student and Parent portal credentials copied to clipboard.',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            alert('Credentials copied to clipboard!');
        }
    }).catch(err => {
        alert('Failed to copy credentials: ' + err);
    });
}

function printCredentialsSlip() {
    window.print();
}

// Bootstrap form validation & double-submission prevention
(function () {
    'use strict';
    const form = document.getElementById('directEnrolForm');
    const submitBtn = document.getElementById('btnSubmitEnrol');
    if (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            } else {
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Enrolling Student...';
                }
            }
            form.classList.add('was-validated');
        }, false);
    }
})();
</script>
