<!doctype html>
<html lang="en" style="<?= e(brand_css()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Portal — <?= e(setting('school_name', APP_NAME)) ?></title>
    <?php if (setting('favicon')): ?><link rel="icon" href="<?= url('uploads/' . setting('favicon')) ?>"><?php endif; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .auth-container {
            width: 100%;
            max-width: 440px;
        }
        .auth-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            color: #fff;
        }
        .auth-card .logo-area {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-card .logo-area .logo-circle {
            width: 64px;
            height: 64px;
            border-radius: 16px;
            background: var(--brand-secondary, #f4b942);
            color: #1e293b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .auth-card h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
            text-align: center;
            color: #fff;
        }
        .auth-card p.subtitle {
            font-size: 13.5px;
            color: rgba(255,255,255,0.6);
            text-align: center;
            margin-bottom: 30px;
        }
        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: rgba(255,255,255,0.8);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #fff;
            padding: 12px 16px;
            font-size: 14.5px;
            border-radius: 8px;
            transition: all 0.2s;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: var(--brand-secondary, #f4b942);
            box-shadow: 0 0 0 4px rgba(244, 185, 66, 0.15);
            color: #fff;
        }
        .btn-auth {
            background: var(--brand-secondary, #f4b942);
            color: #0f172a;
            font-weight: 700;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            width: 100%;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(244, 185, 66, 0.2);
        }
        .btn-auth:hover {
            background: #fff;
            color: #0f172a;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(255, 255, 255, 0.25);
        }
        .auth-footer {
            margin-top: 25px;
            text-align: center;
            font-size: 13px;
            color: rgba(255,255,255,0.45);
        }
        .auth-footer a {
            color: var(--brand-secondary, #f4b942);
            text-decoration: none;
            font-weight: 600;
        }
        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="auth-container">
    <?php foreach (flashes() as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> mb-3" style="border-radius:8px;">
            <?= e($flash['message']) ?>
        </div>
    <?php endforeach; ?>

    <?= $content ?>
</div>

</body>
</html>
