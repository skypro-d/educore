<?php 
$children = $children ?? parent_linked_children();
$activeChildId = (int) ($student['id'] ?? 0);
?>
<div class="parent-topbar">
    <div class="page-title"><i class="ti ti-wallet" style="margin-right:8px;color:#0b3d91;"></i>School Fees & Online Payment</div>
    <div class="topbar-actions">
        <a href="<?= url('parent/payment-history') ?>" class="btn btn-outline-primary btn-sm px-3 fw-semibold">
            <i class="ti ti-receipt me-1"></i> View Payment History
        </a>
        <?php if ($outstanding > 0): ?>
        <span style="padding:6px 14px;background:#fee2e2;color:#dc2626;border-radius:20px;font-size:12px;font-weight:700;"><i class="ti ti-alert-circle" style="margin-right:4px;"></i>₦<?= number_format($outstanding) ?> Outstanding</span>
        <?php else: ?>
        <span style="padding:6px 14px;background:#f0fdf4;color:#16a34a;border-radius:20px;font-size:12px;font-weight:700;"><i class="ti ti-circle-check" style="margin-right:4px;"></i>All Fees Cleared</span>
        <?php endif; ?>
    </div>
</div>

<div class="parent-content">
    <?php if (count($children) > 1): ?>
    <!-- Multi-Children Switcher Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.6px;">
                <i class="ti ti-switch-horizontal me-1 text-primary"></i> Your Registered Children (<?= count($children) ?>)
            </div>
            <span class="small text-muted">Click any child to switch and view their fee schedule</span>
        </div>
        <div class="row g-3">
            <?php foreach ($children as $c): 
                $isActive = (int)$c['id'] === $activeChildId;
            ?>
            <div class="col-md-6 col-lg-4">
                <a href="<?= url('parent/switch-child?id=' . $c['id']) ?>" class="text-decoration-none">
                    <div class="card p-3 rounded-3 h-100 transition-all border <?= $isActive ? 'border-primary shadow-sm bg-primary-subtle' : 'border-light-subtle bg-light-subtle hover-shadow' ?>" style="transition: transform .15s ease-in-out;">
                        <div class="d-flex align-items-center gap-3">
                            <?php if (!empty($c['passport_photo'])): ?>
                                <img src="<?= url('uploads/' . $c['passport_photo']) ?>" alt="Photo" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #fff;">
                            <?php else: ?>
                                <div style="width:40px;height:40px;border-radius:50%;background:#0b3d91;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;">
                                    <?= strtoupper(substr($c['first_name'],0,1).substr($c['last_name'],0,1)) ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-bold text-dark text-truncate" style="font-size: 13.5px;">
                                    <?= e($c['first_name'] . ' ' . $c['last_name']) ?>
                                    <?php if ($isActive): ?>
                                        <span class="badge bg-primary text-white ms-1" style="font-size: 10px;">Active</span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted text-truncate" style="font-size: 11.5px;"><?= e($c['class_name'] ?: 'Enrolled') ?> &bull; <?= e($c['admission_number'] ?: $c['application_number']) ?></div>
                            </div>
                            <?php if ($isActive): ?>
                                <i class="ti ti-check-circle-filled text-primary fs-5"></i>
                            <?php else: ?>
                                <i class="ti ti-chevron-right text-muted"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Student Header Summary Card -->
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
                    ₦<?= number_format($outstanding, 2) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Term Fee Schedule Breakdown -->
    <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;overflow:hidden;" class="shadow-sm mb-4">
        <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;display:flex;align-items:center;justify-content:space-between;">
            <div style="font-weight:700;color:#1a2535;font-size:15px;">
                <i class="ti ti-list-details me-1 text-primary"></i> Applicable Fee Items for <?= e($student['class_name'] ?: 'Current Class') ?>
            </div>
            <span class="badge bg-light text-muted border"><?= count($feeSchedule) ?> Fee Items</span>
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
                        <th class="text-center">Receipt</th>
                        <th class="text-center pe-4" style="width: 140px;">Action</th>
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
                        <td class="text-end fw-semibold">₦<?= number_format((float) $fee['amount'], 2) ?></td>
                        <td class="text-end fw-bold text-success">
                            <?= $paidAmt > 0 ? '₦' . number_format($paidAmt, 2) : '—' ?>
                        </td>
                        <td class="text-end fw-bold" style="color: <?= $balAmt > 0 ? '#dc2626' : '#16a34a' ?>;">
                            ₦<?= number_format($balAmt, 2) ?>
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
                        <td class="text-center">
                            <?php if (!empty($fee['payment_id'])): ?>
                                <a href="<?= url('parent/receipt?id=' . $fee['payment_id']) ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 11.5px;" title="Print Official Receipt">
                                    <i class="ti ti-download me-1"></i> Receipt
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center pe-4">
                            <?php if ($balAmt > 0): ?>
                                <button type="button" class="btn btn-sm btn-primary py-1 px-3 fw-semibold shadow-sm btn-pay-fee" 
                                        data-fee-id="<?= (int)$fee['id'] ?>"
                                        data-fee-name="<?= e($fee['fee_name']) ?>"
                                        data-fee-term="<?= e($fee['term']) ?>"
                                        data-balance="<?= $balAmt ?>"
                                        style="font-size: 12px; background:#0b3d91; border-color:#0b3d91;">
                                    <i class="ti ti-credit-card me-1"></i> <?= $paidAmt > 0 ? 'Pay Balance' : 'Pay Fee' ?>
                                </button>
                            <?php else: ?>
                                <span class="text-success small fw-semibold"><i class="ti ti-circle-check me-1"></i>Settled</span>
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
            <div class="fw-semibold">No active fee schedule configured for this class yet.</div>
            <div class="small text-muted mt-1">Check back soon or contact the school finance department.</div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quick Navigation to Payment History -->
    <div class="card border-0 shadow-sm rounded-4 p-4" style="background:#fff;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h6 class="fw-bold mb-1 text-dark"><i class="ti ti-history me-1 text-primary"></i> Looking for past payment receipts?</h6>
                <p class="text-muted small mb-0">View your full chronological payment history, bank transfers, cash receipts, and download official stamped receipts.</p>
            </div>
            <a href="<?= url('parent/payment-history') ?>" class="btn btn-outline-primary btn-sm px-4 py-2 fw-semibold rounded-3">
                <i class="ti ti-receipt me-1"></i> Open Payment History &rarr;
            </a>
        </div>
    </div>
</div>

<!-- Pay Fee Modal -->
<div class="modal fade" id="payFeeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 p-4 text-white" style="background: linear-gradient(135deg, #0b3d91 0%, #1e40af 100%);">
                <div>
                    <h5 class="modal-title fw-bold mb-1" id="modalFeeTitle"><i class="ti ti-credit-card me-2"></i>Pay School Fee</h5>
                    <div class="small opacity-85" id="modalFeeSubtitle">Secure Online Payment Gateway</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?= url('parent/pay-fee') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="applicant_id" value="<?= $activeChildId ?>">
                <input type="hidden" name="fee_structure_id" id="modalFeeStructureId" value="">
                
                <div class="modal-body p-4">
                    <div class="p-3 mb-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Paying For Child:</span>
                            <span class="fw-semibold text-dark small"><?= e($student['first_name'] . ' ' . $student['last_name']) ?> (<?= e($student['class_name'] ?: 'Student') ?>)</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Target Fee Item:</span>
                            <span class="fw-semibold text-primary small" id="modalFeeNameDisplay">—</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Outstanding Balance:</span>
                            <span class="fw-bold text-danger" id="modalFeeBalanceDisplay">₦0.00</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Payment Option</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="payment_type" id="payFull" value="full" checked onchange="toggleAmountInput(false)">
                                <label class="btn btn-outline-primary w-100 py-2 text-start px-3" for="payFull">
                                    <div class="fw-bold small">Pay Full Balance</div>
                                    <div class="text-muted" style="font-size: 11px;" id="fullAmountLabel">₦0.00</div>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="payment_type" id="payCustom" value="custom" onchange="toggleAmountInput(true)">
                                <label class="btn btn-outline-primary w-100 py-2 text-start px-3" for="payCustom">
                                    <div class="fw-bold small">Partial Payment</div>
                                    <div class="text-muted" style="font-size: 11px;">Custom amount</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small" for="paymentAmountInput">Amount to Pay (NGN)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold text-muted">₦</span>
                            <input type="number" step="0.01" min="100" name="amount" id="paymentAmountInput" class="form-control form-control-lg fw-bold text-dark" required>
                        </div>
                        <div class="form-text text-muted small">Minimum online payment amount is ₦100.00</div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold text-dark small">Select Payment Method</label>
                        <select name="gateway" class="form-select">
                            <option value="paystack">Paystack (Debit Card, USSD, Bank Transfer)</option>
                            <option value="monnify">Monnify (Card, Direct Account Transfer)</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 pt-0 d-flex justify-content-between">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-semibold" style="background:#0b3d91; border-color:#0b3d91;">
                        <i class="ti ti-lock me-1"></i> Proceed to Checkout &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentMaxBalance = 0;

document.addEventListener('DOMContentLoaded', function() {
    const payButtons = document.querySelectorAll('.btn-pay-fee');
    const modalEl = document.getElementById('payFeeModal');
    const modal = new bootstrap.Modal(modalEl);

    payButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const feeId = this.dataset.feeId;
            const feeName = this.dataset.feeName;
            const feeTerm = this.dataset.feeTerm;
            const balance = parseFloat(this.dataset.balance) || 0;

            currentMaxBalance = balance;
            document.getElementById('modalFeeStructureId').value = feeId;
            document.getElementById('modalFeeTitle').innerHTML = '<i class="ti ti-credit-card me-2"></i>Pay ' + feeName;
            document.getElementById('modalFeeNameDisplay').innerText = feeName + ' (' + feeTerm + ' Term)';
            document.getElementById('modalFeeBalanceDisplay').innerText = '₦' + balance.toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            document.getElementById('fullAmountLabel').innerText = '₦' + balance.toLocaleString('en-NG', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            
            document.getElementById('payFull').checked = true;
            document.getElementById('paymentAmountInput').value = balance.toFixed(2);
            document.getElementById('paymentAmountInput').max = balance;
            document.getElementById('paymentAmountInput').readOnly = true;

            modal.show();
        });
    });
});

function toggleAmountInput(isCustom) {
    const amountInput = document.getElementById('paymentAmountInput');
    if (isCustom) {
        amountInput.readOnly = false;
        amountInput.value = '';
        amountInput.focus();
    } else {
        amountInput.readOnly = true;
        amountInput.value = currentMaxBalance.toFixed(2);
    }
}
</script>
