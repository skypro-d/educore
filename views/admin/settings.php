<h1 class="h3 mb-4">School Settings</h1>
<form class="panel" method="post" action="<?= url('admin/settings') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <?php $map = settings_map(); $fields = [
        'school_name' => 'School Name', 'school_motto' => 'School Motto', 'school_address' => 'Address',
        'school_phone' => 'Phone', 'school_email' => 'Email', 'school_website' => 'Website',
        'principal_name' => 'Principal Name', 'admission_fee' => 'Admission Form Fee',
        'acceptance_fee' => 'Acceptance Fee', 'enrollment_fee' => 'Enrollment Fee',
        'students_admitted' => 'Students Admitted', 'success_rate' => 'Success Rate',
        'graduates' => 'Graduates', 'available_spaces' => 'Available Spaces'
    ]; $colors = ['primary_color' => 'Primary Color', 'secondary_color' => 'Secondary Color', 'sidebar_color' => 'Sidebar Color', 'button_color' => 'Button Color', 'dashboard_color' => 'Dashboard Color']; ?>
    <div class="row g-3">
        <?php foreach ($fields as $key => $label): ?>
            <div class="col-md-6">
                <label class="form-label"><?= e($label) ?></label>
                <input class="form-control" name="settings[<?= e($key) ?>]" value="<?= e($map[$key] ?? '') ?>">
            </div>
        <?php endforeach; ?>
        <?php foreach ($colors as $key => $label): ?>
            <div class="col-md-4">
                <label class="form-label"><?= e($label) ?></label>
                <input class="form-control form-control-color" type="color" name="settings[<?= e($key) ?>]" value="<?= e($map[$key] ?? '#0b3d91') ?>">
            </div>
        <?php endforeach; ?>
        <div class="col-md-6"><label class="form-label">School Logo</label><input class="form-control" type="file" name="school_logo" accept=".jpg,.jpeg,.jfif,.png,.webp,.gif"></div>
        <div class="col-md-6"><label class="form-label">Favicon</label><input class="form-control" type="file" name="favicon" accept=".ico,.jpg,.jpeg,.jfif,.png,.webp,.gif"></div>
    </div>
    <h2>Academic & Parent Portal Settings</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">Academic Year</label>
            <input class="form-control" name="settings[academic_year]" value="<?= e($map['academic_year'] ?? '2024/2025') ?>" placeholder="e.g. 2024/2025">
        </div>
        <div class="col-md-3">
            <label class="form-label">Current Term</label>
            <select class="form-select" name="settings[current_term]">
                <?php foreach (['First' => 'First Term', 'Second' => 'Second Term', 'Third' => 'Third Term'] as $val => $lbl): ?>
                    <option value="<?= e($val) ?>" <?= ($map['current_term'] ?? 'First') === $val ? 'selected' : '' ?>><?= e($lbl) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Parent Portal Access</label>
            <select class="form-select" name="settings[parent_portal_enabled]">
                <option value="1" <?= ($map['parent_portal_enabled'] ?? '1') === '1' ? 'selected' : '' ?>>Enabled</option>
                <option value="0" <?= ($map['parent_portal_enabled'] ?? '1') === '0' ? 'selected' : '' ?>>Disabled</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">SMS Gateway</label>
            <select class="form-select" name="settings[sms_gateway]">
                <option value="stub" <?= ($map['sms_gateway'] ?? 'stub') === 'stub' ? 'selected' : '' ?>>Log Stub / Simulation</option>
                <option value="termii" <?= ($map['sms_gateway'] ?? 'stub') === 'termii' ? 'selected' : '' ?>>Termii SMS API</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Termii API Key</label>
            <input class="form-control" name="settings[termii_api_key]" value="<?= e($map['termii_api_key'] ?? '') ?>" placeholder="API key from termii.com">
        </div>
        <div class="col-md-6">
            <label class="form-label">Termii Sender ID</label>
            <input class="form-control" name="settings[termii_sender_id]" value="<?= e($map['termii_sender_id'] ?? 'School') ?>" placeholder="Approved Termii Sender ID">
        </div>
    </div>
    <h2>Acceptance Letter</h2>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Letter Title</label>
            <input class="form-control" name="settings[admission_letter_title]" value="<?= e($map['admission_letter_title'] ?? 'Offer of Admission') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Signature Title</label>
            <input class="form-control" name="settings[admission_letter_signature_title]" value="<?= e($map['admission_letter_signature_title'] ?? 'Principal') ?>">
        </div>
        <div class="col-md-12">
            <label class="form-label">Main Letter Body</label>
            <textarea class="form-control" name="settings[admission_letter_body]" rows="5"><?= e($map['admission_letter_body'] ?? 'We are pleased to inform you that you have been offered admission into {class_name} at {school_name}.') ?></textarea>
            <div class="form-text">Available placeholders: {student_name}, {first_name}, {last_name}, {class_name}, {school_name}, {application_number}, {admission_number}, {date}</div>
        </div>
        <div class="col-md-12">
            <label class="form-label">Next Step / Instruction</label>
            <textarea class="form-control" name="settings[admission_letter_instruction]" rows="3"><?= e($map['admission_letter_instruction'] ?? 'Please report to the school office with original copies of your submitted documents.') ?></textarea>
        </div>
        <div class="col-md-12">
            <label class="form-label">Closing Message</label>
            <textarea class="form-control" name="settings[admission_letter_closing]" rows="2"><?= e($map['admission_letter_closing'] ?? 'Congratulations, and welcome to our academic community.') ?></textarea>
        </div>
    </div>
    <h2>Payment Gateways</h2>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label">Payment Mode</label>
            <select class="form-select" name="settings[payment_environment]">
                <?php foreach (['test' => 'Test / Sandbox', 'live' => 'Live / Production'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= ($map['payment_environment'] ?? 'test') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Use test keys first. Switch to live only after successful testing.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Active Gateway</label>
            <select class="form-select" name="settings[active_payment_gateway]">
                <?php foreach (['paystack' => 'Paystack', 'monnify' => 'Monnify'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= ($map['active_payment_gateway'] ?? 'paystack') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4"><label class="form-label">Paystack Public Key</label><input class="form-control" name="settings[paystack_public_key]" value="<?= e($map['paystack_public_key'] ?? '') ?>" placeholder="pk_test_... or pk_live_..."></div>
        <div class="col-md-4"><label class="form-label">Paystack Secret Key</label><input class="form-control" type="password" autocomplete="new-password" name="settings[paystack_secret_key]" value="<?= e($map['paystack_secret_key'] ?? '') ?>" placeholder="sk_test_... or sk_live_..."></div>
        <div class="col-md-4"><label class="form-label">Monnify API Key</label><input class="form-control" name="settings[monnify_api_key]" value="<?= e($map['monnify_api_key'] ?? '') ?>" placeholder="Sandbox or live API key"></div>
        <div class="col-md-4"><label class="form-label">Monnify Secret Key</label><input class="form-control" type="password" autocomplete="new-password" name="settings[monnify_secret_key]" value="<?= e($map['monnify_secret_key'] ?? '') ?>" placeholder="Sandbox or live secret key"></div>
        <div class="col-md-4"><label class="form-label">Monnify Contract Code</label><input class="form-control" name="settings[monnify_contract_code]" value="<?= e($map['monnify_contract_code'] ?? '') ?>" placeholder="Contract code"></div>
    </div>
    <button class="btn btn-primary mt-4">Save Settings</button>
</form>
