<?php
$map = settings_map();
$admissionOpen = ($map['admission_open'] ?? '1') === '1';

$admissionType = $admissionType ?? 'General';
$admissionTypes = ['General', 'Nursery', 'Primary', 'Junior Secondary', 'Senior Secondary'];

// Parse document requirements settings
$docRequirementsJson = $map['admission_doc_requirements'] ?? '';
$customDocRequirements = [];
if ($docRequirementsJson) {
    $customDocRequirements = json_decode($docRequirementsJson, true) ?: [];
}

$defaultDocs = [
    'General' => ['Passport Photograph', 'Birth Certificate', 'Previous Result'],
    'Nursery' => ['Passport Photograph', 'Birth Certificate', 'Immunization Card'],
    'Primary' => ['Passport Photograph', 'Birth Certificate', 'Last Result', 'Immunization Card'],
    'Junior Secondary' => ['Passport Photograph', 'Birth Certificate', 'Last Result', 'Transfer Letter'],
    'Senior Secondary' => ['Passport Photograph', 'Birth Certificate', 'BECE Result', 'Transfer Letter', 'Medical Report'],
];

$documentProfiles = [];
foreach ($admissionTypes as $type) {
    if (!empty($customDocRequirements[$type])) {
        $items = array_map('trim', explode(',', $customDocRequirements[$type]));
        $documentProfiles[$type] = array_filter($items);
    } else {
        $documentProfiles[$type] = $defaultDocs[$type] ?? $defaultDocs['General'];
    }
}

// Form field status helper
$fieldMapJson = $map['admission_form_fields'] ?? '';
$fieldStatuses = [];
if ($fieldMapJson) {
    $fieldStatuses = json_decode($fieldMapJson, true) ?: [];
}

$getFieldStatus = function($key, $default = 'optional') use ($fieldStatuses) {
    return $fieldStatuses[$key] ?? $default;
};

$isFieldVisible = function($key, $default = 'optional') use ($getFieldStatus) {
    return $getFieldStatus($key, $default) !== 'disabled';
};

$isFieldRequired = function($key, $default = 'optional') use ($getFieldStatus) {
    return $getFieldStatus($key, $default) === 'required';
};
?>

<section class="admission-form-page" style="--primary-color: <?= e($map['primary_color'] ?? '#0b3d91') ?>; --secondary-color: <?= e($map['secondary_color'] ?? '#f4b942') ?>;">
    <div class="admission-form-header">
        <div class="container">
            <div class="admission-form-brand">
                <div class="admission-form-logo">
                    <?php if (setting('school_logo')): ?>
                        <img src="<?= url('uploads/' . setting('school_logo')) ?>" alt="<?= e(setting('school_name', APP_NAME)) ?>">
                    <?php else: ?>
                        <?= e(strtoupper(substr(setting('school_name', APP_NAME), 0, 1))) ?>
                    <?php endif; ?>
                </div>
                <div>
                    <h1><?= e(setting('admission_hero_title', setting('school_name', APP_NAME) . ' Admission Portal')) ?></h1>
                    <p><?= e(setting('admission_hero_subtitle', 'Complete the online student registration form below.')) ?></p>
                </div>
            </div>

            <?php if ($admissionOpen): ?>
            <div class="admission-progress" data-admission-progress>
                <div class="admission-progress-meta">
                    <span>Application Progress</span>
                    <strong data-progress-label>0%</strong>
                </div>
                <div class="admission-progress-track" aria-hidden="true">
                    <div class="admission-progress-fill" data-progress-fill></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="admission-form-container">
        <?php if (!$admissionOpen): ?>
            <div class="alert alert-warning p-4 rounded-3 text-center my-4">
                <i class="ti ti-lock-access mb-2" style="font-size: 48px; color: #b45309;"></i>
                <h3 class="h4 font-bold text-dark">Admissions Currently Closed</h3>
                <p class="text-muted mb-3"><?= e(setting('admission_closed_message', 'Online admissions for the current session are currently closed. Please contact the school administration for inquiry.')) ?></p>
                <div class="d-flex justify-content-center gap-3">
                    <?php if (setting('school_phone')): ?>
                        <a href="tel:<?= e(setting('school_phone')) ?>" class="btn btn-outline-primary"><i class="ti ti-phone me-1"></i> Call <?= e(setting('school_phone')) ?></a>
                    <?php endif; ?>
                    <?php if (setting('school_email')): ?>
                        <a href="mailto:<?= e(setting('school_email')) ?>" class="btn btn-outline-secondary"><i class="ti ti-mail me-1"></i> Email School</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
        <form class="admission-form" id="admissionForm" action="<?= url('apply') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <!-- Section 1: Registration Credentials & Type -->
            <div class="admission-section is-open">
                <button class="admission-section-header" type="button" aria-expanded="true">
                    <span class="admission-section-icon">01</span>
                    <span class="admission-section-title">Applicant Registration</span>
                    <span class="admission-section-status" aria-hidden="true"></span>
                    <span class="admission-section-chevron" aria-hidden="true">v</span>
                </button>
                <div class="admission-section-content">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Parent / Guardian Email *</label>
                            <input class="form-control" type="email" name="parent_email" placeholder="parent@example.com" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Parent / Guardian Phone *</label>
                            <input class="form-control" type="tel" name="parent_phone" placeholder="08012345678" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Admission Type / Category *</label>
                            <select class="form-select" name="admission_type" data-admission-type required>
                                <?php foreach ($admissionTypes as $type): ?>
                                    <option value="<?= e($type) ?>" <?= $admissionType === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Personal Information -->
            <div class="admission-section">
                <button class="admission-section-header" type="button" aria-expanded="false">
                    <span class="admission-section-icon">02</span>
                    <span class="admission-section-title">Personal Information</span>
                    <span class="admission-section-status" aria-hidden="true"></span>
                    <span class="admission-section-chevron" aria-hidden="true">v</span>
                </button>
                <div class="admission-section-content">
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">First Name *</label><input class="form-control" name="first_name" required></div>
                        <?php if ($isFieldVisible('middle_name')): ?>
                            <div class="col-md-4"><label class="form-label">Middle Name <?= $isFieldRequired('middle_name') ? '*' : '' ?></label><input class="form-control" name="middle_name" <?= $isFieldRequired('middle_name') ? 'required' : '' ?>></div>
                        <?php endif; ?>
                        <div class="col-md-4"><label class="form-label">Last Name *</label><input class="form-control" name="last_name" required></div>
                        <div class="col-md-3"><label class="form-label">Gender *</label><select class="form-select" name="gender" required><option value="">Select</option><option>Male</option><option>Female</option></select></div>
                        <div class="col-md-3"><label class="form-label">Date of Birth *</label><input class="form-control" type="date" name="date_of_birth" required></div>
                        <div class="col-md-3"><label class="form-label">State of Origin *</label><input class="form-control" name="state_of_origin" required></div>
                        <?php if ($isFieldVisible('local_government')): ?>
                            <div class="col-md-3"><label class="form-label">Local Government <?= $isFieldRequired('local_government') ? '*' : '' ?></label><input class="form-control" name="local_government" <?= $isFieldRequired('local_government') ? 'required' : '' ?>></div>
                        <?php endif; ?>
                        <div class="col-md-3"><label class="form-label">Nationality *</label><input class="form-control" name="nationality" value="Nigerian" required></div>
                        <?php if ($isFieldVisible('religion')): ?>
                            <div class="col-md-4"><label class="form-label">Religion <?= $isFieldRequired('religion') ? '*' : '' ?></label><input class="form-control" name="religion" <?= $isFieldRequired('religion') ? 'required' : '' ?>></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Section 3: Parent / Guardian Information -->
            <div class="admission-section">
                <button class="admission-section-header" type="button" aria-expanded="false">
                    <span class="admission-section-icon">03</span>
                    <span class="admission-section-title">Parent / Guardian Information</span>
                    <span class="admission-section-status" aria-hidden="true"></span>
                    <span class="admission-section-chevron" aria-hidden="true">v</span>
                </button>
                <div class="admission-section-content">
                    <div class="row g-3">
                        <div class="col-md-12"><label class="form-label">Home Address *</label><textarea class="form-control" name="home_address" rows="3" required></textarea></div>
                        <div class="col-md-4"><label class="form-label">Parent / Guardian Name *</label><input class="form-control" name="parent_name" required></div>
                        <?php if ($isFieldVisible('father_name')): ?>
                            <div class="col-md-4"><label class="form-label">Father Name <?= $isFieldRequired('father_name') ? '*' : '' ?></label><input class="form-control" name="father_name" <?= $isFieldRequired('father_name') ? 'required' : '' ?>></div>
                        <?php endif; ?>
                        <?php if ($isFieldVisible('mother_name')): ?>
                            <div class="col-md-4"><label class="form-label">Mother Name <?= $isFieldRequired('mother_name') ? '*' : '' ?></label><input class="form-control" name="mother_name" <?= $isFieldRequired('mother_name') ? 'required' : '' ?>></div>
                        <?php endif; ?>
                        <?php if ($isFieldVisible('guardian_name')): ?>
                            <div class="col-md-4"><label class="form-label">Guardian Name <?= $isFieldRequired('guardian_name') ? '*' : '' ?></label><input class="form-control" name="guardian_name" <?= $isFieldRequired('guardian_name') ? 'required' : '' ?>></div>
                        <?php endif; ?>
                        <?php if ($isFieldVisible('parent_occupation')): ?>
                            <div class="col-md-4"><label class="form-label">Parent Occupation <?= $isFieldRequired('parent_occupation') ? '*' : '' ?></label><input class="form-control" name="parent_occupation" <?= $isFieldRequired('parent_occupation') ? 'required' : '' ?>></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Section 4: Academic Information -->
            <div class="admission-section">
                <button class="admission-section-header" type="button" aria-expanded="false">
                    <span class="admission-section-icon">04</span>
                    <span class="admission-section-title">Academic Information</span>
                    <span class="admission-section-status" aria-hidden="true"></span>
                    <span class="admission-section-chevron" aria-hidden="true">v</span>
                </button>
                <div class="admission-section-content">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Class Applying For *</label><select class="form-select" name="class_id" required><option value="">Select class</option><?php foreach ($classes as $class): ?><option value="<?= e($class['id']) ?>"><?= e($class['name']) ?></option><?php endforeach; ?></select></div>
                        <?php if ($isFieldVisible('previous_school')): ?>
                            <div class="col-md-4"><label class="form-label">Previous School <?= $isFieldRequired('previous_school') ? '*' : '' ?></label><input class="form-control" name="previous_school" <?= $isFieldRequired('previous_school') ? 'required' : '' ?>></div>
                        <?php endif; ?>
                        <?php if ($isFieldVisible('previous_class')): ?>
                            <div class="col-md-4"><label class="form-label">Previous Class <?= $isFieldRequired('previous_class') ? '*' : '' ?></label><input class="form-control" name="previous_class" <?= $isFieldRequired('previous_class') ? 'required' : '' ?>></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Section 5: Medical & Emergency Contact -->
            <?php if ($isFieldVisible('blood_group') || $isFieldVisible('allergies') || $isFieldVisible('special_needs') || $isFieldVisible('emergency_name') || $isFieldVisible('emergency_phone')): ?>
            <div class="admission-section">
                <button class="admission-section-header" type="button" aria-expanded="false">
                    <span class="admission-section-icon">05</span>
                    <span class="admission-section-title">Medical & Emergency Information</span>
                    <span class="admission-section-status" aria-hidden="true"></span>
                    <span class="admission-section-chevron" aria-hidden="true">v</span>
                </button>
                <div class="admission-section-content">
                    <div class="row g-3">
                        <?php if ($isFieldVisible('blood_group')): ?>
                            <div class="col-md-4"><label class="form-label">Blood Group <?= $isFieldRequired('blood_group') ? '*' : '' ?></label><input class="form-control" name="blood_group" <?= $isFieldRequired('blood_group') ? 'required' : '' ?>></div>
                        <?php endif; ?>
                        <?php if ($isFieldVisible('allergies')): ?>
                            <div class="col-md-4"><label class="form-label">Allergies <?= $isFieldRequired('allergies') ? '*' : '' ?></label><input class="form-control" name="allergies" <?= $isFieldRequired('allergies') ? 'required' : '' ?>></div>
                        <?php endif; ?>
                        <?php if ($isFieldVisible('special_needs')): ?>
                            <div class="col-md-4"><label class="form-label">Special Needs <?= $isFieldRequired('special_needs') ? '*' : '' ?></label><input class="form-control" name="special_needs" <?= $isFieldRequired('special_needs') ? 'required' : '' ?>></div>
                        <?php endif; ?>
                        <?php if ($isFieldVisible('emergency_name')): ?>
                            <div class="col-md-4"><label class="form-label">Emergency Contact Name <?= $isFieldRequired('emergency_name') ? '*' : '' ?></label><input class="form-control" name="emergency_name" <?= $isFieldRequired('emergency_name') ? 'required' : '' ?>></div>
                        <?php endif; ?>
                        <?php if ($isFieldVisible('emergency_phone')): ?>
                            <div class="col-md-4"><label class="form-label">Emergency Phone <?= $isFieldRequired('emergency_phone') ? '*' : '' ?></label><input class="form-control" name="emergency_phone" <?= $isFieldRequired('emergency_phone') ? 'required' : '' ?>></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Section 6: Category Required Documents -->
            <div class="admission-section">
                <button class="admission-section-header" type="button" aria-expanded="false">
                    <span class="admission-section-icon">06</span>
                    <span class="admission-section-title">Required Documents</span>
                    <span class="admission-section-status" aria-hidden="true"></span>
                    <span class="admission-section-chevron" aria-hidden="true">v</span>
                </button>
                <div class="admission-section-content">
                    <p class="admission-doc-note">Upload PDF, JPG, PNG, WEBP, GIF, DOC, or DOCX files. Maximum size is 2MB each.</p>
                    <p class="admission-doc-note font-semibold text-primary" data-document-profile>
                        Required for <?= e($admissionType) ?>: <?= e(implode(', ', $documentProfiles[$admissionType] ?? $documentProfiles['General'])) ?>.
                    </p>

                    <div class="admission-document-grid" data-document-grid>
                        <?php foreach (['passport_photo' => 'Passport Photograph *', 'birth_certificate' => 'Birth Certificate *', 'previous_result' => 'Previous Result *', 'testimonial' => 'Testimonial', 'recommendation_letter' => 'Recommendation Letter'] as $field => $label): ?>
                            <label class="admission-upload-box dropzone" data-upload-box>
                                <span class="admission-upload-icon"><?= str_contains($label, '*') ? 'REQ' : 'OPT' ?></span>
                                <span class="admission-upload-title"><?= e($label) ?></span>
                                <span class="file-name admission-upload-hint">Drag, drop, or choose file</span>
                                <input type="file" name="<?= e($field) ?>" accept=".pdf,.jpg,.jpeg,.jfif,.png,.webp,.gif,.doc,.docx" <?= str_contains($label, '*') ? 'required' : '' ?>>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="admission-info-banner">
                <strong>Important Notice</strong>
                <span>Complete every required field before submitting. After submission, your application will be routed to the school admission office for review.</span>
            </div>

            <div class="admission-actions">
                <a class="btn btn-outline-secondary btn-lg" href="<?= url() ?>">Cancel</a>
                <button class="btn btn-primary btn-lg" type="submit"><i class="ti ti-send me-1"></i> Submit Application</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</section>

<script>
(() => {
    const profiles = <?= json_encode($documentProfiles, JSON_UNESCAPED_SLASHES) ?>;
    const select = document.querySelector('[data-admission-type]');
    const target = document.querySelector('[data-document-profile]');
    if (!select || !target) return;
    
    select.addEventListener('change', () => {
        const docs = profiles[select.value] || profiles.General || [];
        target.textContent = `Required for ${select.value}: ${docs.join(', ')}.`;
    });
})();
</script>