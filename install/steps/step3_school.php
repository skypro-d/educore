<?php
// Step 3: School Information

$errorMsg = '';
$schoolName = $_POST['school_name'] ?? ($_SESSION['install_school']['name'] ?? 'EduCore International Academy');
$schoolEmail = $_POST['school_email'] ?? ($_SESSION['install_school']['email'] ?? 'admin@school.com');
$schoolPhone = $_POST['school_phone'] ?? ($_SESSION['install_school']['phone'] ?? '+234 800 000 0000');
$schoolAddress = $_POST['school_address'] ?? ($_SESSION['install_school']['address'] ?? '1 Excellence Way, Victoria Island');
$principalName = $_POST['principal_name'] ?? ($_SESSION['install_school']['principal'] ?? 'Dr. Elizabeth Johnson');
$currency = $_POST['currency'] ?? ($_SESSION['install_school']['currency'] ?? 'NGN');
$timezone = $_POST['timezone'] ?? ($_SESSION['install_school']['timezone'] ?? 'Africa/Lagos');
$academicSession = $_POST['academic_session'] ?? ($_SESSION['install_school']['session'] ?? '2024/2025');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($schoolName) || empty($schoolEmail)) {
        $errorMsg = "School Name and Email are required.";
    } else {
        $_SESSION['install_school'] = [
            'name' => $schoolName,
            'email' => $schoolEmail,
            'phone' => $schoolPhone,
            'address' => $schoolAddress,
            'principal' => $principalName,
            'currency' => $currency,
            'timezone' => $timezone,
            'session' => $academicSession
        ];

        if (!headers_sent()) {
            header('Location: index.php?step=4');
        }
        echo '<script>window.location.href="index.php?step=4";</script>';
        exit;
    }
}
?>

<div class="mb-4">
    <h4 class="fw-bold mb-1">Step 3: School Information</h4>
    <p class="text-muted small">Configure your institution's profile details. These can be updated anytime in System Settings.</p>
</div>

<?php if ($errorMsg): ?>
    <div class="alert alert-danger mb-4"><?= htmlspecialchars($errorMsg) ?></div>
<?php endif; ?>

<form method="POST" action="index.php?step=3">
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label">School Name</label>
            <input type="text" name="school_name" class="form-control" value="<?= htmlspecialchars($schoolName) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">School Email</label>
            <input type="email" name="school_email" class="form-control" value="<?= htmlspecialchars($schoolEmail) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">School Phone</label>
            <input type="text" name="school_phone" class="form-control" value="<?= htmlspecialchars($schoolPhone) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Principal Name</label>
            <input type="text" name="principal_name" class="form-control" value="<?= htmlspecialchars($principalName) ?>" required>
        </div>
        <div class="col-12">
            <label class="form-label">School Address</label>
            <textarea name="school_address" class="form-control" rows="2" required><?= htmlspecialchars($schoolAddress) ?></textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">Currency Symbol / Code</label>
            <input type="text" name="currency" class="form-control" value="<?= htmlspecialchars($currency) ?>" required placeholder="NGN or $">
        </div>
        <div class="col-md-4">
            <label class="form-label">Timezone</label>
            <select name="timezone" class="form-select">
                <option value="Africa/Lagos" <?= $timezone === 'Africa/Lagos' ? 'selected' : '' ?>>Africa/Lagos (GMT+1)</option>
                <option value="UTC" <?= $timezone === 'UTC' ? 'selected' : '' ?>>UTC (Coordinated Universal Time)</option>
                <option value="America/New_York" <?= $timezone === 'America/New_York' ? 'selected' : '' ?>>America/New_York (EST)</option>
                <option value="Europe/London" <?= $timezone === 'Europe/London' ? 'selected' : '' ?>>Europe/London (GMT)</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Academic Session</label>
            <input type="text" name="academic_session" class="form-control" value="<?= htmlspecialchars($academicSession) ?>" required placeholder="2024/2025">
        </div>
    </div>

    <div class="installer-footer">
        <a href="index.php?step=2" class="btn btn-secondary-custom"><i class="bi bi-arrow-left me-1"></i> Back</a>
        <button type="submit" name="action" value="save_school_info" class="btn btn-primary-custom">Continue to Admin Setup <i class="bi bi-arrow-right ms-1"></i></button>
    </div>
</form>
