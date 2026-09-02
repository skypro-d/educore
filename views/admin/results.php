<?php
// views/admin/results.php — Dynamic Academic Results Management
$components = $components ?? ResultService::getAssessmentComponents();
$classOverviewData = $classOverview ?? [];
?>
<div class="sa-top-bar mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color:#0f172a;">Academic Results Management</h1>
        <p class="text-muted mb-0" style="font-size:13.5px;">Enter student scores, review submitted results, and generate terminal report cards.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="sa-btn" href="<?= url('admin/class-subjects?class_id=' . $classId) ?>" style="border-radius:8px;">
            <i class="ti ti-checklist"></i> Class Subjects (<?= count($subjects) ?>)
        </a>
        <a class="sa-btn" href="<?= url('admin/assessment-components') ?>" style="border-radius:8px;">
            <i class="ti ti-adjustments"></i> Components
        </a>
        <a class="sa-btn" href="<?= url('admin/grading-rules') ?>" style="border-radius:8px;">
            <i class="ti ti-certificate"></i> Grading Scale
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="sa-card p-3 shadow-sm border-0 mb-4" style="border-radius:12px; background:#fff;">
    <form method="GET" action="<?= url('admin/results') ?>" class="row g-2 align-items-end">
        <input type="hidden" name="route" value="results">
        <div class="col-md-4 col-lg-3">
            <label class="form-label text-uppercase text-muted fw-bold" style="font-size:11px;">Class</label>
            <select name="class_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Select class…</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (int)$c['id'] === $classId ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 col-lg-2">
            <label class="form-label text-uppercase text-muted fw-bold" style="font-size:11px;">Term</label>
            <select name="term" class="form-select form-select-sm" onchange="this.form.submit()">
                <?php foreach (['First','Second','Third'] as $t): ?>
                    <option value="<?= $t ?>" <?= $t === $term ? 'selected' : '' ?>><?= $t ?> Term</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 col-lg-2">
            <label class="form-label text-uppercase text-muted fw-bold" style="font-size:11px;">Academic Session</label>
            <input type="text" name="year" value="<?= e($year) ?>" placeholder="2024/2025" class="form-control form-control-sm">
        </div>
        <div class="col-md-2">
            <button type="submit" class="sa-btn sa-btn-primary w-100" style="height:31px; border-radius:6px; font-size:12.5px;">
                <i class="ti ti-filter me-1"></i> Apply Filter
            </button>
        </div>
    </form>
</div>

<?php if ($classId > 0): ?>
    <!-- Class Tabs -->
    <ul class="nav nav-pills mb-3 gap-2" id="resultsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-3 py-1 fw-bold" id="students-tab" data-bs-toggle="pill" data-bs-target="#students-panel" type="button" role="tab" style="font-size:13px;">
                <i class="ti ti-users me-1"></i> Students & Scores (<?= count($students) ?>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-3 py-1 fw-bold" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview-panel" type="button" role="tab" style="font-size:13px;">
                <i class="ti ti-layout-grid me-1"></i> Class Subject Matrix (<?= count($subjects) ?> Subjects)
            </button>
        </li>
    </ul>

    <div class="tab-content" id="resultsTabContent">
        <!-- Panel 1: Students & Scores -->
        <div class="tab-pane fade show active" id="students-panel" role="tabpanel">
            <div class="sa-card p-0 shadow-sm border-0" style="border-radius:12px; background:#fff; overflow:hidden;">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size:14px;">Enrolled Students</h5>
                        <small class="text-muted">Click "Enter Scores" to view or update continuous assessments and examination marks.</small>
                    </div>
                    <span class="badge bg-light text-dark border px-2 py-1" style="font-size:12px;">
                        Curriculum: <?= count($subjects) ?> Subjects
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase" style="font-size:11px; font-weight:700; color:#475569;">
                            <tr>
                                <th style="width:40px;" class="text-center">#</th>
                                <th>Student Name</th>
                                <th style="width:140px;">Reg / Adm No.</th>
                                <th style="width:140px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No active enrolled students found in this class.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students as $i => $s): ?>
                                    <tr>
                                        <td class="text-center text-muted" style="font-size:12px;"><?= $i + 1 ?></td>
                                        <td class="fw-semibold text-dark">
                                            <?= e($s['last_name'] . ' ' . $s['first_name']) ?>
                                        </td>
                                        <td class="font-monospace text-muted" style="font-size:12px;">
                                            <?= e($s['admission_number'] ?? $s['application_number']) ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-inline-flex gap-1">
                                                <button class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size:11.5px; border-radius:6px;" 
                                                        onclick="openScoreModal(<?= $s['id'] ?>, '<?= e(addslashes($s['first_name'] . ' ' . $s['last_name'])) ?>')">
                                                    <i class="ti ti-edit me-1"></i> Scores
                                                </button>
                                                <a class="btn btn-sm btn-outline-success py-1 px-2" style="font-size:11.5px; border-radius:6px;" 
                                                   href="<?= url('admin/result-sheet/' . $s['id'] . '?year=' . urlencode($year) . '&term=' . urlencode($term)) ?>" target="_blank">
                                                    <i class="ti ti-printer me-1"></i> Report
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Panel 2: Class Subject Overview Matrix -->
        <div class="tab-pane fade" id="overview-panel" role="tabpanel">
            <div class="sa-card p-0 shadow-sm border-0" style="border-radius:12px; background:#fff; overflow:hidden;">
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" style="font-size:14px;">Class Subject Completion Matrix</h5>
                        <small class="text-muted">Track the score submission and publication status of every subject in this class.</small>
                    </div>
                    <?php if (!empty($classOverviewData['completion_rate'])): ?>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted" style="font-size:12px;">Overall Completion:</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 font-monospace" style="font-size:13px;">
                                <?= $classOverviewData['completion_rate'] ?>%
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase" style="font-size:11px; font-weight:700; color:#475569;">
                            <tr>
                                <th style="width:40px;" class="text-center">#</th>
                                <th>Subject</th>
                                <th style="width:160px;">Assigned Teacher</th>
                                <th style="width:110px;" class="text-center">Graded</th>
                                <th style="width:110px;" class="text-center">Missing</th>
                                <th style="width:130px;" class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $matrix = $classOverviewData['subjects'] ?? [];
                            if (empty($matrix)): 
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No subjects assigned to this class yet. <a href="<?= url('admin/class-subjects?class_id=' . $classId) ?>">Configure subjects</a>.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($matrix as $idx => $m): ?>
                                    <?php 
                                    $st = $m['status'];
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
                                            <?= e($m['subject_name']) ?>
                                            <?php if (!empty($m['subject_code'])): ?>
                                                <small class="text-muted font-monospace ms-1">(<?= e($m['subject_code']) ?>)</small>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-size:12.5px; color:#334155;">
                                            <?= e($m['teacher_name']) ?>
                                        </td>
                                        <td class="text-center font-monospace fw-bold text-success" style="font-size:12px;">
                                            <?= $m['graded_count'] ?> / <?= $m['total_students'] ?>
                                        </td>
                                        <td class="text-center font-monospace <?= $m['missing_count'] > 0 ? 'text-danger fw-bold' : 'text-muted' ?>" style="font-size:12px;">
                                            <?= $m['missing_count'] ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge rounded-pill <?= $badgeClass ?> px-2 py-1 text-uppercase" style="font-size:10.5px; letter-spacing:0.4px;">
                                                <?= e($st) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Score Entry Modal -->
<div class="modal fade" id="scoreModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content border-0 shadow" style="border-radius:14px; overflow:hidden;">
            <div class="modal-header" style="background:#0f172a; color:#fff;">
                <h5 class="modal-title fw-bold" style="font-size:15px;">
                    <i class="ti ti-pencil me-1 text-teal"></i> <span id="scoreModalName"></span> &mdash; Terminal Score Entry
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('admin/results') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="applicant_id" id="scoreApplicantId">
                <input type="hidden" name="academic_year" value="<?= e($year) ?>">
                <input type="hidden" name="term" value="<?= e($term) ?>">
                <input type="hidden" name="class_id" value="<?= $classId ?>">

                <div class="modal-body p-0" style="max-height:65vh; overflow-y:auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top text-uppercase" style="font-size:11px; font-weight:700; color:#475569; z-index:5;">
                            <tr>
                                <th>Subject (<?= count($subjects) ?>)</th>
                                <?php foreach ($components as $comp): ?>
                                    <th style="width:85px;" class="text-center">
                                        <?= e($comp['name']) ?><br>
                                        <small class="text-muted fw-normal">(/<?= round($comp['max_score']) ?>)</small>
                                    </th>
                                <?php endforeach; ?>
                                <th style="width:75px;" class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subjects as $subj): ?>
                                <tr>
                                    <td class="fw-semibold text-dark" style="font-size:13px;">
                                        <?= e($subj['name']) ?>
                                    </td>
                                    <?php foreach ($components as $comp): ?>
                                        <?php $cCode = $comp['code']; ?>
                                        <td class="text-center">
                                            <input type="number" 
                                                   name="scores[<?= $subj['id'] ?>][<?= $cCode ?>]" 
                                                   min="0" 
                                                   max="<?= e($comp['max_score']) ?>" 
                                                   step="0.5" 
                                                   class="form-control form-control-sm score-input text-center font-monospace" 
                                                   data-subj="<?= $subj['id'] ?>" 
                                                   style="width:70px; margin:0 auto;">
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="text-center font-monospace fw-bold text-teal" id="total-<?= $subj['id'] ?>" style="font-size:13px;">
                                        &mdash;
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer bg-light border-top">
                    <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="sa-btn sa-btn-primary px-4 py-1" style="border-radius:6px; font-weight:700;">
                        <i class="ti ti-device-floppy me-1"></i> Save Terminal Scores
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openScoreModal(id, name) {
    document.getElementById('scoreApplicantId').value = id;
    document.getElementById('scoreModalName').textContent = name;
    new bootstrap.Modal(document.getElementById('scoreModal')).show();
}

document.querySelectorAll('.score-input').forEach(input => {
    input.addEventListener('input', function() {
        const subjId = this.dataset.subj;
        let sum = 0;
        let hasAny = false;
        document.querySelectorAll(`.score-input[data-subj="${subjId}"]`).forEach(i => {
            const v = parseFloat(i.value);
            if (!isNaN(v)) {
                sum += v;
                hasAny = true;
            }
        });
        const totalCell = document.getElementById(`total-${subjId}`);
        if (totalCell) {
            totalCell.textContent = hasAny ? sum.toFixed(1) : '—';
        }
    });
});
</script>
