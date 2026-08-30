<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#061a40,#0b3d91);padding:20px;">
    <div style="background:#fff;border-radius:16px;padding:36px;width:100%;max-width:400px;box-shadow:0 10px 25px rgba(0,0,0,0.2);">
        <div style="text-align:center;margin-bottom:24px;">
            <div style="width:64px;height:64px;background:#e0f2fe;color:#0b3d91;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:32px;margin-bottom:16px;">
                <i class="ti ti-key"></i>
            </div>
            <h1 style="font-size:20px;font-weight:800;color:#1e293b;margin:0;">Reset Password</h1>
            <p style="font-size:13px;color:#64748b;margin-top:6px;line-height:1.4;">Enter a new password for your parent portal account.</p>
        </div>

        <form method="POST" action="<?= url('parent/reset') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="token" value="<?= e($token) ?>">

            <div style="margin-bottom:20px;">
                <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;">New Password</label>
                <input type="password" name="password" required minlength="6" placeholder="At least 6 characters" 
                       style="width:100%;padding:10px 14px;border:1px solid #cbd5e1;border-radius:8px;font-size:14px;outline:none;transition:border-color 0.2s;"
                       onfocus="this.style.borderColor='#0b3d91';" onblur="this.style.borderColor='#cbd5e1';">
            </div>

            <button type="submit" style="width:100%;padding:12px;background:#0b3d91;color:#fff;border:none;border-radius:8px;font-size:14px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background-color 0.2s;"
                    onmouseover="this.style.backgroundColor='#082f6e';" onmouseout="this.style.backgroundColor='#0b3d91';">
                <i class="ti ti-circle-check"></i> Update Password
            </button>
        </form>

        <div style="text-align:center;margin-top:20px;">
            <a href="<?= url('parent/login') ?>" style="font-size:13px;color:#0b3d91;text-decoration:none;font-weight:600;">Return to Login</a>
        </div>
    </div>
</div>
