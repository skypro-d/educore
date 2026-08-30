<section class="container py-5">
    <div class="page-heading">
        <h1>Track Application</h1>
        <p>Enter your application number, for example ADM-2026-00001.</p>
    </div>
    <form class="track-box" method="post" action="<?= url('track') ?>">
        <?= csrf_field() ?>
        <div class="input-group input-group-lg">
            <input class="form-control" name="application_number" value="<?= e($number) ?>" placeholder="Application Number" required>
            <button class="btn btn-primary">Check Status</button>
        </div>
    </form>
    <?php if ($applicant): ?>
        <div class="status-card mt-4">
            <div>
                <div class="text-muted">Applicant</div>
                <h2><?= e($applicant['first_name'] . ' ' . $applicant['last_name']) ?></h2>
                <p class="mb-0"><?= e($applicant['class_name']) ?> | <?= e($applicant['application_number']) ?></p>
                <?php if ($payment): ?>
                    <p class="mb-0 mt-2"><strong>Admission Fee:</strong> <?= e($payment['payment_status']) ?> | NGN <?= number_format((float) $payment['amount']) ?></p>
                <?php endif; ?>
                <?php if (!empty($applicant['admission_number'])): ?>
                    <p class="mb-0 mt-2"><strong>Admission No:</strong> <?= e($applicant['admission_number']) ?></p>
                <?php endif; ?>
            </div>
            <span class="status <?= e(strtolower(str_replace(' ', '-', $applicant['status']))) ?>"><?= e($applicant['status']) ?></span>
        </div>
        <div class="panel mt-4">
            <h2>Progress Timeline</h2>
            <div class="progress-timeline">
                <?php $steps = ['Submitted','Under Review','Approved','Acceptance Fee','Enrolled']; $active = array_search($applicant['status'], $steps, true); if ($applicant['status'] === 'Interview Scheduled' || $applicant['status'] === 'Exam Completed') { $active = 1; } ?>
                <?php foreach ($steps as $i => $step): ?>
                    <div class="progress-step <?= $active !== false && $i <= $active ? 'active' : '' ?>"><span class="progress-dot"></span><span><?= e($step) ?></span></div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php if ($payment && $payment['payment_status'] !== 'Paid'): ?>
            <div class="mt-3">
                <a class="btn btn-primary" href="<?= url('payment/process.php?applicant_id=' . (int) $applicant['id']) ?>">Pay Admission Form Fee</a>
            </div>
        <?php endif; ?>
        <?php if ($applicant['status'] === 'Approved'): ?>
            <div class="mt-3">
                <a class="btn btn-success" href="<?= url('payment/process.php?applicant_id=' . (int) $applicant['id'] . '&fee=acceptance_fee') ?>">Pay Acceptance Fee</a>
                <a class="btn btn-outline-primary" href="<?= url('student?number=' . urlencode($applicant['application_number'])) ?>">Open Student Dashboard</a>
            </div>
        <?php elseif ($applicant['status'] === 'Enrolled'): ?>
            <div class="mt-3">
                <a class="btn btn-success" href="<?= url('student?number=' . urlencode($applicant['application_number'])) ?>">Open Student Profile</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
