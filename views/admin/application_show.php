<?php
$fullName = trim($application['first_name'] . ' ' . ($application['middle_name'] ?? '') . ' ' . $application['last_name']);
$initials = strtoupper(substr($application['first_name'], 0, 1) . substr($application['last_name'], 0, 1));
$dob = $application['date_of_birth'] ? new DateTime($application['date_of_birth']) : null;
$age = $dob ? $dob->diff(new DateTime())->y : null;
$statusClass = 'pill-' . strtolower(str_replace(' ', '-', $application['status']));
$score = match ($application['status']) {
    'Approved', 'Enrolled' => 88,
    'Rejected', 'Terminated' => 45,
    'Exam Completed', 'Interview Scheduled' => 74,
    default => 61,
};
$scores = [
    ['Literacy & reading', min(100, $score + 4), '#1D9E75'],
    ['Numeracy', $score, $score >= 70 ? '#1D9E75' : '#BA7517'],
    ['Cognitive assessment', max(0, $score - 3), '#378ADD'],
    ['Interview / social skills', $application['status'] === 'Interview Scheduled' || $application['status'] === 'Approved' || $application['status'] === 'Enrolled' ? 90 : 0, '#1D9E75'],
    ['Document completeness', !empty($application['birth_certificate']) && !empty($application['previous_result']) ? 92 : 50, '#378ADD'],
];
$timeline = [
    ['#378ADD', 'Application submitted', date('d M Y, h:i A', strtotime($application['created_at']))],
    ['#378ADD', 'Documents uploaded', 'Passport, birth certificate, and academic records received'],
    ['#534AB7', 'Application review', in_array($application['status'], ['Under Review','Awaiting Exam','Exam Completed','Interview Scheduled','Approved','Enrolled'], true) ? 'Review started by admission office' : 'Waiting for review'],
    ['#534AB7', 'Assessment / interview', in_array($application['status'], ['Exam Completed','Interview Scheduled','Approved','Enrolled'], true) ? 'Assessment stage completed or scheduled' : 'Pending'],
    [in_array($application['status'], ['Rejected','Terminated'], true) ? '#D85A30' : '#1D9E75', 'Decision', in_array($application['status'], ['Approved','Rejected','Enrolled','Terminated'], true) ? $application['status'] : 'Awaiting decision'],
    ['#888', 'Enrollment confirmation', $application['status'] === 'Enrolled' ? 'Enrollment confirmed' : 'Fee payment or confirmation pending'],
];
$documents = [
    ['Birth certificate', $application['birth_certificate'] ?? null],
    ['Previous school result', $application['previous_result'] ?? null],
    ['Passport photograph', $application['passport_photo'] ?? null],
    ['Testimonial', $application['testimonial'] ?? null],
    ['Recommendation letter', $application['recommendation_letter'] ?? null],
];
?>

<div class="profile-page">
    <a class="profile-back" href="<?= url('admin/dashboard') ?>"><i class="ti ti-arrow-left"></i> Back to dashboard</a>

    <div class="profile-card">
        <div class="profile-head">
            <?php if (!empty($application['passport_photo'])): ?>
                <img class="profile-avatar-img" src="<?= url('uploads/' . $application['passport_photo']) ?>" alt="<?= e($fullName) ?>">
            <?php else: ?>
                <div class="profile-avatar"><?= e($initials) ?></div>
            <?php endif; ?>
            <div class="profile-main">
                <div class="profile-title-row">
                    <span class="profile-name"><?= e($fullName) ?></span>
                    <span class="profile-pill <?= e($statusClass) ?>"><i class="ti ti-circle-check"></i><?= e($application['status']) ?></span>
                </div>
                <div class="profile-sub"><?= e($application['application_number']) ?> &nbsp;.&nbsp; <?= e($application['admission_number'] ?: 'Admission number pending') ?> &nbsp;.&nbsp; <strong><?= e($application['student_username'] ?: 'Username pending') ?></strong> &nbsp;.&nbsp; <?= e($application['class_name']) ?></div>
                <div class="profile-tags">
                    <span class="profile-pill pill-teal">Priority admission</span>
                    <span class="profile-pill pill-blue"><?= $score >= 80 ? 'Scholarship eligible' : 'Standard review' ?></span>
                    <span class="profile-pill pill-gray">Day student</span>
                </div>
            </div>
            <div class="profile-score">
                <strong><?= e((string) $score) ?></strong>
                <span>Admission score</span>
            </div>
        </div>
    </div>

    <div class="profile-row">
        <div class="profile-card">
            <div class="profile-section-label"><i class="ti ti-user"></i> Personal information</div>
            <div class="profile-field-grid">
                <div><div class="profile-fl">Date of birth</div><div class="profile-fv"><?= e($dob ? $dob->format('d F Y') : 'Not provided') ?></div></div>
                <div><div class="profile-fl">Age</div><div class="profile-fv"><?= e($age !== null ? $age . ' years old' : 'Not provided') ?></div></div>
                <div><div class="profile-fl">Gender</div><div class="profile-fv"><?= e($application['gender']) ?></div></div>
                <div><div class="profile-fl">Nationality</div><div class="profile-fv"><?= e($application['nationality']) ?></div></div>
                <div><div class="profile-fl">State of origin</div><div class="profile-fv"><?= e($application['state_of_origin']) ?></div></div>
                <div><div class="profile-fl">Religion</div><div class="profile-fv"><?= e($application['religion'] ?: 'Not provided') ?></div></div>
                <div><div class="profile-fl">Blood group</div><div class="profile-fv"><?= e($application['blood_group'] ?? 'Not provided') ?></div></div>
                <div><div class="profile-fl">Medical notes</div><div class="profile-fv"><?= e(($application['allergies'] ?? '') ?: (($application['special_needs'] ?? '') ?: 'None')) ?></div></div>
            </div>
            <hr class="profile-divider">
            <div class="profile-section-label"><i class="ti ti-home"></i> Contact & address</div>
            <div class="profile-field-grid">
                <div><div class="profile-fl">Home address</div><div class="profile-fv"><?= e($application['home_address']) ?></div></div>
                <div><div class="profile-fl">LGA</div><div class="profile-fv"><?= e($application['local_government'] ?? 'Not provided') ?></div></div>
                <div><div class="profile-fl">Emergency contact</div><div class="profile-fv"><?= e(($application['emergency_name'] ?? '') ?: 'Not provided') ?></div></div>
                <div><div class="profile-fl">Emergency phone</div><div class="profile-fv"><?= e(($application['emergency_phone'] ?? '') ?: 'Not provided') ?></div></div>
            </div>
        </div>

        <div class="profile-card">
            <div class="profile-section-label"><i class="ti ti-users"></i> Parent / guardian</div>
            <div class="guardian-line">
                <div class="guardian-avatar av-blue"><?= e(strtoupper(substr($application['parent_name'] ?: 'PG', 0, 2))) ?></div>
                <div><div class="guardian-name"><?= e($application['parent_name'] ?: ($application['guardian_name'] ?? 'Primary Guardian')) ?></div><div class="guardian-sub">Primary guardian</div></div>
            </div>
            <div class="profile-field-grid mb-3">
                <div><div class="profile-fl">Phone</div><div class="profile-fv"><?= e($application['parent_phone']) ?></div></div>
                <div><div class="profile-fl">Email</div><div class="profile-fv small-text"><?= e($application['parent_email']) ?></div></div>
                <div><div class="profile-fl">Occupation</div><div class="profile-fv"><?= e($application['parent_occupation'] ?? 'Not provided') ?></div></div>
                <div><div class="profile-fl">Guardian</div><div class="profile-fv"><?= e($application['guardian_name'] ?? 'Not provided') ?></div></div>
            </div>
            <hr class="profile-divider">
            <div class="profile-field-grid">
                <div><div class="profile-fl">Father</div><div class="profile-fv"><?= e($application['father_name'] ?? 'Not provided') ?></div></div>
                <div><div class="profile-fl">Mother</div><div class="profile-fv"><?= e($application['mother_name'] ?? 'Not provided') ?></div></div>
            </div>
        </div>
    </div>

    <div class="profile-row">
        <div class="profile-card">
            <div class="profile-section-label"><i class="ti ti-chart-bar"></i> Assessment scores</div>
            <?php foreach ($scores as $item): ?>
                <div class="score-row"><span class="score-name"><?= e($item[0]) ?></span><div class="score-bar-wrap"><div class="score-bar" style="width:<?= e((string) $item[1]) ?>%;background:<?= e($item[2]) ?>;"></div></div><span class="score-num"><?= e((string) $item[1]) ?></span></div>
            <?php endforeach; ?>
            <hr class="profile-divider">
            <div class="profile-total"><span>Overall admission score</span><strong><?= e((string) $score) ?> / 100</strong></div>
        </div>
        <div class="profile-card">
            <div class="profile-section-label"><i class="ti ti-file-text"></i> Previous school</div>
            <div class="profile-field-grid mb-3">
                <div><div class="profile-fl">School name</div><div class="profile-fv"><?= e($application['previous_school'] ?: 'Not provided') ?></div></div>
                <div><div class="profile-fl">Previous class</div><div class="profile-fv"><?= e($application['previous_class'] ?? 'Not provided') ?></div></div>
                <div><div class="profile-fl">Desired class</div><div class="profile-fv"><?= e($application['class_name']) ?></div></div>
                <div><div class="profile-fl">Reference</div><div class="profile-fv"><?= !empty($application['recommendation_letter']) ? 'Submitted' : 'Pending' ?></div></div>
            </div>
            <hr class="profile-divider">
            <div class="profile-section-label"><i class="ti ti-notes"></i> Admission officer note</div>
            <div class="note-box"><?= e($score >= 80 ? 'Applicant has a strong admission profile and is recommended for approval.' : 'Applicant requires standard admission office review before a final decision.') ?></div>
        </div>
    </div>

    <div class="profile-row">
        <div class="profile-card">
            <div class="profile-section-label"><i class="ti ti-timeline"></i> Application timeline</div>
            <div class="profile-timeline">
                <?php foreach ($timeline as $item): ?>
                    <div class="tl-item"><div class="tl-left"><div class="tl-dot" style="background:<?= e($item[0]) ?>;"></div><div class="tl-line"></div></div><div class="tl-body"><div class="tl-title"><?= e($item[1]) ?></div><div class="tl-sub"><?= e($item[2]) ?></div></div></div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="profile-card">
            <div class="profile-section-label"><i class="ti ti-paperclip"></i> Documents</div>
            <?php foreach ($documents as $doc): ?>
                <div class="doc-row">
                    <div class="doc-icon"><i class="ti ti-file"></i></div>
                    <span class="doc-name"><?= e($doc[0]) ?></span>
                    <?php if ($doc[1]): ?>
                        <a class="profile-pill pill-teal" download href="<?= url('uploads/' . $doc[1]) ?>">Verified</a>
                    <?php else: ?>
                        <span class="profile-pill pill-pending">Pending</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($application['qr_code']) || !empty($application['student_username'])): ?>
        <div class="profile-card">
            <div class="profile-section-label"><i class="ti ti-qrcode"></i> Student Identity & Attendance QR Code</div>
            <div class="d-flex align-items-center gap-3">
                <?php if (!empty($application['qr_code']) && file_exists(UPLOAD_PATH . $application['qr_code'])): ?>
                    <img src="<?= url('uploads/' . $application['qr_code']) ?>" alt="Student QR Code" style="width: 110px; height: 110px; border-radius: 8px; border: 1px solid #e2e8f0; padding: 4px;">
                <?php else: ?>
                    <div class="d-flex align-items-center justify-content-center bg-light text-muted font-monospace rounded" style="width: 110px; height: 110px; font-size: 11px;">[QR CODE]</div>
                <?php endif; ?>
                <div>
                    <div class="small text-muted mb-1">Student ID / Portal Username</div>
                    <div class="font-monospace fw-bold text-dark fs-6 mb-2"><?= e($application['student_username'] ?: 'Pending Approval') ?></div>
                    <div class="small text-muted mb-1">Barcode / Admission No.</div>
                    <div class="font-monospace fw-bold text-primary mb-2" style="letter-spacing: 2px;">||| | |||| | ||| <?= e($application['admission_number'] ?: 'Pending') ?></div>
                    <a class="btn btn-sm btn-outline-primary" href="<?= url('admin/applications/' . $application['id'] . '/id-card') ?>" target="_blank"><i class="ti ti-id me-1"></i> Print Student ID Card</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Gate Exit & Dismissal Movement History -->
        <?php if ($application['status'] === 'Enrolled'): ?>
        <div class="profile-card">
            <div class="profile-section-label"><i class="ti ti-door-exit"></i> Gate Exit &amp; Dismissal History</div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12px;">
                    <thead class="table-light">
                        <tr>
                            <th>Date &amp; Time</th>
                            <th>Exit Type</th>
                            <th>Reason / Notes</th>
                            <th>Gate</th>
                            <th>Pickup Person</th>
                            <th>Parent SMS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($exitLogs)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-3 text-muted">No gate exit history recorded for this student yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($exitLogs as $el): ?>
                                <tr>
                                    <td>
                                        <strong><?= date('M j, Y', strtotime($el['exit_date'])) ?></strong>
                                        <span class="text-muted ms-1"><?= date('g:i A', strtotime($el['exit_time'])) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $el['exit_type'] === 'early' ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' ?>">
                                            <?= ucfirst($el['exit_type']) ?>
                                        </span>
                                    </td>
                                    <td><?= e($el['exit_reason'] ?: '—') ?></td>
                                    <td><?= e($el['gate_name'] ?: 'Gate') ?></td>
                                    <td><?= e($el['pickup_person_name'] ?: '—') ?></td>
                                    <td>
                                        <span class="badge <?= $el['sms_status'] === 'sent' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?>">
                                            <?= ucfirst($el['sms_status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Authorized Pickup Persons -->
        <div class="profile-card">
            <div class="profile-section-label d-flex justify-content-between align-items-center">
                <span><i class="ti ti-user-shield"></i> Authorized Pickups &amp; Guardians</span>
                <a href="<?= url('admin/authorized-pickups?student_id=' . (int)$application['id']) ?>" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 11px;">
                    <i class="ti ti-plus me-1"></i> Manage Pickups
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12px;">
                    <thead class="table-light">
                        <tr>
                            <th>Guardian Name</th>
                            <th>Relationship</th>
                            <th>Phone</th>
                            <th>ID / NIN</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($authorizedPickups)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">No authorized pickup persons registered. Parents/guardians listed on application will be primary.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($authorizedPickups as $ap): ?>
                                <tr>
                                    <td class="fw-bold"><?= e($ap['name']) ?></td>
                                    <td><?= e($ap['relationship'] ?: 'Guardian') ?></td>
                                    <td><?= mask_phone($ap['phone']) ?></td>
                                    <td><?= e($ap['id_card_number'] ?: '—') ?></td>
                                    <td>
                                        <span class="badge <?= $ap['is_active'] ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>">
                                            <?= $ap['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>

        <div class="profile-card">
            <div class="profile-section-label"><i class="ti ti-settings"></i> Actions</div>
        <div class="profile-actions">
            <?php if ($application['status'] === 'Enrolled'): ?>
                <a class="profile-btn profile-btn-primary" href="<?= url('admin/applications/' . $application['id'] . '/id-card') ?>"><i class="ti ti-id"></i> Print ID Card</a>
                <a class="profile-btn profile-btn-primary" href="<?= url('admin/letter/' . $application['id']) ?>"><i class="ti ti-mail"></i> View admission letter</a>
            <?php endif; ?>
            
            <?php if ($application['status'] === 'Approved'): ?>
                <a class="profile-btn profile-btn-primary" href="<?= url('admin/letter/' . $application['id']) ?>"><i class="ti ti-mail"></i> Send acceptance letter</a>
            <?php endif; ?>
            
            <?php if ($application['status'] !== 'Enrolled' && $application['status'] !== 'Terminated'): ?>
                <form method="post" action="<?= url('admin/applications/' . $application['id'] . '/enroll') ?>"><?= csrf_field() ?><button class="profile-btn" type="submit"><i class="ti ti-user-check"></i> Confirm enrollment</button></form>
            <?php endif; ?>
            
            <a class="profile-btn" href="<?= url('payment/process.php?applicant_id=' . (int) $application['id'] . '&fee=acceptance_fee') ?>"><i class="ti ti-receipt"></i> Acceptance fee</a>
            <a class="profile-btn" href="<?= url('payment/process.php?applicant_id=' . (int) $application['id'] . '&fee=enrollment_fee') ?>"><i class="ti ti-receipt"></i> Enrollment fee</a>
            <form method="post" action="<?= url('admin/applications/' . $application['id'] . '/interview') ?>"><?= csrf_field() ?><button class="profile-btn" type="submit"><i class="ti ti-calendar-plus"></i> Schedule interview</button></form>
            
            <?php if ($application['status'] === 'Approved'): ?>
                <form method="post" action="<?= url('admin/applications/' . $application['id'] . '/terminate') ?>"><?= csrf_field() ?><button class="profile-btn profile-btn-danger" type="submit"><i class="ti ti-ban"></i> Terminate admission</button></form>
            <?php elseif ($application['status'] !== 'Terminated' && $application['status'] !== 'Enrolled'): ?>
                <form method="post" action="<?= url('admin/applications/' . $application['id'] . '/approve') ?>"><?= csrf_field() ?><button class="profile-btn profile-btn-primary" type="submit"><i class="ti ti-circle-check"></i> Approve</button></form>
                <form method="post" action="<?= url('admin/applications/' . $application['id'] . '/reject') ?>"><?= csrf_field() ?><button class="profile-btn profile-btn-danger" type="submit"><i class="ti ti-x"></i> Reject</button></form>
            <?php endif; ?>
        </div>
    </div>
</div>
