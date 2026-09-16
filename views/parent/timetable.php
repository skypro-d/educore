<?php
/**
 * views/parent/timetable.php
 * Parent Portal Child Timetable View
 */
$childName = !empty($student['first_name']) ? $student['first_name'] . ' ' . $student['last_name'] : 'Child';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h1 class="h3 fw-bold text-dark mb-1"><i class="ti ti-calendar-time text-primary me-2"></i><?= e($student['first_name'] ?? 'Child') ?>'s Weekly Timetable</h1>
        <p class="text-muted small mb-0">Official class schedule, lecture hours, and assigned teachers for <?= e($childName) ?>.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-light text-dark border py-2 px-3 fw-bold" style="border-radius: 20px; font-size: 12px;">
            Today: <strong><?= date('l') ?></strong>
        </span>
        <button type="button" class="btn btn-outline-primary btn-sm px-3 shadow-sm rounded-3" onclick="window.print()">
            <i class="ti ti-printer me-1"></i> Print Schedule
        </button>
    </div>
</div>

<div class="row g-4 mb-4">
    <?php foreach ($days as $day): ?>
        <?php 
            $slots = $timetable[$day] ?? []; 
            $isToday = (date('l') === $day);
        ?>
        <div class="col-lg-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="<?= $isToday ? 'border-top: 4px solid var(--parent-primary, #16a34a) !important; background: #fafcff;' : '' ?>">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 15px;"><?= $day ?></h5>
                        <?php if ($isToday): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;">Today</span>
                        <?php endif; ?>
                    </div>
                    <span class="badge bg-light text-muted border" style="font-size: 11px;">
                        <?= count($slots) ?> <?= count($slots) === 1 ? 'Period' : 'Periods' ?>
                    </span>
                </div>
                <div class="card-body p-3">
                    <?php if (empty($slots)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="ti ti-calendar-off d-block mb-1 text-muted" style="font-size: 24px; opacity: 0.4;"></i>
                            <span class="small">No classes scheduled</span>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($slots as $slot): ?>
                                <div class="p-3 bg-light rounded-3 border d-flex flex-column gap-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-white text-dark border font-monospace shadow-sm" style="font-size: 11px;">
                                            <i class="ti ti-clock me-1 text-primary"></i>
                                            <?= date('g:i A', strtotime($slot['start_time'])) ?> - <?= date('g:i A', strtotime($slot['end_time'])) ?>
                                        </span>
                                        <?php if (!empty($slot['subject_code'])): ?>
                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px;"><?= e($slot['subject_code']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="fw-bold text-dark mt-1" style="font-size: 13.5px;">
                                        <?= e($slot['subject_name']) ?>
                                    </div>
                                    <div class="text-muted small" style="font-size: 11.5px;">
                                        <i class="ti ti-user me-1 text-success"></i>
                                        <?= !empty($slot['first_name']) ? e($slot['first_name'] . ' ' . $slot['last_name']) : 'Subject Teacher' ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
@media print {
    .parent-sidebar, .parent-mobile-bar, .btn, .breadcrumb, .powered-credit {
        display: none !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        break-inside: avoid;
    }
}
</style>
