<div class="sa-top-bar">
    <div>
        <h1>Student Fees & Payments</h1>
        <p>Record manual payments and track parent fee transactions</p>
    </div>
    <div class="sa-top-actions">
        <button class="sa-btn sa-btn-primary" data-bs-toggle="modal" data-bs-target="#recordPaymentModal"><i class="ti ti-plus"></i> Record Manual Payment</button>
        <a class="sa-btn" href="<?= url('admin/fee-structures') ?>"><i class="ti ti-settings"></i> Fee Structures</a>
    </div>
</div>

<div class="sa-metrics" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
    <?php
    $totalCollected = 0;
    $totalOutstanding = 0;
    foreach ($payments as $p) {
        if ($p['payment_status'] === 'Paid' || $p['payment_status'] === 'Manual') {
            $totalCollected += (float)$p['amount_paid'];
        } else if ($p['payment_status'] === 'Partial') {
            $totalCollected += (float)$p['amount_paid'];
            $totalOutstanding += (float)$p['balance'];
        } else if ($p['payment_status'] === 'Pending') {
            $totalOutstanding += (float)$p['balance'];
        }
    }
    ?>
    <div class="sa-metric-card">
        <div class="label"><i class="ti ti-wallet"></i> Total Fees Collected</div>
        <div class="value" style="color:#16a34a;">₦<?= number_format($totalCollected) ?></div>
        <div class="sub">Cash, transfer & Paystack</div>
    </div>
    <div class="sa-metric-card">
        <div class="label"><i class="ti ti-alert-circle"></i> Total Outstanding</div>
        <div class="value" style="color:#dc2626;">₦<?= number_format($totalOutstanding) ?></div>
        <div class="sub">Unpaid & partial balances</div>
    </div>
    <div class="sa-metric-card">
        <div class="label"><i class="ti ti-receipt"></i> Total Invoiced</div>
        <div class="value" style="color:#0b3d91;">₦<?= number_format($totalCollected + $totalOutstanding) ?></div>
        <div class="sub">Across all fee structures</div>
    </div>
    <div class="sa-metric-card">
        <div class="label"><i class="ti ti-users"></i> Active Payees</div>
        <div class="value"><?= count(array_unique(array_column($payments, 'applicant_id'))) ?></div>
        <div class="sub">Students with transactions</div>
    </div>
</div>

<div class="sa-card">
    <div class="sa-card-title"><i class="ti ti-filter"></i> Filters</div>
    <form method="GET" action="<?= url('admin/student-fees') ?>" class="row g-3 align-items-end" style="padding: 10px 0;">
        <div class="col-md-3">
            <label class="form-label" style="font-size: 12px; font-weight: 600;">Search Student</label>
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Name or App Number" value="<?= e($filters['q'] ?? '') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label" style="font-size: 12px; font-weight: 600;">Class</label>
            <select name="class_id" class="form-select form-select-sm">
                <option value="">All Classes</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($filters['class_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label" style="font-size: 12px; font-weight: 600;">Payment Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <?php foreach (['Paid', 'Partial', 'Pending', 'Failed'] as $st): ?>
                    <option value="<?= $st ?>" <?= ($filters['status'] ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3" style="display:flex; gap:10px;">
            <button type="submit" class="sa-btn sa-btn-primary" style="flex:1; justify-content:center;"><i class="ti ti-search"></i> Apply</button>
            <a href="<?= url('admin/student-fees') ?>" class="sa-btn" style="flex:1; justify-content:center; border: 1px solid #d1d5db; background:#fff;"><i class="ti ti-refresh"></i> Clear</a>
        </div>
    </form>
</div>

<div class="sa-card" style="margin-top: 20px;">
    <div class="sa-card-title"><i class="ti ti-receipt"></i> Fee Payments Log</div>
    <div class="table-responsive">
        <table class="app-table" id="studentFeesTable">
            <thead>
                <tr>
                    <th>Student Info</th>
                    <th>Class</th>
                    <th>Fee Item</th>
                    <th style="text-align:right;">Fee Amt</th>
                    <th style="text-align:right;">Amt Paid</th>
                    <th style="text-align:right;">Balance</th>
                    <th style="text-align:center;">Method</th>
                    <th style="text-align:center;">Status</th>
                    <th>Date</th>
                    <th style="width:100px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($payments): foreach ($payments as $p):
                    $sc = ['Paid'=>['#f0fdf4','#16a34a'],'Partial'=>['#fef9ec','#d97706'],'Pending'=>['#fee2e2','#dc2626'],'Manual'=>['#f0fdf4','#16a34a'],'Failed'=>['#fee2e2','#dc2626']];
                    [$bg,$col] = $sc[$p['payment_status']] ?? ['#f9fafb','#6b7280'];
                ?>
                <tr>
                    <td>
                        <strong style="color:#1e293b;"><?= e($p['first_name'].' '.$p['last_name']) ?></strong>
                        <div style="font-size:11px;color:#64748b;"><?= e($p['application_number']) ?></div>
                    </td>
                    <td><?= e($p['class_name']) ?></td>
                    <td>
                        <strong><?= e($p['fee_name']) ?></strong>
                        <div style="font-size:11px;color:#64748b;"><?= e($p['term']) ?> Term | <?= e($p['academic_year']) ?></div>
                    </td>
                    <td style="text-align:right;font-weight:600;color:#334155;">₦<?= number_format((float)$p['fee_amount']) ?></td>
                    <td style="text-align:right;font-weight:700;color:#16a34a;">₦<?= number_format((float)$p['amount_paid']) ?></td>
                    <td style="text-align:right;font-weight:700;color:<?= (float)$p['balance'] > 0 ? '#dc2626' : '#16a34a' ?>;">₦<?= number_format((float)$p['balance']) ?></td>
                    <td style="text-align:center;text-transform:capitalize;font-size:12px;"><?= e(str_replace('_', ' ', $p['payment_method'])) ?></td>
                    <td style="text-align:center;"><span style="padding:3px 10px;border-radius:20px;background:<?= $bg ?>;color:<?= $col ?>;font-size:11px;font-weight:700;"><?= e($p['payment_status']) ?></span></td>
                    <td style="color:#64748b;font-size:12px;"><?= e($p['payment_date'] ? date('M j, Y H:i', strtotime($p['payment_date'])) : '—') ?></td>
                    <td>
                        <?php if ((float)$p['balance'] > 0): ?>
                        <button class="sa-btn sa-btn-primary btn-record-partial" style="font-size:11px;padding:4px 8px;" 
                                data-id="<?= $p['id'] ?>" 
                                data-student="<?= e($p['first_name'].' '.$p['last_name']) ?>"
                                data-fee="<?= e($p['fee_name']) ?>"
                                data-balance="<?= $p['balance'] ?>"
                                data-bs-toggle="modal" data-bs-target="#recordPartialPaymentModal">
                            Pay Bal
                        </button>
                        <?php else: ?>
                            <span style="color:#16a34a;font-size:12px;font-weight:600;"><i class="ti ti-check"></i> Complete</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; else: ?>
                <tr>
                    <td colspan="10" style="text-align:center;color:#94a3b8;padding:40px;">No fee payments matches found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Record Manual Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#0b3d91;color:#fff;">
                <h5 class="modal-title"><i class="ti ti-plus" style="margin-right:8px;"></i>Record Manual Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('admin/student-fees') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Select Student <span style="color:#dc2626;">*</span></label>
                        <select name="applicant_id" required class="form-select form-select-sm" id="selectStudent">
                            <option value="">-- Choose Enrolled Student --</option>
                            <?php foreach ($students as $stud): ?>
                                <option value="<?= $stud['id'] ?>" data-class="<?= $stud['class_id'] ?>"><?= e($stud['first_name'].' '.$stud['last_name']) ?> (<?= e($stud['application_number']) ?>) - <?= e($stud['class_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Select Fee Item <span style="color:#dc2626;">*</span></label>
                        <select name="fee_structure_id" required class="form-select form-select-sm" id="selectFee">
                            <option value="">-- Choose Fee Structure --</option>
                            <?php foreach ($feeStructures as $fee): ?>
                                <option value="<?= $fee['id'] ?>" data-class="<?= $fee['class_id'] ?>" data-amount="<?= $fee['amount'] ?>">
                                    <?= e($fee['fee_name']) ?> - ₦<?= number_format($fee['amount']) ?> (<?= e($fee['term']) ?> Term, <?= e($fee['class_name'] ?: 'All Classes') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Amount Paying (NGN) <span style="color:#dc2626;">*</span></label>
                        <input type="number" name="amount_paid" required min="1" step="50" class="form-control form-control-sm" id="amountPaidInput">
                        <div class="form-text" id="amountHelp">Enter full or partial payment amount.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Payment Method</label>
                        <select name="payment_method" class="form-select form-select-sm">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Payment Date</label>
                        <input type="datetime-local" name="payment_date" class="form-control form-control-sm" value="<?= date('Y-m-d\TH:i') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Notes / Reference</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Teller number or transaction memo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="sa-btn sa-btn-primary"><i class="ti ti-device-floppy"></i> Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Balance Payment Modal -->
<div class="modal fade" id="recordPartialPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#16a34a;color:#fff;">
                <h5 class="modal-title"><i class="ti ti-wallet" style="margin-right:8px;"></i>Record Balance Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('admin/student-fees/pay-balance') ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="payment_id" id="partialPaymentId">
                <div class="modal-body">
                    <p style="font-size:13px;color:#4b5563;">
                        Student: <strong id="partialStudentName">—</strong><br>
                        Fee Item: <strong id="partialFeeName">—</strong><br>
                        Remaining Balance: <strong style="color:#dc2626;" id="partialBalanceAmt">₦0.00</strong>
                    </p>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Amount Paying (NGN) <span style="color:#dc2626;">*</span></label>
                        <input type="number" name="amount_paid" required min="1" step="50" class="form-control form-control-sm" id="partialAmountInput">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Payment Method</label>
                        <select name="payment_method" class="form-select form-select-sm">
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Notes / Reference</label>
                        <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Teller or transaction ID..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="sa-btn" style="background:#16a34a;color:#fff;"><i class="ti ti-check"></i> Apply Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectStudent = document.getElementById('selectStudent');
    const selectFee = document.getElementById('selectFee');
    const amountInput = document.getElementById('amountPaidInput');
    
    // Auto fill fee amount when student & fee is selected
    function autoFillAmount() {
        const selectedFeeOption = selectFee.options[selectFee.selectedIndex];
        if (selectedFeeOption && selectedFeeOption.value) {
            const amount = selectedFeeOption.getAttribute('data-amount');
            amountInput.value = amount;
        }
    }
    
    selectFee.addEventListener('change', autoFillAmount);

    // Setup partial payment modal trigger
    const partialBtns = document.querySelectorAll('.btn-record-partial');
    partialBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('partialPaymentId').value = this.getAttribute('data-id');
            document.getElementById('partialStudentName').textContent = this.getAttribute('data-student');
            document.getElementById('partialFeeName').textContent = this.getAttribute('data-fee');
            const balance = parseFloat(this.getAttribute('data-balance'));
            document.getElementById('partialBalanceAmt').textContent = '₦' + balance.toLocaleString();
            document.getElementById('partialAmountInput').value = balance;
            document.getElementById('partialAmountInput').max = balance;
        });
    });
});
</script>
