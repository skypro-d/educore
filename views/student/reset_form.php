<div class="auth-card">
    <div class="logo-area">
        <?php $logoUrl = school_logo_url(); ?>
        <?php if ($logoUrl): ?>
            <img src="<?= e($logoUrl) ?>" alt="Logo" style="width:64px;height:64px;border-radius:16px;object-fit:contain;background:#fff;padding:4px;margin-bottom:15px;box-shadow:0 10px 20px rgba(0,0,0,0.2);" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
            <div class="logo-circle" style="display:none;"><?= strtoupper(substr(setting('school_name', 'S'), 0, 1)) ?></div>
        <?php else: ?>
            <div class="logo-circle"><?= strtoupper(substr(setting('school_name', 'S'), 0, 1)) ?></div>
        <?php endif; ?>
        <h1>Set New Password</h1>
        <p class="subtitle">Account: <strong class="text-warning"><?= e($account['username']) ?></strong></p>
    </div>

    <form method="POST" action="<?= url('student/reset') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        
        <div class="mb-3">
            <label class="form-label" for="password">New Password</label>
            <input class="form-control" type="password" name="password" id="password" required minlength="6" placeholder="At least 6 characters" autofocus>
        </div>

        <div class="mb-4">
            <label class="form-label" for="password_confirmation">Confirm New Password</label>
            <input class="form-control" type="password" name="password_confirmation" id="password_confirmation" required minlength="6" placeholder="Repeat new password">
        </div>
        
        <button type="submit" class="btn-auth">Update Password</button>
    </form>
    
    <div class="auth-footer">
        <p><a href="<?= url('student/login') ?>">&larr; Back to Student Login</a></p>
        <p style="margin-top:10px;font-size:11px;">&copy; <?= date('Y') ?> <?= e(setting('school_name', APP_NAME)) ?>. All rights reserved.</p>
    </div>
</div>
