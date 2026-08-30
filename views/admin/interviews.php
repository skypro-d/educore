<h1 class="h3 mb-4">Interview Management</h1>
<div class="row g-4">
    <div class="col-lg-4">
        <form class="panel" method="post" action="<?= url('admin/interviews') ?>">
            <?= csrf_field() ?>
            <label class="form-label">Applicant</label>
            <select class="form-select mb-3" name="applicant_id"><?php foreach ($applications as $app): ?><option value="<?= e($app['id']) ?>"><?= e($app['application_number'] . ' - ' . $app['first_name'] . ' ' . $app['last_name']) ?></option><?php endforeach; ?></select>
            <label class="form-label">Schedule</label><input class="form-control mb-3" type="datetime-local" name="scheduled_at" required>
            <label class="form-label">Score</label><input class="form-control mb-3" type="number" name="score" min="0" max="100">
            <label class="form-label">Remarks</label><textarea class="form-control mb-3" name="remarks"></textarea>
            <button class="btn btn-primary">Save Interview</button>
        </form>
    </div>
    <div class="col-lg-8"><div class="panel"><table class="table"><thead><tr><th>Applicant</th><th>Schedule</th><th>Score</th><th>Remarks</th></tr></thead><tbody><?php foreach ($interviews as $row): ?><tr><td><?= e($row['application_number'] . ' - ' . $row['first_name'] . ' ' . $row['last_name']) ?></td><td><?= e($row['scheduled_at']) ?></td><td><?= e((string) $row['score']) ?></td><td><?= e($row['remarks']) ?></td></tr><?php endforeach; ?></tbody></table></div></div>
</div>

