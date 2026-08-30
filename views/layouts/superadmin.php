<!doctype html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SST Hub — EduCore Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        :root {
            --hub-navy: #090d16;
            --hub-sidebar: #0d1321;
            --hub-card: #141d30;
            --hub-border: #1e293b;
            --hub-accent: #3b82f6;
            --hub-gold: #f59e0b;
            --hub-text: #f8fafc;
            --hub-muted: #94a3b8;
            --hub-success: #10b981;
            --hub-danger: #ef4444;
        }

        body {
            background-color: var(--hub-navy);
            color: var(--hub-text);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .hub-sidebar {
            width: 260px;
            background-color: var(--hub-sidebar);
            border-right: 1px solid var(--hub-border);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
        }

        .hub-logo {
            padding: 24px;
            font-size: 18px;
            font-weight: 800;
            color: var(--hub-text);
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--hub-border);
            background: linear-gradient(135deg, var(--hub-sidebar), #1e1b4b);
        }
        .hub-logo i {
            color: var(--hub-gold);
            font-size: 24px;
        }
        .hub-logo span {
            color: var(--hub-accent);
        }

        .hub-nav {
            padding: 20px 12px;
            flex: 1;
            overflow-y: auto;
        }

        .hub-nav-section {
            font-size: 10px;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--hub-muted);
            letter-spacing: 1px;
            margin: 16px 12px 8px;
        }

        .hub-nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            color: var(--hub-muted);
            text-decoration: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s;
            margin-bottom: 4px;
        }

        .hub-nav-item:hover {
            color: var(--hub-text);
            background-color: rgba(59, 130, 246, 0.08);
        }

        .hub-nav-item.active {
            color: var(--hub-text);
            background-color: var(--hub-accent);
        }

        .hub-nav-item i {
            font-size: 18px;
        }

        .hub-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--hub-border);
            font-size: 11px;
            color: var(--hub-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Main Shell */
        .hub-main {
            flex: 1;
            margin-left: 260px;
            padding: 40px;
            min-width: 0;
        }

        /* Card and Utility Styling */
        .hub-card {
            background-color: var(--hub-card);
            border: 1px solid var(--hub-border);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .hub-card-title {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid var(--hub-border);
            padding-bottom: 12px;
        }

        .hub-header {
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .hub-header h1 {
            font-size: 26px;
            font-weight: 800;
            margin: 0;
            background: linear-gradient(to right, var(--hub-text), var(--hub-accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hub-header p {
            color: var(--hub-muted);
            margin: 6px 0 0;
            font-size: 14px;
        }

        /* Hub Form Elements */
        .form-control, .form-select {
            background-color: var(--hub-navy);
            border: 1.5px solid var(--hub-border);
            color: var(--hub-text);
            padding: 10px 14px;
            border-radius: 8px;
        }
        .form-control:focus, .form-select:focus {
            background-color: var(--hub-navy);
            color: var(--hub-text);
            border-color: var(--hub-accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        .form-label {
            font-size: 12px;
            font-weight: 700;
            color: var(--hub-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        /* Hub Buttons */
        .btn-hub-primary {
            background-color: var(--hub-accent);
            color: var(--hub-text);
            border: none;
            padding: 10px 20px;
            font-weight: 600;
            font-size: 13px;
            border-radius: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-hub-primary:hover {
            background-color: #2563eb;
            color: var(--hub-text);
            transform: translateY(-1px);
        }

        .btn-hub-secondary {
            background-color: transparent;
            border: 1.5px solid var(--hub-border);
            color: var(--hub-text);
            padding: 9px 18px;
            font-weight: 600;
            font-size: 13px;
            border-radius: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-hub-secondary:hover {
            background-color: var(--hub-border);
            color: var(--hub-text);
        }

        /* Table Styling */
        .hub-table {
            width: 100%;
            border-collapse: collapse;
        }
        .hub-table th {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--hub-muted);
            padding: 14px 16px;
            border-bottom: 2px solid var(--hub-border);
            text-align: left;
        }
        .hub-table td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--hub-border);
            font-size: 13px;
            color: var(--hub-text);
        }
        .hub-table tr:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }

        /* Badge Styling */
        .hub-badge {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .badge-active { background-color: rgba(16, 185, 129, 0.15); color: var(--hub-success); }
        .badge-suspended { background-color: rgba(239, 68, 68, 0.15); color: var(--hub-danger); }
        .badge-trial { background-color: rgba(148, 163, 184, 0.15); color: var(--hub-muted); }
        .badge-enterprise { background-color: rgba(245, 158, 11, 0.15); color: var(--hub-gold); }
    </style>
</head>
<body>

<aside class="hub-sidebar">
    <div class="hub-logo">
        <i class="ti ti-server-cog"></i> SST Hub <span>SaaS</span>
    </div>
    <div class="hub-nav">
        <?php $current = trim($_GET['route'] ?? 'dashboard', '/'); ?>
        
        <div class="hub-nav-section">Main</div>
        <a class="hub-nav-item <?= $current === 'dashboard' ? 'active' : '' ?>" href="<?= url('superadmin/dashboard') ?>">
            <i class="ti ti-layout-dashboard"></i> Dashboard
        </a>
        <a class="hub-nav-item <?= str_starts_with($current, 'schools') ? 'active' : '' ?>" href="<?= url('superadmin/schools') ?>">
            <i class="ti ti-school"></i> Schools Registry
        </a>

        <div class="hub-nav-section">Operations</div>
        <a class="hub-nav-item <?= $current === 'licenses' ? 'active' : '' ?>" href="<?= url('superadmin/licenses') ?>">
            <i class="ti ti-key"></i> License Keys
        </a>
        <a class="hub-nav-item <?= $current === 'subscriptions' ? 'active' : '' ?>" href="<?= url('superadmin/subscriptions') ?>">
            <i class="ti ti-credit-card"></i> Invoices & Billing
        </a>
        <a class="hub-nav-item <?= $current === 'payment-settings' ? 'active' : '' ?>" href="<?= url('superadmin/payment-settings') ?>">
            <i class="ti ti-adjustments-dollar"></i> Payment Settings
        </a>
        <a class="hub-nav-item <?= $current === 'marketplace' ? 'active' : '' ?>" href="<?= url('superadmin/marketplace') ?>">
            <i class="ti ti-shopping-cart"></i> Add-on Orders
        </a>
        <a class="hub-nav-item <?= $current === 'coupons' ? 'active' : '' ?>" href="<?= url('superadmin/coupons') ?>">
            <i class="ti ti-tag"></i> Coupons & Promos
        </a>
        <a class="hub-nav-item <?= $current === 'sms-usage' ? 'active' : '' ?>" href="<?= url('superadmin/sms-usage') ?>">
            <i class="ti ti-message-dots"></i> SMS Credits & Costs
        </a>
        <a class="hub-nav-item <?= $current === 'announcements' ? 'active' : '' ?>" href="<?= url('superadmin/announcements') ?>">
            <i class="ti ti-speakerphone"></i> Announcements
        </a>
        <a class="hub-nav-item <?= $current === 'tickets' ? 'active' : '' ?>" href="<?= url('superadmin/tickets') ?>">
            <i class="ti ti-help-circle"></i> Support Tickets
        </a>

        <div class="hub-nav-section">Infrastructure</div>
        <a class="hub-nav-item <?= str_starts_with($current, 'releases') ? 'active' : '' ?>" href="<?= url('superadmin/releases') ?>">
            <i class="ti ti-cloud-upload"></i> Software Releases
        </a>
        <a class="hub-nav-item <?= $current === 'backups' ? 'active' : '' ?>" href="<?= url('superadmin/backups') ?>">
            <i class="ti ti-database-export"></i> Backup Center
        </a>
        <a class="hub-nav-item <?= $current === 'api-keys' ? 'active' : '' ?>" href="<?= url('superadmin/api-keys') ?>">
            <i class="ti ti-api"></i> API Key Manager
        </a>
        <a class="hub-nav-item <?= $current === 'reports' ? 'active' : '' ?>" href="<?= url('superadmin/reports') ?>">
            <i class="ti ti-file-analytics"></i> Platform Reports
        </a>
        <a class="hub-nav-item <?= $current === 'audit-log' ? 'active' : '' ?>" href="<?= url('superadmin/audit-log') ?>">
            <i class="ti ti-shield-alert"></i> Audit Trails
        </a>
        <a class="hub-nav-item <?= $current === 'security' ? 'active' : '' ?>" href="<?= url('superadmin/security') ?>">
            <i class="ti ti-lock"></i> Security Center
        </a>

        <div class="hub-nav-section">Customer Portal</div>
        <a class="hub-nav-item <?= $current === 'customers' ? 'active' : '' ?>" href="<?= url('superadmin/customers') ?>">
            <i class="ti ti-user-circle"></i> Customer Accounts
        </a>
        <a class="hub-nav-item <?= $current === 'marketplace-products' ? 'active' : '' ?>" href="<?= url('superadmin/marketplace-products') ?>">
            <i class="ti ti-shopping-bag"></i> Marketplace Products
        </a>

        <div class="hub-nav-section">Business Center</div>
        <a class="hub-nav-item <?= str_starts_with($current, 'business-settings') ? 'active' : '' ?>" href="<?= url('superadmin/business-settings') ?>">
            <i class="ti ti-building-cog"></i> Business Settings
        </a>
        <a class="hub-nav-item <?= $current === 'settings' ? 'active' : '' ?>" href="<?= url('superadmin/settings') ?>">
            <i class="ti ti-settings"></i> Gateway Settings
        </a>
    </div>
    
    <div class="hub-footer">
        <span>SST Hub v2.0.0</span>
        <a href="<?= url('superadmin/logout') ?>" style="color:var(--hub-danger);text-decoration:none;"><i class="ti ti-logout"></i> Exit</a>
    </div>
</aside>

<main class="hub-main">
    <?php foreach (flashes() as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert" style="background-color: var(--hub-card); border-color: var(--hub-border); color: var(--hub-text);">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endforeach; ?>

    <?= $content ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
