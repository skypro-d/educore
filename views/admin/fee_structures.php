<div class="sa-top-bar">
    <div><h1>Fee Structures</h1><p>Configure school fees by class and term</p></div>
    <div class="sa-top-actions">
        <button class="sa-btn sa-btn-primary" data-bs-toggle="modal" data-bs-target="#addFeeModal"><i class="ti ti-plus"></i> Add Fee Item</button>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;align-items:start;">

    <!-- Summary -->
    <div class="sa-card">
        <div class="sa-card-title"><i class="ti ti-chart-bar"></i> Fee Overview</div>
        <?php
        $byTerm = [];
        foreach ($structures as $s) {
            $key = $s['term'].' Term ('.$s['academic_year'].')';
            $byTerm[$key] = ($byTerm[$key] ?? 0) + (float) $s['amount'];
        }
        ?>
        <?php if ($byTerm): foreach ($byTerm as $label => $total): ?>
        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #f3f4f6;">
            <span style="font-size:13px;color:#374151;"><?= e($label) ?></span>
            <span style="font-size:13px;font-weight:700;color:#0b3d91;">NGN <?= number_format($total) ?></span>
        </div>
        <?php endforeach; else: ?>
        <p style="color:#9ca3af;font-size:13px;text-align:center;padding:20px 0;">No fee structures configured yet.</p>
        <?php endif; ?>
    </div>

    <!-- Fee List -->
    <div class="sa-card">
        <div class="sa-card-title"><i class="ti ti-list"></i> All Fee Items</div>
        <div class="table-responsive">
        <table class="app-table" id="feeTable">
            <thead>
                <tr>
                    <th>Fee Name</th>
                    <th>Class</th>
                    <th>Term</th>
                    <th>Year</th>
                    <th style="text-align:right;">Amount</th>
                    <th style="text-align:center;">Optional</th>
                    <th style="text-align:center;">Active</th>
                    <th style="width:80px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($structures as $s): ?>
                <tr>
                    <td style="font-weight:500;"><?= e($s['fee_name']) ?></td>
                    <td style="font-size:12px;"><?= e($s['class_name'] ?: 'All Classes') ?></td>
                    <td><?= e($s['term']) ?></td>
                    <td style="color:#888;"><?= e($s['academic_year']) ?></td>
                    <td style="text-align:right;font-weight:700;color:#0b3d91;">₦<?= number_format((float)$s['amount']) ?></td>
                    <td style="text-align:center;"><?= $s['is_optional'] ? '<span style="color:#d97706;">Yes</span>' : 'No' ?></td>
                    <td style="text-align:center;"><?= $s['is_active'] ? '<span style="color:#16a34a;font-weight:700;">✓</span>' : '<span style="color:#dc2626;">✗</span>' ?></td>
                    <td style="text-align:center;">
                        <form method="POST" action="<?= url('admin/fee-structures/'.$s['id'].'/delete') ?>" onsubmit="return confirm('Delete this fee?');" style="display:inline;">
                            <?= csrf_field() ?>
                            <button class="sa-btn" style="font-size:11px;padding:3px 8px;color:#dc2626;"><i class="ti ti-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

<!-- Fee Payments Overview -->
<div class="sa-card" style="margin-top:20px;">
    <div class="sa-card-title" style="justify-content:space-between;">
        <span><i class="ti ti-receipt"></i> Recent Fee Payments</span>
        <a href="<?= url('admin/student-fees') ?>" class="sa-btn" style="font-size:12px;">View All Payments →</a>
    </div>
    <div class="table-responsive">
    <table class="app-table">
        <thead>
            <tr>
                <th>Student</th>
                <th>Fee Item</th>
                <th>Term</th>
                <th style="text-align:right;">Amount Paid</th>
                <th style="text-align:right;">Balance</th>
                <th style="text-align:center;">Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($payments): foreach ($payments as $p):
                $sc = ['Paid'=>['#f0fdf4','#16a34a'],'Partial'=>['#fef9ec','#d97706'],'Pending'=>['#fee2e2','#dc2626'],'Manual'=>['#f0fdf4','#16a34a'],'Failed'=>['#fee2e2','#dc2626']];
                [$bg,$col] = $sc[$p['payment_status']] ?? ['#f9fafb','#6b7280'];
            ?>
            <tr>
                <td><?= e($p['first_name'].' '.$p['last_name']) ?><br><span style="font-size:11px;color:#9ca3af;"><?= e($p['application_number']) ?></span></td>
                <td><?= e($p['fee_name']) ?></td>
                <td><?= e($p['term']) ?></td>
                <td style="text-align:right;font-weight:700;color:#16a34a;">₦<?= number_format((float)$p['amount_paid']) ?></td>
                <td style="text-align:right;color:<?= (float)$p['balance'] > 0 ? '#dc2626' : '#16a34a' ?>;font-weight:600;">₦<?= number_format((float)$p['balance']) ?></td>
                <td style="text-align:center;"><span style="padding:3px 10px;border-radius:20px;background:<?= $bg ?>;color:<?= $col ?>;font-size:11px;font-weight:700;"><?= e($p['payment_status']) ?></span></td>
                <td style="color:#888;font-size:12px;"><?= e($p['payment_date'] ? date('M j, Y', strtotime($p['payment_date'])) : '—') ?></td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="7" style="text-align:center;color:#9ca3af;padding:24px;">No payments recorded yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Add Fee Modal -->
<div class="modal fade" id="addFeeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#0b3d91;color:#fff;">
                <h5 class="modal-title"><i class="ti ti-plus" style="margin-right:8px;"></i>Add Fee Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="<?= url('admin/fee-structures') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Fee Name <span style="color:#dc2626;">*</span></label>
                        <input type="text" name="fee_name" required class="form-control form-control-sm" placeholder="e.g. Tuition Fee">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Amount (NGN) <span style="color:#dc2626;">*</span></label>
                        <input type="number" name="amount" required min="0" step="100" class="form-control form-control-sm" placeholder="50000">
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="mb-3">
                        <div>
                            <label class="form-label" style="font-size:13px;font-weight:600;">Term</label>
                            <select name="term" class="form-select form-select-sm">
                                <?php foreach (['First','Second','Third','Annual'] as $t): ?>
                                    <option value="<?= $t ?>"><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label" style="font-size:13px;font-weight:600;">Academic Year</label>
                            <input type="text" name="academic_year" class="form-control form-control-sm" value="<?= e(setting('academic_year','')) ?>" placeholder="2024/2025">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" style="font-size:13px;font-weight:600;">Class (leave blank = all)</label>
                        <select name="class_id" class="form-select form-select-sm">
                            <option value="">All Classes</option>
                            <?php foreach ($classes as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="is_optional" value="1" class="form-check-input" id="feeOptional">
                        <label class="form-check-label" for="feeOptional" style="font-size:13px;">Optional fee (parents can choose to pay)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="sa-btn sa-btn-primary"><i class="ti ti-device-floppy"></i> Save Fee</button>
                </div>
            </form>
        </div>
    </div>
</div>
