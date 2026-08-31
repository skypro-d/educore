<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#061a40 0%,#0b3d91 100%);padding:20px;">
    <div style="background:#fff;border-radius:20px;padding:44px 40px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,.25);">
        <div style="text-align:center;margin-bottom:32px;">
            <?php $logoUrl = school_logo_url(); ?>
            <?php if ($logoUrl): ?>
                <img src="<?= e($logoUrl) ?>" alt="Logo" style="height:60px;max-height:60px;max-width:140px;object-fit:contain;margin-bottom:12px;border-radius:10px;background:#fff;padding:2px;" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                <div style="display:none;width:60px;height:60px;border-radius:14px;background:linear-gradient(135deg,#0b3d91,#1a6dd8);align-items:center;justify-content:center;margin:0 auto 12px;"><i class="ti ti-users" style="font-size:28px;color:#fff;"></i></div>
            <?php else: ?>
                <div style="width:60px;height:60px;border-radius:14px;background:linear-gradient(135deg,#0b3d91,#1a6dd8);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;"><i class="ti ti-users" style="font-size:28px;color:#fff;"></i></div>
            <?php endif; ?>
            <h1 style="font-size:22px;font-weight:700;color:#1a2535;margin:0;">Parent Portal</h1>
            <p style="font-size:13px;color:#6b7280;margin:4px 0 0;"><?= e(setting('school_name', 'School Management System')) ?></p>
        </div>

        <?php foreach (flashes() as $f): ?>
            <div class="alert alert-<?= e($f['type']) ?>" style="border-radius:10px;margin-bottom:16px;"><?= e($f['message']) ?></div>
        <?php endforeach; ?>

        <form method="POST" action="<?= url('parent/login') ?>">
            <?= csrf_field() ?>
            <div style="margin-bottom:16px;">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">Email Address</label>
                <div style="position:relative;">
                    <i class="ti ti-mail" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:17px;"></i>
                    <input type="email" name="email" required placeholder="parent@email.com"
                        style="width:100%;padding:10px 12px 10px 38px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:14px;outline:none;transition:border .2s;"
                        onfocus="this.style.borderColor='#0b3d91'" onblur="this.style.borderColor='#e5e7eb'">
                </div>
            </div>
            <div style="margin-bottom:20px;">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">Password</label>
                <div style="position:relative;">
                    <i class="ti ti-lock" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:17px;"></i>
                    <input type="password" name="password" required placeholder="••••••••"
                        style="width:100%;padding:10px 12px 10px 38px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:14px;outline:none;transition:border .2s;"
                        onfocus="this.style.borderColor='#0b3d91'" onblur="this.style.borderColor='#e5e7eb'">
                </div>
            </div>
            <button type="submit" style="width:100%;background:linear-gradient(135deg,#0b3d91,#1a6dd8);color:#fff;border:none;border-radius:10px;padding:12px;font-size:15px;font-weight:600;cursor:pointer;transition:opacity .2s;" onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
                <i class="ti ti-login" style="margin-right:6px;"></i> Sign In
            </button>
        </form>

        <div style="text-align:center;margin-top:20px;">
            <a href="<?= url('parent/reset-request') ?>" style="font-size:13px;color:#6b7280;text-decoration:none;">Forgot your password?</a>
        </div>

        <div style="text-align:center;margin-top:24px;padding-top:20px;border-top:1px solid #f3f4f6;">
            <a href="<?= url('') ?>" style="font-size:12px;color:#9ca3af;text-decoration:none;"><i class="ti ti-arrow-left" style="font-size:13px;"></i> Back to school portal</a>
        </div>
    </div>
</div>
