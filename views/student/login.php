<div class="auth-card">
    <div class="logo-area">
        <?php $logoUrl = school_logo_url(); ?>
        <?php if ($logoUrl): ?>
            <img src="<?= e($logoUrl) ?>" alt="Logo" style="width:64px;height:64px;border-radius:16px;object-fit:contain;background:#fff;padding:4px;margin-bottom:15px;box-shadow:0 10px 20px rgba(0,0,0,0.2);" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
            <div class="logo-circle" style="display:none;"><?= strtoupper(substr(setting('school_name', 'S'), 0, 1)) ?></div>
        <?php else: ?>
            <div class="logo-circle"><?= strtoupper(substr(setting('school_name', 'S'), 0, 1)) ?></div>
        <?php endif; ?>
        <h1><?= e(setting('school_name', APP_NAME)) ?></h1>
        <p class="subtitle">Student Portal Login</p>
    </div>

    <form method="POST" action="<?= url('student/login') ?>">
        <?= csrf_field() ?>
        
        <div class="mb-3">
            <label class="form-label" for="username">Student Username / Admission ID</label>
            <input class="form-control" type="text" name="username" id="username" required placeholder="e.g. SCH20260001" autofocus>
        </div>
        
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <label class="form-label mb-0" for="password">Password</label>
                <a href="<?= url('student/reset-request') ?>" style="font-size:12px;color:#0b3d91;text-decoration:none;font-weight:600;">Forgot password?</a>
            </div>
            <input class="form-control mt-1" type="password" name="password" id="password" required placeholder="••••••••">
        </div>
        
        <button type="submit" class="btn-auth">Access Portal</button>
    </form>
    
    <div class="auth-footer">
        <p class="mb-1"><a href="<?= url('student/reset-request') ?>" style="color:#64748b;text-decoration:none;">Forgot your student password?</a></p>
        <p>Parent? <a href="<?= url('parent/login') ?>">Log in here</a></p>
        <p style="margin-top:10px;font-size:11px;">&copy; <?= date('Y') ?> <?= e(setting('school_name', APP_NAME)) ?>. All rights reserved.</p>
    </div>
</div>
