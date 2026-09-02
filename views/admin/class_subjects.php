<div class="sa-top-bar mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color:#0f172a;">Class Subjects Allocation</h1>
        <p class="text-muted mb-0" style="font-size:13.5px;">Configure the exact subjects offered by each class. Zero hardcoded subject limits.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="sa-btn" href="<?= url('admin/subjects') ?>" style="border-radius:8px;">
            <i class="ti ti-books"></i> Master Subjects
        </a>
        <a class="sa-btn" href="<?= url('admin/assessment-components') ?>" style="border-radius:8px;">
            <i class="ti ti-adjustments"></i> Assessment Components
        </a>
        <a class="sa-btn" href="<?= url('admin/grading-rules') ?>" style="border-radius:8px;">
            <i class="ti ti-certificate"></i> Grading Scale
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Class Selector Sidebar -->
    <div class="col-md-4 col-lg-3">
        <div class="sa-card p-3 shadow-sm border-0" style="border-radius:14px; background:#fff;">
            <div class="sa-card-title mb-3" style="font-size:13px; font-weight:700; color:#475569; text-transform:uppercase;">
                <i class="ti ti-school me-1 text-teal"></i> Select Class
            </div>
            <div class="list-group list-group-flush border-0">
                <?php foreach ($classes as $c): ?>
                    <?php 
                    $cId = (int)$c['id'];
                    $cnt = $classCounts[$cId] ?? 0;
                    $isActive = $cId === $classId;
                    ?>
                    <a href="<?= url('admin/class-subjects?class_id=' . $cId) ?>" 
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3 border-0 mb-1 rounded"
                       style="font-size:13.5px; font-weight:<?= $isActive ? '700' : '500' ?>; background:<?= $isActive ? '#f0fdf4' : 'transparent' ?>; color:<?= $isActive ? '#15803d' : '#1e293b' ?>;">
                        <span><?= e($c['name']) ?></span>
                        <span class="badge rounded-pill <?= $cnt > 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-muted border' ?>" style="font-size:11px;">
                            <?= $cnt ?> Subjects
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Subject Assignment Checklist -->
    <div class="col-md-8 col-lg-9">
        <?php if (!$selectedClass): ?>
            <div class="sa-card p-5 text-center text-muted" style="border-radius:14px; background:#fff;">
                <i class="ti ti-school-off fs-1 d-block mb-3 opacity-50"></i>
                <h4 class="fw-bold" style="font-size:1.1rem; color:#1e293b;">No Class Selected</h4>
                <p class="mb-0 text-muted" style="font-size:13px;">Please select a class from the list on the left to configure its subjects.</p>
            </div>
        <?php else: ?>
            <div class="sa-card p-4 shadow-sm border-0" style="border-radius:14px; background:#fff;">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 pb-3 border-bottom">
                    <div>
                        <h4 class="h5 fw-bold mb-1" style="color:#0f172a;">
                            <?= e($selectedClass['name']) ?> &mdash; Subjects Configuration
                        </h4>
                        <div class="text-muted" style="font-size:12.5px;">
                            Currently offering <strong id="selectedCountBadge" class="text-success"><?= count($assignedSubjectIds) ?></strong> subjects.
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <input type="text" id="subjectSearch" class="form-control form-control-sm" placeholder="Filter subjects…" style="width:180px; border-radius:8px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAll(true)">Check All</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="selectAll(false)">Clear</button>
                    </div>
                </div>

                <form method="POST" action="<?= url('admin/class-subjects/save') ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="class_id" value="<?= $classId ?>">

                    <div class="row g-2 mb-4" id="subjectGrid" style="max-height: 480px; overflow-y: auto; padding-right: 5px;">
                        <?php foreach ($allSubjects as $subj): ?>
                            <?php 
                            $sId = (int)$subj['id'];
                            $isChecked = in_array($sId, $assignedSubjectIds, true);
                            ?>
                            <div class="col-md-6 col-lg-4 subject-item" data-name="<?= strtolower(e($subj['name'])) ?>">
                                <label class="p-2 border rounded d-flex align-items-center gap-2 w-100 mb-0" 
                                       style="cursor:pointer; background:<?= $isChecked ? '#f8fafc' : '#fff' ?>; border-color:<?= $isChecked ? '#cbd5e1' : '#e2e8f0' ?> !important; font-size:13px;">
                                    <input type="checkbox" name="subject_ids[]" value="<?= $sId ?>" 
                                           class="form-check-input subject-checkbox" 
                                           <?= $isChecked ? 'checked' : '' ?>
                                           onchange="updateCounter()">
                                    <div class="d-flex flex-column text-truncate">
                                        <span class="fw-semibold text-dark text-truncate"><?= e($subj['name']) ?></span>
                                        <?php if (!empty($subj['code'])): ?>
                                            <span class="text-muted font-monospace" style="font-size:11px;"><?= e($subj['code']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                        <div class="text-muted" style="font-size:12px;">
                            <i class="ti ti-info-circle me-1"></i> Changes take effect immediately for all students enrolled in <?= e($selectedClass['name']) ?>.
                        </div>
                        <button type="submit" class="sa-btn sa-btn-primary px-4 py-2" style="border-radius:8px; font-weight:700;">
                            <i class="ti ti-device-floppy me-1"></i> Save Class Subjects
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function updateCounter() {
    const checked = document.querySelectorAll('.subject-checkbox:checked').length;
    const badge = document.getElementById('selectedCountBadge');
    if (badge) {
        badge.textContent = checked;
    }
}

function selectAll(state) {
    document.querySelectorAll('.subject-checkbox').forEach(cb => {
        cb.checked = state;
    });
    updateCounter();
}

// Live search inside subject grid
document.getElementById('subjectSearch')?.addEventListener('input', function(e) {
    const term = e.target.value.toLowerCase().trim();
    document.querySelectorAll('.subject-item').forEach(el => {
        const name = el.getAttribute('data-name') || '';
        el.style.display = name.includes(term) ? '' : 'none';
    });
});
</script>
