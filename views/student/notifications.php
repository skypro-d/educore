<?php
// views/student/notifications.php
?>
<style>
    .notif-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .notif-card {
        background: #fff;
        border-radius: var(--radius, 12px);
        padding: 1.5rem;
        box-shadow: 0 4px 18px rgba(0,0,0,0.03);
        border: 1px solid #e8eef4;
        margin-bottom: 1.5rem;
    }
    .notif-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }
    .notif-header h2 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .notif-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .notif-row {
        display: flex;
        gap: 16px;
        align-items: flex-start;
        padding: 1.25rem;
        border-radius: var(--radius, 10px);
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease-in-out;
        position: relative;
    }
    .notif-row:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border-color: #cbd5e1;
    }
    .notif-row.unread {
        background: #eff6ff;
        border-color: #bfdbfe;
    }
    .notif-row.unread:hover {
        border-color: #93c5fd;
    }
    .notif-bullet {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #3b82f6;
        position: absolute;
        top: 20px;
        right: 20px;
    }
    .notif-icon-box {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .notif-row.unread .notif-icon-box {
        background: #dbeafe;
        color: #2563eb;
    }
    .notif-body {
        flex: 1;
    }
    .notif-title {
        font-size: 0.95rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0 0 4px 0;
    }
    .notif-msg {
        font-size: 0.875rem;
        color: #475569;
        margin: 0 0 8px 0;
        line-height: 1.5;
    }
    .notif-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .notif-time {
        font-size: 0.75rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .btn-mark-read {
        background: none;
        border: none;
        color: #2563eb;
        font-size: 0.775rem;
        font-weight: 600;
        padding: 0;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 4px;
        transition: color 0.15s;
    }
    .btn-mark-read:hover {
        color: #1d4ed8;
        text-decoration: underline;
    }
    .notif-empty {
        text-align: center;
        padding: 3rem 1.5rem;
        color: #64748b;
    }
    .notif-empty i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #cbd5e1;
        display: block;
    }
</style>

<div class="notif-container">
    <div class="notif-card">
        <div class="notif-header">
            <h2><i class="ti ti-bell"></i> Notifications</h2>
            <span class="badge bg-primary rounded-pill">
                <?= count(array_filter($notifications, fn($n) => !$n['is_read'])) ?> Unread
            </span>
        </div>

        <?php if (empty($notifications)): ?>
            <div class="notif-empty">
                <i class="ti ti-bell-off"></i>
                <p class="mb-0">You have no notifications at the moment.</p>
            </div>
        <?php else: ?>
            <div class="notif-list">
                <?php foreach ($notifications as $n): ?>
                    <div class="notif-row <?= !$n['is_read'] ? 'unread' : '' ?>">
                        <?php if (!$n['is_read']): ?>
                            <span class="notif-bullet" title="Unread"></span>
                        <?php endif; ?>
                        
                        <div class="notif-icon-box">
                            <i class="ti ti-info-circle"></i>
                        </div>
                        
                        <div class="notif-body">
                            <h4 class="notif-title"><?= e($n['title']) ?></h4>
                            <p class="notif-msg"><?= nl2br(e($n['message'])) ?></p>
                            
                            <div class="notif-meta">
                                <span class="notif-time">
                                    <i class="ti ti-clock"></i>
                                    <?= date('M d, Y h:i A', strtotime($n['created_at'])) ?>
                                </span>
                                
                                <?php if (!$n['is_read']): ?>
                                    <form method="POST" action="<?= url('student/notifications') ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="notification_id" value="<?= (int)$n['id'] ?>">
                                        <button type="submit" class="btn-mark-read">
                                            <i class="ti ti-check"></i> Mark as read
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
