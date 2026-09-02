<?php
// views/teacher/student_profile.php — Controlled Student Profile
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="<?= url('teacher/students') ?>" class="text-decoration-none text-muted" style="font-size:12px; font-weight:600;">
            ← Back to My Students
        </a>
        <h1 class="mt-1" style="font-size:1.5rem; font-weight:700; color:#0f172a; margin:0 0 4px 0;">
            <?= e($student['first_name'] . ' ' . $student['last_name']) ?>
        </h1>
        <p style="color:#64748b; font-size:0.875rem; margin:0;">
            Class: <strong><?= e($student['class_name']) ?></strong> &nbsp;|&nbsp; Admission No: <span class="font-monospace"><strong><?= e($student['admission_number'] ?: 'Pending') ?></strong></span>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('teacher/messages?student_id=' . (int)$student['id']) ?>" class="btn btn-sm btn-outline-primary" style="font-weight:600; border-radius:8px;">
            <i class="ti ti-message me-1"></i> Message Parent
        </a>
    </div>
</div>

<!-- Header Card: Photo & Basic Details -->
<div class="card border-0 shadow-sm p-4 mb-4" style="border-radius:14px; background:#fff;">
    <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
        <?php if (!empty($student['passport_photo'])): ?>
            <img src="<?= url('uploads/' . e($student['passport_photo'])) ?>" alt="Passport" style="width:90px;height:90px;border-radius:16px;object-fit:cover;border:2px solid #e2e8f0;">
        <?php else: ?>
            <div style="width:90px;height:90px;border-radius:16px;background:#f1f5f9;color:#475569;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:700;">
                <?= strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)) ?>
            </div>
        <?php endif; ?>
        <div class="flex-grow-1">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <h3 class="h5 fw-bold mb-0" style="color:#0f172a;"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h3>
                <span class="badge bg-success-subtle text-success border border-success-subtle py-1 px-2" style="font-size:11px;">
                    <?= e($student['student_status'] ?? 'Active') ?>
                </span>
                <span class="badge bg-light text-dark border py-1 px-2" style="font-size:11px;">
                    <?= e($student['gender']) ?>
                </span>
            </div>
            <div class="row g-2 text-muted" style="font-size:12.5px;">
                <div class="col-sm-6">
                    <i class="ti ti-calendar me-1"></i> Date of Birth: <strong><?= e($student['date_of_birth'] ? date('M d, Y', strtotime($student['date_of_birth'])) : 'N/A') ?></strong>
                </div>
                <div class="col-sm-6">
                    <i class="ti ti-id me-1"></i> Application No: <span class="font-monospace"><strong><?= e($student['application_number']) ?></strong></span>
                </div>
            </div>
        </div>
        <!-- Attendance Quick Stat -->
        <div class="border-start ps-md-4 text-center">
            <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Attendance Rate</div>
            <div style="font-size:1.8rem; font-weight:800; color:<?= $attendanceRate >= 75 ? '#15803d' : '#b45309' ?>;">
                <?= $attendanceRate ?>%
            </div>
            <small class="text-muted" style="font-size:11px;"><?= $presentCount ?> Present / <?= $totalDays ?> Days</small>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<ul class="nav nav-pills mb-4 gap-2" id="profileTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active py-2 px-3 fw-bold" style="border-radius:8px; font-size:13px;" id="academic-tab" data-bs-toggle="tab" data-bs-target="#academic" type="button" role="tab">
            <i class="ti ti-report-analytics me-1"></i> Academic Results
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link py-2 px-3 fw-bold" style="border-radius:8px; font-size:13px;" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance" type="button" role="tab">
            <i class="ti ti-calendar-check me-1"></i> Attendance Breakdown
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link py-2 px-3 fw-bold" style="border-radius:8px; font-size:13px;" id="assignments-tab" data-bs-toggle="tab" data-bs-target="#assignments" type="button" role="tab">
            <i class="ti ti-notebook me-1"></i> Coursework & Assignments
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link py-2 px-3 fw-bold" style="border-radius:8px; font-size:13px;" id="parent-tab" data-bs-toggle="tab" data-bs-target="#parent" type="button" role="tab">
            <i class="ti ti-user-heart me-1"></i> Parent / Guardian Contact
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link py-2 px-3 fw-bold" style="border-radius:8px; font-size:13px;" id="behaviour-tab" data-bs-toggle="tab" data-bs-target="#behaviour" type="button" role="tab">
            <i class="ti ti-notes me-1"></i> Teacher Remarks
        </button>
    </li>
</ul>

<!-- Tab Content Panes -->
<div class="tab-content" id="profileTabsContent">
    <!-- 1. ACADEMIC RESULTS -->
    <div class="tab-pane fade show active" id="academic" role="tabpanel">
        <div class="card border-0 shadow-sm p-4" style="border-radius:14px; background:#fff;">
            <h4 class="h6 fw-bold mb-3" style="color:#0f172a;">Academic Performance Record</h4>
            <?php if (empty($results)): ?>
                <p class="text-muted text-center py-4 mb-0">No academic results recorded for this student yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase" style="font-size:11px; font-weight:700; color:#64748b;">
                            <tr>
                                <th>Subject</th>
                                <th>Term / Session</th>
                                <th class="text-center">CA 1 (10)</th>
                                <th class="text-center">CA 2 (10)</th>
                                <th class="text-center">Mid-Term (20)</th>
                                <th class="text-center">Exam (60)</th>
                                <th class="text-center">Total (100)</th>
                                <th class="text-center">Grade</th>
                                <th>Teacher Remark</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($results as $res): ?>
                                <tr>
                                    <td class="fw-bold" style="color:#0f172a;"><?= e($res['subject_name']) ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= e($res['term']) ?> Term (<?= e($res['academic_year']) ?>)</span></td>
                                    <td class="text-center font-monospace"><?= $res['ca1'] !== null ? number_format((float)$res['ca1'], 1) : '—' ?></td>
                                    <td class="text-center font-monospace"><?= $res['ca2'] !== null ? number_format((float)$res['ca2'], 1) : '—' ?></td>
                                    <td class="text-center font-monospace"><?= $res['mid_term'] !== null ? number_format((float)$res['mid_term'], 1) : '—' ?></td>
                                    <td class="text-center font-monospace"><?= $res['exam'] !== null ? number_format((float)$res['exam'], 1) : '—' ?></td>
                                    <td class="text-center fw-bold font-monospace" style="color:#0f766e;"><?= $res['total'] !== null ? number_format((float)$res['total'], 1) : '—' ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-dark px-2 py-1"><?= e($res['grade'] ?? '—') ?></span>
                                    </td>
                                    <td style="font-size:12px; color:#475569;"><?= e($res['teacher_remark'] ?: '—') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 2. ATTENDANCE BREAKDOWN -->
    <div class="tab-pane fade" id="attendance" role="tabpanel">
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 text-center" style="border-radius:12px; background:#fff;">
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Days Present</div>
                    <div style="font-size:1.8rem; font-weight:800; color:#16a34a;"><?= $presentCount ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 text-center" style="border-radius:12px; background:#fff;">
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Late Arrivals</div>
                    <div style="font-size:1.8rem; font-weight:800; color:#d97706;"><?= $lateCount ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 text-center" style="border-radius:12px; background:#fff;">
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Days Absent</div>
                    <div style="font-size:1.8rem; font-weight:800; color:#dc2626;"><?= $absentCount ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm p-3 text-center" style="border-radius:12px; background:#fff;">
                    <div style="font-size:11px; font-weight:700; text-transform:uppercase; color:#64748b;">Overall Rate</div>
                    <div style="font-size:1.8rem; font-weight:800; color:#0f766e;"><?= $attendanceRate ?>%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. ASSIGNMENTS -->
    <div class="tab-pane fade" id="assignments" role="tabpanel">
        <div class="card border-0 shadow-sm p-4" style="border-radius:14px; background:#fff;">
            <h4 class="h6 fw-bold mb-3" style="color:#0f172a;">Coursework Submissions</h4>
            <?php if (empty($submissions)): ?>
                <p class="text-muted text-center py-4 mb-0">No assignment submissions recorded for this student.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase" style="font-size:11px; font-weight:700; color:#64748b;">
                            <tr>
                                <th>Assignment</th>
                                <th>Subject</th>
                                <th>Submitted On</th>
                                <th>Score</th>
                                <th>Teacher Feedback</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $sub): ?>
                                <tr>
                                    <td class="fw-bold" style="color:#0f172a;"><?= e($sub['assignment_title']) ?></td>
                                    <td><?= e($sub['subject_name']) ?></td>
                                    <td style="font-size:12px; color:#64748b;"><?= date('M d, Y', strtotime($sub['submitted_at'])) ?></td>
                                    <td class="fw-bold" style="color:#0f766e;">
                                        <?= $sub['score'] !== null ? e($sub['score']) . ' / ' . e($sub['max_score']) : '<span class="badge bg-warning text-dark">Pending Grade</span>' ?>
                                    </td>
                                    <td style="font-size:12px; color:#475569;"><?= e($sub['feedback'] ?: 'No feedback entered') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- 4. PARENT INFORMATION (CONTROLLED) -->
    <div class="tab-pane fade" id="parent" role="tabpanel">
        <div class="card border-0 shadow-sm p-4" style="border-radius:14px; background:#fff;">
            <h4 class="h6 fw-bold mb-3" style="color:#0f172a;">Guardian Information</h4>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted" style="font-size:12px;">Parent / Guardian Name</label>
                    <div class="fw-bold" style="font-size:14px; color:#0f172a;"><?= e($student['parent_name'] ?: 'Not Provided') ?></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted" style="font-size:12px;">Contact Phone</label>
                    <div class="fw-bold" style="font-size:14px; color:#0f172a;">
                        <?= e($student['parent_phone'] ?: 'Not Provided') ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted" style="font-size:12px;">Contact Email</label>
                    <div class="fw-bold" style="font-size:14px; color:#0f172a;"><?= e($student['parent_email'] ?: 'Not Provided') ?></div>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted" style="font-size:12px;">Emergency Contact</label>
                    <div class="fw-bold" style="font-size:14px; color:#0f172a;">
                        <?= e($student['emergency_name'] ?: '—') ?> (<?= e($student['emergency_phone'] ?: '—') ?>)
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. BEHAVIOUR & REMARKS -->
    <div class="tab-pane fade" id="behaviour" role="tabpanel">
        <div class="card border-0 shadow-sm p-4" style="border-radius:14px; background:#fff;">
            <h4 class="h6 fw-bold mb-3" style="color:#0f172a;">Teacher Term Remarks & Rankings</h4>
            <?php if (empty($remarks)): ?>
                <p class="text-muted text-center py-4 mb-0">No behaviour or term remarks recorded yet.</p>
            <?php else: ?>
                <div class="d-flex flex-column gap-3">
                    <?php foreach ($remarks as $rem): ?>
                        <div class="p-3 border rounded-3 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary-subtle text-primary border" style="font-size:11px;">
                                    <?= e($rem['term']) ?> Term (<?= e($rem['academic_year']) ?>) — <?= e($rem['class_name']) ?>
                                </span>
                                <?php if (!empty($rem['position'])): ?>
                                    <span class="badge bg-warning text-dark fw-bold">
                                        Position: <?= e($rem['position']) ?> of <?= e($rem['class_size'] ?? '—') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div style="font-size:13px; color:#334155;">
                                <strong>Class Teacher Remark:</strong> <?= e($rem['class_teacher_remark'] ?: 'No remark entered') ?>
                            </div>
                            <?php if (!empty($rem['principal_remark'])): ?>
                                <div class="mt-1" style="font-size:12.5px; color:#64748b;">
                                    <strong>Principal Remark:</strong> <?= e($rem['principal_remark']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
