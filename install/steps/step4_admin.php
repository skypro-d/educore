<?php
// Step 4: Create System Administrator Account

$errorMsg = '';
$fullName = $_POST['admin_name'] ?? ($_SESSION['install_admin']['name'] ?? 'System Administrator');
$email = $_POST['admin_email'] ?? ($_SESSION['install_admin']['email'] ?? 'admin@school.com');
$password = $_POST['admin_pass'] ?? '';
$confirmPassword = $_POST['admin_confirm'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($fullName) || empty($email) || empty($password)) {
        $errorMsg = "Full Name, Email, and Password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $errorMsg = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $errorMsg = "Passwords do not match.";
    } else {
        $adminData = [
            'name' => $fullName,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'plain_password' => $password
        ];
        $_SESSION['install_admin'] = $adminData;
        if (function_exists('save_installer_state')) {
            save_installer_state(['install_admin' => $adminData]);
        }

        if (!headers_sent()) {
            header('Location: index.php?step=5');
        }
        echo '<script>window.location.href="index.php?step=5";</script>';
        exit;
    }
}
?>

<div class="mb-4">
    <h4 class="fw-bold mb-1">Step 4: System Administrator Setup</h4>
    <p class="text-muted small">Create the primary System Administrator account for managing EduCore.</p>
</div>

<?php if ($errorMsg): ?>
    <div class="alert alert-danger mb-4"><?= htmlspecialchars($errorMsg) ?></div>
<?php endif; ?>

<form method="POST" action="index.php?step=4">
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" name="admin_name" class="form-control" value="<?= htmlspecialchars($fullName) ?>" required placeholder="System Administrator">
        </div>
        <div class="col-md-6">
            <label class="form-label">Email Address (Login Username)</label>
            <input type="email" name="admin_email" class="form-control" value="<?= htmlspecialchars($email) ?>" required placeholder="admin@school.com">
        </div>
        <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" name="admin_pass" class="form-control" required placeholder="Minimum 6 characters">
        </div>
        <div class="col-md-6">
            <label class="form-label">Confirm Password</label>
            <input type="password" name="admin_confirm" class="form-control" required placeholder="Re-enter password">
        </div>
    </div>

    <div class="installer-footer">
        <a href="index.php?step=3" class="btn btn-secondary-custom"><i class="bi bi-arrow-left me-1"></i> Back</a>
        <button type="submit" name="action" value="save_admin" class="btn btn-primary-custom">Continue to License Activation <i class="bi bi-arrow-right ms-1"></i></button>
    </div>
</form>
