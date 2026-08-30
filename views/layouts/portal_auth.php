<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Portal — <?= e(platform_setting('company_name', 'EduCore')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: #07071a;
            display: flex; align-items: center; justify-content: center;
            position: relative; overflow: hidden;
        }
        /* Animated background orbs */
        body::before {
            content: '';
            position: fixed; top: -200px; left: -200px;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(124,58,237,.2) 0%, transparent 70%);
            border-radius: 50%;
            animation: pulse 8s ease-in-out infinite;
        }
        body::after {
            content: '';
            position: fixed; bottom: -200px; right: -200px;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(59,130,246,.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: pulse 10s ease-in-out infinite reverse;
        }
        @keyframes pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.1)} }

        .auth-wrap {
            position: relative; z-index: 10;
            width: 100%; max-width: 440px; padding: 24px;
        }
        .auth-card {
            background: rgba(13,13,43,.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(124,58,237,.2);
            border-radius: 20px; padding: 40px;
            box-shadow: 0 20px 80px rgba(0,0,0,.6), 0 0 40px rgba(124,58,237,.1);
        }
        .auth-logo {
            text-align: center; margin-bottom: 32px;
        }
        .auth-logo-icon {
            width: 60px; height: 60px; border-radius: 16px;
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 28px; color: white; margin-bottom: 14px;
            box-shadow: 0 0 30px rgba(124,58,237,.5);
        }
        .auth-logo h1 { font-size: 22px; font-weight: 800; color: #f1f0ff; margin: 0 0 4px; }
        .auth-logo p  { font-size: 13px; color: #8b8aad; margin: 0; }

        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block; font-size: 11px; font-weight: 700;
            color: #8b8aad; text-transform: uppercase; letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .form-input {
            width: 100%; background: rgba(255,255,255,.04);
            border: 1.5px solid #1e1e4a; color: #f1f0ff;
            border-radius: 10px; padding: 12px 14px; font-size: 14px;
            font-family: 'Inter', sans-serif; outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-input:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 3px rgba(124,58,237,.2);
        }
        .input-icon-wrap { position: relative; }
        .input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #8b8aad; font-size: 16px; }
        .form-input-icon { padding-left: 42px; }

        .btn-login {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
            color: white; border: none; border-radius: 10px;
            font-size: 14px; font-weight: 700; cursor: pointer;
            font-family: 'Inter', sans-serif;
            box-shadow: 0 4px 20px rgba(124,58,237,.4);
            transition: all 0.2s; margin-top: 8px;
        }
        .btn-login:hover { background: linear-gradient(135deg, #6d28d9, #5b21b6); transform: translateY(-1px); }

        .alert {
            padding: 12px 16px; border-radius: 10px;
            font-size: 13px; font-weight: 600;
            margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
        }
        .alert-danger { background: rgba(239,68,68,.12); border: 1px solid rgba(239,68,68,.3); color: #f87171; }

        .auth-footer { text-align: center; margin-top: 24px; font-size: 12px; color: #8b8aad; }
        .auth-footer a { color: #a78bfa; text-decoration: none; }

        input[type="hidden"] { display: none; }
    </style>
</head>
<body>
<div class="auth-wrap">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="auth-logo-icon"><i class="ti ti-school"></i></div>
            <h1>Customer Portal</h1>
            <p><?= e(platform_setting('company_name', 'EduCore by SkySavingTech Hub')) ?></p>
        </div>

        <?php foreach (flashes() as $flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <i class="ti ti-alert-circle"></i> <?= e($flash['message']) ?>
            </div>
        <?php endforeach; ?>

        <?= $content ?>
    </div>

    <div class="auth-footer">
        <a href="<?= url('') ?>">← Back to main website</a>
        &nbsp;·&nbsp;
        <a href="mailto:<?= e(platform_setting('support_email', 'support@skysavingtech.com.ng')) ?>">Contact Support</a>
    </div>
</div>
</body>
</html>
