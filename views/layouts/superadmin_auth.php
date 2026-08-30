<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SST Hub Login — EduCore Super Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        :root {
            --hub-navy: #090d16;
            --hub-sidebar: #0d1321;
            --hub-card: #141d30;
            --hub-border: #1e293b;
            --hub-accent: #3b82f6;
            --hub-text: #f8fafc;
            --hub-muted: #94a3b8;
        }

        body {
            background-color: var(--hub-navy);
            color: var(--hub-text);
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .auth-card {
            background-color: var(--hub-card);
            border: 1px solid var(--hub-border);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .auth-logo {
            text-align: center;
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 30px;
        }
        .auth-logo i {
            color: #f59e0b;
            font-size: 32px;
            vertical-align: middle;
            margin-right: 8px;
        }
        .auth-logo span {
            color: var(--hub-accent);
        }

        .form-control {
            background-color: var(--hub-navy);
            border: 1.5px solid var(--hub-border);
            color: var(--hub-text);
            padding: 12px;
            border-radius: 8px;
        }
        .form-control:focus {
            background-color: var(--hub-navy);
            color: var(--hub-text);
            border-color: var(--hub-accent);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .btn-hub-primary {
            background-color: var(--hub-accent);
            color: var(--hub-text);
            border: none;
            padding: 12px;
            font-weight: 700;
            border-radius: 8px;
            width: 100%;
            transition: all 0.2s;
        }
        .btn-hub-primary:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="auth-logo">
        <i class="ti ti-server-cog"></i> SST Hub <span>SaaS</span>
    </div>
    
    <?php foreach (flashes() as $flash): ?>
        <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); border-color: var(--hub-border); color: #f87171; font-size: 13px;">
            <?= e($flash['message']) ?>
        </div>
    <?php endforeach; ?>

    <?= $content ?>
</div>

</body>
</html>
