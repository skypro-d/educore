<div class="auth-card mx-auto">
    <div class="text-center mb-4">
        <span class="logo-mark">S</span>
        <h1 class="h3 mt-3">Admin Login</h1>
        <p class="text-muted">Secure admission management panel</p>
    </div>
    <form method="post" action="<?= url('admin/login') ?>">
        <?= csrf_field() ?>
        <label class="form-label">Email</label>
        <input class="form-control mb-3" type="email" name="email" required>
        <label class="form-label">Password</label>
        <input class="form-control mb-3" type="password" name="password" required>
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember">
            <label class="form-check-label" for="remember">Remember session</label>
        </div>
        <button class="btn btn-primary w-100">Login</button>
    </form>
</div>

