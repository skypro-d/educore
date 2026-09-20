<?php
$s = $student;
$dob = $s['date_of_birth'] ? date('M j, Y', strtotime($s['date_of_birth'])) : '—';
$age = $s['date_of_birth'] ? (int) date_diff(new DateTime($s['date_of_birth']), new DateTime())->y . ' years' : '—';
$children = $children ?? parent_linked_children();
$activeChildId = (int) ($s['id'] ?? 0);
?>
<div class="parent-topbar">
    <div class="page-title"><i class="ti ti-user" style="margin-right:8px;color:#d97706;"></i>Child Profile</div>
    <?php if (count($children) > 1): ?>
    <div class="topbar-actions">
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fw-semibold">
            <i class="ti ti-users me-1"></i> <?= count($children) ?> Linked Children
        </span>
    </div>
    <?php endif; ?>
</div>

<div class="parent-content">

    <?php if (count($children) > 1): ?>
    <!-- Multi-Children Switcher Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.6px;">
                <i class="ti ti-switch-horizontal me-1 text-primary"></i> Your Registered Children (<?= count($children) ?>)
            </div>
            <span class="small text-muted">Click any child to switch and view their portal records</span>
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
                                <img src="<?= url('uploads/' . $c['passport_photo']) ?>" alt="Photo" style="width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid #fff;">
                            <?php else: ?>
                                <div style="width:48px;height:48px;border-radius:50%;background:#0b3d91;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;">
                                    <?= strtoupper(substr($c['first_name'],0,1).substr($c['last_name'],0,1)) ?>
                                </div>
                            <?php endif; ?>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-bold text-dark text-truncate" style="font-size: 14px;">
                                    <?= e($c['first_name'] . ' ' . $c['last_name']) ?>
                                    <?php if ($isActive): ?>
                                        <span class="badge bg-primary text-white ms-1" style="font-size: 10px;">Active</span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-muted text-truncate"><?= e($c['class_name'] ?: 'Enrolled') ?> &bull; <?= e($c['admission_number'] ?: $c['application_number']) ?></div>
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

    <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">

        <!-- Photo Card -->
        <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;padding:28px;text-align:center;">
            <?php if ($s['passport_photo']): ?>
                <img src="<?= url('uploads/' . $s['passport_photo']) ?>" alt="Photo" style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid #eef4ff;margin-bottom:16px;">
            <?php else: ?>
                <div style="width:120px;height:120px;border-radius:50%;background:linear-gradient(135deg,#0b3d91,#1a6dd8);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:40px;font-weight:700;color:#fff;"><?= strtoupper(substr($s['first_name'],0,1).substr($s['last_name'],0,1)) ?></div>
            <?php endif; ?>
            <div style="font-size:18px;font-weight:700;color:#1a2535;"><?= e($s['first_name'] . ' ' . ($s['middle_name'] ? $s['middle_name'].' ' : '') . $s['last_name']) ?></div>
            <div style="font-size:13px;color:#6b7280;margin-top:4px;"><?= e($s['class_name'] ?? '—') ?></div>
            <div style="margin-top:12px;display:inline-flex;align-items:center;gap:6px;padding:5px 14px;background:#f0fdf4;color:#16a34a;border-radius:20px;font-size:12px;font-weight:700;">
                <i class="ti ti-circle-check" style="font-size:14px;"></i><?= e($s['status']) ?>
            </div>
            <div style="margin-top:16px;padding:12px;background:#f9fafb;border-radius:10px;text-align:left;">
                <div style="font-size:11px;font-weight:700;color:#6b7280;margin-bottom:4px;">REG. NUMBER</div>
                <div style="font-size:13px;font-weight:700;color:#0b3d91;"><?= e($s['admission_number'] ?? $s['application_number']) ?></div>
            </div>
        </div>

        <!-- Details -->
        <div>
            <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;overflow:hidden;margin-bottom:16px;">
                <div style="padding:14px 20px;border-bottom:1px solid #f3f4f6;font-size:13px;font-weight:700;color:#374151;"><i class="ti ti-info-circle" style="margin-right:7px;color:#0b3d91;"></i>Personal Information</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;">
                    <?php $fields = [
                        ['Date of Birth', $dob], ['Age', $age],
                        ['Gender', $s['gender']], ['Blood Group', $s['blood_group'] ?: '—'],
                        ['State of Origin', $s['state_of_origin']], ['Nationality', $s['nationality']],
                        ['Religion', $s['religion'] ?: '—'], ['L.G.A.', $s['local_government'] ?: '—'],
                    ]; ?>
                    <?php foreach ($fields as [$label, $value]): ?>
                    <div style="padding:12px 20px;border-bottom:1px solid #f9fafb;border-right:1px solid #f9fafb;">
                        <div style="font-size:11px;font-weight:700;color:#9ca3af;margin-bottom:3px;"><?= $label ?></div>
                        <div style="font-size:13px;color:#374151;"><?= e($value) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;overflow:hidden;margin-bottom:16px;">
                <div style="padding:14px 20px;border-bottom:1px solid #f3f4f6;font-size:13px;font-weight:700;color:#374151;"><i class="ti ti-users" style="margin-right:7px;color:#0b3d91;"></i>Parent / Guardian</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0;">
                    <?php $pfields = [
                        ["Father's Name", $s['father_name'] ?: '—'], ["Mother's Name", $s['mother_name'] ?: '—'],
                        ['Guardian Name', $s['guardian_name'] ?: '—'], ['Phone', $s['parent_phone']],
                        ['Email', $s['parent_email']], ['Occupation', $s['parent_occupation'] ?: '—'],
                    ]; ?>
                    <?php foreach ($pfields as [$label, $value]): ?>
                    <div style="padding:12px 20px;border-bottom:1px solid #f9fafb;border-right:1px solid #f9fafb;">
                        <div style="font-size:11px;font-weight:700;color:#9ca3af;margin-bottom:3px;"><?= $label ?></div>
                        <div style="font-size:13px;color:#374151;"><?= e($value) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ($s['emergency_name']): ?>
            <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;overflow:hidden;">
                <div style="padding:14px 20px;border-bottom:1px solid #f3f4f6;font-size:13px;font-weight:700;color:#374151;"><i class="ti ti-alert-triangle" style="margin-right:7px;color:#dc2626;"></i>Emergency Contact</div>
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0;">
                    <?php $efields = [['Name',$s['emergency_name']],['Relationship',$s['emergency_relationship']],['Phone',$s['emergency_phone']]]; ?>
                    <?php foreach ($efields as [$label, $value]): ?>
                    <div style="padding:12px 20px;border-bottom:1px solid #f9fafb;border-right:1px solid #f9fafb;">
                        <div style="font-size:11px;font-weight:700;color:#9ca3af;margin-bottom:3px;"><?= $label ?></div>
                        <div style="font-size:13px;color:#374151;"><?= e($value) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Child Payment History & Official Receipts -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mt-4 bg-white">
        <div class="p-3 px-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <div style="width:38px;height:38px;border-radius:10px;background:#f0fdf4;color:#16a34a;display:flex;align-items:center;justify-content:center;font-size:18px;">
                    <i class="ti ti-receipt"></i>
                </div>
                <div>
                    <div class="fw-bold text-dark" style="font-size: 15px;">
                        Payment History &amp; Receipts for <?= e($s['first_name'] . ' ' . $s['last_name']) ?>
                    </div>
                    <div class="small text-muted">View all past term fees, admission charges, and download official receipts</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 fw-semibold" style="font-size: 12px;">
                    <i class="ti ti-wallet me-1"></i> Total Paid: ₦<?= number_format($totalFeePaid ?? 0, 2) ?>
                </span>
                <?php if (($outstanding ?? 0) > 0): ?>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 fw-semibold" style="font-size: 12px;">
                        <i class="ti ti-alert-circle me-1"></i> Balance Due: ₦<?= number_format($outstanding, 2) ?>
                    </span>
                    <a href="<?= url('parent/fees') ?>" class="btn btn-sm btn-primary px-3 fw-semibold" style="background:#0b3d91; border-color:#0b3d91; font-size:12px;">
                        <i class="ti ti-credit-card me-1"></i> Pay Fees
                    </a>
                <?php else: ?>
                    <span class="badge bg-light text-muted border px-3 py-2 fw-semibold" style="font-size: 12px;">
                        <i class="ti ti-shield-check me-1 text-success"></i> Fully Settled
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- 1. School Fee Schedule & Term Invoices (Paid, Partial, Unpaid) -->
        <div class="p-3 px-4 mb-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px; font-size: 11.5px;">
                    <i class="ti ti-calendar-dollar text-primary me-1"></i> Fee Schedule &amp; Invoices (<?= count($feeSchedule ?? []) ?>)
                </div>
                <a href="<?= url('parent/fees') ?>" class="btn btn-sm btn-primary py-1 px-3 fw-semibold" style="font-size: 11.5px; border-radius: 8px;">
                    <i class="ti ti-credit-card me-1"></i> Pay School Fees
                </a>
            </div>

            <?php if (!empty($feeSchedule)): ?>
                <div class="table-responsive rounded-3 border mb-3">
                    <table class="table align-middle mb-0" style="font-size: 13px;">
                        <thead style="background: #f8fafc; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                            <tr>
                                <th class="ps-3">Fee Item</th>
                                <th>Term / Session</th>
                                <th class="text-end">Total Amount</th>
                                <th class="text-end">Amount Paid</th>
                                <th class="text-end">Balance Due</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-3">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feeSchedule as $fs): 
                                $status = $fs['payment_status'] ?? 'Unpaid';
                                $statusClass = match($status) {
                                    'Paid' => 'bg-success-subtle text-success border-success-subtle',
                                    'Partial' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                    default => 'bg-danger-subtle text-danger border-danger-subtle'
                                };
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-dark"><?= e($fs['fee_name']) ?></div>
                                    <?php if (!empty($fs['is_optional'])): ?>
                                        <span class="badge bg-secondary-subtle text-secondary" style="font-size: 10px;">Optional Fee</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= e($fs['term']) ?> Term</span>
                                    <?php if (!empty($fs['academic_year'])): ?>
                                        <div class="text-muted small" style="font-size: 11px;"><?= e($fs['academic_year']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end fw-semibold text-dark">
                                    ₦<?= number_format((float) $fs['fee_amount'], 2) ?>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    ₦<?= number_format((float) $fs['total_paid'], 2) ?>
                                </td>
                                <td class="text-end fw-bold" style="color: <?= (float)$fs['balance_due'] > 0 ? '#dc2626' : '#16a34a' ?>;">
                                    ₦<?= number_format((float) $fs['balance_due'], 2) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $statusClass ?> border px-2 py-1 font-monospace fw-bold">
                                        <?= e($status) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <?php if ($status !== 'Paid'): ?>
                                        <a href="<?= url('parent/fees') ?>" class="btn btn-sm btn-outline-primary py-1 px-2 fw-semibold" style="font-size: 11px;">
                                            <i class="ti ti-wallet me-1"></i> Pay Now
                                        </a>
                                    <?php else: ?>
                                        <span class="text-success small fw-semibold"><i class="ti ti-circle-check-filled me-1"></i>Cleared</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="p-3 bg-light rounded-3 text-center text-muted mb-3" style="font-size: 12.5px;">
                    <i class="ti ti-info-circle me-1"></i> No fee structures configured for this child's class yet.
                </div>
            <?php endif; ?>
        </div>

        <!-- 2. School Fee Payment Transactions Table -->
        <div class="p-3 px-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px; font-size: 11.5px;">
                    <i class="ti ti-receipt text-primary me-1"></i> Payment Receipts &amp; Transactions (<?= count($feePayments ?? []) ?>)
                </div>
                <a href="<?= url('parent/payment-history') ?>" class="small text-decoration-none fw-semibold text-primary">
                    View Full Payment Ledger &rarr;
                </a>
            </div>

            <?php if (!empty($feePayments)): ?>
                <div class="table-responsive rounded-3 border">
                    <table class="table align-middle mb-0" style="font-size: 13px;">
                        <thead style="background: #f8fafc; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                            <tr>
                                <th class="ps-3">Receipt No.</th>
                                <th>Fee Item</th>
                                <th>Term / Session</th>
                                <th>Method</th>
                                <th>Date Paid</th>
                                <th class="text-end">Amount Paid</th>
                                <th class="text-end">Balance</th>
                                <th class="text-center">Status</th>
                                <th class="text-center pe-3">Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($feePayments as $fp): 
                                $status = $fp['payment_status'] ?? 'Paid';
                                $statusClass = match($status) {
                                    'Paid', 'Manual' => 'bg-success-subtle text-success border-success-subtle',
                                    'Partial' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                    default => 'bg-danger-subtle text-danger border-danger-subtle'
                                };
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="font-monospace fw-bold text-primary"><?= e($fp['receipt_number'] ?: 'REC-'.$fp['id']) ?></span>
                                    <?php if (!empty($fp['payment_reference'])): ?>
                                        <div class="text-muted font-monospace" style="font-size: 10.5px;"><?= e($fp['payment_reference']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($fp['fee_name'] ?? 'School Fee') ?></div>
                                    <?php if (!empty($fp['notes'])): ?>
                                        <div class="text-muted small" style="font-size: 11px;"><?= e(mb_strimwidth($fp['notes'], 0, 45, '...')) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= e($fp['term'] ?? 'First') ?> Term</span>
                                    <?php if (!empty($fp['academic_year'])): ?>
                                        <div class="text-muted small" style="font-size: 11px;"><?= e($fp['academic_year']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                        <?= e(ucwords(str_replace('_', ' ', $fp['payment_method'] ?? 'cash'))) ?>
                                    </span>
                                </td>
                                <td class="text-muted" style="font-size: 12px;">
                                    <?= !empty($fp['payment_date']) ? date('d M Y, h:i A', strtotime($fp['payment_date'])) : date('d M Y', strtotime($fp['created_at'])) ?>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    ₦<?= number_format((float) $fp['amount_paid'], 2) ?>
                                </td>
                                <td class="text-end fw-semibold" style="color: <?= (float)$fp['balance'] > 0 ? '#dc2626' : '#16a34a' ?>;">
                                    ₦<?= number_format((float) $fp['balance'], 2) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?= $statusClass ?> border px-2 py-1"><?= e($status) ?></span>
                                </td>
                                <td class="text-center pe-3">
                                    <a href="<?= url('parent/receipt?id=' . $fp['id']) ?>" class="btn btn-sm btn-outline-primary py-1 px-2 fw-semibold" style="font-size: 11.5px;">
                                        <i class="ti ti-download me-1"></i> Receipt
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-4 bg-light rounded-3 text-center text-muted" style="font-size: 13px;">
                    <i class="ti ti-receipt-off fs-3 d-block mb-1 text-secondary opacity-50"></i>
                    No school term fee payments recorded for <?= e($student['first_name'] ?? 'student') ?> yet.
                </div>
            <?php endif; ?>
        </div>

        <!-- Admission / Portal Payments (if any) -->
        <?php if (!empty($admissionPayments)): ?>
            <div class="mt-4">
                <div class="fw-bold text-dark small text-uppercase mb-2" style="letter-spacing: 0.5px; font-size: 11.5px;">
                    <i class="ti ti-credit-card text-primary me-1"></i> Admission &amp; Portal Payments (<?= count($admissionPayments) ?>)
                </div>
                <div class="table-responsive rounded-3 border">
                    <table class="table align-middle mb-0" style="font-size: 13px;">
                        <thead style="background: #f8fafc; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b;">
                            <tr>
                                <th class="ps-3">Reference</th>
                                <th>Fee Purpose</th>
                                <th>Channel</th>
                                <th>Date Paid</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center pe-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admissionPayments as $ap): 
                                $admStatus = $ap['payment_status'] ?? 'Pending';
                                $admBadge = match($admStatus) {
                                    'Paid' => 'bg-success-subtle text-success border-success-subtle',
                                    'Pending' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
                                    default => 'bg-danger-subtle text-danger border-danger-subtle'
                                };
                            ?>
                            <tr>
                                <td class="ps-3 font-monospace fw-bold text-dark"><?= e($ap['transaction_reference']) ?></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                                        <?= e(ucwords(str_replace('_', ' ', $ap['fee_type'] ?? 'Admission Fee'))) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">
                                        <?= e(ucfirst($ap['gateway'] ?? 'paystack')) ?>
                                    </span>
                                </td>
                                <td class="text-muted" style="font-size: 12px;">
                                    <?= !empty($ap['payment_date']) ? date('d M Y, h:i A', strtotime($ap['payment_date'])) : date('d M Y, h:i A', strtotime($ap['created_at'])) ?>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    ₦<?= number_format((float) $ap['amount'], 2) ?>
                                </td>
                                <td class="text-center pe-3">
                                    <span class="badge <?= $admBadge ?> border px-2 py-1"><?= e($admStatus) ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

