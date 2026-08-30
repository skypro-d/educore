<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register School — EduCore SaaS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        :root {
            --bg-dark: #070b13;
            --card-dark: #0f172a;
            --border-dark: #1e293b;
            --accent-blue: #3b82f6;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-light);
            font-family: 'Segoe UI', system-ui, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 40px 20px;
        }

        .register-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 640px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .form-control, .form-select {
            background-color: var(--bg-dark);
            border: 1.5px solid var(--border-dark);
            color: var(--text-light);
            padding: 10px 14px;
            border-radius: 8px;
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--bg-dark);
            color: var(--text-light);
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .form-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .btn-sst-primary {
            background-color: var(--accent-blue);
            color: var(--text-light);
            border: none;
            padding: 12px;
            font-weight: 700;
            border-radius: 8px;
            width: 100%;
            transition: all 0.2s;
        }

        .btn-sst-primary:hover {
            background-color: #2563eb;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="register-card">
    <div style="text-align:center; margin-bottom:30px;">
        <h3 class="fw-bold"><i class="ti ti-school text-warning me-1"></i> Register Tenant School</h3>
        <p class="text-muted" style="font-size:13px;">Create your school's EduCore SaaS instance instantly</p>
    </div>

    <?php 
        $chosenPlan = $_GET['plan'] ?? 'professional';
        $plans = ['basic' => 'Basic Plan (NGN 95,000/yr)', 'professional' => 'Professional Plan (NGN 180,000/yr)', 'enterprise' => 'Enterprise Plan (NGN 350,000/yr)', 'self_hosted' => 'Self-Hosted Setup Key (NGN 500,000)'];
    ?>

    <form method="POST" action="<?= url('register-school') ?>">
        <?= csrf_field() ?>

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label">School Name <span class="text-danger">*</span></label>
                <input type="text" name="school_name" required class="form-control" placeholder="e.g. Bluefield International College">
            </div>
            
            <div class="col-md-4">
                <label class="form-label">School Code <span class="text-danger">*</span></label>
                <input type="text" name="school_code" required class="form-control" placeholder="e.g. BIC" style="text-transform:uppercase;">
            </div>

            <div class="col-md-12">
                <label class="form-label">Portal Domain / Subdomain <span class="text-danger">*</span></label>
                <input type="text" name="domain" required class="form-control" placeholder="e.g. bluefield.educore.com or school.domain.com" style="text-transform:lowercase;">
                <small class="text-muted" style="font-size:11px; text-transform:none;">The host/domain name used to access your school portal.</small>
            </div>

            <div class="col-md-6">
                <label class="form-label">Principal / Director Name</label>
                <input type="text" name="principal_name" class="form-control" placeholder="e.g. Dr. John Doe">
            </div>

            <div class="col-md-6">
                <label class="form-label">Pricing Plan <span class="text-danger">*</span></label>
                <select name="plan" class="form-select" required>
                    <?php foreach ($plans as $key => $lbl): ?>
                        <option value="<?= $key ?>" <?= $key === $chosenPlan ? 'selected' : '' ?>><?= e($lbl) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Administrator Email <span class="text-danger">*</span></label>
                <input type="email" name="email" required class="form-control" placeholder="admin@school.com">
            </div>

            <div class="col-md-6">
                <label class="form-label">Contact Phone <span class="text-danger">*</span></label>
                <input type="text" name="phone" required class="form-control" placeholder="+234 800 000 0000">
            </div>

            <div class="col-md-6">
                <label class="form-label">Portal Password <span class="text-danger">*</span></label>
                <input type="password" name="password" required class="form-control" placeholder="••••••••">
            </div>

            <div class="col-md-6">
                <label class="form-label">Promo / Coupon Code</label>
                <input type="text" name="coupon" class="form-control" placeholder="e.g. SSTLAUNCH50" style="text-transform:uppercase;">
            </div>

            <div class="col-12">
                <label class="form-label">School Address</label>
                <textarea name="address" class="form-control" rows="2" placeholder="e.g. No. 1 Excellence Drive, Lagos"></textarea>
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn-sst-primary">
                <i class="ti ti-credit-card me-1"></i> Proceed to Paystack Verification
            </button>
        </div>
        
        <div class="text-center mt-3" style="font-size:12px;">
            <a href="<?= url('') ?>" class="text-muted text-decoration-none"><i class="ti ti-arrow-left"></i> Cancel & Return Home</a>
        </div>
    </form>
</div>

</body>
</html>
