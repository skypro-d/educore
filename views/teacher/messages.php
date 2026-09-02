<?php
// views/teacher/messages.php — Parent Messages & Communications
$preselectedId = (int)($_GET['student_id'] ?? 0);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 style="font-size:1.4rem; font-weight:700; color:#0f172a; margin:0 0 4px 0;">Parent Communication</h1>
        <p style="color:#64748b; font-size:0.875rem; margin:0;">Send academic and behavioral notifications to parents of your assigned students</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4" style="border-radius:14px; background:#fff;">
            <h4 class="h6 fw-bold mb-3" style="color:#0f172a;">
                <i class="ti ti-send text-primary me-2"></i> Compose Parent Notice
            </h4>
            <form method="POST" action="<?= url('teacher/messages/send') ?>">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label" style="font-size:12px; font-weight:600;">Select Student <span class="text-danger">*</span></label>
                    <select name="student_id" class="form-select" required>
                        <option value="">-- Choose Student --</option>
                        <?php foreach ($students as $st): ?>
                            <option value="<?= $st['id'] ?>" <?= $preselectedId === (int)$st['id'] ? 'selected' : '' ?>>
                                <?= e($st['first_name'] . ' ' . $st['last_name']) ?> (<?= e($st['class_name']) ?>) — Parent: <?= e($st['parent_name'] ?: 'N/A') ?> (<?= e($st['parent_phone'] ?: 'No Phone') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-size:12px; font-weight:600;">Dispatch Channel</label>
                    <div class="d-flex gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="channel" id="chSms" value="sms" checked>
                            <label class="form-check-label" for="chSms" style="font-size:13px;">SMS Message</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="channel" id="chEmail" value="email">
                            <label class="form-check-label" for="chEmail" style="font-size:13px;">Email Notice</label>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" style="font-size:12px; font-weight:600;">Message Content <span class="text-danger">*</span></label>
                    <textarea name="message" class="form-control" rows="5" required placeholder="Type notice to guardian..."></textarea>
                    <small class="text-muted" style="font-size:11px;">Messages are automatically prefixed with the teacher and school identification.</small>
                </div>

                <button type="submit" class="btn btn-primary px-4" style="font-weight:600; border-radius:8px;">
                    <i class="ti ti-send me-1"></i> Send Dispatch
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius:14px; background:#fff;">
            <h4 class="h6 fw-bold mb-3" style="color:#0f172a;">Communication Guidelines</h4>
            <div class="alert alert-info py-2 px-3 border-0 d-flex align-items-center gap-2 mb-3" style="font-size:12px; border-radius:8px;">
                <i class="ti ti-shield-check fs-5"></i>
                <span>Privacy & Multi-Tenant Protection is strictly enforced. You can only communicate with parents of students assigned to your classes.</span>
            </div>
            <ul class="text-muted" style="font-size:13px; line-height:1.6;">
                <li>Use parent messaging for academic check-ins, homework follow-ups, and behavioral reminders.</li>
                <li>Daily arrival and departure updates are already sent automatically via EduCore's QR Check-In system.</li>
                <li>All outgoing messages are logged and subject to school administrative review.</li>
            </ul>
        </div>
    </div>
</div>
