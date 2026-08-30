<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-2 text-gray-800"><i class="ti ti-credit-card me-2"></i>Subscription & Licenses Portal</h1>
        <p class="text-muted">Manage your school's EduCore SaaS contract, purchase extra SMS credits, order Android POS terminals, and view invoices.</p>
    </div>
</div>

<div class="row">
    <!-- Active License status card -->
    <div class="col-md-7 mb-4">
        <div class="card shadow border-left-primary">
            <div class="card-header py-3 d-flex justify-content-between align-items-center" style="background:#0b3d91; color:white;">
                <h6 class="m-0 fw-bold"><i class="ti ti-key me-1"></i> Active License Status</h6>
                <span class="badge bg-success font-monospace" style="font-size:10px;">Verified Active</span>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless" style="font-size:14px;">
                    <tr>
                        <td class="text-muted fw-bold" style="width:180px;">License Key</td>
                        <td><code class="text-primary fw-bold font-monospace"><?= e($lic['license_key'] ?? 'N/A') ?></code></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-bold">Active Plan Tier</td>
                        <td>
                            <span class="badge bg-warning text-dark text-uppercase fw-bold" style="font-size:10px;">
                                <?= e($lic['plan'] ?? 'Trial') ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-bold">Expires Date</td>
                        <td>
                            <?php if ($lic['expires_at']): ?>
                                <strong class="text-dark"><?= date('F j, Y', strtotime($lic['expires_at'])) ?></strong>
                                <?php 
                                    $diff = strtotime($lic['expires_at']) - time();
                                    $days = round($diff / (30 * 24 * 60 * 60), 0);
                                ?>
                                <span class="text-muted" style="font-size:12px;">(approx. <?= $days ?> month(s) left)</span>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-bold">API Integration Key</td>
                        <td><code class="text-secondary font-monospace" style="font-size:12px;"><?= e($school['api_key'] ?? 'None') ?></code></td>
                    </tr>
                    <tr>
                        <td class="text-muted fw-bold">Installation ID</td>
                        <td><code class="text-secondary font-monospace" style="font-size:12px;"><?= e($school['installation_id'] ?? 'None') ?></code></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <!-- Subscription Invoices History list -->
        <div class="card shadow mt-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 fw-bold text-gray-800"><i class="ti ti-history me-1"></i> Billing & Invoice History</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size:13px;">
                        <thead class="table-light">
                            <tr>
                                <th>Invoice #</th>
                                <th>Plan</th>
                                <th>Payment Date</th>
                                <th style="text-align:right;">Amount (₦)</th>
                                <th>Reference</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($invoices)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">No invoices found.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($invoices as $inv): ?>
                                    <tr>
                                        <td class="font-monospace fw-bold"><?= e($inv['invoice_number']) ?></td>
                                        <td class="text-uppercase fw-bold"><?= e($inv['plan']) ?></td>
                                        <td><?= date('M j, Y', strtotime($inv['payment_date'])) ?></td>
                                        <td style="text-align:right; font-weight:700;">₦<?= number_format($inv['amount'], 2) ?></td>
                                        <td class="font-monospace"><?= e($inv['transaction_ref']) ?></td>
                                        <td><span class="badge bg-success">Paid</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Marketplace purchases sidepanel -->
    <div class="col-md-5 mb-4">
        <!-- Purchase SMS widget -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 fw-bold text-gray-800"><i class="ti ti-message-dots me-1"></i> SMS Balance: <strong><?= number_format($school['sms_balance'] ?? 0) ?></strong></h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('admin/billing/purchase-sms') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label text-uppercase font-monospace" style="font-size:10px;">Select SMS Credits Count</label>
                        <select name="sms_credits" class="form-select form-select-sm">
                            <option value="1000">1,000 Credits (₦4,500.00)</option>
                            <option value="5000">5,000 Credits (₦22,500.00)</option>
                            <option value="10000" selected>10,000 Credits (₦45,000.00)</option>
                            <option value="25000">25,000 Credits (₦112,500.00)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold">
                        <i class="ti ti-circle-plus"></i> Recharge SMS Credits
                    </button>
                </form>
            </div>
        </div>

        <!-- Purchase POS hardware widget -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 fw-bold text-gray-800"><i class="ti ti-device-nfc me-1"></i> Order POS Scanners</h6>
            </div>
            <div class="card-body">
                <p class="text-muted" style="font-size:12px; line-height:1.5;">
                    Purchase additional Android POS attendance terminals with built-in QR scanner. Dispatched pre-configured. Price: ₦65,000.00 each.
                </p>
                <form method="POST" action="<?= url('admin/billing/purchase-device') ?>">
                    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label text-uppercase font-monospace" style="font-size:10px;">Terminals Quantity</label>
                        <input type="number" name="quantity" class="form-control form-control-sm" value="1" min="1">
                    </div>
                    <button type="submit" class="btn btn-sm btn-warning text-dark w-100 fw-bold">
                        <i class="ti ti-shopping-cart"></i> Submit POS Order
                    </button>
                </form>
            </div>
        </div>

        <!-- Marketplace Transactions -->
        <div class="card shadow">
            <div class="card-header py-3 bg-white">
                <h6 class="m-0 fw-bold text-gray-800"><i class="ti ti-list me-1"></i> Add-on Purchases Logs</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0" style="font-size:12px;">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th style="text-align:center;">Qty</th>
                                <th style="text-align:right;">Cost (₦)</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($marketplace)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No add-on transactions logged.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($marketplace as $tx): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary text-uppercase" style="font-size:9px;">
                                                <?= e($tx['item_type']) ?>
                                            </span>
                                        </td>
                                        <td style="text-align:center; font-weight:700;"><?= number_format($tx['quantity']) ?></td>
                                        <td style="text-align:right; font-weight:700;">₦<?= number_format($tx['cost'], 2) ?></td>
                                        <td><?= date('M j', strtotime($tx['created_at'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
