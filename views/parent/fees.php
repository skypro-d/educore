<div class="parent-topbar">
    <div class="page-title"><i class="ti ti-receipt" style="margin-right:8px;color:#16a34a;"></i>School Fees</div>
    <div class="topbar-actions">
        <?php if ($outstanding > 0): ?>
        <span style="padding:6px 14px;background:#fee2e2;color:#dc2626;border-radius:20px;font-size:12px;font-weight:700;"><i class="ti ti-alert-circle" style="margin-right:4px;"></i>NGN <?= number_format($outstanding) ?> outstanding</span>
        <?php else: ?>
        <span style="padding:6px 14px;background:#f0fdf4;color:#16a34a;border-radius:20px;font-size:12px;font-weight:700;"><i class="ti ti-circle-check" style="margin-right:4px;"></i>All fees paid</span>
        <?php endif; ?>
    </div>
</div>
<div class="parent-content">

    <!-- Year Filter -->
    <form method="GET" action="<?= url('parent/fees') ?>" style="background:#fff;border-radius:14px;border:1px solid #e8eef4;padding:16px 20px;margin-bottom:20px;display:flex;gap:10px;align-items:center;">
        <input type="hidden" name="route" value="fees">
        <label style="font-size:13px;font-weight:600;color:#374151;">Academic Year:</label>
        <input type="text" name="year" value="<?= e($year) ?>" placeholder="2024/2025" style="padding:8px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;width:110px;">
        <button type="submit" style="padding:8px 18px;background:#0b3d91;color:#fff;border:none;border-radius:8px;font-size:13px;cursor:pointer;">Filter</button>
    </form>

    <!-- Payments Table -->
    <div style="background:#fff;border-radius:14px;border:1px solid #e8eef4;overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid #f3f4f6;font-weight:600;color:#1a2535;font-size:14px;">
            Fee Payments — <?= e($year ?: 'All Years') ?>
        </div>
        <?php if ($payments): ?>
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="padding:10px 20px;text-align:left;font-size:11px;color:#6b7280;font-weight:700;">FEE ITEM</th>
                    <th style="padding:10px 16px;text-align:left;font-size:11px;color:#6b7280;font-weight:700;">TERM</th>
                    <th style="padding:10px 16px;text-align:right;font-size:11px;color:#6b7280;font-weight:700;">AMOUNT</th>
                    <th style="padding:10px 16px;text-align:right;font-size:11px;color:#6b7280;font-weight:700;">PAID</th>
                    <th style="padding:10px 16px;text-align:right;font-size:11px;color:#6b7280;font-weight:700;">BALANCE</th>
                    <th style="padding:10px 16px;text-align:center;font-size:11px;color:#6b7280;font-weight:700;">STATUS</th>
                    <th style="padding:10px 16px;text-align:center;font-size:11px;color:#6b7280;font-weight:700;">ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payments as $p):
                    $statusColors = ['Paid'=>['#f0fdf4','#16a34a'],'Partial'=>['#fef9ec','#d97706'],'Pending'=>['#fee2e2','#dc2626'],'Failed'=>['#fee2e2','#dc2626'],'Manual'=>['#f0fdf4','#16a34a']];
                    [$bg,$col] = $statusColors[$p['payment_status']] ?? ['#f9fafb','#6b7280'];
                ?>
                <tr style="border-bottom:1px solid #f9fafb;">
                    <td style="padding:12px 20px;font-size:13px;font-weight:500;color:#374151;"><?= e($p['fee_name']) ?></td>
                    <td style="padding:12px 16px;font-size:13px;color:#6b7280;"><?= e($p['term']) ?></td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;color:#374151;">NGN <?= number_format((float)$p['fee_amount']) ?></td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:600;color:#16a34a;">NGN <?= number_format((float)$p['amount_paid']) ?></td>
                    <td style="padding:12px 16px;text-align:right;font-size:13px;color:<?= (float)$p['balance'] > 0 ? '#dc2626' : '#16a34a' ?>;font-weight:600;">NGN <?= number_format((float)$p['balance']) ?></td>
                    <td style="padding:12px 16px;text-align:center;"><span style="padding:3px 10px;border-radius:20px;background:<?= $bg ?>;color:<?= $col ?>;font-size:11px;font-weight:700;"><?= e($p['payment_status']) ?></span></td>
                    <td style="padding:12px 16px;text-align:center;">
                        <?php if (in_array($p['payment_status'], ['Paid','Manual'], true) && $p['receipt_number']): ?>
                            <a href="<?= url('parent/fees?route=fees&receipt=' . $p['id']) ?>" style="font-size:12px;color:#0b3d91;text-decoration:none;"><i class="ti ti-download"></i> Receipt</a>
                        <?php elseif ($p['payment_status'] === 'Pending'): ?>
                            <span style="font-size:12px;color:#6b7280;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php else: ?>
            <div style="text-align:center;padding:48px;color:#9ca3af;">
                <i class="ti ti-receipt" style="font-size:40px;display:block;margin-bottom:12px;"></i>
                No fee records found for this year.
            </div>
        <?php endif; ?>
    </div>

    <?php if ($outstanding > 0): ?>
    <!-- Pay Now CTA -->
    <div style="margin-top:20px;background:linear-gradient(135deg,#0b3d91,#1a6dd8);border-radius:14px;padding:24px;display:flex;align-items:center;justify-content:space-between;gap:16px;">
        <div>
            <div style="color:#fff;font-size:16px;font-weight:700;margin-bottom:4px;">Outstanding Balance</div>
            <div style="color:rgba(255,255,255,.8);font-size:13px;">You have NGN <?= number_format($outstanding) ?> unpaid. Pay now to avoid penalties.</div>
        </div>
        <a href="#" onclick="alert('Payment integration — connect Paystack here')" style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:#f4b942;color:#1a2535;border-radius:10px;text-decoration:none;font-size:14px;font-weight:700;white-space:nowrap;"><i class="ti ti-credit-card"></i> Pay Now</a>
    </div>
    <?php endif; ?>

</div>
