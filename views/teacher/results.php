<?php
// views/teacher/results.php — Comprehensive Teacher Result Entry & Analytics
$isLocked = in_array($workflowStatus, ['approved', 'published'], true) && !staff_can('results.approve');
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-teal text-white py-1 px-2 fw-semibold" style="font-size:11px;">TEACHER PORTAL</span>
            <span class="text-muted" style="font-size:12px;">/</span>
            <span class="text-muted fw-semibold" style="font-size:12px;">RESULTS MANAGEMENT</span>
        </div>
        <h1 class="h3 fw-bold mb-1" style="color:#0f172a; font-size:1.6rem;">
            <?= e($selectedClass['name'] ?? 'Class') ?> — Results Entry
        </h1>
        <p class="text-muted mb-0" style="font-size:13.5px;">
            Enter and manage student continuous assessment scores, tests, assignments, and examination grades.
        </p>
    </div>

    <div class="d-flex align-items-center gap-2">
        <?php
        $statusMap = [
            'draft'     => ['bg' => '#fef3c7', 'col' => '#92400e', 'border' => '#fde68a', 'icon' => 'ti-edit', 'label' => 'Draft / In-Progress'],
            'submitted' => ['bg' => '#ffedd5', 'col' => '#9a3412', 'border' => '#fed7aa', 'icon' => 'ti-send', 'label' => 'Submitted for Approval'],
            'approved'  => ['bg' => '#dcfce7', 'col' => '#166534', 'border' => '#bbf7d0', 'icon' => 'ti-check', 'label' => 'Approved (Locked)'],
            'published' => ['bg' => '#e0e7ff', 'col' => '#3730a3', 'border' => '#c7d2fe', 'icon' => 'ti-world-check', 'label' => 'Published to Parents & Students'],
        ];
        $currSt = $statusMap[$workflowStatus] ?? $statusMap['draft'];
        ?>
        <div class="px-3 py-2 border rounded-pill d-flex align-items-center gap-2 shadow-sm"
             style="background:<?= $currSt['bg'] ?>; color:<?= $currSt['col'] ?>; border-color:<?= $currSt['border'] ?> !important; font-size:12.5px; font-weight:700;">
            <i class="ti <?= $currSt['icon'] ?>"></i>
            <span><?= $currSt['label'] ?></span>
        </div>

        <?php if (!empty($classOverview['subjects'])): ?>
            <button type="button" class="btn btn-outline-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-1"
                    data-bs-toggle="modal" data-bs-target="#classOverviewModal"
                    style="border-radius:20px; font-weight:600; font-size:12.5px; background:#fff;">
                <i class="ti ti-layout-grid"></i>
                <span>Class Subject Matrix (<?= count($classOverview['subjects']) ?>)</span>
            </button>
        <?php endif; ?>

        <?php if ($classId > 0 && $subjectId > 0 && !empty($students)): ?>
            <a href="<?= url("teacher/results/print?class_id={$classId}&subject_id={$subjectId}&term=" . urlencode($term) . "&academic_year=" . urlencode($academicYear)) ?>"
               target="_blank" class="btn btn-outline-secondary btn-sm px-3 shadow-sm d-flex align-items-center gap-1"
               style="border-radius:20px; font-weight:600; font-size:12.5px; background:#fff;">
                <i class="ti ti-printer"></i>
                <span>Print Broadsheet</span>
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- 4-Selector Filter Bar: Academic Session, Term, Class, Subject -->
<div class="card border-0 shadow-sm mb-4" style="border-radius:14px; background:#fff;">
    <div class="card-body p-3">
        <form method="GET" action="<?= url('teacher/results') ?>" id="resultsFilterForm" class="row g-2 align-items-end">
            <!-- 1. Academic Session -->
            <div class="col-6 col-md-3">
                <label class="form-label mb-1" style="font-size:11px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.5px;">
                    <i class="ti ti-calendar text-teal me-1"></i> Academic Session
                </label>
                <select name="academic_year" class="form-select form-select-sm" onchange="this.form.submit()" style="border-radius:8px; font-weight:600;">
                    <?php 
                    $currYear = current_academic_year();
                    $sessions = array_unique([$currYear, '2024/2025', '2025/2026', '2026/2027']);
                    foreach ($sessions as $ses): 
                    ?>
                        <option value="<?= e($ses) ?>" <?= $ses === $academicYear ? 'selected' : '' ?>><?= e($ses) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- 2. Term -->
            <div class="col-6 col-md-2">
                <label class="form-label mb-1" style="font-size:11px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.5px;">
                    <i class="ti ti-clock text-teal me-1"></i> Term
                </label>
                <select name="term" class="form-select form-select-sm" onchange="this.form.submit()" style="border-radius:8px; font-weight:600;">
                    <option value="First" <?= $term === 'First' ? 'selected' : '' ?>>First Term</option>
                    <option value="Second" <?= $term === 'Second' ? 'selected' : '' ?>>Second Term</option>
                    <option value="Third" <?= $term === 'Third' ? 'selected' : '' ?>>Third Term</option>
                </select>
            </div>

            <!-- 3. Class -->
            <div class="col-12 col-md-3">
                <label class="form-label mb-1" style="font-size:11px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.5px;">
                    <i class="ti ti-school text-teal me-1"></i> Assigned Class
                </label>
                <select name="class_id" class="form-select form-select-sm" onchange="this.form.submit()" style="border-radius:8px; font-weight:600;">
                    <?php if (empty($classes)): ?>
                        <option value="0">No classes assigned</option>
                    <?php else: foreach ($classes as $c): ?>
                        <option value="<?= (int)$c['id'] ?>" <?= (int)$c['id'] === $classId ? 'selected' : '' ?>>
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>

            <!-- 4. Subject -->
            <div class="col-12 col-md-4">
                <label class="form-label mb-1" style="font-size:11px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:0.5px;">
                    <i class="ti ti-book text-teal me-1"></i> Subject
                </label>
                <select name="subject_id" class="form-select form-select-sm" onchange="this.form.submit()" style="border-radius:8px; font-weight:600;">
                    <?php if (empty($subjects)): ?>
                        <option value="0">No subjects assigned for this class</option>
                    <?php else: foreach ($subjects as $sb): ?>
                        <option value="<?= (int)$sb['id'] ?>" <?= (int)$sb['id'] === $subjectId ? 'selected' : '' ?>>
                            <?= e($sb['name']) ?> <?= !empty($sb['code']) ? '(' . e($sb['code']) . ')' : '' ?>
                        </option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
        </form>
    </div>
</div>

<?php if ($classId <= 0 || $subjectId <= 0 || empty($selectedClass) || empty($selectedSubject)): ?>
    <div class="card border-0 shadow-sm p-5 text-center text-muted" style="border-radius:14px; background:#fff;">
        <i class="ti ti-notes-off fs-1 d-block mb-3 text-secondary opacity-50"></i>
        <h4 class="fw-bold" style="font-size:1.1rem; color:#1e293b;">No Class / Subject Selected</h4>
        <p class="mb-0 text-muted" style="font-size:13px;">Please select your assigned class and subject from the filters above to load the student grade sheet.</p>
    </div>
<?php else: ?>

    <!-- Class & Teacher Role Banner -->
    <div class="alert alert-light border shadow-sm p-3 mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2" style="border-radius:12px; background:#f8fafc;">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fw-bold" style="font-size:11.5px;">
                <?= e($selectedClass['name']) ?>
            </span>
            <span class="text-muted">•</span>
            <span class="fw-bold text-dark" style="font-size:13.5px;"><?= e($selectedSubject['name']) ?></span>
            <?php if ($isFormTeacher): ?>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fw-bold ms-1" style="font-size:11px;">
                    <i class="ti ti-award me-1"></i> Form Teacher (Full Class Oversight)
                </span>
            <?php endif; ?>
        </div>
        <div class="text-muted" style="font-size:12px;">
            Evaluation Scale: <strong>Test 1 + Test 2 + Assignment + Exam = Total (100)</strong>
        </div>
    </div>

    <!-- Class Performance Analytics Cards -->
    <div class="row g-3 mb-4">
        <!-- 1. Enrolled Students -->
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px; background:#fff;">
                <div class="text-muted fw-semibold mb-1" style="font-size:11px; text-transform:uppercase;">Enrolled</div>
                <div class="h4 fw-bold mb-0 text-dark" id="statTotalStudents"><?= $totalStudents ?></div>
                <div class="text-muted mt-1" style="font-size:11px;">Class Roster</div>
            </div>
        </div>

        <!-- 2. Graded Students -->
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px; background:#fff;">
                <div class="text-muted fw-semibold mb-1" style="font-size:11px; text-transform:uppercase;">Graded</div>
                <div class="h4 fw-bold mb-0 text-success" id="statGradedCount"><?= $gradedCount ?></div>
                <div class="text-muted mt-1" style="font-size:11px;">Scores recorded</div>
            </div>
        </div>

        <!-- 3. Missing Scores -->
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px; background:#fff; cursor:pointer;" onclick="toggleMissingFilter()" title="Click to filter missing students">
                <div class="text-muted fw-semibold mb-1" style="font-size:11px; text-transform:uppercase;">Missing</div>
                <div class="h4 fw-bold mb-0 <?= $missingCount > 0 ? 'text-warning' : 'text-muted' ?>" id="statMissingCount">
                    <?= $missingCount ?>
                </div>
                <div class="text-primary mt-1" style="font-size:11px; text-decoration:underline;">Click to toggle filter</div>
            </div>
        </div>

        <!-- 4. Class Average -->
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px; background:#fff;">
                <div class="text-muted fw-semibold mb-1" style="font-size:11px; text-transform:uppercase;">Average</div>
                <div class="h4 fw-bold mb-0 text-teal" id="statClassAverage"><?= $classAverage ?>%</div>
                <div class="text-muted mt-1" style="font-size:11px;">Class Mean Score</div>
            </div>
        </div>

        <!-- 5. Highest / Lowest -->
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px; background:#fff;">
                <div class="text-muted fw-semibold mb-1" style="font-size:11px; text-transform:uppercase;">Top / Low</div>
                <div class="h5 fw-bold mb-0 text-dark" id="statHighLow">
                    <?= $highestScore ?>% <span class="text-muted fw-normal">/</span> <?= $lowestScore ?>%
                </div>
                <div class="text-muted mt-1" style="font-size:11px;">High & Low Scores</div>
            </div>
        </div>

        <!-- 6. Pass Rate -->
        <div class="col-6 col-md-2">
            <div class="card border-0 shadow-sm p-3 h-100" style="border-radius:12px; background:#fff;">
                <div class="text-muted fw-semibold mb-1" style="font-size:11px; text-transform:uppercase;">Pass Rate</div>
                <div class="h4 fw-bold mb-0 text-info" id="statPassRate"><?= $passRate ?>%</div>
                <div class="text-muted mt-1" style="font-size:11px;">Score &ge; 50%</div>
            </div>
        </div>
    </div>

    <?php if ($isLocked): ?>
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3 mb-4 p-3" style="border-radius:12px; background:#fef3c7; color:#92400e;">
            <i class="ti ti-lock fs-4"></i>
            <div style="font-size:13px;">
                <strong>Results Locked:</strong> This result sheet has been <strong><?= strtoupper($workflowStatus) ?></strong> by the school administration. 
                Scores cannot be silently modified. Any authorized revisions must be made through an administrator and are logged in the audit trail.
            </div>
        </div>
    <?php endif; ?>

    <!-- Score Entry Table Card -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius:14px; overflow:hidden; background:#fff;">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-0" style="font-size:15px; color:#0f172a;">
                    <?= e($selectedClass['name']) ?> &mdash; Student Grade Sheet
                </h5>
                <small class="text-muted" style="font-size:11.5px;">Auto-calculating total, grade letter, and performance remarks</small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btnFilterMissing" onclick="toggleMissingFilter()" style="border-radius:8px; font-size:12px;">
                    <i class="ti ti-filter me-1"></i> <span id="filterLabel">Show Missing Only</span>
                </button>
            </div>
        </div>

        <form method="POST" action="<?= url('teacher/results/save') ?>" id="resultsEntryForm">
            <?= csrf_field() ?>
            <input type="hidden" name="class_id" value="<?= (int)$classId ?>">
            <input type="hidden" name="subject_id" value="<?= (int)$subjectId ?>">
            <input type="hidden" name="term" value="<?= e($term) ?>">
            <input type="hidden" name="academic_year" value="<?= e($academicYear) ?>">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="resultsTable">
                    <thead class="table-light text-uppercase" style="font-size:11px; font-weight:700; color:#475569; letter-spacing:0.5px;">
                        <tr>
                            <th class="ps-4 py-3" style="width:45px;">#</th>
                            <th style="min-width:220px;">Student</th>
                            <th class="text-center" style="width:85px;" title="First Continuous Assessment (Max: 15)">Test 1</th>
                            <th class="text-center" style="width:85px;" title="Second Continuous Assessment (Max: 10)">Test 2</th>
                            <th class="text-center" style="width:90px;" title="Assignment & Homework (Max: 10)">Assign</th>
                            <th class="text-center" style="width:90px;" title="Mid-Term Test (Max: 10)">Mid-Term</th>
                            <th class="text-center" style="width:90px;" title="Final Examination (Max: 55)">Exam</th>
                            <th class="text-center" style="width:80px;">Total</th>
                            <th class="text-center" style="width:70px;">Grade</th>
                            <th class="text-center" style="width:100px;">Remark</th>
                            <th class="pe-4" style="min-width:180px;">Teacher Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="11" class="text-center py-5 text-muted">
                                    <i class="ti ti-users-off fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                    No active enrolled students found in <?= e($selectedClass['name']) ?>.
                                </td>
                            </tr>
                        <?php else: $idx = 1; foreach ($students as $st): ?>
                            <?php
                            $r = $existingResults[$st['id']] ?? [];
                            $appId = (int)$st['id'];
                            $isMissing = ($r['ca1'] ?? null) === null && ($r['ca2'] ?? null) === null && ($r['assignment'] ?? null) === null && ($r['exam'] ?? null) === null;
                            ?>
                            <tr class="result-row <?= $isMissing ? 'is-missing' : '' ?>" data-app-id="<?= $appId ?>">
                                <td class="ps-4 py-3 text-muted" style="font-size:12px;"><?= $idx++ ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px; height:32px; border-radius:8px; background:#e0f2fe; color:#0369a1; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; flex-shrink:0;">
                                            <?= strtoupper(substr($st['first_name'], 0, 1) . substr($st['last_name'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold" style="font-size:13.5px; color:#0f172a;">
                                                <?= e($st['last_name'] . ' ' . $st['first_name']) ?>
                                            </div>
                                            <div class="font-monospace text-muted" style="font-size:11px;"><?= e($st['admission_number']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <!-- Test 1 -->
                                <td class="text-center">
                                    <input type="number" step="0.5" min="0" max="15" name="ca1[<?= $appId ?>]"
                                           class="form-control form-control-sm text-center score-input input-test1 fw-bold"
                                           value="<?= isset($r['ca1']) && $r['ca1'] !== null ? e($r['ca1']) : '' ?>"
                                           placeholder="—" <?= $isLocked ? 'disabled' : '' ?>>
                                </td>
                                <!-- Test 2 -->
                                <td class="text-center">
                                    <input type="number" step="0.5" min="0" max="10" name="ca2[<?= $appId ?>]"
                                           class="form-control form-control-sm text-center score-input input-test2 fw-bold"
                                           value="<?= isset($r['ca2']) && $r['ca2'] !== null ? e($r['ca2']) : '' ?>"
                                           placeholder="—" <?= $isLocked ? 'disabled' : '' ?>>
                                </td>
                                <!-- Assignment -->
                                <td class="text-center">
                                    <input type="number" step="0.5" min="0" max="10" name="assignment[<?= $appId ?>]"
                                           class="form-control form-control-sm text-center score-input input-assign fw-bold"
                                           value="<?= isset($r['assignment']) && $r['assignment'] !== null ? e($r['assignment']) : '' ?>"
                                           placeholder="—" <?= $isLocked ? 'disabled' : '' ?>>
                                </td>
                                <!-- Mid-Term -->
                                <td class="text-center">
                                    <input type="number" step="0.5" min="0" max="10" name="mid_term[<?= $appId ?>]"
                                           class="form-control form-control-sm text-center score-input input-midterm fw-bold"
                                           value="<?= isset($r['mid_term']) && $r['mid_term'] !== null ? e($r['mid_term']) : '' ?>"
                                           placeholder="—" <?= $isLocked ? 'disabled' : '' ?>>
                                </td>
                                <!-- Exam -->
                                <td class="text-center">
                                    <input type="number" step="0.5" min="0" max="60" name="exam[<?= $appId ?>]"
                                           class="form-control form-control-sm text-center score-input input-exam fw-bold"
                                           value="<?= isset($r['exam']) && $r['exam'] !== null ? e($r['exam']) : '' ?>"
                                           placeholder="—" <?= $isLocked ? 'disabled' : '' ?>>
                                </td>
                                <!-- Total (Live calculated) -->
                                <td class="text-center fw-bold font-monospace cell-total" style="font-size:13.5px; color:#0f766e;">
                                    <?= isset($r['total']) && $r['total'] !== null ? number_format((float)$r['total'], 1) : '—' ?>
                                </td>
                                <!-- Grade (Live calculated) -->
                                <td class="text-center">
                                    <?php
                                    $grade = $r['grade'] ?? '';
                                    $badgeCol = '#475569'; $badgeBg = '#f1f5f9';
                                    if (in_array($grade, ['A', 'A1'])) { $badgeBg = '#dcfce7'; $badgeCol = '#15803d'; }
                                    elseif (in_array($grade, ['B', 'B2', 'B3'])) { $badgeBg = '#e0f2fe'; $badgeCol = '#0284c7'; }
                                    elseif (in_array($grade, ['C', 'C4', 'C5', 'C6'])) { $badgeBg = '#ccfbf1'; $badgeCol = '#0f766e'; }
                                    elseif (in_array($grade, ['D', 'D7', 'E', 'E8'])) { $badgeBg = '#fef3c7'; $badgeCol = '#b45309'; }
                                    elseif (in_array($grade, ['F', 'F9'])) { $badgeBg = '#fee2e2'; $badgeCol = '#b91c1c'; }
                                    ?>
                                    <span class="badge cell-grade py-1 px-2 fw-bold" style="background:<?= $badgeBg ?>; color:<?= $badgeCol ?>; font-size:11.5px;">
                                        <?= e($grade ?: '—') ?>
                                    </span>
                                </td>
                                <!-- Remark (Live calculated) -->
                                <td class="text-center cell-remark fw-semibold text-muted" style="font-size:11.5px;">
                                    <?= e($r['remark'] ?? '—') ?>
                                </td>
                                <!-- Teacher Comment -->
                                <td class="pe-4">
                                    <input type="text" name="teacher_remark[<?= $appId ?>]" class="form-control form-control-sm"
                                           value="<?= e($r['teacher_remark'] ?? '') ?>" placeholder="e.g. Excellent student"
                                           style="font-size:12px; border-radius:6px;" <?= $isLocked ? 'disabled' : '' ?>>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!$isLocked && !empty($students)): ?>
                <div class="card-footer bg-white p-4 border-top d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="text-muted d-flex align-items-center gap-2" style="font-size:12.5px;">
                        <i class="ti ti-bulb text-warning fs-5"></i>
                        <span>All totals, letter grades (A1 to F9), and remarks recalculate in real-time as you type.</span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4 py-2 d-flex align-items-center gap-2 shadow-sm" style="font-weight:700; border-radius:10px;">
                            <i class="ti ti-device-floppy"></i>
                            <span>Save Draft</span>
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Workflow Lifecycle Bar -->
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius:14px; background:#fff;">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h5 class="fw-bold mb-1" style="font-size:14px; color:#0f172a;">Result Approval & Publishing Lifecycle</h5>
                <p class="text-muted mb-0" style="font-size:12.5px;">
                    <?php if ($workflowStatus === 'draft'): ?>
                        Scores are saved as a <strong>Draft</strong>. Teachers can freely modify scores until submitting for review.
                    <?php elseif ($workflowStatus === 'submitted'): ?>
                        Scores have been <strong>Submitted</strong> to the Principal / Administration for verification.
                    <?php elseif ($workflowStatus === 'approved'): ?>
                        Scores are <strong>Approved</strong> by school administration and locked from regular teacher modifications.
                    <?php elseif ($workflowStatus === 'published'): ?>
                        Scores are <strong>Published</strong> to student report cards and visible on parent portals.
                    <?php endif; ?>
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <!-- 1. Submit for Approval (Teacher) -->
                <?php if (staff_can('results.submit') && $workflowStatus === 'draft' && !empty($students)): ?>
                    <form method="POST" action="<?= url('teacher/results/submit') ?>" onsubmit="return confirm('Submit these grades to the administration for review and approval?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="class_id" value="<?= (int)$classId ?>">
                        <input type="hidden" name="subject_id" value="<?= (int)$subjectId ?>">
                        <input type="hidden" name="term" value="<?= e($term) ?>">
                        <input type="hidden" name="academic_year" value="<?= e($academicYear) ?>">
                        <button type="submit" class="btn btn-warning fw-bold text-dark px-3 py-2 d-flex align-items-center gap-1 shadow-sm" style="border-radius:8px; font-size:13px;">
                            <i class="ti ti-send"></i>
                            <span>Submit for Approval</span>
                        </button>
                    </form>
                <?php endif; ?>

                <!-- 2. Approve (Principal / Admin) -->
                <?php if (staff_can('results.approve') && in_array($workflowStatus, ['draft', 'submitted'], true)): ?>
                    <form method="POST" action="<?= url('teacher/results/approve') ?>" onsubmit="return confirm('Approve these results? Once approved, regular teacher edits are locked.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="class_id" value="<?= (int)$classId ?>">
                        <input type="hidden" name="subject_id" value="<?= (int)$subjectId ?>">
                        <input type="hidden" name="term" value="<?= e($term) ?>">
                        <input type="hidden" name="academic_year" value="<?= e($academicYear) ?>">
                        <button type="submit" class="btn btn-success fw-bold text-white px-3 py-2 d-flex align-items-center gap-1 shadow-sm" style="border-radius:8px; font-size:13px;">
                            <i class="ti ti-check"></i>
                            <span>Approve Results</span>
                        </button>
                    </form>
                <?php endif; ?>

                <!-- 3. Publish to Parent Portal (Admin / Principal) -->
                <?php if (staff_can('results.publish') && $workflowStatus === 'approved'): ?>
                    <form method="POST" action="<?= url('teacher/results/publish') ?>" onsubmit="return confirm('Publish results to students and parents? They will be immediately visible on terminal report cards.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="class_id" value="<?= (int)$classId ?>">
                        <input type="hidden" name="subject_id" value="<?= (int)$subjectId ?>">
                        <input type="hidden" name="term" value="<?= e($term) ?>">
                        <input type="hidden" name="academic_year" value="<?= e($academicYear) ?>">
                        <button type="submit" class="btn btn-primary fw-bold text-white px-3 py-2 d-flex align-items-center gap-1 shadow-sm" style="border-radius:8px; font-size:13px; background:#4338ca; border-color:#4338ca;">
                            <i class="ti ti-world-upload"></i>
                            <span>Publish to Portal</span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
    </div>

    <!-- Class Subject Overview Modal (For Form Teachers & Overview) -->
    <div class="modal fade" id="classOverviewModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow" style="border-radius:14px; overflow:hidden;">
                <div class="modal-header" style="background:#0f172a; color:#fff;">
                    <div>
                        <h5 class="modal-title fw-bold" style="font-size:15px;">
                            <i class="ti ti-layout-grid me-1 text-teal"></i> <?= e($selectedClass['name'] ?? '') ?> &mdash; Subject Completion Matrix
                        </h5>
                        <small style="opacity:0.8; font-size:12px;">Session: <?= e($academicYear) ?> &bull; Term: <?= e($term) ?> Term</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <?php if (!empty($classOverview['completion_rate'])): ?>
                        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-3">
                                <div><small class="text-muted d-block" style="font-size:11px;">SUBJECTS OFFERED</small><strong><?= count($classOverview['subjects'] ?? []) ?></strong></div>
                                <div><small class="text-muted d-block" style="font-size:11px;">ENROLLED STUDENTS</small><strong><?= $classOverview['total_students'] ?? 0 ?></strong></div>
                                <div><small class="text-muted d-block" style="font-size:11px;">COMPLETED SUBJECTS</small><strong class="text-success"><?= $classOverview['completed_subjects'] ?? 0 ?></strong></div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted" style="font-size:12px;">Overall Completion:</span>
                                <span class="badge bg-success text-white px-2 py-1 font-monospace" style="font-size:13px;">
                                    <?= $classOverview['completion_rate'] ?? 0 ?>%
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive" style="max-height:55vh; overflow-y:auto;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light sticky-top text-uppercase" style="font-size:11px; font-weight:700; color:#475569;">
                                <tr>
                                    <th style="width:40px;" class="text-center">#</th>
                                    <th>Subject</th>
                                    <th style="width:160px;">Assigned Teacher</th>
                                    <th style="width:110px;" class="text-center">Graded</th>
                                    <th style="width:100px;" class="text-center">Missing</th>
                                    <th style="width:120px;" class="text-center">Status</th>
                                    <th style="width:100px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $ovSubjects = $classOverview['subjects'] ?? [];
                                foreach ($ovSubjects as $idx => $os): 
                                    $st = $os['status'];
                                    $badgeClass = match($st) {
                                        'published' => 'bg-success text-white',
                                        'approved'  => 'bg-primary text-white',
                                        'submitted' => 'bg-info text-dark',
                                        default     => 'bg-secondary-subtle text-secondary'
                                    };
                                ?>
                                    <tr>
                                        <td class="text-center text-muted" style="font-size:12px;"><?= $idx + 1 ?></td>
                                        <td class="fw-semibold text-dark">
                                            <?= e($os['subject_name']) ?>
                                            <?php if (!empty($os['subject_code'])): ?>
                                                <small class="text-muted font-monospace ms-1">(<?= e($os['subject_code']) ?>)</small>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-size:12.5px; color:#334155;"><?= e($os['teacher_name']) ?></td>
                                        <td class="text-center font-monospace fw-bold text-success" style="font-size:12px;">
                                            <?= $os['graded_count'] ?> / <?= $os['total_students'] ?>
                                        </td>
                                        <td class="text-center font-monospace <?= $os['missing_count'] > 0 ? 'text-danger fw-bold' : 'text-muted' ?>" style="font-size:12px;">
                                            <?= $os['missing_count'] ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill <?= $badgeClass ?> px-2 py-1 text-uppercase" style="font-size:10px;">
                                                <?= e($st) ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="<?= url("teacher/results?class_id={$classId}&subject_id={$os['subject_id']}&term=" . urlencode($term) . "&academic_year=" . urlencode($academicYear)) ?>"
                                               class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:11px; border-radius:4px;">
                                                Manage
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>

<script>
// Live Automatic Score Calculation & Grade Evaluation
function evaluateScore(total) {
    if (total >= 75) return { grade: 'A1', remark: 'Excellent', bg: '#dcfce7', col: '#15803d' };
    if (total >= 70) return { grade: 'B2', remark: 'Very Good', bg: '#e0f2fe', col: '#0284c7' };
    if (total >= 65) return { grade: 'B3', remark: 'Good', bg: '#e0f2fe', col: '#0284c7' };
    if (total >= 60) return { grade: 'C4', remark: 'Credit', bg: '#ccfbf1', col: '#0f766e' };
    if (total >= 55) return { grade: 'C5', remark: 'Credit', bg: '#ccfbf1', col: '#0f766e' };
    if (total >= 50) return { grade: 'C6', remark: 'Credit', bg: '#ccfbf1', col: '#0f766e' };
    if (total >= 45) return { grade: 'D7', remark: 'Pass', bg: '#fef3c7', col: '#b45309' };
    if (total >= 40) return { grade: 'E8', remark: 'Pass', bg: '#fef3c7', col: '#b45309' };
    return { grade: 'F9', remark: 'Fail', bg: '#fee2e2', col: '#b91c1c' };
}

function updateRow(row) {
    const t1 = parseFloat(row.querySelector('.input-test1')?.value) || 0;
    const t2 = parseFloat(row.querySelector('.input-test2')?.value) || 0;
    const as = parseFloat(row.querySelector('.input-assign')?.value) || 0;
    const md = parseFloat(row.querySelector('.input-midterm')?.value) || 0;
    const ex = parseFloat(row.querySelector('.input-exam')?.value) || 0;

    const hasValue = row.querySelector('.input-test1')?.value !== '' ||
                     row.querySelector('.input-test2')?.value !== '' ||
                     row.querySelector('.input-assign')?.value !== '' ||
                     row.querySelector('.input-midterm')?.value !== '' ||
                     row.querySelector('.input-exam')?.value !== '';

    const totalCell = row.querySelector('.cell-total');
    const gradeBadge = row.querySelector('.cell-grade');
    const remarkCell = row.querySelector('.cell-remark');

    if (!hasValue) {
        if (totalCell) totalCell.textContent = '—';
        if (gradeBadge) {
            gradeBadge.textContent = '—';
            gradeBadge.style.background = '#f1f5f9';
            gradeBadge.style.color = '#475569';
        }
        if (remarkCell) remarkCell.textContent = '—';
        row.classList.add('is-missing');
        return null;
    }

    const total = t1 + t2 + as + md + ex;
    if (totalCell) totalCell.textContent = total.toFixed(1);

    const evalResult = evaluateScore(total);
    if (gradeBadge) {
        gradeBadge.textContent = evalResult.grade;
        gradeBadge.style.background = evalResult.bg;
        gradeBadge.style.color = evalResult.col;
    }
    if (remarkCell) remarkCell.textContent = evalResult.remark;
    row.classList.remove('is-missing');
    return total;
}

function recalculateAll() {
    const rows = document.querySelectorAll('#resultsTable tbody .result-row');
    const totals = [];
    let missing = 0;
    let graded = 0;

    rows.forEach(r => {
        const t = updateRow(r);
        if (t !== null) {
            totals.push(t);
            graded++;
        } else {
            missing++;
        }
    });

    // Update Analytics Badges
    const statGraded = document.getElementById('statGradedCount');
    const statMissing = document.getElementById('statMissingCount');
    const statAvg = document.getElementById('statClassAverage');
    const statHighLow = document.getElementById('statHighLow');
    const statPass = document.getElementById('statPassRate');

    if (statGraded) statGraded.textContent = graded;
    if (statMissing) statMissing.textContent = missing;

    if (totals.length > 0) {
        const sum = totals.reduce((a, b) => a + b, 0);
        const avg = (sum / totals.length).toFixed(1);
        const max = Math.max(...totals);
        const min = Math.min(...totals);
        const passCount = totals.filter(t => t >= 50).length;
        const passRate = ((passCount / totals.length) * 100).toFixed(1);

        if (statAvg) statAvg.textContent = avg + '%';
        if (statHighLow) statHighLow.innerHTML = max + '% <span class="text-muted fw-normal">/</span> ' + min + '%';
        if (statPass) statPass.textContent = passRate + '%';
    }
}

// Bind live events
document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('.score-input');
    inputs.forEach(inp => {
        inp.addEventListener('input', recalculateAll);
    });
});

// Toggle Missing Filter
let showingMissingOnly = false;
function toggleMissingFilter() {
    showingMissingOnly = !showingMissingOnly;
    const rows = document.querySelectorAll('#resultsTable tbody .result-row');
    const filterLabel = document.getElementById('filterLabel');

    rows.forEach(r => {
        if (showingMissingOnly) {
            r.style.display = r.classList.contains('is-missing') ? '' : 'none';
        } else {
            r.style.display = '';
        }
    });

    if (filterLabel) {
        filterLabel.textContent = showingMissingOnly ? 'Show All Students' : 'Show Missing Only';
    }
}
</script>
