<div class="sa-top-bar mb-4">
    <div>
        <h1 class="h3 fw-bold mb-1" style="color:#0f172a;">Assessment Components Configuration</h1>
        <p class="text-muted mb-0" style="font-size:13.5px;">Configure continuous assessment components, tests, assignments, exams, and maximum score limits.</p>
    </div>
    <div class="d-flex gap-2">
        <a class="sa-btn" href="<?= url('admin/class-subjects') ?>" style="border-radius:8px;">
            <i class="ti ti-checklist"></i> Class Subjects
        </a>
        <a class="sa-btn" href="<?= url('admin/grading-rules') ?>" style="border-radius:8px;">
            <i class="ti ti-certificate"></i> Grading Scale
        </a>
    </div>
</div>

<div class="sa-card p-4 shadow-sm border-0" style="border-radius:14px; background:#fff; max-width:850px;">
    <div class="mb-3 pb-2 border-bottom">
        <h5 class="fw-bold mb-0" style="color:#0f172a;">Active Assessment Components</h5>
        <small class="text-muted">The sum of component maximum scores should ideally equal 100%.</small>
    </div>

    <form method="POST" action="<?= url('admin/assessment-components') ?>">
        <?= csrf_field() ?>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light text-uppercase" style="font-size:11px; font-weight:700; color:#475569;">
                    <tr>
                        <th style="min-width:180px;">Component Name</th>
                        <th style="width:130px;">Field Key</th>
                        <th style="width:130px;" class="text-center">Max Score</th>
                        <th style="width:130px;" class="text-center">Weight (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($components as $i => $c): ?>
                        <tr>
                            <td>
                                <input type="hidden" name="id[]" value="<?= e($c['id'] ?? '') ?>">
                                <input type="text" name="name[]" class="form-control form-control-sm fw-semibold" value="<?= e($c['name']) ?>" required>
                            </td>
                            <td>
                                <input type="text" name="code[]" class="form-control form-control-sm font-monospace bg-light" value="<?= e($c['code']) ?>" readonly>
                            </td>
                            <td class="text-center">
                                <input type="number" step="0.5" min="1" max="100" name="max_score[]" 
                                       class="form-control form-control-sm text-center font-monospace fw-bold" 
                                       value="<?= e($c['max_score']) ?>" required>
                            </td>
                            <td class="text-center">
                                <input type="number" step="0.5" min="1" max="100" name="weight_percent[]" 
                                       class="form-control form-control-sm text-center font-monospace" 
                                       value="<?= e($c['weight_percent']) ?>">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
            <div class="text-muted" style="font-size:12px;">
                <i class="ti ti-info-circle me-1"></i> Score inputs in teacher and admin result sheets are validated server-side against these maximums.
            </div>
            <button type="submit" class="sa-btn sa-btn-primary px-4 py-2" style="border-radius:8px; font-weight:700;">
                <i class="ti ti-device-floppy me-1"></i> Save Assessment Components
            </button>
        </div>
    </form>
</div>
