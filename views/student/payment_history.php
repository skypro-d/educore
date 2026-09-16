<div class="student-topbar">
    <div class="page-title"><i class="ti ti-receipt" style="margin-right:8px;color:#0b3d91;"></i>Payment History & Receipts</div>
    <div>
        <a href="<?= url('student/fees') ?>" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
            <i class="ti ti-wallet me-1"></i> Fee Schedule
        </a>
    </div>
</div>

<div class="student-content">
    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <form method="GET" action="<?= url('student/payment-history') ?>" class="row g-2 align-items-center">
            <input type="hidden" name="route" value="payment-history">
            <div class="col-auto">
                <label class="form-label mb-0 fw-semibold text-muted small"><i class="ti ti-filter me-1"></i> Academic Year:</label>
            </div>
            <div class="col-auto">
                <input type="text" name="year" value="<?= e($year) ?>" placeholder="e.g. 2026/2027" class="form-control form-control-sm" style="width: 140px;">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold" style="background:#0b3d91; border-color:#0b3d91;">Filter</button>
            </div>
            <?php if ($year !== ''): ?>
            <div class="col-auto">
                <a href="<?= url('student/payment-history') ?>" class="btn btn-light btn-sm px-3 text-muted border">Reset</a>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <!-- Payments Ledger Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
        <div class="p-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <div class="fw-bold text-dark" style="font-size: 15px;">
                <i class="ti ti-history me-1 text-primary"></i> Payment Records for <?= e($student['first_name'] . ' ' . $student['last_name']) ?>
            </div>
            <span class="badge bg-light text-muted border"><?= count($payments) ?> Transaction<?= count($payments) === 1 ? '' : 's' ?></span>
        </div>

        <?php if (!empty($payments)): ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size: 13.5px;">
                <thead style="background: #f8fafc; font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b;">
                    <tr>
                        <th class="ps-4">Receipt No.</th>
                        <th>Fee Description</th>
                        <th>Term / Session</th>
                        <th>Method</th>
                        <th>Date Paid</th>
                        <th class="text-end">Amount Paid</th>
                        <th class="text-end">Balance</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payments as $p): 
                        $status = $p['payment_status'] ?? 'Paid';
                        $statusClass = match($status) {
                            'Paid', 'Manual' => 'bg-success-subtle text-success border-success-subtle',
                            'Partial' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                            default => 'bg-danger-subtle text-danger border-danger-subtle'
                        };
                    ?>
                    <tr>
                        <td class="ps-4">
                            <span class="font-monospace fw-bold text-primary"><?= e($p['receipt_number'] ?: 'REC-'.$p['id']) ?></span>
                            <?php if (!empty($p['payment_reference'])): ?>
                                <div class="text-muted font-monospace" style="font-size: 10.5px;"><?= e($p['payment_reference']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark"><?= e($p['fee_name']) ?></div>
                            <?php if (!empty($p['notes'])): ?>
                                <div class="text-muted small" style="font-size: 11.5px;"><?= e(mb_strimwidth($p['notes'], 0, 45, '...')) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border"><?= e($p['term']) ?> Term</span>
                            <?php if (!empty($p['academic_year'])): ?>
                                <div class="text-muted small" style="font-size: 11px;"><?= e($p['academic_year']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                <?= e(ucwords(str_replace('_', ' ', $p['payment_method'] ?? 'cash'))) ?>
                            </span>
                        </td>
                        <td class="text-muted" style="font-size: 12.5px;">
                            <?= !empty($p['payment_date']) ? date('d M Y, h:i A', strtotime($p['payment_date'])) : date('d M Y', strtotime($p['created_at'])) ?>
                        </td>
                        <td class="text-end fw-bold text-success">
                            NGN <?= number_format((float) $p['amount_paid'], 2) ?>
                        </td>
                        <td class="text-end fw-semibold" style="color: <?= (float)$p['balance'] > 0 ? '#dc2626' : '#16a34a' ?>;">
                            NGN <?= number_format((float) $p['balance'], 2) ?>
                        </td>
                        <td class="text-center">
                            <span class="badge <?= $statusClass ?> border px-2 py-1"><?= e($status) ?></span>
                        </td>
                        <td class="text-center pe-4">
                            <a href="<?= url('student/receipt?id=' . $p['id']) ?>" class="btn btn-sm btn-outline-primary py-1 px-3 fw-semibold" style="font-size: 12px;">
                                <i class="ti ti-download me-1"></i> Receipt
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:50px 20px;color:#9ca3af;">
            <i class="ti ti-receipt-off" style="font-size:46px;display:block;margin-bottom:12px;opacity:0.6;"></i>
            <div class="fw-bold text-dark">No payment records found</div>
            <div class="small text-muted mt-1">Recorded payments made via bank transfer or cash will be listed here.</div>
        </div>
        <?php endif; ?>
    </div>
</div>
