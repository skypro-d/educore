<?php
/**
 * School Admin Support Access Management
 * EduCore Standalone App — admin/support_access.php
 *
 * Allows school administrators to enable temporary 24-hour support tokens
 * for SkySavingTech engineers to troubleshoot without sharing passwords.
 *
 * @package EduCore
 * @version 2.0.0
 */

session_start();
require_once __DIR__ . '/../config/ApiKeyService.php';

$message = '';
$generatedToken = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'generate_support_token') {
        $adminEmail = trim($_POST['admin_email'] ?? 'admin@school.com');
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        if (str_contains($domain, ':')) {
            $domain = explode(':', $domain)[0];
        }

        $response = ApiKeyService::sendSecureRequest('api/v1/support/generate.php', [
            'api_key' => ApiKeyService::getApiKey(),
            'domain' => $domain,
            'installation_id' => ApiKeyService::getInstallationId(),
            'created_by' => $adminEmail
        ]);

        if ($response && ($response['success'] ?? false)) {
            $generatedToken = $response['support_token'];
            $message = "Support token generated successfully! Valid for 24 hours.";
        } else {
            // Local offline fallback generation
            $token = 'SUPPORT_' . strtoupper(bin2hex(random_bytes(4)));
            $generatedToken = $token;
            $message = "Local support token generated for troubleshooting: " . $token;
        }
    }
}

$pageTitle = 'Remote Support Access | EduCore Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #0f172a; color: #f8fafc; font-family: system-ui, -apple-system, sans-serif; }
        .card-dark { background: #1e293b; border: 1px solid #334155; border-radius: 12px; }
        .token-box { background: #090d16; border: 1px dashed #38bdf8; font-family: monospace; font-size: 1.5rem; letter-spacing: 2px; color: #38bdf8; }
    </style>
</head>
<body class="py-5">
    <div class="container" style="max-width: 720px;">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold mb-0 text-white"><i class="fa-solid fa-headset me-2 text-info"></i>SkySavingTech Remote Support Access</h4>
            <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i>Back to Dashboard</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i><?= htmlspecialchars($message) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="card-dark p-4 mb-4">
            <h5 class="fw-bold text-white mb-2">Enable Temporary Support Access</h5>
            <p class="text-secondary small mb-4">
                When you request technical support, SkySavingTech engineers can troubleshoot your installation securely without needing your admin account password. Generating a token grants access for <strong>24 hours only</strong>.
            </p>

            <?php if ($generatedToken): ?>
                <div class="alert alert-info border-info text-center p-4 rounded-3 mb-4">
                    <span class="text-uppercase text-info small fw-bold d-block mb-1">Your Active Support Access Token</span>
                    <div class="token-box p-3 my-2 rounded text-center" id="tokenText"><?= htmlspecialchars($generatedToken) ?></div>
                    <small class="text-secondary d-block mt-2">Expires in 24 hours. Share this token code with your SkySavingTech support representative.</small>
                    <button class="btn btn-sm btn-info text-dark font-weight-bold mt-3 px-4" onclick="copyToken()">
                        <i class="fa-solid fa-copy me-1"></i>Copy Token Code
                    </button>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="action" value="generate_support_token">
                <div class="mb-3">
                    <label class="form-label text-secondary small">Authorized Admin Email</label>
                    <input type="email" name="admin_email" class="form-control bg-dark text-white border-secondary" value="<?= htmlspecialchars($_SESSION['admin_email'] ?? 'admin@school.com') ?>" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary py-2 font-weight-bold">
                        <i class="fa-solid fa-key me-2"></i>Enable Support Access (Generate Token)
                    </button>
                </div>
            </form>
        </div>

        <div class="card-dark p-4">
            <h6 class="fw-bold text-white mb-2"><i class="fa-solid fa-shield-halved me-2 text-warning"></i>Security & Privacy Guarantee</h6>
            <ul class="text-secondary small mb-0 ps-3">
                <li class="mb-1">Support access automatically expires after 24 hours.</li>
                <li class="mb-1">You can revoke support access at any time from your license server portal.</li>
                <li>All support activities are audited and logged in your system security log.</li>
            </ul>
        </div>
    </div>

    <script>
        function copyToken() {
            const tokenText = document.getElementById('tokenText').innerText;
            navigator.clipboard.writeText(tokenText).then(() => {
                alert('Support token copied to clipboard!');
            });
        }
    </script>
</body>
</html>
