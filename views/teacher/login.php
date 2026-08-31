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
        <p class="subtitle">Teacher Portal Login</p>
    </div>

    <form method="POST" action="<?= url('teacher/login') ?>">
        <?= csrf_field() ?>
        
        <div class="mb-3">
            <label class="form-label" for="username">Staff Username</label>
            <input class="form-control" type="text" name="username" id="username" required placeholder="e.g. STF-2026-0001" autofocus>
        </div>
        
        <div class="mb-4">
            <label class="form-label" for="password">Password</label>
            <input class="form-control" type="password" name="password" id="password" required placeholder="••••••••">
        </div>
        
        <button type="submit" class="btn-auth">Access Portal</button>
    </form>
    
    <div class="auth-footer">
        <p>Admin? <a href="<?= url('admin/login') ?>">Log in here</a></p>
        <p style="margin-top:10px;font-size:11px;">&copy; <?= date('Y') ?> <?= e(setting('school_name', 'School')) ?>. All rights reserved.</p>
    </div>
</div>
