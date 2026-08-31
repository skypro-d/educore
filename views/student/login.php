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
        
        <div class="mb-4">
            <label class="form-label" for="password">Password</label>
            <input class="form-control" type="password" name="password" id="password" required placeholder="••••••••">
        </div>
        
        <button type="submit" class="btn-auth">Access Portal</button>
    </form>
    
    <div class="auth-footer">
        <p>Parent? <a href="<?= url('parent/login') ?>">Log in here</a></p>
        <p style="margin-top:10px;font-size:11px;">&copy; <?= date('Y') ?> Bluefield School Admission. All rights reserved.</p>
    </div>
</div>
