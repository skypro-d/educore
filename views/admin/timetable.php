<?php
/**
 * views/admin/timetable.php
 * Master Class Timetable Schedule & Period Allocation Management
 * Fintech-inspired clean administrative interface.
 */
$csrfToken = csrf_token();
$selectedClassName = $selectedClass['name'] ?? 'Class';
$totalPeriods = count($schedule);
$uniqueSubjects = count(array_unique(array_filter(array_column($schedule, 'subject_id'))));
$uniqueTeachers = count(array_unique(array_filter(array_column($schedule, 'teacher_id'))));
?>

<div class="container-fluid px-0">
    <!-- Top Bar & Breadcrumb -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 text-muted small">
                    <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>" class="text-decoration-none text-muted">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= url('admin/subjects') ?>" class="text-decoration-none text-muted">Academics</a></li>
                    <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Class Timetable</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark mb-1" style="letter-spacing: -0.02em;">Class Timetable Management</h1>
            <p class="text-muted small mb-0">Configure weekly periods, assign subject instructors, and sync timetables across Student, Parent, and Teacher portals.</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <button type="button" class="btn btn-outline-secondary btn-sm px-3 shadow-sm rounded-3" onclick="window.print()">
                <i class="ti ti-printer me-1"></i> Print Timetable
            </button>
            <button type="button" class="btn btn-outline-primary btn-sm px-3 shadow-sm rounded-3" data-bs-toggle="modal" data-bs-target="#copyTimetableModal">
                <i class="ti ti-copy me-1"></i> Copy to Another Class
            </button>
            <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm rounded-3" data-bs-toggle="modal" data-bs-target="#addPeriodModal">
                <i class="ti ti-plus me-1"></i> Add Timetable Period
            </button>
        </div>
    </div>

    <!-- Class Selector Bar -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="text-muted small fw-semibold text-uppercase me-2" style="font-size:11px; letter-spacing:0.05em;">
                        <i class="ti ti-building me-1"></i> Select Class:
                    </span>
                    <?php foreach ($classes as $c): ?>
                        <?php 
                            $cId = (int)$c['id'];
                            $isActive = $cId === $classId;
                            $count = $classCounts[$cId] ?? 0;
                        ?>
                        <a href="<?= url('admin/timetable?class_id=' . $cId) ?>" 
                           class="btn btn-sm rounded-3 text-nowrap <?= $isActive ? 'btn-primary shadow-sm' : 'btn-light border text-dark' ?>"
                           style="font-size: 12.5px; font-weight: 500;">
                            <?= e($c['name']) ?>
                            <span class="badge rounded-pill <?= $isActive ? 'bg-white text-primary' : 'bg-secondary-subtle text-secondary' ?> ms-1" style="font-size: 10px;">
                                <?= $count ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if ($classId > 0 && $totalPeriods > 0): ?>
                    <div>
                        <form action="<?= url('admin/timetable/clear') ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to clear ALL timetable slots for <?= addslashes(e($selectedClassName)) ?>?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="class_id" value="<?= $classId ?>">
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-3 px-2 py-1" style="font-size: 11px;">
                                <i class="ti ti-trash me-1"></i> Clear Class Schedule
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Metrics Strip for Selected Class -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="ti ti-calendar-time"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Total Periods</div>
                        <h4 class="fw-bold mb-0 text-dark"><?= $totalPeriods ?> <span class="fs-6 fw-normal text-muted">slots</span></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="ti ti-books"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Subjects Scheduled</div>
                        <h4 class="fw-bold mb-0 text-dark"><?= $uniqueSubjects ?> <span class="fs-6 fw-normal text-muted">courses</span></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="ti ti-users"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Assigned Instructors</div>
                        <h4 class="fw-bold mb-0 text-dark"><?= $uniqueTeachers ?> <span class="fs-6 fw-normal text-muted">staff</span></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: #f3e8ff; color: #9333ea; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                        <i class="ti ti-device-laptop"></i>
                    </div>
                    <div>
                        <div class="text-muted small">Portal Status</div>
                        <h4 class="fw-bold mb-0 text-dark" style="font-size:15px;">
                            <span class="badge bg-success-subtle text-success border border-success-subtle">Live on All Portals</span>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Schedule Grid View -->
    <div class="row g-4">
        <?php foreach ($days as $day): ?>
            <?php $daySlots = $timetableByDay[$day] ?? []; ?>
            <div class="col-xl-4 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="<?= date('l') === $day ? 'border-top: 4px solid var(--brand-primary, #0284c7) !important;' : '' ?>">
                    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold text-dark" style="font-size: 15px;"><?= $day ?></span>
                            <?php if (date('l') === $day): ?>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 10px;">Today</span>
                            <?php endif; ?>
                            <span class="badge bg-light text-muted border" style="font-size: 11px;">
                                <?= count($daySlots) ?> <?= count($daySlots) === 1 ? 'Period' : 'Periods' ?>
                            </span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-2" style="font-size: 11px;" onclick="openAddPeriodForDay('<?= $day ?>')">
                            <i class="ti ti-plus me-1"></i> Add
                        </button>
                    </div>

                    <div class="card-body p-3">
                        <?php if (empty($daySlots)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="ti ti-calendar-off d-block mb-2 text-muted" style="font-size: 28px; opacity: 0.5;"></i>
                                <span class="small">No periods scheduled for <?= $day ?>.</span>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-xs btn-light border rounded-pill px-3" style="font-size: 11px;" onclick="openAddPeriodForDay('<?= $day ?>')">
                                        <i class="ti ti-plus me-1"></i> Schedule Period
                                    </button>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-2">
                                <?php foreach ($daySlots as $slot): ?>
                                    <div class="p-3 bg-light rounded-3 border d-flex flex-column gap-2 position-relative" style="transition: all 0.2s ease;">
                                        <!-- Time & Actions Header -->
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-white text-dark border font-monospace shadow-sm" style="font-size: 11px;">
                                                <i class="ti ti-clock me-1 text-primary"></i>
                                                <?= date('g:i A', strtotime($slot['start_time'])) ?> - <?= date('g:i A', strtotime($slot['end_time'])) ?>
                                            </span>
                                            <div class="d-flex align-items-center gap-1">
                                                <button type="button" class="btn btn-link text-muted p-0 me-1" title="Edit Period" 
                                                        onclick='openEditPeriodModal(<?= json_encode($slot) ?>)'>
                                                    <i class="ti ti-edit text-primary" style="font-size: 15px;"></i>
                                                </button>
                                                <form action="<?= url('admin/timetable/' . $slot['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Delete this period slot?');">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="class_id" value="<?= $classId ?>">
                                                    <button type="submit" class="btn btn-link text-muted p-0" title="Delete Period">
                                                        <i class="ti ti-trash text-danger" style="font-size: 15px;"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>

                                        <!-- Subject Title -->
                                        <div class="d-flex align-items-baseline gap-2">
                                            <span class="fw-bold text-dark" style="font-size: 13.5px;">
                                                <?= e($slot['subject_name']) ?>
                                            </span>
                                            <?php if (!empty($slot['subject_code'])): ?>
                                                <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px;">
                                                    <?= e($slot['subject_code']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Assigned Teacher -->
                                        <div class="d-flex align-items-center gap-2 text-muted small" style="font-size: 11.5px;">
                                            <i class="ti ti-user-check text-success"></i>
                                            <?php if (!empty($slot['first_name'])): ?>
                                                <span class="text-dark fw-medium"><?= e($slot['first_name'] . ' ' . $slot['last_name']) ?></span>
                                                <?php if (!empty($slot['teacher_staff_code'])): ?>
                                                    <span class="text-muted">(<?= e($slot['teacher_staff_code']) ?>)</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">No teacher assigned</span>
                                            <?php endif; ?>
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
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: ADD TIMETABLE PERIOD                                             -->
<!-- ========================================================================= -->
<div class="modal fade" id="addPeriodModal" tabindex="-1" aria-labelledby="addPeriodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="<?= url('admin/timetable/save') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark" id="addPeriodModalLabel" style="font-size: 16px;">
                        <i class="ti ti-calendar-plus me-1 text-primary"></i> Add Timetable Period
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Class <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3 shadow-none" name="class_id" id="addModalClassId" required>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= e($c['id']) ?>" <?= (int)$c['id'] === $classId ? 'selected' : '' ?>>
                                        <?= e($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Day of Week <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3 shadow-none" name="day_of_week" id="addModalDayOfWeek" required>
                                <?php foreach ($days as $d): ?>
                                    <option value="<?= $d ?>"><?= $d ?></option>
                                <?php endforeach; ?>
                                <option value="Saturday">Saturday</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">Subject <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3 shadow-none" name="subject_id" required>
                                <option value="">-- Choose Subject --</option>
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?= e($s['id']) ?>">
                                        <?= e($s['name']) ?> <?= !empty($s['code']) ? '(' . e($s['code']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">Subject Teacher / Instructor</label>
                            <select class="form-select rounded-3 shadow-none" name="teacher_id">
                                <option value="">-- No Specific Teacher / TBA --</option>
                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?= e($t['id']) ?>">
                                        <?= e($t['first_name'] . ' ' . $t['last_name']) ?> <?= !empty($t['staff_id']) ? '(' . e($t['staff_id']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted" style="font-size: 11px;">Assigning a teacher makes this period show on their Teacher portal.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control rounded-3 shadow-none" name="start_time" value="08:00" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control rounded-3 shadow-none" name="end_time" value="08:45" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-light border rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold shadow-sm">
                        <i class="ti ti-check me-1"></i> Save Period Slot
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: EDIT TIMETABLE PERIOD                                            -->
<!-- ========================================================================= -->
<div class="modal fade" id="editPeriodModal" tabindex="-1" aria-labelledby="editPeriodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="<?= url('admin/timetable/save') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="editModalSlotId" value="0">
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark" id="editPeriodModalLabel" style="font-size: 16px;">
                        <i class="ti ti-edit me-1 text-primary"></i> Edit Timetable Period
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Class <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3 shadow-none" name="class_id" id="editModalClassId" required>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= e($c['id']) ?>"><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Day of Week <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3 shadow-none" name="day_of_week" id="editModalDayOfWeek" required>
                                <?php foreach ($days as $d): ?>
                                    <option value="<?= $d ?>"><?= $d ?></option>
                                <?php endforeach; ?>
                                <option value="Saturday">Saturday</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">Subject <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3 shadow-none" name="subject_id" id="editModalSubjectId" required>
                                <option value="">-- Choose Subject --</option>
                                <?php foreach ($subjects as $s): ?>
                                    <option value="<?= e($s['id']) ?>">
                                        <?= e($s['name']) ?> <?= !empty($s['code']) ? '(' . e($s['code']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small text-dark">Subject Teacher / Instructor</label>
                            <select class="form-select rounded-3 shadow-none" name="teacher_id" id="editModalTeacherId">
                                <option value="">-- No Specific Teacher / TBA --</option>
                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?= e($t['id']) ?>">
                                        <?= e($t['first_name'] . ' ' . $t['last_name']) ?> <?= !empty($t['staff_id']) ? '(' . e($t['staff_id']) . ')' : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control rounded-3 shadow-none" name="start_time" id="editModalStartTime" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control rounded-3 shadow-none" name="end_time" id="editModalEndTime" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-light border rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold shadow-sm">
                        <i class="ti ti-device-floppy me-1"></i> Update Period
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: COPY TIMETABLE TO ANOTHER CLASS                                  -->
<!-- ========================================================================= -->
<div class="modal fade" id="copyTimetableModal" tabindex="-1" aria-labelledby="copyTimetableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="<?= url('admin/timetable/copy') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-header border-bottom py-3 px-4">
                    <h5 class="modal-title fw-bold text-dark" id="copyTimetableModalLabel" style="font-size: 16px;">
                        <i class="ti ti-copy me-1 text-primary"></i> Copy Class Timetable
                    </h5>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Duplicate the complete weekly schedule from one class to another with one click.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Source Class <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3 shadow-none" name="from_class_id" required>
                                <?php foreach ($classes as $c): ?>
                                    <option value="<?= e($c['id']) ?>" <?= (int)$c['id'] === $classId ? 'selected' : '' ?>>
                                        <?= e($c['name']) ?> (<?= $classCounts[(int)$c['id']] ?? 0 ?> slots)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-dark">Target Destination Class <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3 shadow-none" name="to_class_id" required>
                                <option value="">-- Select Target --</option>
                                <?php foreach ($classes as $c): ?>
                                    <?php if ((int)$c['id'] !== $classId): ?>
                                        <option value="<?= e($c['id']) ?>"><?= e($c['name']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="overwrite" value="1" id="checkOverwrite" checked>
                                <label class="form-check-label small fw-semibold text-dark" for="checkOverwrite">
                                    Overwrite and replace existing periods in target class
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-light border rounded-3 px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-semibold shadow-sm">
                        <i class="ti ti-copy me-1"></i> Copy Timetable
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAddPeriodForDay(day) {
    document.getElementById('addModalDayOfWeek').value = day;
    const modalEl = document.getElementById('addPeriodModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}

function openEditPeriodModal(slot) {
    document.getElementById('editModalSlotId').value = slot.id;
    document.getElementById('editModalClassId').value = slot.class_id;
    document.getElementById('editModalDayOfWeek').value = slot.day_of_week;
    document.getElementById('editModalSubjectId').value = slot.subject_id;
    document.getElementById('editModalTeacherId').value = slot.teacher_id ? slot.teacher_id : '';
    document.getElementById('editModalStartTime').value = slot.start_time.substring(0, 5);
    document.getElementById('editModalEndTime').value = slot.end_time.substring(0, 5);

    const modalEl = document.getElementById('editPeriodModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
}
</script>

<style>
@media print {
    .sidebar, .admin-mobile-bar, nav[aria-label="breadcrumb"], .btn, .card-header button, #addPeriodModal, #editPeriodModal, #copyTimetableModal, .admin-powered-credit {
        display: none !important;
    }
    .admin-main {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    .card {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
        break-inside: avoid;
    }
}
</style>
