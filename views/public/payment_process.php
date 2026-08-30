<section class="container py-5">
    <div class="page-heading">
        <h1>Complete Payment</h1>
        <p>Pay the <?= e(strtolower($feeLabel)) ?> to continue this admission process.</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="panel">
                <h2><?= e($applicant['first_name'] . ' ' . $applicant['last_name']) ?></h2>
                <p><strong>Application Number:</strong> <?= e($applicant['application_number']) ?></p>
                <p><strong>Desired Class:</strong> <?= e($applicant['class_name'] ?? '') ?></p>
                <p><strong>Parent Email:</strong> <?= e($applicant['parent_email']) ?></p>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel">
                <h2><?= e($feeLabel) ?></h2>
                <div class="display-6 fw-bold mb-3">NGN <?= number_format($feeAmount, 2) ?></div>
                <p><strong>Status:</strong> <?= e($payment['payment_status'] ?? 'Pending') ?></p>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                <?php endif; ?>
                <?php if (($payment['payment_status'] ?? '') === 'Paid'): ?>
                    <a class="btn btn-success" href="<?= url('track?number=' . urlencode($applicant['application_number'])) ?>">View Application Status</a>
                <?php else: ?>
                    <form method="post">
                        <?= csrf_field() ?>
                        <button class="btn btn-primary btn-lg w-100" type="submit">Proceed to Secure Payment</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
