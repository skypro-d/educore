<div class="student-topbar">
    <div class="page-title"><i class="ti ti-wallet" style="margin-right:8px;color:#0b3d91;"></i>Fee Schedule & Online Payment</div>
    <div class="d-flex align-items-center gap-2">
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
                    ₦<?= number_format($outstanding, 2) ?>
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
                                <a href="<?= url('student/receipt?id=' . $fee['payment_id']) ?>" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size: 11.5px;">
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
            <a href="<?= url('student/payment-history') ?>" class="btn btn-outline-primary btn-sm px-4 py-2 fw-semibold rounded-3">
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
            <form method="POST" action="<?= url('student/pay-fee') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="fee_structure_id" id="modalFeeStructureId" value="">
                
                <div class="modal-body p-4">
                    <div class="p-3 mb-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Student Name:</span>
                            <span class="fw-semibold text-dark small"><?= e($student['first_name'] . ' ' . $student['last_name']) ?></span>
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
