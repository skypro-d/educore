<div class="auth-card">
    <div class="logo-area">
        <div class="logo-circle" style="background:#ef4444;color:#fff;"><i class="ti ti-lock" style="font-size:28px;"></i></div>
        <h1>Update Your Password</h1>
        <p class="subtitle">For security reasons, you must change your temporary password before accessing the portal dashboard.</p>
    </div>

    <form method="POST" action="<?= url('student/change-password') ?>">
        <?= csrf_field() ?>
        
        <div class="mb-3">
            <label class="form-label" for="password">New Secure Password</label>
            <input class="form-control" type="password" name="password" id="password" minlength="6" required placeholder="Min 6 characters">
            <div style="font-size:11px;color:rgba(255,255,255,0.5);margin-top:6px;">Choose a strong password containing letters, numbers, and symbols.</div>
        </div>

        <button type="submit" class="btn-auth" style="background:#ef4444;color:#fff;box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);">Save &amp; Continue</button>
    </form>

    <div class="auth-footer">
        <p><a href="<?= url('student/logout') ?>">Sign Out</a></p>
    </div>
</div>
