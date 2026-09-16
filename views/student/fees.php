<div class="student-topbar">
    <div class="page-title"><i class="ti ti-wallet" style="margin-right:8px;color:#0b3d91;"></i>Fee Schedule & Status</div>
    <div>
        <a href="<?= url('student/payment-history') ?>" class="btn btn-outline-primary btn-sm px-3 fw-semibold">
            <i class="ti ti-receipt me-1"></i> View Payment History
        </a>
    </div>
</div>

<div class="student-content">
    <!-- Student Fee Status Banner -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, #0b3d91 0%, #1e40af 100%); color: #fff;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div style="width: 52px; height: 52px; border-radius: 14px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; font-size: 24px;">
                    <i class="ti ti-school text-warning"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1" style="font-size: 18px;"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h5>
                    <div style="font-size: 13px; opacity: 0.85;">
                        Class: <strong><?= e($student['class_name'] ?: 'Enrolled') ?></strong> &bull; 
                        Reg No: <strong><?= e($student['admission_number'] ?: $student['application_number']) ?></strong> &bull; 
                        Academic Session: <strong><?= e($year) ?></strong>
                    </div>
                </div>
            </div>
            <div class="text-md-end">
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.8px; opacity: 0.8;">Total Outstanding Balance</div>
                <div style="font-size: 24px; font-weight: 800; color: <?= $outstanding > 0 ? '#fca5a5' : '#86efac' ?>;">
                    NGN <?= number_format($outstanding, 2) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Fee Items Table -->
    <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;overflow:hidden;" class="shadow-sm mb-4">
        <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
            <div style="font-weight:700;color:#1a2535;font-size:15px;">
                <i class="ti ti-list-details me-1 text-primary"></i> Applicable Class Fees (<?= e($student['class_name'] ?: 'Current Class') ?>)
            </div>
            <span class="badge bg-light text-muted border"><?= count($feeSchedule) ?> Fee Item<?= count($feeSchedule) === 1 ? '' : 's' ?></span>
        </div>

        <?php if (!empty($feeSchedule)): ?>
        <div class="table-responsive">
            <table class="table align-middle mb-0" style="font-size: 13.5px;">
                <thead style="background: #f8fafc; font-size: 11px; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b;">
                    <tr>
                        <th class="ps-4">Fee Item</th>
                        <th>Term</th>
                        <th>Target Class</th>
                        <th class="text-end">Standard Fee</th>
                        <th class="text-end">Amount Paid</th>
                        <th class="text-end">Balance</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($feeSchedule as $fee): 
                        $paidAmt = (float) ($fee['amount_paid'] ?? 0);
                        $balAmt = isset($fee['balance']) ? (float)$fee['balance'] : (float)$fee['amount'];
                        $status = $fee['payment_status'] ?? ($paidAmt >= (float)$fee['amount'] && (float)$fee['amount'] > 0 ? 'Paid' : ($paidAmt > 0 ? 'Partial' : 'Unpaid'));
                    ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark"><?= e($fee['fee_name']) ?></div>
                            <div class="text-muted" style="font-size: 11px;"><?= e($fee['academic_year'] ?: $year) ?></div>
                        </td>
                        <td><span class="badge bg-light text-dark border"><?= e($fee['term']) ?> Term</span></td>
                        <td><span class="badge bg-info-subtle text-info border"><?= e($fee['class_name'] ?: 'All Classes') ?></span></td>
                        <td class="text-end fw-semibold">NGN <?= number_format((float) $fee['amount'], 2) ?></td>
                        <td class="text-end fw-bold text-success">
                            <?= $paidAmt > 0 ? 'NGN ' . number_format($paidAmt, 2) : '—' ?>
                        </td>
                        <td class="text-end fw-bold" style="color: <?= $balAmt > 0 ? '#dc2626' : '#16a34a' ?>;">
                            NGN <?= number_format($balAmt, 2) ?>
                        </td>
                        <td class="text-center">
                            <?php if ($status === 'Paid'): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1"><i class="ti ti-check me-1"></i>Paid</span>
                            <?php elseif ($status === 'Partial'): ?>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">Partial</span>
                            <?php else: ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">Unpaid</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center pe-4">
                            <?php if (!empty($fee['payment_id'])): ?>
                                <a href="<?= url('student/receipt?id=' . $fee['payment_id']) ?>" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 11.5px;">
                                    <i class="ti ti-download me-1"></i> Receipt
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div style="text-align:center;padding:48px;color:#9ca3af;">
            <i class="ti ti-receipt-off" style="font-size:44px;display:block;margin-bottom:12px;opacity:0.6;"></i>
            <div class="fw-semibold">No active fee schedule configured for your class yet.</div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick Navigation to Payment History -->
    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h6 class="fw-bold mb-1 text-dark"><i class="ti ti-history me-1 text-primary"></i> Need payment records & official receipts?</h6>
                <p class="text-muted small mb-0">View all past recorded transactions and download computer-generated payment receipts.</p>
            </div>
            <a href="<?= url('student/payment-history') ?>" class="btn btn-primary btn-sm px-4 py-2 fw-semibold rounded-3 shadow-sm" style="background:#0b3d91; border-color:#0b3d91;">
                <i class="ti ti-receipt me-1"></i> Open Payment History &rarr;
            </a>
        </div>
    </div>
</div>
