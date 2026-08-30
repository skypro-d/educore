<div class="auth-card">
    <div class="logo-area">
        <?php if (setting('school_logo')): ?>
            <img src="<?= url('uploads/' . setting('school_logo')) ?>" alt="Logo" style="width:64px;height:64px;border-radius:16px;object-fit:cover;margin-bottom:15px;box-shadow:0 10px 20px rgba(0,0,0,0.2);">
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
