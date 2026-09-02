<?php
// views/teacher/announcements.php — Announcements
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 style="font-size:1.4rem; font-weight:700; color:#0f172a; margin:0 0 4px 0;">Announcements</h1>
        <p style="color:#64748b; font-size:0.875rem; margin:0;">School-wide notices, departmental bulletins, and classroom updates</p>
    </div>
    <?php if (staff_can('announcements.create')): ?>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAnnModal" style="font-weight:600; font-size:13px; border-radius:8px;">
            <i class="ti ti-plus me-1"></i> Post Announcement
        </button>
    <?php endif; ?>
</div>

<div class="card border-0 shadow-sm p-4" style="border-radius:14px; background:#fff;">
    <h4 class="h6 fw-bold mb-3" style="color:#0f172a;">Active Bulletins & Announcements</h4>
    <?php if (empty($announcements)): ?>
        <p class="text-muted text-center py-5 mb-0">No active announcements available.</p>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($announcements as $ann): ?>
                <div class="p-3 border rounded-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="fw-bold mb-0" style="font-size:14px; color:#0f172a;"><?= e($ann['title']) ?></h5>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-secondary-subtle text-secondary border" style="font-size:10.5px;">
                                Audience: <?= ucfirst(e($ann['audience'])) ?><?= $ann['class_name'] ? ' (' . e($ann['class_name']) . ')' : '' ?>
                            </span>
                            <small class="text-muted" style="font-size:11px;">
                                <i class="ti ti-clock"></i> <?= date('M d, Y', strtotime($ann['published_at'] ?? $ann['created_at'])) ?>
                            </small>
                        </div>
                    </div>
                    <p class="mb-0 text-muted" style="font-size:13px; line-height:1.5;">
                        <?= nl2br(e($ann['body'])) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Post Announcement -->
<?php if (staff_can('announcements.create')): ?>
    <div class="modal fade" id="createAnnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius:14px;">
                <div class="modal-header border-0 px-4 pt-4 pb-2">
                    <h5 class="modal-title font-weight-bold" style="font-weight:700; color:#0f172a;">Post New Announcement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="<?= url('teacher/announcements/create') ?>">
                    <?= csrf_field() ?>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label" style="font-size:12px; font-weight:600;">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required placeholder="Announcement headline...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:12px; font-weight:600;">Target Audience</label>
                            <select name="audience" class="form-select" id="annAudienceSelect">
                                <option value="class">Specific Class</option>
                                <option value="staff">Staff Only</option>
                                <?php if (StaffAuth::isSchoolAdmin()): ?>
                                    <option value="all">Entire School</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3" id="classSelectDiv">
                            <label class="form-label" style="font-size:12px; font-weight:600;">Select Target Class</label>
                            <select name="class_id" class="form-select">
                                <option value="">-- Choose Class --</option>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" style="font-size:12px; font-weight:600;">Announcement Body <span class="text-danger">*</span></label>
                            <textarea name="body" class="form-control" rows="5" required placeholder="Write your announcement message..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4">Publish</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php endif; ?>
