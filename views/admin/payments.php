<?php
$totalRevenue = array_sum(array_column(array_filter($payments, fn($p) => $p['payment_status'] === 'Paid'), 'amount'));
$totalPending = count(array_filter($payments, fn($p) => $p['payment_status'] === 'Pending'));
$totalPaid    = count(array_filter($payments, fn($p) => $p['payment_status'] === 'Paid'));
$totalFailed  = count(array_filter($payments, fn($p) => $p['payment_status'] === 'Failed'));
?>
<style>
/* ── Payment page ── */
.pay-stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem}
.pay-stat{background:var(--surface);border-radius:12px;padding:1.25rem 1.5rem;display:flex;flex-direction:column;gap:.3rem;border-left:4px solid transparent}
.pay-stat.revenue{border-color:#22c55e}.pay-stat.pending{border-color:#f59e0b}.pay-stat.paid{border-color:#3b82f6}.pay-stat.failed{border-color:#ef4444}
.pay-stat .ps-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:var(--muted)}
.pay-stat .ps-val{font-size:1.4rem;font-weight:700;color:var(--ink)}
.pay-tabs{display:flex;gap:.5rem;margin-bottom:1rem;flex-wrap:wrap}
.pay-tab{padding:.45rem 1.1rem;border-radius:2rem;border:1px solid var(--line);background:transparent;color:var(--muted);cursor:pointer;font-size:.85rem;transition:.15s}
.pay-tab:hover{background:var(--soft);color:var(--ink)}.pay-tab.active{background:var(--brand-primary);border-color:var(--brand-primary);color:#fff;font-weight:600}
.badge-status{display:inline-block;padding:.2rem .6rem;border-radius:2rem;font-size:.75rem;font-weight:600}
.badge-status.paid{background:#16a34a22;color:#16a34a}.badge-status.pending{background:#d9770622;color:#d97706}.badge-status.failed{background:#dc262622;color:#dc2626}
.action-btn{padding:.28rem .65rem;border-radius:.35rem;font-size:.78rem;font-weight:600;border:none;cursor:pointer;transition:.15s;margin-right:3px}
.action-btn.approve{background:#22c55e22;color:#16a34a}.action-btn.approve:hover{background:#22c55e44}
.action-btn.reject{background:#ef444422;color:#dc2626}.action-btn.reject:hover{background:#ef444444}
.action-btn.mark-paid{background:#3b82f622;color:#2563eb}.action-btn.mark-paid:hover{background:#3b82f644}
/* Modals */
.pay-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:9999;align-items:center;justify-content:center}
.pay-modal-overlay.open{display:flex}
.pay-modal{background:var(--surface);border-radius:12px;padding:2rem;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,.4)}
.pay-modal h3{margin:0 0 1rem;font-size:1.1rem}
.pay-modal .form-group{margin-bottom:1rem}
.pay-modal label{font-size:.82rem;color:var(--muted);display:block;margin-bottom:.3rem}
.pay-modal input,.pay-modal select,.pay-modal textarea{width:100%;padding:.55rem .8rem;border-radius:.45rem;border:1px solid var(--line);background:var(--soft);color:var(--ink);font-size:.9rem;box-sizing:border-box}
.pay-modal textarea{resize:vertical;min-height:80px}
.pay-modal .modal-actions{display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.25rem}
.pay-modal .btn-cancel{background:transparent;border:1px solid var(--line);color:var(--muted);padding:.5rem 1.2rem;border-radius:.4rem;cursor:pointer}
.pay-modal .btn-submit{background:var(--brand-primary);border:none;color:#fff;padding:.5rem 1.4rem;border-radius:.4rem;cursor:pointer;font-weight:600}
</style>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Payments &amp; Approvals</h1>
    <?php if ($totalPending > 0): ?>
        <span class="badge-status pending" style="font-size:.85rem;padding:.35rem .9rem">
            <?= $totalPending ?> Pending Approval
        </span>
    <?php endif; ?>
</div>

<!-- Stats -->
<div class="pay-stat-grid">
    <div class="pay-stat revenue">
        <span class="ps-label">Total Revenue</span>
        <span class="ps-val">&#8358;<?= number_format($totalRevenue) ?></span>
    </div>
    <div class="pay-stat paid">
        <span class="ps-label">Paid</span>
        <span class="ps-val"><?= $totalPaid ?></span>
    </div>
    <div class="pay-stat pending">
        <span class="ps-label">Pending Approval</span>
        <span class="ps-val"><?= $totalPending ?></span>
    </div>
    <div class="pay-stat failed">
        <span class="ps-label">Failed / Declined</span>
        <span class="ps-val"><?= $totalFailed ?></span>
    </div>
</div>

<!-- Filter Tabs -->
<div class="pay-tabs" id="payTabs">
    <button class="pay-tab active" data-filter="all">All (<?= count($payments) ?>)</button>
    <button class="pay-tab" data-filter="pending">Pending (<?= $totalPending ?>)</button>
    <button class="pay-tab" data-filter="paid">Paid (<?= $totalPaid ?>)</button>
    <button class="pay-tab" data-filter="failed">Failed (<?= $totalFailed ?>)</button>
</div>

<!-- Table -->
<div class="panel">
    <div class="table-responsive">
        <table class="table align-middle data-table" id="paymentsTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reference</th>
                    <th>Applicant</th>
                    <th>Fee Type</th>
                    <th>Amount</th>
                    <th>Gateway</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $i => $p): ?>
                <tr data-status="<?= strtolower(e($p['payment_status'])) ?>">
                    <td style="color:var(--muted);font-size:.8rem"><?= $i + 1 ?></td>
                    <td><code style="font-size:.75rem;word-break:break-all"><?= e($p['transaction_reference']) ?></code></td>
                    <td>
                        <div style="font-weight:600;font-size:.88rem"><?= e($p['first_name'] . ' ' . $p['last_name']) ?></div>
                        <div style="font-size:.75rem;color:var(--muted)"><?= e($p['application_number']) ?></div>
                    </td>
                    <td><?= e(ucwords(str_replace('_', ' ', $p['fee_type'] ?? 'admission_fee'))) ?></td>
                    <td style="font-weight:600">&#8358;<?= number_format((float) $p['amount']) ?></td>
                    <td><?= e(ucfirst($p['gateway'] ?? 'paystack')) ?></td>
                    <td>
                        <span class="badge-status <?= strtolower(e($p['payment_status'])) ?>">
                            <?= e($p['payment_status']) ?>
                        </span>
                    </td>
                    <td style="font-size:.82rem;white-space:nowrap"><?= e($p['payment_date'] ?? $p['created_at']) ?></td>
                    <td style="white-space:nowrap">
                        <?php if ($p['payment_status'] === 'Pending'): ?>
                            <button class="action-btn approve"
                                onclick="openApprovalModal(<?= (int)$p['id'] ?>, '<?= addslashes(e($p['transaction_reference'])) ?>', '<?= addslashes(e($p['first_name'].' '.$p['last_name'])) ?>', <?= (float)$p['amount'] ?>)">
                                ✓ Approve
                            </button>
                            <button class="action-btn reject"
                                onclick="openRejectModal(<?= (int)$p['id'] ?>, '<?= addslashes(e($p['transaction_reference'])) ?>', '<?= addslashes(e($p['first_name'].' '.$p['last_name'])) ?>')">
                                ✕ Reject
                            </button>
                        <?php elseif ($p['payment_status'] === 'Failed'): ?>
                            <button class="action-btn mark-paid"
                                onclick="openApprovalModal(<?= (int)$p['id'] ?>, '<?= addslashes(e($p['transaction_reference'])) ?>', '<?= addslashes(e($p['first_name'].' '.$p['last_name'])) ?>', <?= (float)$p['amount'] ?>)">
                                &#8593; Mark Paid
                            </button>
                        <?php else: ?>
                            <span style="color:var(--muted);font-size:.8rem">&#8212;</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($payments)): ?>
                <tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--muted)">No payments recorded yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══════════ APPROVE / MARK-PAID MODAL ═══════════ -->
<div class="pay-modal-overlay" id="approveModal">
    <div class="pay-modal">
        <h3 id="approveModalTitle">✓ Approve Payment</h3>
        <form method="POST" action="?route=payments/approve">
            <?= csrf_field() ?>
            <input type="hidden" name="payment_id" id="appPaymentId">
            <div class="form-group">
                <label>Applicant</label>
                <input type="text" id="appApplicantName" readonly style="opacity:.7">
            </div>
            <div class="form-group">
                <label>Amount</label>
                <input type="text" id="appAmount" readonly style="opacity:.7">
            </div>
            <div class="form-group">
                <label>Payment Method</label>
                <select name="method">
                    <option value="manual_bank">Manual Bank Transfer</option>
                    <option value="cash">Cash</option>
                    <option value="cheque">Cheque</option>
                    <option value="pos">POS Terminal</option>
                    <option value="paystack">Paystack (override)</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>Approval Notes <span style="color:var(--muted)">(optional)</span></label>
                <textarea name="notes" placeholder="e.g. Confirmed via bank teller slip, Ref: XXX…"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModals()">Cancel</button>
                <button type="submit" class="btn-submit">&#10003; Confirm Approval</button>
            </div>
        </form>
    </div>
</div>

<!-- ═══════════ REJECT MODAL ═══════════ -->
<div class="pay-modal-overlay" id="rejectModal">
    <div class="pay-modal">
        <h3>✕ Reject / Decline Payment</h3>
        <form method="POST" action="?route=payments/reject">
            <?= csrf_field() ?>
            <input type="hidden" name="payment_id" id="rejPaymentId">
            <div class="form-group">
                <label>Applicant</label>
                <input type="text" id="rejApplicantName" readonly style="opacity:.7">
            </div>
            <div class="form-group">
                <label>Reason for Rejection <span style="color:#dc2626">*</span></label>
                <textarea name="reason" required placeholder="e.g. Duplicate submission, incorrect amount, unverified transfer…"></textarea>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModals()">Cancel</button>
                <button type="submit" class="btn-submit" style="background:#dc2626">&#10005; Reject Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
// Filter tabs
document.getElementById('payTabs').addEventListener('click', function(e) {
    const btn = e.target.closest('.pay-tab');
    if (!btn) return;
    document.querySelectorAll('.pay-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const filter = btn.dataset.filter;
    document.querySelectorAll('#paymentsTable tbody tr').forEach(row => {
        row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
    });
});

// Approve modal
function openApprovalModal(id, ref, name, amount) {
    document.getElementById('appPaymentId').value    = id;
    document.getElementById('appApplicantName').value = name;
    document.getElementById('appAmount').value       = '\u20A6' + Number(amount).toLocaleString();
    document.getElementById('approveModalTitle').textContent = '\u2713 Approve Payment \u2014 ' + ref;
    document.getElementById('approveModal').classList.add('open');
}

// Reject modal
function openRejectModal(id, ref, name) {
    document.getElementById('rejPaymentId').value    = id;
    document.getElementById('rejApplicantName').value = name;
    document.getElementById('rejectModal').classList.add('open');
}

// Close modals
function closeModals() {
    document.querySelectorAll('.pay-modal-overlay').forEach(m => m.classList.remove('open'));
}
document.querySelectorAll('.pay-modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) { if (e.target === this) closeModals(); });
});
</script>
