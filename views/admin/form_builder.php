<?php
$map = settings_map();

// Helper to retrieve field setting
$getFieldSetting = function($fieldKey, $defaultStatus = 'optional') use ($map) {
    $fieldsJson = $map['admission_form_fields'] ?? '';
    if ($fieldsJson) {
        $decoded = json_decode($fieldsJson, true);
        if (is_array($decoded) && isset($decoded[$fieldKey])) {
            return $decoded[$fieldKey];
        }
    }
    return $defaultStatus;
};

// Form fields configurable by admin
$formFields = [
    'middle_name' => ['label' => 'Middle Name', 'default' => 'optional'],
    'religion' => ['label' => 'Religion', 'default' => 'optional'],
    'father_name' => ['label' => 'Father\'s Name', 'default' => 'optional'],
    'mother_name' => ['label' => 'Mother\'s Name', 'default' => 'optional'],
    'guardian_name' => ['label' => 'Guardian Name', 'default' => 'optional'],
    'parent_occupation' => ['label' => 'Parent Occupation', 'default' => 'optional'],
    'blood_group' => ['label' => 'Blood Group', 'default' => 'optional'],
    'allergies' => ['label' => 'Allergies & Medical Notes', 'default' => 'optional'],
    'special_needs' => ['label' => 'Special Needs / Learning Assistance', 'default' => 'optional'],
    'emergency_name' => ['label' => 'Emergency Contact Name', 'default' => 'optional'],
    'emergency_phone' => ['label' => 'Emergency Contact Phone', 'default' => 'optional'],
    'previous_school' => ['label' => 'Previous School Name', 'default' => 'optional'],
    'previous_class' => ['label' => 'Previous Class Passed', 'default' => 'optional'],
    'local_government' => ['label' => 'Local Government Area (LGA)', 'default' => 'optional'],
];

// Document requirements map per category
$docRequirementsJson = $map['admission_doc_requirements'] ?? '';
$docRequirements = [];
if ($docRequirementsJson) {
    $docRequirements = json_decode($docRequirementsJson, true) ?: [];
}

$defaultDocs = [
    'General' => 'Passport Photograph, Birth Certificate, Previous Result',
    'Nursery' => 'Passport Photograph, Birth Certificate, Immunization Card',
    'Primary' => 'Passport Photograph, Birth Certificate, Last Result, Immunization Card',
    'Junior Secondary' => 'Passport Photograph, Birth Certificate, Last Result, Transfer Letter',
    'Senior Secondary' => 'Passport Photograph, Birth Certificate, BECE Result, Transfer Letter, Medical Report',
];

$schoolInfo = SchoolContext::info();
$schoolDomain = trim($schoolInfo['domain'] ?? '');
if ($schoolDomain !== '' && $schoolDomain !== 'localhost' && $schoolDomain !== '127.0.0.1') {
    $baseUrl = 'https://' . $schoolDomain;
} else {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $baseUrl = $scheme . '://' . $host . BASE_URL;
}
?>

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1">Registration Portal & Form Builder</h1>
        <p class="text-muted mb-0">Customize your school's branded admission form, custom registration links, and required documents.</p>
    </div>
    <div>
        <a class="btn btn-outline-primary" href="<?= e($baseUrl . '/register') ?>" target="_blank">
            <i class="ti ti-external-link me-1"></i> Preview Live Portal
        </a>
    </div>
</div>

<form class="panel" method="post" action="<?= url('admin/form-builder') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <!-- 1. Admission Window & Status -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 text-primary"><i class="ti ti-calendar-event me-2"></i>Admission Period & Portal Status</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label font-semibold">Admission Portal Status</label>
                    <select class="form-select" name="settings[admission_open]">
                        <option value="1" <?= ($map['admission_open'] ?? '1') === '1' ? 'selected' : '' ?>>Open (Accepting Applications)</option>
                        <option value="0" <?= ($map['admission_open'] ?? '1') === '0' ? 'selected' : '' ?>>Closed (Disabled)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Opening Date</label>
                    <input class="form-control" type="date" name="settings[admission_start_date]" value="<?= e($map['admission_start_date'] ?? date('Y-01-01')) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Closing Date</label>
                    <input class="form-control" type="date" name="settings[admission_end_date]" value="<?= e($map['admission_end_date'] ?? date('Y-12-31')) ?>">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Admission Closed Notice Message</label>
                    <textarea class="form-control" name="settings[admission_closed_message]" rows="2" placeholder="Message shown to visitors when admissions are closed..."><?= e($map['admission_closed_message'] ?? 'Online admissions for the current session are currently closed. Please contact the school administration for inquiry.') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Form Field Configuration -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 text-primary"><i class="ti ti-adjustments-horizontal me-2"></i>Registration Form Fields Builder</h5>
            <small class="text-muted">Control which form fields are visible, optional, or mandatory on your registration portal. (Core fields like First/Last Name, DOB, Gender, Parent Email/Phone, Class Applying For are always required).</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Field Name</th>
                            <th>Field Status / Requirement Level</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($formFields as $key => $meta): ?>
                            <?php $currentStatus = $getFieldSetting($key, $meta['default']); ?>
                            <tr>
                                <td>
                                    <strong><?= e($meta['label']) ?></strong>
                                    <input type="hidden" name="form_fields[<?= e($key) ?>][label]" value="<?= e($meta['label']) ?>">
                                </td>
                                <td>
                                    <div class="btn-group w-100" role="group" style="max-width: 400px;">
                                        <input type="radio" class="btn-check" name="form_fields[<?= e($key) ?>][status]" id="status_req_<?= $key ?>" value="required" <?= $currentStatus === 'required' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-danger btn-sm" for="status_req_<?= $key ?>"><i class="ti ti-asterisk me-1"></i>Required (*)</label>

                                        <input type="radio" class="btn-check" name="form_fields[<?= e($key) ?>][status]" id="status_opt_<?= $key ?>" value="optional" <?= $currentStatus === 'optional' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-secondary btn-sm" for="status_opt_<?= $key ?>"><i class="ti ti-minus me-1"></i>Optional</label>

                                        <input type="radio" class="btn-check" name="form_fields[<?= e($key) ?>][status]" id="status_dis_<?= $key ?>" value="disabled" <?= $currentStatus === 'disabled' ? 'checked' : '' ?>>
                                        <label class="btn btn-outline-warning btn-sm" for="status_dis_<?= $key ?>"><i class="ti ti-eye-off me-1"></i>Disabled (Hidden)</label>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 3. Dynamic Category Document Requirements -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 text-primary"><i class="ti ti-files me-2"></i>Required Documents per Admission Category</h5>
            <small class="text-muted">Set specific required documents for each admission level (comma-separated list).</small>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <?php foreach (['General', 'Nursery', 'Primary', 'Junior Secondary', 'Senior Secondary'] as $cat): ?>
                    <?php $catVal = $docRequirements[$cat] ?? ($defaultDocs[$cat] ?? ''); ?>
                    <div class="col-md-6">
                        <label class="form-label font-semibold"><i class="ti ti-folder me-1"></i><?= e($cat) ?> Category Documents</label>
                        <input class="form-control" name="doc_requirements[<?= e($cat) ?>]" value="<?= e($catVal) ?>" placeholder="e.g. Passport Photograph, Birth Certificate, Result...">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- 4. Registration Branding & Links -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 text-primary"><i class="ti ti-palette me-2"></i>Branding & Custom Registration Links</h5>
        </div>
        <div class="card-body">
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Portal Hero Banner Title</label>
                    <input class="form-control" name="settings[admission_hero_title]" value="<?= e($map['admission_hero_title'] ?? (setting('school_name', APP_NAME) . ' Admission Portal')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Portal Tagline / Subtitle</label>
                    <input class="form-control" name="settings[admission_hero_subtitle]" value="<?= e($map['admission_hero_subtitle'] ?? 'Complete the online student admission form below.') ?>">
                </div>
            </div>

            <h6><i class="ti ti-link me-1"></i> Your Branded School Registration Links</h6>
            <p class="small text-muted mb-3">Share these custom admission links with parents or embed them on your official school website:</p>

            <div class="list-group">
                <?php
                $links = [
                    'General Registration' => '/register',
                    'Nursery Admission' => '/register/nursery',
                    'Primary Admission' => '/register/primary',
                    'Junior Secondary' => '/register/junior-secondary',
                    'Senior Secondary' => '/register/senior-secondary',
                ];
                foreach ($links as $label => $path):
                    $fullUrl = $baseUrl . $path;
                ?>
                    <div class="list-group-item d-flex align-items-center justify-content-between">
                        <div>
                            <strong><?= e($label) ?>:</strong>
                            <code class="ms-2 text-primary"><?= e($fullUrl) ?></code>
                        </div>
                        <a href="<?= e($fullUrl) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="ti ti-external-link"></i> Test Link
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button class="btn btn-primary btn-lg" type="submit"><i class="ti ti-device-floppy me-1"></i> Save Form Builder Settings</button>
    </div>
</form>
