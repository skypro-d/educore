<?php
// views/teacher/timetable.php — Staff Weekly & Daily Timetable
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 style="font-size:1.4rem; font-weight:700; color:#0f172a; margin:0 0 4px 0;">Teacher Timetable</h1>
        <p style="color:#64748b; font-size:0.875rem; margin:0;">Weekly classroom schedule and period allocations</p>
    </div>
    <span class="badge bg-light text-dark border py-2 px-3 fw-bold" style="border-radius:20px; font-size:12px;">
        Today is: <strong><?= date('l') ?></strong>
    </span>
</div>

<div class="row g-4">
    <?php foreach ($days as $day): ?>
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100 p-4" style="border-radius:14px; background:#fff; <?= date('l') === $day ? 'border-top: 4px solid var(--teacher-accent) !important;' : '' ?>">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h4 class="h6 fw-bold mb-0" style="color:#0f172a;"><?= e($day) ?></h4>
                    <?php if (date('l') === $day): ?>
                        <span class="badge bg-teal-subtle text-teal-emphasis border border-teal-subtle py-0 px-2" style="background:#e6fffa; color:#0d9488; font-size:10px;">Today</span>
                    <?php endif; ?>
                </div>

                <?php $periods = $scheduleByDay[$day] ?? []; ?>
                <?php if (empty($periods)): ?>
                    <p class="text-muted text-center py-4 mb-0" style="font-size:12px;">No classes scheduled.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($periods as $p): ?>
                            <div class="p-2 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold" style="font-size:13px; color:#0f172a;"><?= e($p['subject_name']) ?></div>
                                    <small class="text-muted" style="font-size:11.5px;"><?= e($p['class_name']) ?></small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-white text-dark border font-monospace" style="font-size:10px;">
                                        <?= date('g:i A', strtotime($p['start_time'])) ?> - <?= date('g:i A', strtotime($p['end_time'])) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
