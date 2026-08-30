<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#061a40 0%,#0b3d91 100%);padding:20px;">
    <div style="background:#fff;border-radius:20px;padding:44px 40px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,.25);">
        <div style="text-align:center;margin-bottom:32px;">
            <div style="width:60px;height:60px;border-radius:14px;background:#ef4444;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;"><i class="ti ti-lock" style="font-size:28px;color:#fff;"></i></div>
            <h1 style="font-size:22px;font-weight:700;color:#1a2535;margin:0;">Update Password</h1>
            <p style="font-size:13px;color:#6b7280;margin:4px 0 0;">Please change your temporary password before proceeding.</p>
        </div>

        <form method="POST" action="<?= url('parent/change-password') ?>">
            <?= csrf_field() ?>
            <div style="margin-bottom:20px;">
                <label style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">New Secure Password</label>
                <div style="position:relative;">
                    <i class="ti ti-lock" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#9ca3af;font-size:17px;"></i>
                    <input type="password" name="password" minlength="6" required placeholder="Min 6 characters"
                        style="width:100%;padding:10px 12px 10px 38px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:14px;outline:none;transition:border .2s;"
                        onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#e5e7eb'">
                </div>
                <div style="font-size:11px;color:#6b7280;margin-top:6px;">Choose a strong password containing letters, numbers, and symbols.</div>
            </div>
            
            <button type="submit" style="width:100%;background:#ef4444;color:#fff;border:none;border-radius:10px;padding:12px;font-size:15px;font-weight:600;cursor:pointer;transition:opacity .2s;box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);" onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
                Save &amp; Continue
            </button>
        </form>

        <div style="text-align:center;margin-top:24px;padding-top:20px;border-top:1px solid #f3f4f6;">
            <a href="<?= url('parent/logout') ?>" style="font-size:12px;color:#9ca3af;text-decoration:none;"><i class="ti ti-logout" style="font-size:13px;"></i> Sign Out</a>
        </div>
    </div>
</div>
