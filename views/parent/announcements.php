<div class="parent-topbar">
    <div class="page-title"><i class="ti ti-speakerphone" style="margin-right:8px;color:#d97706;"></i>Announcements</div>
</div>
<div class="parent-content">
    <?php if ($announcements): foreach ($announcements as $ann): ?>
    <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;padding:22px;margin-bottom:14px;">
        <div style="display:flex;align-items:flex-start;gap:14px;">
            <div style="width:42px;height:42px;border-radius:10px;background:#fef9ec;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="ti ti-speakerphone" style="font-size:20px;color:#d97706;"></i></div>
            <div style="flex:1;">
                <div style="font-size:15px;font-weight:700;color:#1a2535;margin-bottom:6px;"><?= e($ann['title']) ?></div>
                <div style="font-size:13px;color:#374151;line-height:1.7;"><?= nl2br(e($ann['body'])) ?></div>
                <div style="margin-top:10px;font-size:11px;color:#9ca3af;"><i class="ti ti-clock" style="margin-right:4px;"></i><?= $ann['published_at'] ? date('D, M j Y \a\t g:i a', strtotime($ann['published_at'])) : '—' ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; else: ?>
    <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;padding:60px;text-align:center;">
        <i class="ti ti-speakerphone" style="font-size:48px;color:#d1d5db;display:block;margin-bottom:12px;"></i>
        <div style="font-size:16px;font-weight:600;color:#6b7280;margin-bottom:6px;">No Announcements</div>
        <div style="font-size:13px;color:#9ca3af;">There are no active announcements at the moment.</div>
    </div>
    <?php endif; ?>
</div>
