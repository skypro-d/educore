<?php
// views/teacher/dashboard.php
$teacher = $_SESSION['teacher'] ?? [];
$firstName = $teacher['first_name'] ?? 'Staff Member';
?>

<!-- Welcome Banner -->
<div class="card border-0 text-white mb-4 position-relative overflow-hidden shadow-sm" style="background: linear-gradient(135deg, #0f766e, #0d9488); border-radius: 16px;">
    <div class="card-body p-4 p-md-5 position-relative" style="z-index: 2;">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <?php if (!empty($teacher['photo'])): ?>
                    <img src="<?= url('uploads/' . e($teacher['photo'])) ?>" alt="Avatar" style="width:64px;height:64px;border-radius:16px;object-fit:cover;border:2px solid rgba(255,255,255,0.4);box-shadow:0 4px 12px rgba(0,0,0,0.15);">
                <?php else: ?>
                    <div style="width:64px;height:64px;border-radius:16px;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:700;border:2px solid rgba(255,255,255,0.3);">
                        <?= strtoupper(substr($firstName, 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <h1 class="h3 fw-bold mb-1" style="font-size:1.75rem;">Welcome, <?= e($teacher['name'] ?? 'Staff Member') ?> 👋</h1>
                    <p class="mb-0 opacity-75" style="font-size:14px;">
                        Role: <strong><?= e($teacher['role_title'] ?? ucwords(str_replace('_', ' ', $teacher['role'] ?? 'Teacher'))) ?></strong> &nbsp;|&nbsp; Staff ID: <span class="font-monospace"><strong><?= e($teacher['staff_id'] ?? '') ?></strong></span>
                    </p>
                </div>
            </div>
            <div class="text-md-end">
                <span class="badge bg-white text-dark py-2 px-3 fw-bold shadow-sm" style="border-radius:20px; font-size:12px;">
                    <i class="ti ti-calendar me-1 text-teal"></i> <?= e($academicYear) ?> (<?= e($term) ?> Term)
                </span>
                <?php if ($formClassName !== 'None'): ?>
                    <div class="mt-2 text-white-50" style="font-size:12px;">
                        Form Teacher of: <strong class="text-white"><?= e($formClassName) ?></strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- 8 Statistics Cards -->
<div class="row g-3 mb-4">
    <!-- Card 1: My Classes -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 14px; background:#fff;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;">My Classes</span>
                <div style="width:36px;height:36px;border-radius:8px;background:#e0f2fe;color:#0284c7;display:flex;align-items:center;justify-content:center;font-size:18px;">
                    <i class="ti ti-chalkboard"></i>
                </div>
            </div>
            <div style="font-size:1.8rem; font-weight:800; color:#0f172a;"><?= $classCount ?></div>
            <a href="<?= url('teacher/classes') ?>" class="text-decoration-none mt-2" style="font-size:12px; font-weight:600; color:#0284c7;">
                View class rosters →
            </a>
        </div>
    </div>

    <!-- Card 2: My Students -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 14px; background:#fff;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;">My Students</span>
                <div style="width:36px;height:36px;border-radius:8px;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:18px;">
                    <i class="ti ti-users"></i>
                </div>
            </div>
            <div style="font-size:1.8rem; font-weight:800; color:#0f172a;"><?= $studentCount ?></div>
            <a href="<?= url('teacher/students') ?>" class="text-decoration-none mt-2" style="font-size:12px; font-weight:600; color:#16a34a;">
                Search student directory →
            </a>
        </div>
    </div>

    <!-- Card 3: Present Today -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 14px; background:#fff;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;">Present Today</span>
                <div style="width:36px;height:36px;border-radius:8px;background:#ccfbf1;color:#0f766e;display:flex;align-items:center;justify-content:center;font-size:18px;">
                    <i class="ti ti-user-check"></i>
                </div>
            </div>
            <div style="font-size:1.8rem; font-weight:800; color:#0f766e;"><?= $presentToday ?></div>
            <div class="text-muted mt-2" style="font-size:12px;">Enrolled students marked in class</div>
        </div>
    </div>

    <!-- Card 4: Absent Today -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 14px; background:#fff;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;">Absent Today</span>
                <div style="width:36px;height:36px;border-radius:8px;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;font-size:18px;">
                    <i class="ti ti-user-x"></i>
                </div>
            </div>
            <div style="font-size:1.8rem; font-weight:800; color:#dc2626;"><?= $absentToday ?></div>
            <div class="text-muted mt-2" style="font-size:12px;">Unexcused absences</div>
        </div>
    </div>

    <!-- Card 5: Late Today -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 14px; background:#fff;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;">Late Arrivals</span>
                <div style="width:36px;height:36px;border-radius:8px;background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;font-size:18px;">
                    <i class="ti ti-clock-exclamation"></i>
                </div>
            </div>
            <div style="font-size:1.8rem; font-weight:800; color:#d97706;"><?= $lateToday ?></div>
            <div class="text-muted mt-2" style="font-size:12px;">Check-ins after morning threshold</div>
        </div>
    </div>

    <!-- Card 6: Attendance % -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 14px; background:#fff;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;">Attendance Rate</span>
                <div style="width:36px;height:36px;border-radius:8px;background:#f3e8ff;color:#9333ea;display:flex;align-items:center;justify-content:center;font-size:18px;">
                    <i class="ti ti-percentage"></i>
                </div>
            </div>
            <div style="font-size:1.8rem; font-weight:800; color:#9333ea;"><?= $attendancePercentage ?>%</div>
            <div class="text-muted mt-2" style="font-size:12px;">Daily attendance percentage</div>
        </div>
    </div>

    <!-- Card 7: Pending Assignments -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 14px; background:#fff;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;">Pending Assignments</span>
                <div style="width:36px;height:36px;border-radius:8px;background:#e0e7ff;color:#4338ca;display:flex;align-items:center;justify-content:center;font-size:18px;">
                    <i class="ti ti-file-certificate"></i>
                </div>
            </div>
            <div style="font-size:1.8rem; font-weight:800; color:#4338ca;"><?= $pendingAssignments ?></div>
            <a href="<?= url('teacher/assignments') ?>" class="text-decoration-none mt-2" style="font-size:12px; font-weight:600; color:#4338ca;">
                Review submissions →
            </a>
        </div>
    </div>

    <!-- Card 8: Pending Results -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 14px; background:#fff;">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <span style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.5px;">Pending Results</span>
                <div style="width:36px;height:36px;border-radius:8px;background:#ffedd5;color:#c2410c;display:flex;align-items:center;justify-content:center;font-size:18px;">
                    <i class="ti ti-report-analytics"></i>
                </div>
            </div>
            <div style="font-size:1.8rem; font-weight:800; color:#c2410c;"><?= $pendingResults ?></div>
            <a href="<?= url('teacher/results') ?>" class="text-decoration-none mt-2" style="font-size:12px; font-weight:600; color:#c2410c;">
                Enter & submit grades →
            </a>
        </div>
    </div>
</div>

<!-- Quick Actions Section -->
<div class="card border-0 shadow-sm mb-4 p-4" style="border-radius:14px; background:#fff;">
    <h3 class="h5 fw-bold mb-3" style="color:#0f172a; font-size:1.1rem;">
        <i class="ti ti-bolt text-warning me-2"></i> Quick Actions
    </h3>
    <div class="row g-3">
        <?php if (staff_can('attendance.mark')): ?>
            <div class="col-md-4 col-sm-6">
                <a href="<?= url('teacher/attendance') ?>" class="btn btn-outline-primary w-100 p-3 d-flex align-items-center gap-3 text-start" style="border-radius:10px;">
                    <div style="width:38px;height:38px;border-radius:8px;background:#e0f2fe;color:#0284c7;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                        <i class="ti ti-calendar-check"></i>
                    </div>
                    <div>
                        <div style="font-weight:700; color:#0f172a; font-size:13.5px;">Mark Attendance</div>
                        <small class="text-muted" style="font-size:11.5px;">Register presence or scan QR code</small>
                    </div>
                </a>
            </div>
        <?php endif; ?>

        <?php if (staff_can('results.enter')): ?>
            <div class="col-md-4 col-sm-6">
                <a href="<?= url('teacher/results') ?>" class="btn btn-outline-teal w-100 p-3 d-flex align-items-center gap-3 text-start" style="border-radius:10px; border-color:#0f766e; color:#0f766e;">
                    <div style="width:38px;height:38px;border-radius:8px;background:#ccfbf1;color:#0f766e;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                        <i class="ti ti-report-analytics"></i>
                    </div>
                    <div>
                        <div style="font-weight:700; color:#0f172a; font-size:13.5px;">Enter Results</div>
                        <small class="text-muted" style="font-size:11.5px;">Record CA tests, exam scores, and grades</small>
                    </div>
                </a>
            </div>
        <?php endif; ?>

        <?php if (staff_can('students.view')): ?>
            <div class="col-md-4 col-sm-6">
                <a href="<?= url('teacher/students') ?>" class="btn btn-outline-secondary w-100 p-3 d-flex align-items-center gap-3 text-start" style="border-radius:10px;">
                    <div style="width:38px;height:38px;border-radius:8px;background:#f1f5f9;color:#475569;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                        <i class="ti ti-users"></i>
                    </div>
                    <div>
                        <div style="font-weight:700; color:#0f172a; font-size:13.5px;">View Students</div>
                        <small class="text-muted" style="font-size:11.5px;">Access student profiles & academic history</small>
                    </div>
                </a>
            </div>
        <?php endif; ?>

        <?php if (staff_can('assignments.create')): ?>
            <div class="col-md-4 col-sm-6">
                <a href="<?= url('teacher/assignments/create') ?>" class="btn btn-outline-success w-100 p-3 d-flex align-items-center gap-3 text-start" style="border-radius:10px;">
                    <div style="width:38px;height:38px;border-radius:8px;background:#dcfce7;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                        <i class="ti ti-file-plus"></i>
                    </div>
                    <div>
                        <div style="font-weight:700; color:#0f172a; font-size:13.5px;">Create Assignment</div>
                        <small class="text-muted" style="font-size:11.5px;">Assign coursework to student classes</small>
                    </div>
                </a>
            </div>
        <?php endif; ?>

        <?php if (staff_can('timetable.view')): ?>
            <div class="col-md-4 col-sm-6">
                <a href="<?= url('teacher/timetable') ?>" class="btn btn-outline-info w-100 p-3 d-flex align-items-center gap-3 text-start" style="border-radius:10px;">
                    <div style="width:38px;height:38px;border-radius:8px;background:#e0f2fe;color:#0284c7;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                        <i class="ti ti-calendar-time"></i>
                    </div>
                    <div>
                        <div style="font-weight:700; color:#0f172a; font-size:13.5px;">View Timetable</div>
                        <small class="text-muted" style="font-size:11.5px;">Check daily periods & classroom allocation</small>
                    </div>
                </a>
            </div>
        <?php endif; ?>

        <?php if (staff_can('announcements.create')): ?>
            <div class="col-md-4 col-sm-6">
                <a href="<?= url('teacher/announcements') ?>" class="btn btn-outline-warning w-100 p-3 d-flex align-items-center gap-3 text-start" style="border-radius:10px;">
                    <div style="width:38px;height:38px;border-radius:8px;background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;">
                        <i class="ti ti-speakerphone"></i>
                    </div>
                    <div>
                        <div style="font-weight:700; color:#0f172a; font-size:13.5px;">Send Announcement</div>
                        <small class="text-muted" style="font-size:11.5px;">Broadcast updates to your classes</small>
                    </div>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Schedule & Announcements Row -->
<div class="row g-4">
    <!-- Today's Timetable Schedule -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius:14px; background:#fff;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="h6 fw-bold mb-0" style="color:#0f172a; font-size:1rem;">
                    <i class="ti ti-calendar-time text-primary me-2"></i> Today's Timetable (<?= date('l') ?>)
                </h4>
                <a href="<?= url('teacher/timetable') ?>" class="text-decoration-none" style="font-size:12px; font-weight:600; color:var(--teacher-accent);">Weekly View →</a>
            </div>

            <?php if (empty($todaySchedule)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-calendar-off fs-1 d-block mb-2 text-secondary opacity-50"></i>
                    No classes or periods scheduled for today.
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($todaySchedule as $sch): ?>
                        <div class="p-3 border rounded-3 d-flex justify-content-between align-items-center" style="background:#f8fafc;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="px-2 py-1 bg-white border rounded text-center" style="min-width:70px;">
                                    <span style="font-size:11.5px; font-weight:700; color:#0f172a; display:block;"><?= date('g:i A', strtotime($sch['start_time'])) ?></span>
                                    <small class="text-muted" style="font-size:10px;"><?= date('g:i A', strtotime($sch['end_time'])) ?></small>
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold" style="font-size:14px; color:#0f172a;"><?= e($sch['subject_name']) ?></h5>
                                    <small class="text-muted" style="font-size:12px;"><?= e($sch['class_name']) ?></small>
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border py-1 px-2" style="font-size:11px;">Assigned</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Staff Announcements -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4 h-100" style="border-radius:14px; background:#fff;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="h6 fw-bold mb-0" style="color:#0f172a; font-size:1rem;">
                    <i class="ti ti-speakerphone text-warning me-2"></i> Staff Announcements
                </h4>
                <a href="<?= url('teacher/announcements') ?>" class="text-decoration-none" style="font-size:12px; font-weight:600; color:var(--teacher-accent);">All Announcements →</a>
            </div>

            <?php if (empty($announcements)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="ti ti-notes-off fs-1 d-block mb-2 text-secondary opacity-50"></i>
                    No staff notices published at this time.
                </div>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($announcements as $ann): ?>
                        <div class="p-3 border-start border-4 border-teal rounded-end bg-light" style="border-color:#0d9488 !important;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h5 class="fw-bold mb-0" style="font-size:13.5px; color:#0f172a;"><?= e($ann['title']) ?></h5>
                                <small class="text-muted" style="font-size:10.5px;">
                                    <?= date('M d, Y', strtotime($ann['published_at'] ?? $ann['created_at'])) ?>
                                </small>
                            </div>
                            <p class="mb-0 text-muted" style="font-size:12px; line-height:1.4;"><?= nl2br(e($ann['body'])) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
