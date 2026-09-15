<div class="auth-card">
    <div class="logo-area">
        <?php $logoUrl = school_logo_url(); ?>
        <?php if ($logoUrl): ?>
            <img src="<?= e($logoUrl) ?>" alt="Logo" style="width:64px;height:64px;border-radius:16px;object-fit:contain;background:#fff;padding:4px;margin-bottom:15px;box-shadow:0 10px 20px rgba(0,0,0,0.2);" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
            <div class="logo-circle" style="display:none;"><?= strtoupper(substr(setting('school_name', 'S'), 0, 1)) ?></div>
        <?php else: ?>
            <div class="logo-circle"><?= strtoupper(substr(setting('school_name', 'S'), 0, 1)) ?></div>
        <?php endif; ?>
        <h1>Forgot Password</h1>
        <p class="subtitle">Reset your Student Portal access</p>
    </div>

    <p style="font-size:13px;color:#94a3b8;text-align:center;margin-bottom:24px;">
        Enter your Student Username, Admission Number, or registered Parent Email to receive reset instructions.
    </p>

    <form method="POST" action="<?= url('student/reset-request') ?>">
        <?= csrf_field() ?>
        
        <div class="mb-4">
            <label class="form-label" for="identifier">Username / Admission No / Parent Email</label>
            <input class="form-control" type="text" name="identifier" id="identifier" required placeholder="e.g. SCH20260001 or email@domain.com" autofocus>
        </div>
        
        <button type="submit" class="btn-auth">Send Reset Link</button>
    </form>
    
    <div class="auth-footer">
        <p><a href="<?= url('student/login') ?>">&larr; Back to Student Login</a></p>
        <p style="margin-top:10px;font-size:11px;">&copy; <?= date('Y') ?> <?= e(setting('school_name', APP_NAME)) ?>. All rights reserved.</p>
    </div>
</div>
