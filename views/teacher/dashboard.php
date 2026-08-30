<?php
// views/teacher/dashboard.php
?>
<style>
    .t-banner {
        background: linear-gradient(135deg, var(--teacher-primary), #0d9488);
        color: #fff;
        padding: 2.5rem;
        border-radius: 16px;
        position: relative;
        overflow: hidden;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(15, 118, 110, 0.15);
    }
    .t-banner h1 { font-size: 2rem; font-weight: 700; margin: 0 0 0.5rem; }
    .t-banner p { font-size: 1.05rem; opacity: 0.9; margin: 0; }
    .t-banner .badge-role {
        background: rgba(255,255,255,0.15);
        padding: 0.4rem 1rem;
        border-radius: 2rem;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
        margin-top: 1rem;
        border: 1px solid rgba(255,255,255,0.25);
    }
    
    .t-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
    .t-card {
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 18px rgba(0,0,0,0.03);
        border: 1px solid #e8eef4;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .t-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 22px rgba(0,0,0,0.06);
    }
    .t-card .card-head { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
    .t-card .card-title { font-size: 0.82rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
    .t-card .card-icon { font-size: 1.6rem; color: var(--teacher-primary); }
    .t-card .card-value { font-size: 1.8rem; font-weight: 700; color: #1e293b; margin-bottom: 0.5rem; }
    
    .schedule-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        border-radius: 8px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        margin-bottom: 10px;
    }
    .schedule-time {
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
        background: #e2e8f0;
        padding: 4px 8px;
        border-radius: 4px;
    }
    .schedule-details {
        flex: 1;
        margin-left: 15px;
    }
    .schedule-subject {
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    .schedule-class {
        font-size: 0.8rem;
        color: #64748b;
        margin: 0;
    }
    
    .ann-item {
        border-left: 4px solid var(--teacher-accent);
        padding: 12px 15px;
        background: #fdfdfd;
        border-radius: 0 8px 8px 0;
        margin-bottom: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.01);
    }
</style>

<div class="t-banner">
    <div style="z-index: 5; position: relative;">
        <p>Welcome back,</p>
        <h1><?= e($_SESSION['teacher']['name']) ?></h1>
        <p>Staff ID: <strong><?= e($_SESSION['teacher']['staff_id']) ?></strong></p>
        <span class="badge-role"><i class="ti ti-briefcase" style="margin-right:6px"></i><?= e($_SESSION['teacher']['role']) ?></span>
    </div>
</div>

<div class="t-grid">
    <!-- Card 1: Classes -->
    <div class="t-card">
        <div class="card-head">
            <span class="card-title">My Assigned Classes</span>
            <i class="ti ti-users card-icon" style="color:#0f766e"></i>
        </div>
        <div>
            <div class="card-value"><?= $classCount ?></div>
            <a href="<?= url('teacher/class-list') ?>" style="font-size:12px; text-decoration:none; color:var(--teacher-accent); font-weight:600;">View class list →</a>
        </div>
    </div>

    <!-- Card 2: Subjects -->
    <div class="t-card">
        <div class="card-head">
            <span class="card-title">Subjects Taught</span>
            <i class="ti ti-book card-icon" style="color:#14b8a6"></i>
        </div>
        <div>
            <div class="card-value"><?= $subjectCount ?></div>
            <a href="<?= url('teacher/results') ?>" style="font-size:12px; text-decoration:none; color:var(--teacher-accent); font-weight:600;">Manage results →</a>
        </div>
    </div>

    <!-- Card 3: Form Teacher -->
    <div class="t-card">
        <div class="card-head">
            <span class="card-title">Form Teacher Of</span>
            <i class="ti ti-school card-icon" style="color:#f59e0b"></i>
        </div>
        <div>
            <div class="card-value" style="font-size: 1.5rem;"><?= e($formClassName) ?></div>
            <span style="font-size:12px; color:#64748b;">Form class overview</span>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Today's Schedule -->
    <div class="col-lg-6">
        <div class="t-card" style="justify-content: flex-start; height: 100%;">
            <h3 style="font-size:1.1rem; font-weight:700; margin-bottom:1.25rem; color:#1e293b; display:flex; align-items:center; gap:8px;">
                <i class="ti ti-calendar-event" style="color:var(--teacher-primary)"></i> Today's Schedule (<?= date('l') ?>)
            </h3>
            
            <?php if (empty($todaySchedule)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-calendar-off" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                    No classes scheduled for today.
                </div>
            <?php else: ?>
                <div>
                    <?php foreach ($todaySchedule as $sch): ?>
                        <div class="schedule-item">
                            <span class="schedule-time"><?= date('h:i A', strtotime($sch['start_time'])) ?></span>
                            <div class="schedule-details">
                                <h4 class="schedule-subject"><?= e($sch['subject_name']) ?></h4>
                                <p class="schedule-class"><?= e($sch['class_name']) ?></p>
                            </div>
                            <span class="badge bg-light text-dark"><?= date('h:i A', strtotime($sch['end_time'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Announcements -->
    <div class="col-lg-6">
        <div class="t-card" style="justify-content: flex-start; height: 100%;">
            <h3 style="font-size:1.1rem; font-weight:700; margin-bottom:1.25rem; color:#1e293b; display:flex; align-items:center; gap:8px;">
                <i class="ti ti-speakerphone" style="color:var(--teacher-primary)"></i> Staff Announcements
            </h3>
            
            <?php if (empty($announcements)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-notes" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                    No announcements published.
                </div>
            <?php else: ?>
                <div>
                    <?php foreach ($announcements as $ann): ?>
                        <div class="ann-item">
                            <h4 style="font-size:0.95rem; font-weight:700; margin:0 0 5px 0; color:#1e293b;"><?= e($ann['title']) ?></h4>
                            <p style="font-size:0.85rem; color:#475569; margin:0 0 8px 0; line-height:1.4;"><?= nl2br(e($ann['body'])) ?></p>
                            <small style="font-size:0.75rem; color:#94a3b8;"><i class="ti ti-clock"></i> <?= date('M d, Y', strtotime($ann['published_at'] ?? $ann['created_at'])) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
