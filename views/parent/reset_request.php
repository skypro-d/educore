<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#061a40,#0b3d91);padding:20px;">
    <div style="background:#fff;border-radius:16px;padding:36px;width:100%;max-width:400px;box-shadow:0 20px 60px rgba(0,0,0,.25);">
        <div style="text-align:center;margin-bottom:28px;">
            <i class="ti ti-lock-open" style="font-size:40px;color:#0b3d91;"></i>
            <h1 style="font-size:20px;font-weight:700;color:#1a2535;margin:8px 0 4px;">Reset Password</h1>
            <p style="font-size:13px;color:#6b7280;">Enter your email to receive a reset link</p>
        </div>
        <?php foreach (flashes() as $f): ?><div class="alert alert-<?= e($f['type']) ?>" style="border-radius:8px;margin-bottom:12px;"><?= e($f['message']) ?></div><?php endforeach; ?>
        <form method="POST" action="<?= url('parent/reset-request') ?>">
            <?= csrf_field() ?>
            <input type="email" name="email" required placeholder="Your email address" style="width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:14px;margin-bottom:16px;box-sizing:border-box;">
            <button type="submit" style="width:100%;padding:11px;background:#0b3d91;color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:600;cursor:pointer;">Send Reset Link</button>
        </form>
        <div style="text-align:center;margin-top:16px;"><a href="<?= url('parent/login') ?>" style="font-size:13px;color:#6b7280;">← Back to login</a></div>
    </div>
</div>
