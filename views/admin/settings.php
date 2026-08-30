<?php
/**
 * views/admin/settings.php
 * School Settings — General & Website, Branding, Academic, Letters, Payments, Stats
 */
$map = settings_map();
$activeWebsite = school_website_url();
?>

<style>
.settings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 16px;
}
.settings-header h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}
.settings-header p {
    font-size: 0.875rem;
    color: #64748b;
    margin: 4px 0 0 0;
}

.settings-nav-tabs {
    display: flex;
    gap: 8px;
    border-bottom: 2px solid #e2e8f0;
    margin-bottom: 24px;
    overflow-x: auto;
    padding-bottom: 2px;
}
.settings-tab-btn {
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    border: none;
    background: transparent;
    color: #64748b;
    border-bottom: 3px solid transparent;
    margin-bottom: -2px;
    border-radius: 6px 6px 0 0;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    white-space: nowrap;
}
.settings-tab-btn:hover:not(.active) {
    color: #0b3d91;
    background: #f8fafc;
}
.settings-tab-btn.active {
    color: var(--brand-primary, #0b3d91);
    border-bottom-color: var(--brand-primary, #0b3d91);
    background: #eff6ff;
}

.settings-tab-panel {
    display: none;
}
.settings-tab-panel.active {
    display: block;
    animation: fadeIn 0.25s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}

.settings-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}
.settings-card-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.settings-card-title i {
    color: var(--brand-primary, #0b3d91);
}

.website-highlight-box {
    background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);
    border: 1.5px solid #bfdbfe;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 24px;
}
.website-preview-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #e0f2fe;
    color: #0369a1;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 8px;
    word-break: break-all;
}
</style>

<div class="settings-header">
    <div>
        <h1>School Settings</h1>
        <p>Manage institution profile, official website link, branding, session rules, and payment gateways.</p>
    </div>
    <div>
        <a href="<?= e($activeWebsite) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm">
            <i class="ti ti-world"></i> View School Website <i class="ti ti-external-link small ms-1"></i>
        </a>
    </div>
</div>

<form class="panel shadow-sm border-0 p-0" method="post" action="<?= url('admin/settings') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>

    <div class="settings-nav-tabs" id="settingsTabs" role="tablist">
        <button type="button" class="settings-tab-btn active" data-tab="tab-general">
            <i class="ti ti-school"></i> General &amp; Website
        </button>
        <button type="button" class="settings-tab-btn" data-tab="tab-branding">
            <i class="ti ti-palette"></i> Branding &amp; Visuals
        </button>
        <button type="button" class="settings-tab-btn" data-tab="tab-academic">
            <i class="ti ti-calendar-event"></i> Academic &amp; Portal
        </button>
        <button type="button" class="settings-tab-btn" data-tab="tab-admission-fees">
            <i class="ti ti-receipt"></i> Admission Fees &amp; Stats
        </button>
        <button type="button" class="settings-tab-btn" data-tab="tab-letter">
            <i class="ti ti-mail"></i> Acceptance Letter
        </button>
        <button type="button" class="settings-tab-btn" data-tab="tab-gateways">
            <i class="ti ti-credit-card"></i> Payment Gateways
        </button>
    </div>

    <!-- TAB 1: General & Website -->
    <div class="settings-tab-panel active" id="tab-general">
        <!-- Prominent School Website Card -->
        <div class="website-highlight-box">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                <div>
                    <h5 class="fw-bold text-primary mb-1">
                        <i class="ti ti-world me-1"></i> Official School Website (Designed URL)
                    </h5>
                    <p class="text-muted small mb-0">
                        Configure the destination URL for your official or custom designed school website. The <strong>"View School Website"</strong> button in the admin sidebar and portal links will redirect here.
                    </p>
                </div>
                <a href="<?= e($activeWebsite) ?>" id="previewWebBtn" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                    <i class="ti ti-external-link"></i> Test / Preview Website
                </a>
            </div>

            <div class="mt-3">
                <label class="form-label fw-semibold" for="school_website_input">Designed School Website URL</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="ti ti-link text-muted"></i></span>
                    <input type="text" 
                           id="school_website_input"
                           class="form-control form-control-lg fs-6" 
                           name="settings[school_website]" 
                           value="<?= e($map['school_website'] ?? $map['website'] ?? '') ?>" 
                           placeholder="https://www.your-school-website.com"
                           oninput="updateWebPreview(this.value)">
                </div>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2">
                    <div class="form-text text-muted">
                        Format: <code>https://www.yourschool.com</code> or <code>https://school.example.edu</code> (leave empty to use default portal landing).
                    </div>
                    <div>
                        <span class="website-preview-badge">
                            <i class="ti ti-check-circle"></i> Active Link: <span id="currentWebLabel" class="fw-bold ms-1"><?= e($activeWebsite) ?></span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-card">
            <h5 class="settings-card-title"><i class="ti ti-building"></i> School Profile Details</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">School Name</label>
                    <input class="form-control" name="settings[school_name]" value="<?= e($map['school_name'] ?? '') ?>" placeholder="e.g. Bluefield International School">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">School Motto</label>
                    <input class="form-control" name="settings[school_motto]" value="<?= e($map['school_motto'] ?? $map['motto'] ?? '') ?>" placeholder="e.g. Excellence, Character, and Innovation">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Official Phone Number</label>
                    <input class="form-control" name="settings[school_phone]" value="<?= e($map['school_phone'] ?? $map['phone'] ?? '') ?>" placeholder="+234 800 000 0000">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Official Email Address</label>
                    <input class="form-control" type="email" name="settings[school_email]" value="<?= e($map['school_email'] ?? $map['email'] ?? '') ?>" placeholder="info@school.test">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Principal / Head of School Name</label>
                    <input class="form-control" name="settings[principal_name]" value="<?= e($map['principal_name'] ?? '') ?>" placeholder="Mrs. Adeola Johnson">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Campus Address</label>
                    <input class="form-control" name="settings[school_address]" value="<?= e($map['school_address'] ?? $map['address'] ?? '') ?>" placeholder="No. 1 Excellence Avenue, Lagos, Nigeria">
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 2: Branding & Visuals -->
    <div class="settings-tab-panel" id="tab-branding">
        <div class="settings-card">
            <h5 class="settings-card-title"><i class="ti ti-photo"></i> School Logos &amp; Icons</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">School Logo</label>
                    <input class="form-control" type="file" name="school_logo" accept=".jpg,.jpeg,.jfif,.png,.webp,.gif">
                    <?php if (!empty($map['school_logo']) || !empty($map['logo'])): ?>
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <span class="small text-muted">Current Logo:</span>
                            <img src="<?= url('uploads/' . ($map['school_logo'] ?? $map['logo'])) ?>" alt="Logo" style="height: 38px; border-radius: 6px; border: 1px solid #e2e8f0; background: #fff; padding: 2px;">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Browser Favicon</label>
                    <input class="form-control" type="file" name="favicon" accept=".ico,.jpg,.jpeg,.jfif,.png,.webp,.gif">
                    <?php if (!empty($map['favicon'])): ?>
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <span class="small text-muted">Current Favicon:</span>
                            <img src="<?= url('uploads/' . $map['favicon']) ?>" alt="Favicon" style="height: 24px; width: 24px; border-radius: 4px; border: 1px solid #e2e8f0; background: #fff; padding: 2px;">
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="settings-card">
            <h5 class="settings-card-title"><i class="ti ti-color-swatch"></i> Color Palette &amp; Theme</h5>
            <?php 
            $colors = [
                'primary_color' => 'Primary Brand Color', 
                'secondary_color' => 'Secondary Accent Color', 
                'sidebar_color' => 'Sidebar Background Color', 
                'button_color' => 'Button Accent Color', 
                'dashboard_color' => 'Dashboard Header Color'
            ]; 
            ?>
            <div class="row g-3">
                <?php foreach ($colors as $key => $label): ?>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold"><?= e($label) ?></label>
                        <div class="input-group">
                            <input class="form-control form-control-color" type="color" name="settings[<?= e($key) ?>]" value="<?= e($map[$key] ?? '#0b3d91') ?>">
                            <input type="text" class="form-control form-control-sm text-uppercase" value="<?= e($map[$key] ?? '#0b3d91') ?>" readonly style="max-width: 100px;">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- TAB 3: Academic & Portal -->
    <div class="settings-tab-panel" id="tab-academic">
        <div class="settings-card">
            <h5 class="settings-card-title"><i class="ti ti-calendar"></i> Academic Session &amp; Term</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Academic Year / Session</label>
                    <input class="form-control" name="settings[academic_year]" value="<?= e($map['academic_year'] ?? $map['academic_session'] ?? '2024/2025') ?>" placeholder="e.g. 2024/2025">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Current Active Term</label>
                    <select class="form-select" name="settings[current_term]">
                        <?php foreach (['First' => 'First Term', 'Second' => 'Second Term', 'Third' => 'Third Term'] as $val => $lbl): ?>
                            <option value="<?= e($val) ?>" <?= ($map['current_term'] ?? $map['term'] ?? 'First') === $val ? 'selected' : '' ?>><?= e($lbl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Parent Portal Access</label>
                    <select class="form-select" name="settings[parent_portal_enabled]">
                        <option value="1" <?= ($map['parent_portal_enabled'] ?? '1') === '1' ? 'selected' : '' ?>>Enabled (Allow Parent Logins)</option>
                        <option value="0" <?= ($map['parent_portal_enabled'] ?? '1') === '0' ? 'selected' : '' ?>>Disabled (Maintenance)</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="settings-card">
            <h5 class="settings-card-title"><i class="ti ti-message-dots"></i> SMS Gateway Notifications</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">SMS Provider Gateway</label>
                    <select class="form-select" name="settings[sms_gateway]">
                        <option value="stub" <?= ($map['sms_gateway'] ?? 'stub') === 'stub' ? 'selected' : '' ?>>Log Stub / Simulation Mode</option>
                        <option value="termii" <?= ($map['sms_gateway'] ?? 'stub') === 'termii' ? 'selected' : '' ?>>Termii SMS API (Live)</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Termii API Key</label>
                    <input class="form-control" name="settings[termii_api_key]" value="<?= e($map['termii_api_key'] ?? '') ?>" placeholder="API key from termii.com">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Termii Sender ID</label>
                    <input class="form-control" name="settings[termii_sender_id]" value="<?= e($map['termii_sender_id'] ?? 'School') ?>" placeholder="Approved Termii Sender ID">
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 4: Admission Fees & Stats -->
    <div class="settings-tab-panel" id="tab-admission-fees">
        <div class="settings-card">
            <h5 class="settings-card-title"><i class="ti ti-cash"></i> Admission &amp; Enrollment Fees (₦)</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Admission Form Fee</label>
                    <input class="form-control" type="number" name="settings[admission_fee]" value="<?= e($map['admission_fee'] ?? '0') ?>" placeholder="e.g. 5000">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Acceptance Fee</label>
                    <input class="form-control" type="number" name="settings[acceptance_fee]" value="<?= e($map['acceptance_fee'] ?? '0') ?>" placeholder="e.g. 25000">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Enrollment Fee</label>
                    <input class="form-control" type="number" name="settings[enrollment_fee]" value="<?= e($map['enrollment_fee'] ?? '0') ?>" placeholder="e.g. 50000">
                </div>
            </div>
        </div>

        <div class="settings-card">
            <h5 class="settings-card-title"><i class="ti ti-chart-bar"></i> Public Website Display Metrics</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Students Admitted</label>
                    <input class="form-control" name="settings[students_admitted]" value="<?= e($map['students_admitted'] ?? '1,250') ?>" placeholder="1,250">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Success Rate</label>
                    <input class="form-control" name="settings[success_rate]" value="<?= e($map['success_rate'] ?? '96%') ?>" placeholder="96%">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Graduates Count</label>
                    <input class="form-control" name="settings[graduates]" value="<?= e($map['graduates'] ?? '800+') ?>" placeholder="800+">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Available Spaces</label>
                    <input class="form-control" name="settings[available_spaces]" value="<?= e($map['available_spaces'] ?? '120') ?>" placeholder="120">
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 5: Acceptance Letter -->
    <div class="settings-tab-panel" id="tab-letter">
        <div class="settings-card">
            <h5 class="settings-card-title"><i class="ti ti-file-text"></i> Admission Offer Letter Configuration</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Letter Title</label>
                    <input class="form-control" name="settings[admission_letter_title]" value="<?= e($map['admission_letter_title'] ?? 'Offer of Admission') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Signatory Title</label>
                    <input class="form-control" name="settings[admission_letter_signature_title]" value="<?= e($map['admission_letter_signature_title'] ?? 'Principal') ?>">
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Main Letter Body</label>
                    <textarea class="form-control" name="settings[admission_letter_body]" rows="4"><?= e($map['admission_letter_body'] ?? 'We are pleased to inform you that you have been offered admission into {class_name} at {school_name}.') ?></textarea>
                    <div class="form-text text-muted">Available placeholders: <code>{student_name}</code>, <code>{first_name}</code>, <code>{last_name}</code>, <code>{class_name}</code>, <code>{school_name}</code>, <code>{application_number}</code>, <code>{admission_number}</code>, <code>{date}</code></div>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Next Step / Instruction</label>
                    <textarea class="form-control" name="settings[admission_letter_instruction]" rows="2"><?= e($map['admission_letter_instruction'] ?? 'Please report to the school office with original copies of your submitted documents.') ?></textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label fw-semibold">Closing Message</label>
                    <textarea class="form-control" name="settings[admission_letter_closing]" rows="2"><?= e($map['admission_letter_closing'] ?? 'Congratulations, and welcome to our academic community.') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 6: Payment Gateways -->
    <div class="settings-tab-panel" id="tab-gateways">
        <div class="settings-card">
            <h5 class="settings-card-title"><i class="ti ti-lock"></i> Payment Gateway Credentials</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Payment Mode</label>
                    <select class="form-select" name="settings[payment_environment]">
                        <?php foreach (['test' => 'Test / Sandbox Mode', 'live' => 'Live / Production Mode'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= ($map['payment_environment'] ?? 'test') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text text-muted">Use test mode keys first. Switch to live once payments are tested.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Active Gateway Provider</label>
                    <select class="form-select" name="settings[active_payment_gateway]">
                        <?php foreach (['paystack' => 'Paystack Gateway', 'monnify' => 'Monnify Gateway'] as $value => $label): ?>
                            <option value="<?= e($value) ?>" <?= ($map['active_payment_gateway'] ?? 'paystack') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12"><hr class="my-2"></div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Paystack Public Key</label>
                    <input class="form-control" name="settings[paystack_public_key]" value="<?= e($map['paystack_public_key'] ?? '') ?>" placeholder="pk_test_... or pk_live_...">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Paystack Secret Key</label>
                    <input class="form-control" type="password" autocomplete="new-password" name="settings[paystack_secret_key]" value="<?= e($map['paystack_secret_key'] ?? '') ?>" placeholder="sk_test_... or sk_live_...">
                </div>

                <div class="col-12"><hr class="my-2"></div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Monnify API Key</label>
                    <input class="form-control" name="settings[monnify_api_key]" value="<?= e($map['monnify_api_key'] ?? '') ?>" placeholder="Sandbox or live API key">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Monnify Secret Key</label>
                    <input class="form-control" type="password" autocomplete="new-password" name="settings[monnify_secret_key]" value="<?= e($map['monnify_secret_key'] ?? '') ?>" placeholder="Sandbox or live secret key">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Monnify Contract Code</label>
                    <input class="form-control" name="settings[monnify_contract_code]" value="<?= e($map['monnify_contract_code'] ?? '') ?>" placeholder="Contract code">
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-between pt-3 border-top mt-4">
        <span class="text-muted small"><i class="ti ti-info-circle"></i> Changes will take effect immediately upon saving.</span>
        <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
            <i class="ti ti-device-floppy me-1"></i> Save Settings
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.settings-tab-btn');
    const panels = document.querySelectorAll('.settings-tab-panel');

    function switchTab(targetId) {
        tabs.forEach(t => t.classList.toggle('active', t.getAttribute('data-tab') === targetId));
        panels.forEach(p => p.classList.toggle('active', p.id === targetId));
        if (history.replaceState) {
            history.replaceState(null, null, '#' + targetId);
        }
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const target = this.getAttribute('data-tab');
            switchTab(target);
        });
    });

    const hash = window.location.hash.replace('#', '');
    if (hash && document.getElementById(hash)) {
        switchTab(hash);
    }
});

function updateWebPreview(val) {
    val = (val || '').trim();
    let previewUrl = val;
    if (previewUrl) {
        if (!/^https?:\/\//i.test(previewUrl) && !previewUrl.startsWith('/')) {
            previewUrl = 'https://' + previewUrl;
        }
    } else {
        previewUrl = '<?= e($activeWebsite) ?>';
    }
    const label = document.getElementById('currentWebLabel');
    if (label) label.textContent = previewUrl;
    const btn = document.getElementById('previewWebBtn');
    if (btn) btn.href = previewUrl;
}
</script>
