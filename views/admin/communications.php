<div class="sa-top-bar">
    <div>
        <h1>Communication Center</h1>
        <p>Send bulk SMS messages to parent contacts and manage parent portal announcements</p>
    </div>
</div>

<div class="row g-4">
    <!-- Announcements Setup -->
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-title"><i class="ti ti-speakerphone"></i> Post New Announcement</div>
            <form method="POST" action="<?= url('admin/announcements') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;font-weight:600;">Announcement Title <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="title" required class="form-control form-control-sm" placeholder="e.g. End of Term Closure / PTA Meeting">
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;font-weight:600;">Audience Target</label>
                    <select name="audience" id="annAudience" class="form-select form-select-sm" required>
                        <option value="all">Everyone</option>
                        <option value="parents">Parents Only</option>
                        <option value="staff">Staff Only</option>
                        <option value="class">Specific Class</option>
                    </select>
                </div>
                <div class="mb-3" id="annClassGroup" style="display:none;">
                    <label class="form-label" style="font-size:13px;font-weight:600;">Target Class</label>
                    <select name="class_id" class="form-select form-select-sm">
                        <option value="">-- Select Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;font-weight:600;">Message Body <span style="color:#dc2626;">*</span></label>
                    <textarea name="body" required class="form-control form-control-sm" rows="4" placeholder="Type announcement details here... HTML tags allowed."></textarea>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" name="is_published" value="1" class="form-check-input" id="isPublished" checked>
                    <label class="form-check-label" for="isPublished" style="font-size:13px;">Publish immediately (visible in portal)</label>
                </div>
                <button type="submit" class="sa-btn sa-btn-primary w-100 justify-content-center"><i class="ti ti-device-floppy"></i> Save Announcement</button>
            </form>
        </div>
    </div>

    <!-- Bulk SMS Setup -->
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-title"><i class="ti ti-message-dots"></i> Send Bulk SMS</div>
            <form method="POST" action="<?= url('admin/send-sms') ?>">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;font-weight:600;">Recipient Target</label>
                    <select name="target" id="smsTarget" class="form-select form-select-sm" required>
                        <option value="all_parents">All Parents</option>
                        <option value="class_parents">Parents of a Class</option>
                        <option value="individual">Individual Parent</option>
                    </select>
                </div>
                <div class="mb-3" id="smsClassGroup" style="display:none;">
                    <label class="form-label" style="font-size:13px;font-weight:600;">Target Class</label>
                    <select name="class_id" class="form-select form-select-sm">
                        <option value="">-- Select Class --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label" style="font-size:13px;font-weight:600;">SMS Message (Max 160 chars per unit) <span style="color:#dc2626;">*</span></label>
                    <textarea name="message" id="smsMessage" required class="form-control form-control-sm" rows="4" placeholder="Enter SMS text to deliver to parents..." maxlength="320"></textarea>
                    <div class="form-text text-end" id="charCount">0 / 160 characters (1 SMS page)</div>
                </div>
                <button type="submit" class="sa-btn sa-btn-primary w-100 justify-content-center" style="background:#16a34a; border-color:#16a34a;"><i class="ti ti-send"></i> Send SMS Blast</button>
            </form>
        </div>
    </div>
</div>

<div class="row g-4 style-grid" style="margin-top:20px;">
    <!-- Active Announcements Log -->
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-title"><i class="ti ti-list"></i> Recent Announcements</div>
            <div style="max-height: 400px; overflow-y: auto;">
                <?php if ($announcements): foreach ($announcements as $ann): ?>
                    <div style="border-bottom:1px solid #f1f5f9; padding: 12px 0;">
                        <div style="display:flex; justify-content:space-between; align-items:start;">
                            <strong style="font-size:13px; color:#1e293b;"><?= e($ann['title']) ?></strong>
                            <span style="font-size:10px; font-weight:700; padding:2px 8px; border-radius:12px; background:<?= $ann['is_published'] ? '#dcfce7' : '#fee2e2' ?>; color:<?= $ann['is_published'] ? '#15803d' : '#b91c1c' ?>;">
                                <?= $ann['is_published'] ? 'Active' : 'Draft' ?>
                            </span>
                        </div>
                        <p style="font-size:12px; color:#475569; margin: 6px 0;"><?= strip_tags($ann['body']) ?></p>
                        <div style="font-size:10px; color:#94a3b8;">
                            Audience: <span style="text-transform: capitalize;"><?= e($ann['audience']) ?></span> <?= $ann['class_name'] ? '('.e($ann['class_name']).')' : '' ?>
                            &nbsp;|&nbsp; Posted: <?= date('M j, Y H:i', strtotime($ann['created_at'])) ?>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <p style="color:#94a3b8; text-align:center; padding: 40px 0;">No announcements published yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent SMS logs -->
    <div class="col-md-6">
        <div class="sa-card">
            <div class="sa-card-title"><i class="ti ti-history"></i> Recent SMS Log</div>
            <div style="max-height: 400px; overflow-y: auto;">
                <?php if ($smsLogs): foreach ($smsLogs as $log): ?>
                    <div style="border-bottom:1px solid #f1f5f9; padding: 12px 0; font-size:12px;">
                        <div style="display:flex; justify-content:space-between;">
                            <strong><?= e($log['recipient_name'] ?: 'Parent') ?> (<?= e($log['recipient_phone']) ?>)</strong>
                            <span style="color:<?= $log['status'] === 'sent' ? '#16a34a' : '#dc2626' ?>; font-weight:700;"><?= e(strtoupper($log['status'])) ?></span>
                        </div>
                        <div style="color:#475569; margin:4px 0; font-style:italic;">"<?= e($log['message']) ?>"</div>
                        <div style="font-size:10px; color:#94a3b8;"><?= date('M j, Y H:i', strtotime($log['created_at'])) ?></div>
                    </div>
                <?php endforeach; else: ?>
                    <p style="color:#94a3b8; text-align:center; padding: 40px 0;">No SMS history logs found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Announcements Target Toggle
    const annAudience = document.getElementById('annAudience');
    const annClassGroup = document.getElementById('annClassGroup');
    if (annAudience && annClassGroup) {
        annAudience.addEventListener('change', function() {
            annClassGroup.style.display = this.value === 'class' ? 'block' : 'none';
        });
    }

    // SMS Target Toggle
    const smsTarget = document.getElementById('smsTarget');
    const smsClassGroup = document.getElementById('smsClassGroup');
    if (smsTarget && smsClassGroup) {
        smsTarget.addEventListener('change', function() {
            smsClassGroup.style.display = this.value === 'class_parents' ? 'block' : 'none';
        });
    }

    // SMS character limit counter
    const smsMsg = document.getElementById('smsMessage');
    const charCount = document.getElementById('charCount');
    if (smsMsg && charCount) {
        smsMsg.addEventListener('input', function() {
            const len = this.value.length;
            const pages = Math.ceil(len / 160) || 1;
            charCount.textContent = `${len} / ${pages * 160} characters (${pages} SMS page${pages > 1 ? 's' : ''})`;
        });
    }
});
</script>
