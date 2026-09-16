<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Official Payment Receipt — <?= e($payment['receipt_number'] ?? 'Receipt') ?></title>
    <?php if (setting('favicon')): ?><link rel="icon" href="<?= url('uploads/' . setting('favicon')) ?>"><?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            margin: 0;
            padding: 30px 15px;
        }
        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        .action-bar {
            max-width: 800px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .school-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #0b3d91;
            padding-bottom: 24px;
            margin-bottom: 24px;
        }
        .school-logo {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 12px;
            background: #f8fafc;
            padding: 4px;
            border: 1px solid #e2e8f0;
        }
        .logo-placeholder {
            width: 70px;
            height: 70px;
            border-radius: 12px;
            background: #0b3d91;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 800;
        }
        .school-details h2 {
            font-size: 20px;
            font-weight: 800;
            color: #0b3d91;
            margin: 0 0 4px 0;
            letter-spacing: -0.02em;
        }
        .school-details p {
            margin: 0;
            font-size: 12px;
            color: #64748b;
            line-height: 1.4;
        }
        .receipt-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-paid {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }
        .badge-partial {
            background: #fef3c7;
            color: #b45309;
            border: 1px solid #fde68a;
        }
        .badge-pending {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fca5a5;
        }
        .meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 28px;
        }
        .meta-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
        }
        .meta-card-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #64748b;
            margin-bottom: 12px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 6px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 6px;
        }
        .meta-row:last-child { margin-bottom: 0; }
        .meta-label { color: #64748b; font-weight: 500; }
        .meta-val { font-weight: 600; color: #0f172a; text-align: right; }
        .mono { font-family: 'JetBrains Mono', monospace; font-size: 12.5px; }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        .items-table th {
            background: #f1f5f9;
            color: #334155;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            padding: 12px 16px;
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #cbd5e1;
        }
        .items-table td {
            padding: 14px 16px;
            font-size: 13.5px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }
        .summary-box {
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 20px;
            margin-left: auto;
            width: 320px;
            margin-bottom: 28px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            margin-bottom: 8px;
            color: #475569;
        }
        .summary-row.total {
            font-size: 16px;
            font-weight: 800;
            color: #0b3d91;
            border-top: 2px solid #e2e8f0;
            padding-top: 10px;
            margin-top: 10px;
            margin-bottom: 0;
        }

        .receipt-footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-top: 24px;
            border-top: 1px dashed #cbd5e1;
            font-size: 12px;
            color: #64748b;
        }
        .signature-box {
            text-align: center;
            width: 200px;
        }
        .signature-line {
            border-bottom: 1px solid #94a3b8;
            margin-bottom: 6px;
            height: 40px;
        }
        .watermark-stamp {
            position: absolute;
            right: 40px;
            bottom: 110px;
            width: 140px;
            height: 140px;
            border: 3px solid <?= ($payment['payment_status'] ?? 'Paid') === 'Paid' ? '#16a34a' : '#d97706' ?>;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transform: rotate(-15deg);
            opacity: 0.18;
            pointer-events: none;
            text-align: center;
            font-weight: 800;
            color: <?= ($payment['payment_status'] ?? 'Paid') === 'Paid' ? '#16a34a' : '#d97706' ?>;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .action-bar { display: none !important; }
            .receipt-container {
                box-shadow: none;
                border: 1px solid #cbd5e1;
                border-radius: 0;
                padding: 30px;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="action-bar">
    <a href="<?= e($backUrl ?? 'javascript:history.back()') ?>" class="btn btn-outline-secondary btn-sm px-3 fw-semibold">
        <i class="ti ti-arrow-left me-1"></i> Back
    </a>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-4 fw-bold shadow-sm" style="background:#0b3d91; border-color:#0b3d91;">
            <i class="ti ti-printer me-1"></i> Print / Download PDF
        </button>
    </div>
</div>

<div class="receipt-container">
    <!-- School Header -->
    <div class="school-header">
        <div class="d-flex align-items-center gap-3">
            <?php $logoUrl = school_logo_url(); ?>
            <?php if ($logoUrl): ?>
                <img src="<?= e($logoUrl) ?>" alt="Logo" class="school-logo">
            <?php else: ?>
                <div class="logo-placeholder"><?= strtoupper(substr(setting('school_name', 'S'), 0, 1)) ?></div>
            <?php endif; ?>
            <div class="school-details">
                <h2><?= e(setting('school_name', APP_NAME)) ?></h2>
                <p><?= e(setting('school_address', 'Excellence in Education & Character')) ?></p>
                <p>Phone: <?= e(setting('school_phone', '—')) ?> &bull; Email: <?= e(setting('school_email', '—')) ?></p>
            </div>
        </div>
        <div class="text-end">
            <div class="mb-2">
                <?php
                $status = $payment['payment_status'] ?? 'Paid';
                $badgeClass = match($status) {
                    'Paid' => 'badge-paid',
                    'Partial' => 'badge-partial',
                    default => 'badge-pending'
                };
                ?>
                <span class="receipt-badge <?= $badgeClass ?>">
                    <i class="ti ti-circle-check"></i> <?= e($status) ?>
                </span>
            </div>
            <div style="font-size:11px; color:#64748b; font-weight:600;">OFFICIAL PAYMENT RECEIPT</div>
        </div>
    </div>

    <!-- Metadata Grid -->
    <div class="meta-grid">
        <!-- Receipt Info -->
        <div class="meta-card">
            <div class="meta-card-title">Transaction Information</div>
            <div class="meta-row">
                <span class="meta-label">Receipt Number:</span>
                <span class="meta-val mono text-primary fw-bold"><?= e($payment['receipt_number'] ?: 'N/A') ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Payment Reference:</span>
                <span class="meta-val mono"><?= e($payment['payment_reference'] ?: 'N/A') ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Payment Date:</span>
                <span class="meta-val"><?= !empty($payment['payment_date']) ? date('d M Y, h:i A', strtotime($payment['payment_date'])) : date('d M Y') ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Payment Method:</span>
                <span class="meta-val"><?= e(ucwords(str_replace('_', ' ', $payment['payment_method'] ?? 'Cash'))) ?></span>
            </div>
        </div>

        <!-- Student & Parent Info -->
        <div class="meta-card">
            <div class="meta-card-title">Student & Parent Details</div>
            <div class="meta-row">
                <span class="meta-label">Student Name:</span>
                <span class="meta-val"><?= e(($payment['first_name'] ?? '') . ' ' . ($payment['last_name'] ?? '')) ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Registration / ID:</span>
                <span class="meta-val mono"><?= e($payment['admission_number'] ?: ($payment['application_number'] ?? '—')) ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Class:</span>
                <span class="meta-val"><?= e($payment['class_name'] ?? 'N/A') ?></span>
            </div>
            <div class="meta-row">
                <span class="meta-label">Parent / Sponsor:</span>
                <span class="meta-val"><?= e($payment['parent_name'] ?? ($payment['guardian_name'] ?? 'Parent / Guardian')) ?></span>
            </div>
        </div>
    </div>

    <!-- Payment Item Breakdown Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:45%;">Fee Description</th>
                <th style="width:20%; text-align:center;">Term / Session</th>
                <th style="width:15%; text-align:right;">Billed Amount</th>
                <th style="width:20%; text-align:right;">Amount Paid</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div class="fw-bold text-dark"><?= e($payment['fee_name'] ?? 'School Fee') ?></div>
                    <div class="small text-muted"><?= e($payment['class_name'] ? "Class: {$payment['class_name']}" : "All Classes") ?></div>
                </td>
                <td style="text-align:center;">
                    <span class="badge bg-light text-dark border"><?= e($payment['term'] ?? 'Current') ?> Term</span>
                    <?php if (!empty($payment['academic_year'])): ?>
                        <div class="small text-muted mt-1"><?= e($payment['academic_year']) ?></div>
                    <?php endif; ?>
                </td>
                <td style="text-align:right; font-weight:600;">
                    ₦<?= number_format((float) ($payment['fee_amount'] ?? $payment['amount_paid']), 2) ?>
                </td>
                <td style="text-align:right; font-weight:700; color:#15803d;">
                    ₦<?= number_format((float) $payment['amount_paid'], 2) ?>
                </td>
            </tr>
            <?php if (!empty($payment['notes'])): ?>
            <tr>
                <td colspan="4" style="background:#f8fafc; font-size:12px; color:#64748b;">
                    <strong>Memo / Transaction Notes:</strong> <?= nl2br(e($payment['notes'])) ?>
                </td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Financial Summary Box -->
    <div class="d-flex justify-content-end">
        <div class="summary-box">
            <div class="summary-row">
                <span>Total Fee Due:</span>
                <span class="fw-semibold">₦<?= number_format((float) ($payment['fee_amount'] ?? $payment['amount_paid']), 2) ?></span>
            </div>
            <div class="summary-row">
                <span>Total Amount Paid:</span>
                <span class="fw-bold text-success">₦<?= number_format((float) $payment['amount_paid'], 2) ?></span>
            </div>
            <div class="summary-row total">
                <span>Remaining Balance:</span>
                <span style="color:<?= (float)($payment['balance'] ?? 0) > 0 ? '#dc2626' : '#15803d' ?>;">
                    ₦<?= number_format((float) ($payment['balance'] ?? 0), 2) ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Stamp Watermark -->
    <div class="watermark-stamp">
        <div style="font-size:10px; letter-spacing:1px;"><?= strtoupper(setting('school_name', APP_NAME)) ?></div>
        <div style="font-size:16px; margin:4px 0;"><?= strtoupper($status) ?></div>
        <div style="font-size:9px;"><?= date('d/m/Y') ?></div>
    </div>

    <!-- Footer Signoff -->
    <div class="receipt-footer">
        <div>
            <div>This is a computer-generated official receipt issued by <strong><?= e(setting('school_name', APP_NAME)) ?></strong>.</div>
            <div style="font-size:11px; margin-top:2px;">Issued on <?= date('d M Y, h:i A') ?> &bull; Verification Code: <span class="mono fw-bold"><?= substr(md5(($payment['receipt_number'] ?? '').($payment['id'] ?? '')), 0, 10) ?></span></div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="fw-bold text-dark">Bursar / Accounts Office</div>
            <div style="font-size:10px;">Official Signatory</div>
        </div>
    </div>
</div>

</body>
</html>
