<!doctype html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>EduCore Customer Portal — <?= e(platform_setting('company_name', 'SkySavingTech Hub')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        :root {
            --cp-bg:        #07071a;
            --cp-sidebar:   #0d0d2b;
            --cp-card:      #12122e;
            --cp-border:    #1e1e4a;
            --cp-accent:    #7c3aed;
            --cp-accent2:   #a78bfa;
            --cp-gold:      #f59e0b;
            --cp-success:   #10b981;
            --cp-danger:    #ef4444;
            --cp-text:      #f1f0ff;
            --cp-muted:     #8b8aad;
            --cp-glow:      rgba(124, 58, 237, 0.18);
        }

        * { box-sizing: border-box; }

        body {
            background-color: var(--cp-bg);
            color: var(--cp-text);
            font-family: 'Inter', system-ui, sans-serif;
            margin: 0; padding: 0;
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .cp-sidebar {
            width: 270px;
            background: linear-gradient(180deg, var(--cp-sidebar) 0%, #0a0a20 100%);
            border-right: 1px solid var(--cp-border);
            display: flex; flex-direction: column;
            position: fixed; top: 0; bottom: 0; left: 0;
            z-index: 100;
            overflow-y: auto;
        }

        .cp-logo {
            padding: 22px 24px;
            display: flex; align-items: center; gap: 12px;
            border-bottom: 1px solid var(--cp-border);
            background: linear-gradient(135deg, rgba(124,58,237,.15), transparent);
        }
        .cp-logo-icon {
            width: 40px; height: 40px; border-radius: 10px;
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; color: white;
            box-shadow: 0 0 20px rgba(124,58,237,.4);
        }
        .cp-logo-text { flex: 1; }
        .cp-logo-title { font-size: 14px; font-weight: 800; color: var(--cp-text); line-height: 1.2; }
        .cp-logo-sub { font-size: 10px; color: var(--cp-muted); font-weight: 500; }

        /* Plan badge in sidebar */
        .cp-plan-badge {
            margin: 16px 16px 0;
            padding: 12px 16px;
            background: linear-gradient(135deg, rgba(124,58,237,.2), rgba(167,139,250,.08));
            border: 1px solid rgba(124,58,237,.3);
            border-radius: 10px;
        }
        .cp-plan-badge .plan-label { font-size: 9px; font-weight: 700; color: var(--cp-muted); text-transform: uppercase; letter-spacing: 1px; }
        .cp-plan-badge .plan-name  { font-size: 15px; font-weight: 800; color: var(--cp-accent2); margin: 2px 0; }
        .cp-plan-badge .plan-days  { font-size: 11px; color: var(--cp-muted); }
        .cp-plan-badge .plan-days span { color: var(--cp-gold); font-weight: 700; }

        .cp-nav { padding: 16px 12px; flex: 1; }
        .cp-nav-section {
            font-size: 9px; font-weight: 800; text-transform: uppercase;
            color: var(--cp-muted); letter-spacing: 1.5px;
            margin: 16px 12px 8px;
        }
        .cp-nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px;
            color: var(--cp-muted);
            text-decoration: none;
            border-radius: 8px;
            font-size: 13px; font-weight: 600;
            transition: all 0.2s;
            margin-bottom: 2px;
        }
        .cp-nav-item i { font-size: 17px; }
        .cp-nav-item:hover {
            color: var(--cp-text);
            background: rgba(124,58,237,.1);
        }
        .cp-nav-item.active {
            color: var(--cp-text);
            background: linear-gradient(135deg, rgba(124,58,237,.3), rgba(124,58,237,.1));
            border-left: 2px solid var(--cp-accent);
        }
        .cp-nav-item.active i { color: var(--cp-accent2); }
        .cp-nav-badge {
            margin-left: auto;
            background: var(--cp-danger);
            color: white; font-size: 9px; font-weight: 800;
            padding: 2px 6px; border-radius: 20px;
        }

        .cp-sidebar-footer {
            padding: 16px 20px;
            border-top: 1px solid var(--cp-border);
            font-size: 11px; color: var(--cp-muted);
        }
        .cp-sidebar-footer a { color: var(--cp-danger); text-decoration: none; }

        /* ── Main ── */
        .cp-main {
            flex: 1;
            margin-left: 270px;
            padding: 36px 40px;
            min-width: 0;
        }

        /* ── Header ── */
        .cp-header {
            margin-bottom: 32px;
            display: flex; justify-content: space-between; align-items: flex-start;
        }
        .cp-header h1 {
            font-size: 24px; font-weight: 800; margin: 0;
            background: linear-gradient(135deg, var(--cp-text) 30%, var(--cp-accent2));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .cp-header p { color: var(--cp-muted); margin: 6px 0 0; font-size: 13px; }

        /* ── Cards ── */
        .cp-card {
            background: var(--cp-card);
            border: 1px solid var(--cp-border);
            border-radius: 14px;
            padding: 24px;
            margin-bottom: 24px;
        }
        .cp-card-glow {
            box-shadow: 0 0 40px var(--cp-glow);
        }
        .cp-card-title {
            font-size: 13px; font-weight: 700;
            color: var(--cp-muted); text-transform: uppercase;
            letter-spacing: 0.5px; margin-bottom: 18px;
            display: flex; align-items: center; gap: 8px;
            padding-bottom: 12px; border-bottom: 1px solid var(--cp-border);
        }
        .cp-card-title i { color: var(--cp-accent2); font-size: 16px; }

        /* ── Stat Cards ── */
        .cp-stat {
            background: var(--cp-card);
            border: 1px solid var(--cp-border);
            border-radius: 14px; padding: 20px;
            display: flex; align-items: center; gap: 18px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .cp-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(0,0,0,.3);
        }
        .cp-stat-icon {
            width: 52px; height: 52px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; flex-shrink: 0;
        }
        .cp-stat-label { font-size: 10px; font-weight: 700; color: var(--cp-muted); text-transform: uppercase; letter-spacing: 0.5px; }
        .cp-stat-value { font-size: 22px; font-weight: 800; margin: 4px 0 2px; }
        .cp-stat-sub   { font-size: 11px; color: var(--cp-muted); }

        /* ── Buttons ── */
        .btn-cp-primary {
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: white; border: none;
            padding: 10px 22px; font-weight: 700; font-size: 13px;
            border-radius: 8px; cursor: pointer;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
            transition: all 0.2s;
            box-shadow: 0 4px 15px rgba(124,58,237,.3);
        }
        .btn-cp-primary:hover {
            background: linear-gradient(135deg, #6d28d9, #5b21b6);
            color: white; transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(124,58,237,.4);
        }
        .btn-cp-secondary {
            background: transparent;
            border: 1.5px solid var(--cp-border); color: var(--cp-text);
            padding: 9px 20px; font-weight: 600; font-size: 13px;
            border-radius: 8px; cursor: pointer;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
            transition: all 0.2s;
        }
        .btn-cp-secondary:hover { background: rgba(255,255,255,.05); color: var(--cp-text); }

        .btn-cp-success {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white; border: none;
            padding: 10px 22px; font-weight: 700; font-size: 13px;
            border-radius: 8px; cursor: pointer;
            text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
            transition: all 0.2s;
        }
        .btn-cp-success:hover { background: linear-gradient(135deg, #047857, #059669); color: white; transform: translateY(-1px); }

        .btn-cp-sm { padding: 6px 14px; font-size: 12px; }

        /* ── Form Inputs ── */
        .form-control, .form-select {
            background-color: rgba(255,255,255,.04);
            border: 1.5px solid var(--cp-border);
            color: var(--cp-text); border-radius: 8px;
            padding: 10px 14px; font-size: 13px;
        }
        .form-control:focus, .form-select:focus {
            background-color: rgba(124,58,237,.08);
            border-color: var(--cp-accent); color: var(--cp-text);
            box-shadow: 0 0 0 3px rgba(124,58,237,.2);
        }
        .form-label {
            font-size: 11px; font-weight: 700; color: var(--cp-muted);
            text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;
        }

        /* ── Table ── */
        .cp-table { width: 100%; border-collapse: collapse; }
        .cp-table th {
            font-size: 10px; font-weight: 800; text-transform: uppercase;
            color: var(--cp-muted); padding: 12px 16px;
            border-bottom: 2px solid var(--cp-border); text-align: left;
        }
        .cp-table td {
            padding: 14px 16px; border-bottom: 1px solid rgba(30,30,74,.5);
            font-size: 13px; vertical-align: middle;
        }
        .cp-table tr:hover { background: rgba(124,58,237,.04); }

        /* ── Badges ── */
        .cp-badge {
            padding: 4px 10px; border-radius: 20px;
            font-size: 10px; font-weight: 800; text-transform: uppercase;
        }
        .badge-active    { background: rgba(16,185,129,.15); color: #34d399; }
        .badge-trial     { background: rgba(245,158,11,.12); color: var(--cp-gold); }
        .badge-suspended { background: rgba(239,68,68,.12);  color: #f87171; }
        .badge-paid      { background: rgba(16,185,129,.15); color: #34d399; }
        .badge-pending   { background: rgba(245,158,11,.12); color: var(--cp-gold); }
        .badge-open      { background: rgba(96,165,250,.12); color: #93c5fd; }
        .badge-closed    { background: rgba(100,116,139,.1); color: var(--cp-muted); }
        .badge-enterprise { background: rgba(167,139,250,.12); color: var(--cp-accent2); }

        /* ── Key display ── */
        .key-display {
            font-family: 'Courier New', monospace;
            background: rgba(0,0,0,.4);
            border: 1px solid var(--cp-border);
            border-radius: 8px; padding: 12px 16px;
            font-size: 13px; letter-spacing: 1px;
            display: flex; align-items: center; justify-content: space-between;
            gap: 12px; word-break: break-all;
        }
        .key-copy-btn {
            background: rgba(124,58,237,.2); border: 1px solid rgba(124,58,237,.3);
            color: var(--cp-accent2); border-radius: 6px; padding: 4px 10px;
            font-size: 11px; cursor: pointer; white-space: nowrap; flex-shrink: 0;
            transition: background 0.2s;
        }
        .key-copy-btn:hover { background: rgba(124,58,237,.4); }

        /* ── Flash alerts ── */
        .alert { border-radius: 10px; padding: 14px 18px; font-size: 13px; font-weight: 600; border: 1px solid; }
        .alert-success { background: rgba(16,185,129,.1); border-color: rgba(16,185,129,.3); color: #34d399; }
        .alert-danger  { background: rgba(239,68,68,.1);  border-color: rgba(239,68,68,.3);  color: #f87171; }
        .alert-warning { background: rgba(245,158,11,.1); border-color: rgba(245,158,11,.3); color: var(--cp-gold); }
        .alert-info    { background: rgba(96,165,250,.1); border-color: rgba(96,165,250,.3); color: #93c5fd; }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .cp-sidebar { width: 220px; }
            .cp-main    { margin-left: 220px; padding: 24px; }
        }
    </style>
</head>
<body>

<aside class="cp-sidebar">
    <div class="cp-logo">
        <div class="cp-logo-icon"><i class="ti ti-school"></i></div>
        <div class="cp-logo-text">
            <div class="cp-logo-title"><?= e(platform_setting('company_name', 'EduCore')) ?></div>
            <div class="cp-logo-sub">Customer Portal</div>
        </div>
    </div>

    <?php
    $c       = customer();
    $route   = trim(preg_replace('#^portal/?#', '', $_GET['route'] ?? 'dashboard'), '/');
    $daysStr = '';
    if ($c && !empty($c['expires_at'])) {
        $days    = (int) (new DateTime('today'))->diff(new DateTime($c['expires_at']))->format('%r%a');
        $daysStr = $days >= 0 ? "{$days} days left" : 'EXPIRED';
    }
    ?>

    <?php if ($c): ?>
    <div class="cp-plan-badge">
        <div class="plan-label">Current Plan</div>
        <div class="plan-name"><?= e(ucfirst(str_replace('_', ' ', $c['plan'] ?? 'Trial'))) ?></div>
        <div class="plan-days"><?= $daysStr ? "<span>{$daysStr}</span>" : '<span style="color:var(--cp-muted)">No expiry set</span>' ?></div>
    </div>
    <?php endif; ?>

    <nav class="cp-nav">
        <div class="cp-nav-section">Overview</div>
        <a class="cp-nav-item <?= $route === 'dashboard' || $route === '' ? 'active' : '' ?>" href="<?= url('portal/dashboard') ?>">
            <i class="ti ti-layout-dashboard"></i> Dashboard
        </a>
        <a class="cp-nav-item <?= $route === 'subscription' ? 'active' : '' ?>" href="<?= url('portal/subscription') ?>">
            <i class="ti ti-credit-card"></i> Subscription
        </a>

        <div class="cp-nav-section">License &amp; Access</div>
        <a class="cp-nav-item <?= $route === 'licenses' ? 'active' : '' ?>" href="<?= url('portal/licenses') ?>">
            <i class="ti ti-key"></i> License &amp; API Keys
        </a>
        <a class="cp-nav-item <?= $route === 'downloads' ? 'active' : '' ?>" href="<?= url('portal/downloads') ?>">
            <i class="ti ti-cloud-download"></i> Downloads
        </a>
        <a class="cp-nav-item <?= $route === 'domains' ? 'active' : '' ?>" href="<?= url('portal/domains') ?>">
            <i class="ti ti-world"></i> Domain Management
        </a>

        <div class="cp-nav-section">Billing</div>
        <a class="cp-nav-item <?= $route === 'invoices' ? 'active' : '' ?>" href="<?= url('portal/invoices') ?>">
            <i class="ti ti-receipt"></i> Invoices &amp; Receipts
        </a>

        <div class="cp-nav-section">Add-ons</div>
        <a class="cp-nav-item <?= $route === 'sms-credits' ? 'active' : '' ?>" href="<?= url('portal/sms-credits') ?>">
            <i class="ti ti-message-dots"></i> SMS Credits
        </a>
        <a class="cp-nav-item <?= $route === 'marketplace' ? 'active' : '' ?>" href="<?= url('portal/marketplace') ?>">
            <i class="ti ti-shopping-cart"></i> Marketplace
        </a>

        <div class="cp-nav-section">Help</div>
        <a class="cp-nav-item <?= str_starts_with($route, 'support') ? 'active' : '' ?>" href="<?= url('portal/support') ?>">
            <i class="ti ti-help-circle"></i> Support Tickets
        </a>
        <a class="cp-nav-item <?= $route === 'profile' ? 'active' : '' ?>" href="<?= url('portal/profile') ?>">
            <i class="ti ti-user-circle"></i> My Profile
        </a>
    </nav>

    <div class="cp-sidebar-footer">
        <div style="margin-bottom:8px;">
            <?php if ($c): ?>
            <strong style="color:var(--cp-text);"><?= e($c['name']) ?></strong><br>
            <span><?= e($c['email']) ?></span>
            <?php endif; ?>
        </div>
        <a href="<?= url('portal/logout') ?>"><i class="ti ti-logout"></i> Sign Out</a>
    </div>
</aside>

<main class="cp-main">
    <?php foreach (flashes() as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> mb-3 d-flex align-items-center gap-2" role="alert">
            <i class="ti ti-<?= $flash['type'] === 'success' ? 'circle-check' : 'alert-circle' ?>"></i>
            <?= e($flash['message']) ?>
        </div>
    <?php endforeach; ?>

    <?= $content ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const orig = btn.textContent;
        btn.textContent = '✓ Copied';
        btn.style.background = 'rgba(16,185,129,.3)';
        setTimeout(() => { btn.textContent = orig; btn.style.background = ''; }, 2000);
    });
}
</script>
</body>
</html>
