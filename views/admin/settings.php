<?php
/**
 * views/admin/settings.php
 * School Settings — Unified Single-Page Document ("All-on-One-Paper") Layout
 */
$map = settings_map();
$activeWebsite = school_website_url();
?>

<style>
/* Modern Unified Settings Paper Layout */
.settings-paper-wrapper {
    max-width: 100%;
    margin: 0 auto;
    padding-bottom: 60px;
}

.settings-header-banner {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 24px 28px;
    margin-bottom: 24px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.settings-header-banner h1 {
    font-size: 1.6rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    letter-spacing: -0.02em;
}

.settings-header-banner p {
    font-size: 0.9rem;
    color: #64748b;
    margin: 6px 0 0 0;
}

/* Quick Jump Anchors (No Horizontal Scrolling, Clean Flex Wrap) */
.settings-quicknav {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 24px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

.settings-quicknav-label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #64748b;
    margin-right: 4px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.settings-quicknav-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.settings-quicknav-link:hover {
    background: #eff6ff;
    border-color: #3b82f6;
    color: #1d4ed8;
    transform: translateY(-1px);
}

/* Section Paper Cards */
.settings-section-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 26px 28px;
    margin-bottom: 24px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    transition: box-shadow 0.2s ease;
    scroll-margin-top: 20px;
}

.settings-section-card:hover {
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.05);
}

.settings-section-header {
    border-bottom: 1.5px solid #f1f5f9;
    padding-bottom: 16px;
    margin-bottom: 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.settings-section-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.settings-section-title i {
    color: var(--brand-primary, #0b3d91);
    font-size: 1.3rem;
}

.settings-section-desc {
    font-size: 0.85rem;
    color: #64748b;
    margin-top: 4px;
    margin-bottom: 0;
}

.website-highlight-box {
    background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);
    border: 1.5px solid #bfdbfe;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 20px;
}

.website-preview-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #e0f2fe;
    color: #0369a1;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 8px;
    word-break: break-all;
}

/* Sticky Action Footer */
.settings-sticky-footer {
    position: sticky;
    bottom: 20px;
    background: rgba(255, 255, 255, 0.94);
    backdrop-filter: blur(10px);
    border: 1.5px solid #cbd5e1;
    border-radius: 14px;
    padding: 16px 24px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    z-index: 100;
    margin-top: 32px;
}
</style>

<div class="settings-paper-wrapper">

    <!-- Top Banner -->
    <div class="settings-header-banner">
        <div>
            <h1><i class="ti ti-settings-cog text-primary me-2"></i>School Settings &amp; Configuration</h1>
            <p>Manage institution profile, public website link, visuals, outgoing SMTP email, academic terms, and payment credentials on one unified page.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="<?= e($activeWebsite) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary fw-semibold">
                <i class="ti ti-world me-1"></i> View School Website <i class="ti ti-external-link small ms-1"></i>
            </a>
            <button type="button" onclick="document.getElementById('settingsMainForm').requestSubmit()" class="btn btn-primary px-4 fw-bold">
                <i class="ti ti-device-floppy me-1"></i> Save Changes
            </button>
        </div>
    </div>

    <!-- Quick Jump Links (Pill list with flex-wrap, 100% visible, no horizontal scroll) -->
    <div class="settings-quicknav">
        <span class="settings-quicknav-label"><i class="ti ti-compass"></i> Jump to:</span>
        <a href="#sec-website" class="settings-quicknav-link"><i class="ti ti-world"></i> Website &amp; Profile</a>
        <a href="#sec-branding" class="settings-quicknav-link"><i class="ti ti-palette"></i> Branding &amp; Colors</a>
        <a href="#sec-smtp" class="settings-quicknav-link"><i class="ti ti-mail-fast"></i> Email &amp; SMTP</a>
        <a href="#sec-academic" class="settings-quicknav-link"><i class="ti ti-calendar"></i> Academic &amp; Portal</a>
        <a href="#sec-sms" class="settings-quicknav-link"><i class="ti ti-message-dots"></i> SMS Gateway</a>
        <a href="#sec-fees" class="settings-quicknav-link"><i class="ti ti-receipt"></i> Fees &amp; Stats</a>
        <a href="#sec-letter" class="settings-quicknav-link"><i class="ti ti-file-text"></i> Offer Letter</a>
        <a href="#sec-gateways" class="settings-quicknav-link"><i class="ti ti-credit-card"></i> Payment Gateways</a>
    </div>

    <form id="settingsMainForm" method="post" action="<?= url('admin/settings') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <!-- ==========================================
             SECTION 1: Official Website & Profile
        =========================================== -->
        <div class="settings-section-card" id="sec-website">
            <div class="settings-section-header">
                <div>
                    <h2 class="settings-section-title"><i class="ti ti-world"></i> Official Website &amp; School Profile</h2>
                    <p class="settings-section-desc">Manage institution identity, contact details, and the designed website link.</p>
                </div>
            </div>

            <!-- Prominent Designed Website Box -->
            <div class="website-highlight-box">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                    <div>
                        <h5 class="fw-bold text-primary mb-1">
                            <i class="ti ti-link me-1"></i> Official Designed School Website URL
                        </h5>
                        <p class="text-muted small mb-0">
                            Configure the destination URL for your official designed school website. The <strong>"View School Website"</strong> button across portals redirects here.
                        </p>
                    </div>
                    <a href="<?= e($activeWebsite) ?>" id="previewWebBtn" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-primary">
                        <i class="ti ti-external-link me-1"></i> Test / Preview Link
                    </a>
                </div>

                <div class="mt-3">
                    <label class="form-label fw-semibold" for="school_website_input">Designed School Website URL</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="ti ti-world text-muted"></i></span>
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
                            Format: <code>https://www.yourschool.com</code> (leave empty to use default portal landing).
                        </div>
                        <div>
                            <span class="website-preview-badge">
                                <i class="ti ti-check-circle"></i> Active Link: <span id="currentWebLabel" class="fw-bold ms-1"><?= e($activeWebsite) ?></span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Details Grid -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">School Name</label>
                    <input class="form-control" name="settings[school_name]" value="<?= e($map['school_name'] ?? '') ?>" placeholder="e.g. My Future My Pride Model School">
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
                    <input class="form-control" type="email" name="settings[school_email]" value="<?= e($map['school_email'] ?? $map['email'] ?? '') ?>" placeholder="info@school.com">
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

        <!-- ==========================================
             SECTION 2: Branding & Visuals
        =========================================== -->
        <div class="settings-section-card" id="sec-branding">
            <div class="settings-section-header">
                <div>
                    <h2 class="settings-section-title"><i class="ti ti-palette"></i> Branding &amp; Visual Appearance</h2>
                    <p class="settings-section-desc">Upload school emblems, browser favicon, and customize the interface color palette.</p>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">School Logo</label>
                    <input class="form-control" type="file" name="school_logo" accept=".jpg,.jpeg,.jfif,.png,.webp,.gif">
                    <?php if (!empty($map['school_logo']) || !empty($map['logo'])): ?>
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <span class="small text-muted">Current Logo:</span>
                            <img src="<?= url('uploads/' . ($map['school_logo'] ?? $map['logo'])) ?>" alt="Logo" style="height: 38px; max-height: 38px; width: auto; max-width: 120px; object-fit: contain; border-radius: 6px; border: 1px solid #e2e8f0; background: #fff; padding: 2px;">
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

            <h6 class="fw-bold text-secondary mb-3"><i class="ti ti-color-swatch me-1"></i> Color Palette &amp; Theme Tokens</h6>
            <?php 
            $colors = [
                'primary_color' => 'Primary Brand Color', 
                'secondary_color' => 'Secondary Accent Color', 
                'sidebar_color' => 'Sidebar Background Color', 
                'button_color' => 'Button Accent Color', 
                'dashboard_color' => 'Dashboard Header Color'
            ]; 
            ?>
            <div class="row g-3 mb-4">
                <?php foreach ($colors as $key => $label): ?>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-secondary"><?= e($label) ?></label>
                        <div class="input-group">
                            <input class="form-control form-control-color" type="color" name="settings[<?= e($key) ?>]" value="<?= e($map[$key] ?? '#0b3d91') ?>" onchange="this.nextElementSibling.value=this.value.toUpperCase()">
                            <input type="text" class="form-control form-control-sm text-uppercase font-monospace" value="<?= e($map[$key] ?? '#0b3d91') ?>" readonly style="max-width: 110px;">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Student ID Card Color Theming -->
            <div class="p-3 rounded-3 border bg-light mt-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
                    <div>
                        <h6 class="fw-bold text-dark mb-1"><i class="ti ti-id text-primary me-1"></i> Student ID Card Theme &amp; Colors</h6>
                        <p class="small text-muted mb-0">Choose official colors for Student ID Cards across printouts, Student Portals, and Parent Portals.</p>
                    </div>
                    <div>
                        <a href="<?= url('admin/applications') ?>" class="btn btn-sm btn-outline-primary"><i class="ti ti-external-link me-1"></i> Preview on Student Card</a>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-secondary">ID Card Primary Accent</label>
                        <div class="input-group">
                            <input class="form-control form-control-color" type="color" id="settingsIdPrimary" name="settings[id_card_primary_color]" value="<?= e($map['id_card_primary_color'] ?? $map['primary_color'] ?? '#0b3d91') ?>" onchange="this.nextElementSibling.value=this.value.toUpperCase(); updateSettingsIdPreview();">
                            <input type="text" class="form-control form-control-sm text-uppercase font-monospace" value="<?= e($map['id_card_primary_color'] ?? $map['primary_color'] ?? '#0b3d91') ?>" readonly style="max-width: 110px;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-secondary">ID Card Secondary Wave Accent</label>
                        <div class="input-group">
                            <input class="form-control form-control-color" type="color" id="settingsIdSecondary" name="settings[id_card_secondary_color]" value="<?= e($map['id_card_secondary_color'] ?? $map['secondary_color'] ?? '#1e40af') ?>" onchange="this.nextElementSibling.value=this.value.toUpperCase(); updateSettingsIdPreview();">
                            <input type="text" class="form-control form-control-sm text-uppercase font-monospace" value="<?= e($map['id_card_secondary_color'] ?? $map['secondary_color'] ?? '#1e40af') ?>" readonly style="max-width: 110px;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small text-secondary">ID Card Header Dark Base</label>
                        <div class="input-group">
                            <input class="form-control form-control-color" id="settingsIdHeader" name="settings[id_card_header_bg]" value="<?= e($map['id_card_header_bg'] ?? '#0f172a') ?>" onchange="this.nextElementSibling.value=this.value.toUpperCase(); updateSettingsIdPreview();">
                            <input type="text" class="form-control form-control-sm text-uppercase font-monospace" value="<?= e($map['id_card_header_bg'] ?? '#0f172a') ?>" readonly style="max-width: 110px;">
                        </div>
                    </div>
                </div>

                <!-- 1-Click ID Card Color Presets -->
                <div class="mt-3 pt-3 border-top d-flex align-items-center flex-wrap gap-2">
                    <span class="small fw-semibold text-secondary me-2">Quick Presets:</span>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" onclick="applySettingsIdPreset('#0b3d91','#1e40af','#0f172a')"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#0b3d91;margin-right:4px;"></span>Navy Blue</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" onclick="applySettingsIdPreset('#047857','#10b981','#064e3b')"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#047857;margin-right:4px;"></span>Emerald Green</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" onclick="applySettingsIdPreset('#6b21a8','#9333ea','#3b0764')"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#6b21a8;margin-right:4px;"></span>Royal Purple</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" onclick="applySettingsIdPreset('#991b1b','#dc2626','#450a0a')"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#991b1b;margin-right:4px;"></span>Crimson Red</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" onclick="applySettingsIdPreset('#b45309','#f59e0b','#451a03')"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#b45309;margin-right:4px;"></span>Gold Amber</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" onclick="applySettingsIdPreset('#0f766e','#06b6d4','#134e4a')"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#0f766e;margin-right:4px;"></span>Deep Teal</button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" onclick="applySettingsIdPreset('#334155','#64748b','#0f172a')"><span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:#334155;margin-right:4px;"></span>Slate Onyx</button>
                    <button type="button" class="btn btn-xs btn-primary rounded-pill ms-auto" onclick="syncSettingsIdWithBrand()"><i class="ti ti-wand me-1"></i>Sync with School Primary Color</button>
                </div>
            </div>
        </div>

        <!-- ==========================================
             SECTION 3: Outgoing Email & SMTP
        =========================================== -->
        <div class="settings-section-card" id="sec-smtp">
            <div class="settings-section-header">
                <div>
                    <h2 class="settings-section-title"><i class="ti ti-mail-fast"></i> Outgoing Email &amp; SMTP Configuration</h2>
                    <p class="settings-section-desc">Configure your custom SMTP mail server for automated student notifications, receipts, application updates, and offer letters.</p>
                </div>
                <div>
                    <?php if (!empty($map['smtp_host'])): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill font-monospace">
                            <i class="ti ti-circle-check-filled me-1"></i> SMTP Configured
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill font-monospace">
                            <i class="ti ti-alert-circle me-1"></i> Default / PHP Mail
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Presets Toolbar -->
            <div class="p-3 bg-light rounded-3 mb-4 border">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <span class="small fw-semibold text-secondary"><i class="ti ti-wand me-1"></i> Auto-Fill Presets:</span>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary bg-white" onclick="applySmtpPreset('cpanel')">
                            <i class="ti ti-server me-1"></i> cPanel / Webmail (SSL 465)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary bg-white" onclick="applySmtpPreset('gmail')">
                            <i class="ti ti-brand-google me-1"></i> Gmail / Workspace (TLS 587)
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary bg-white" onclick="applySmtpPreset('office365')">
                            <i class="ti ti-brand-windows me-1"></i> Microsoft 365 (TLS 587)
                        </button>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">SMTP Host / Server Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="ti ti-server text-muted"></i></span>
                        <input type="text" id="smtp_host" class="form-control" name="settings[smtp_host]" value="<?= e($map['smtp_host'] ?? SMTP_HOST ?? '') ?>" placeholder="mail.yourdomain.com or smtp.gmail.com">
                    </div>
                    <div class="form-text text-muted">Outgoing mail server host (e.g. <code>mail.yourdomain.com</code>).</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">SMTP Port</label>
                    <input type="number" id="smtp_port" class="form-control" name="settings[smtp_port]" value="<?= e($map['smtp_port'] ?? (string)SMTP_PORT ?: '465') ?>" placeholder="465">
                    <div class="form-text text-muted">Standard: <strong>465</strong> (SSL) or <strong>587</strong> (TLS)</div>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Encryption Protocol</label>
                    <select id="smtp_secure" class="form-select" name="settings[smtp_secure]">
                        <?php 
                        $curSec = strtolower($map['smtp_secure'] ?? SMTP_SECURE ?? 'smtps');
                        $secOptions = [
                            'smtps' => 'SSL / TLS (smtps - Port 465)',
                            'tls'   => 'STARTTLS (tls - Port 587)',
                            'none'  => 'None (Unencrypted)'
                        ];
                        foreach ($secOptions as $k => $lbl):
                        ?>
                            <option value="<?= e($k) ?>" <?= ($curSec === $k || ($k === 'smtps' && $curSec === 'ssl')) ? 'selected' : '' ?>><?= e($lbl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">SMTP Username / Login Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="ti ti-user text-muted"></i></span>
                        <input type="text" id="smtp_username" class="form-control" name="settings[smtp_username]" value="<?= e($map['smtp_username'] ?? SMTP_USERNAME ?? '') ?>" placeholder="support@yourdomain.com">
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">SMTP Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="ti ti-key text-muted"></i></span>
                        <input type="password" id="smtp_password" class="form-control" name="settings[smtp_password]" value="<?= e($map['smtp_password'] ?? SMTP_PASSWORD ?? '') ?>" placeholder="Enter email account password" autocomplete="new-password">
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('smtp_password', this)">
                            <i class="ti ti-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">From / Sender Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="ti ti-at text-muted"></i></span>
                        <input type="email" id="smtp_from_email" class="form-control" name="settings[smtp_from_email]" value="<?= e($map['smtp_from_email'] ?? SMTP_FROM_EMAIL ?? ($map['school_email'] ?? '')) ?>" placeholder="support@yourdomain.com">
                    </div>
                    <div class="form-text text-muted">The sender address displayed on outgoing emails.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">From / Sender Display Name</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="ti ti-signature text-muted"></i></span>
                        <input type="text" id="smtp_from_name" class="form-control" name="settings[smtp_from_name]" value="<?= e($map['smtp_from_name'] ?? SMTP_FROM_NAME ?? ($map['school_name'] ?? APP_NAME)) ?>" placeholder="My Future My Pride Model School">
                    </div>
                </div>
            </div>

            <!-- Live Diagnostic & Test Email Card -->
            <div class="p-3 mt-4 rounded-3 border border-primary-subtle" style="background: linear-gradient(135deg, #f8faff 0%, #ffffff 100%);">
                <h6 class="fw-bold text-primary mb-1"><i class="ti ti-send me-1"></i> Send Diagnostic Test Email</h6>
                <p class="text-muted small mb-3">Send an immediate test email to verify your SMTP authentication and outbound delivery in real-time.</p>

                <div class="row g-3 align-items-end">
                    <div class="col-md-7">
                        <label class="form-label fw-semibold small">Recipient Email Address for Test</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="ti ti-mail text-muted"></i></span>
                            <input type="email" id="test_smtp_recipient" class="form-control" value="<?= e($_SESSION['admin']['email'] ?? 'admin@school.com') ?>" placeholder="recipient@example.com">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <button type="button" id="btnTestSmtp" class="btn btn-primary w-100 py-2 fw-semibold" onclick="runSmtpTest()">
                            <i class="ti ti-send me-1"></i> Test SMTP Connection
                        </button>
                    </div>
                </div>

                <!-- Live Result Container -->
                <div id="smtpTestResult" class="mt-3" style="display:none;"></div>
            </div>
        </div>

        <!-- ==========================================
             SECTION 4: Academic Session & Portal
        =========================================== -->
        <div class="settings-section-card" id="sec-academic">
            <div class="settings-section-header">
                <div>
                    <h2 class="settings-section-title"><i class="ti ti-calendar"></i> Academic Session &amp; Portal Rules</h2>
                    <p class="settings-section-desc">Control active academic session, active school term, and student/parent access controls.</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Academic Year / Session</label>
                    <input class="form-control" name="settings[academic_year]" value="<?= e($map['academic_year'] ?? $map['academic_session'] ?? '2025/2026') ?>" placeholder="e.g. 2025/2026">
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

        <!-- ==========================================
             SECTION 5: SMS Gateway Notifications
        =========================================== -->
        <div class="settings-section-card" id="sec-sms">
            <div class="settings-section-header">
                <div>
                    <h2 class="settings-section-title"><i class="ti ti-message-dots"></i> SMS Gateway Notifications</h2>
                    <p class="settings-section-desc">Configure Termii or stub SMS gateway for instant guardian arrival and checkout alerts.</p>
                </div>
            </div>

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

        <!-- ==========================================
             SECTION 6: Admission Fees & Public Stats
        =========================================== -->
        <div class="settings-section-card" id="sec-fees">
            <div class="settings-section-header">
                <div>
                    <h2 class="settings-section-title"><i class="ti ti-receipt"></i> Admission Fees &amp; Public Metrics</h2>
                    <p class="settings-section-desc">Manage standard admission form fee, acceptance fee, and website showcase counters.</p>
                </div>
            </div>

            <h6 class="fw-bold text-secondary mb-3"><i class="ti ti-cash me-1"></i> Admission &amp; Enrollment Fees (₦)</h6>
            <div class="row g-3 mb-4">
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

            <h6 class="fw-bold text-secondary mb-3"><i class="ti ti-chart-bar me-1"></i> Public Website Display Metrics</h6>
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

        <!-- ==========================================
             SECTION 7: Acceptance / Admission Letter
        =========================================== -->
        <div class="settings-section-card" id="sec-letter">
            <div class="settings-section-header">
                <div>
                    <h2 class="settings-section-title"><i class="ti ti-file-text"></i> Admission Offer Letter Template</h2>
                    <p class="settings-section-desc">Customize the official admission letter generated for accepted applicants.</p>
                </div>
            </div>

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

        <!-- ==========================================
             SECTION 8: Payment Gateways
        =========================================== -->
        <div class="settings-section-card" id="sec-gateways">
            <div class="settings-section-header">
                <div>
                    <h2 class="settings-section-title"><i class="ti ti-credit-card"></i> Payment Gateway Credentials</h2>
                    <p class="settings-section-desc">Manage API credentials for Paystack and Monnify online school payment processing.</p>
                </div>
            </div>

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
                    <div class="input-group">
                        <input class="form-control" type="password" id="paystack_secret_key" autocomplete="new-password" name="settings[paystack_secret_key]" value="<?= e($map['paystack_secret_key'] ?? '') ?>" placeholder="sk_test_... or sk_live_...">
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('paystack_secret_key', this)">
                            <i class="ti ti-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="col-12"><hr class="my-2"></div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Monnify API Key</label>
                    <input class="form-control" name="settings[monnify_api_key]" value="<?= e($map['monnify_api_key'] ?? '') ?>" placeholder="Sandbox or live API key">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Monnify Secret Key</label>
                    <div class="input-group">
                        <input class="form-control" type="password" id="monnify_secret_key" autocomplete="new-password" name="settings[monnify_secret_key]" value="<?= e($map['monnify_secret_key'] ?? '') ?>" placeholder="Sandbox or live secret key">
                        <button type="button" class="btn btn-outline-secondary" onclick="togglePasswordVisibility('monnify_secret_key', this)">
                            <i class="ti ti-eye"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Monnify Contract Code</label>
                    <input class="form-control" name="settings[monnify_contract_code]" value="<?= e($map['monnify_contract_code'] ?? '') ?>" placeholder="Contract code">
                </div>
            </div>
        </div>

        <!-- Sticky Bottom Save Bar -->
        <div class="settings-sticky-footer">
            <div>
                <span class="fw-bold text-dark"><i class="ti ti-info-circle me-1 text-primary"></i> Ready to save changes?</span>
                <span class="text-muted small ms-2 d-none d-md-inline">All modified parameters across sections will be updated immediately.</span>
            </div>
            <button type="submit" class="btn btn-primary btn-lg px-5 py-2 fw-bold shadow-sm">
                <i class="ti ti-device-floppy me-1"></i> Save All Settings
            </button>
        </div>

    </form>
</div>

<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const isPassword = input.type === 'password';
    input.type = isPassword ? 'text' : 'password';
    const icon = btn.querySelector('i');
    if (icon) {
        icon.className = isPassword ? 'ti ti-eye-off' : 'ti ti-eye';
    }
}

function applySmtpPreset(type) {
    const hostEl = document.getElementById('smtp_host');
    const portEl = document.getElementById('smtp_port');
    const secEl = document.getElementById('smtp_secure');

    if (type === 'cpanel') {
        const domain = window.location.hostname || 'yourdomain.com';
        if (hostEl && (!hostEl.value || hostEl.value.includes('smtp.') || hostEl.value === 'localhost')) {
            hostEl.value = 'mail.' + domain;
        }
        if (portEl) portEl.value = '465';
        if (secEl) secEl.value = 'smtps';
    } else if (type === 'gmail') {
        if (hostEl) hostEl.value = 'smtp.gmail.com';
        if (portEl) portEl.value = '587';
        if (secEl) secEl.value = 'tls';
    } else if (type === 'office365') {
        if (hostEl) hostEl.value = 'smtp.office365.com';
        if (portEl) portEl.value = '587';
        if (secEl) secEl.value = 'tls';
    }
}

function runSmtpTest() {
    const btn = document.getElementById('btnTestSmtp');
    const resultBox = document.getElementById('smtpTestResult');
    const recipient = (document.getElementById('test_smtp_recipient')?.value || '').trim();

    if (!recipient) {
        alert('Please enter a recipient email address to send the test email.');
        return;
    }

    const host = document.getElementById('smtp_host')?.value || '';
    const port = document.getElementById('smtp_port')?.value || '465';
    const secure = document.getElementById('smtp_secure')?.value || 'smtps';
    const user = document.getElementById('smtp_username')?.value || '';
    const pass = document.getElementById('smtp_password')?.value || '';
    const fromEmail = document.getElementById('smtp_from_email')?.value || '';
    const fromName = document.getElementById('smtp_from_name')?.value || '';

    // Extract CSRF token
    const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Testing SMTP Connection...';
    resultBox.style.display = 'none';
    resultBox.innerHTML = '';

    const formData = new FormData();
    formData.append('csrf_token', csrfToken);
    formData.append('ajax', '1');
    formData.append('test_email', recipient);
    formData.append('smtp_host', host);
    formData.append('smtp_port', port);
    formData.append('smtp_secure', secure);
    formData.append('smtp_username', user);
    formData.append('smtp_password', pass);
    formData.append('smtp_from_email', fromEmail);
    formData.append('smtp_from_name', fromName);

    fetch('<?= url("admin/settings/test-smtp") ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-send me-1"></i> Test SMTP Connection';
        resultBox.style.display = 'block';

        if (data.success) {
            resultBox.innerHTML = `
                <div class="alert alert-success d-flex align-items-start gap-2 shadow-sm mb-0">
                    <i class="ti ti-circle-check-filled fs-4 text-success mt-1"></i>
                    <div>
                        <h6 class="fw-bold mb-1">SMTP Connection Verified!</h6>
                        <p class="mb-0 small">${escapeHtml(data.message)}</p>
                    </div>
                </div>
            `;
        } else {
            resultBox.innerHTML = `
                <div class="alert alert-danger d-flex align-items-start gap-2 shadow-sm mb-0">
                    <i class="ti ti-alert-triangle-filled fs-4 text-danger mt-1"></i>
                    <div class="w-100">
                        <h6 class="fw-bold mb-1">SMTP Test Delivery Failed</h6>
                        <p class="mb-2 small">${escapeHtml(data.message)}</p>
                        ${data.debug ? `
                            <details class="mt-2">
                                <summary class="small text-danger fw-semibold" style="cursor:pointer;">View Diagnostic Logs</summary>
                                <pre class="p-2 mt-2 bg-dark text-light rounded small font-monospace" style="max-height:180px;overflow:auto;font-size:11.5px;">${escapeHtml(data.debug)}</pre>
                            </details>
                        ` : ''}
                    </div>
                </div>
            `;
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-send me-1"></i> Test SMTP Connection';
        resultBox.style.display = 'block';
        resultBox.innerHTML = `
            <div class="alert alert-danger d-flex align-items-start gap-2 shadow-sm mb-0">
                <i class="ti ti-alert-triangle-filled fs-4 text-danger mt-1"></i>
                <div>
                    <h6 class="fw-bold mb-1">Request Error</h6>
                    <p class="mb-0 small">An unexpected error occurred while communicating with the server: ${escapeHtml(err.message || err)}</p>
                </div>
            </div>
        `;
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

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

function applySettingsIdPreset(primary, secondary, header) {
    const p = document.getElementById('settingsIdPrimary');
    const s = document.getElementById('settingsIdSecondary');
    const h = document.getElementById('settingsIdHeader');
    if (p) { p.value = primary; p.nextElementSibling.value = primary.toUpperCase(); }
    if (s) { s.value = secondary; s.nextElementSibling.value = secondary.toUpperCase(); }
    if (h) { h.value = header; h.nextElementSibling.value = header.toUpperCase(); }
}

function syncSettingsIdWithBrand() {
    const brandColorInput = document.querySelector('input[name="settings[primary_color]"]');
    const brandColor = brandColorInput ? brandColorInput.value : '#0b3d91';
    applySettingsIdPreset(brandColor, brandColor, '#0f172a');
}
</script>
