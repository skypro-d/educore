<?php
// Step 5: License Activation
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/version.php';

$errorMsg = '';
$domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (str_contains($domain, ':')) {
    $domain = explode(':', $domain)[0];
}

// Generate stable Installation ID if not already created in session
if (empty($_SESSION['install_id'])) {
    $_SESSION['install_id'] = 'edc_inst_' . bin2hex(random_bytes(16));
}
$installationId = $_SESSION['install_id'];

$licenseKey = $_POST['license_key'] ?? ($_SESSION['install_license']['key'] ?? '');
$liveServerUrl = $_POST['license_server_url'] ?? ($_SESSION['install_license']['server_url'] ?? 'http://localhost/EduCore-LicenseServer');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $licenseKey = trim($_POST['license_key'] ?? '');
    $inputServerUrl = rtrim(trim($_POST['license_server_url'] ?? 'http://localhost/EduCore-LicenseServer'), '/');

    if (empty($licenseKey)) {
        $errorMsg = "Please enter a valid EduCore Activation / License Key.";
    } else {
        $serverIp = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()) ?: '127.0.0.1';

        $payload = json_encode([
            'license_key' => $licenseKey,
            'domain' => $domain,
            'server_ip' => $serverIp,
            'installation_id' => $installationId,
            'software_version' => EDUCORE_VERSION,
            'php_version' => PHP_VERSION,
            'release_channel' => 'stable'
        ]);

        // Candidate URLs to try (in order of likelihood)
        $candidates = [];
        $candidates[] = $inputServerUrl . '/index.php?route=api/v1/license/activate';
        $candidates[] = $inputServerUrl . '/api/v1/license/activate';

        // If URL contains subpath (like /EduCore-LicenseServer), also try root domain
        $parsedUrl = parse_url($inputServerUrl);
        if (!empty($parsedUrl['scheme']) && !empty($parsedUrl['host'])) {
            $rootUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'] . (!empty($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '');
            if ($rootUrl !== $inputServerUrl) {
                $candidates[] = $rootUrl . '/index.php?route=api/v1/license/activate';
                $candidates[] = $rootUrl . '/api/v1/license/activate';
            }
            // If http, also try https
            if ($parsedUrl['scheme'] === 'http') {
                $httpsInput = 'https://' . substr($inputServerUrl, 7);
                $candidates[] = $httpsInput . '/index.php?route=api/v1/license/activate';
                $candidates[] = $httpsInput . '/api/v1/license/activate';
                $httpsRoot = 'https://' . $parsedUrl['host'] . (!empty($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '');
                if ($httpsRoot !== $httpsInput) {
                    $candidates[] = $httpsRoot . '/index.php?route=api/v1/license/activate';
                    $candidates[] = $httpsRoot . '/api/v1/license/activate';
                }
            }
        }

        $activationSuccess = false;
        $licenseData = [];
        $lastHttpCode = 0;
        $lastCurlError = '';
        $effectiveWorkingUrl = $inputServerUrl;

        foreach (array_unique($candidates) as $testApiUrl) {
            $ch = curl_init($testApiUrl);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'X-Timestamp: ' . time()
                ],
                CURLOPT_TIMEOUT => 8,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            $lastHttpCode = $httpCode;
            $lastCurlError = $curlError;

            if ($response !== false && ($httpCode === 200 || $httpCode === 201)) {
                $data = json_decode($response, true);
                if (!empty($data['status']) && ($data['status'] === 'active' || $data['status'] === 'success')) {
                    $activationSuccess = true;
                    $licenseData = $data;
                    
                    // Deduce the working base URL
                    if (str_contains($testApiUrl, '/index.php?route=')) {
                        $effectiveWorkingUrl = explode('/index.php?route=', $testApiUrl)[0];
                    } elseif (str_contains($testApiUrl, '/api/v1/')) {
                        $effectiveWorkingUrl = explode('/api/v1/', $testApiUrl)[0];
                    }
                    break;
                } else {
                    $errorMsg = $data['message'] ?? 'License activation failed. Invalid key or domain conflict on EduCore Live.';
                    break;
                }
            }
        }

        if (!$activationSuccess && empty($errorMsg)) {
            $hint = '';
            if ($lastHttpCode === 404) {
                $hint = " Hint: If your EduCore Live Server is deployed on a custom domain or subdomain (e.g. <code>http://educore.skysaveings.com.ng</code>), do not include <code>/EduCore-LicenseServer</code> in the URL.";
            }
            $errorMsg = "Unable to connect to EduCore Live Server at {$inputServerUrl}. (HTTP Status: {$lastHttpCode}" . ($lastCurlError ? ", cURL: {$lastCurlError}" : "") . ").{$hint}";
        }

        if ($activationSuccess) {
            $_SESSION['install_license'] = [
                'key' => $licenseKey,
                'server_url' => $effectiveWorkingUrl,
                'domain' => $domain,
                'installation_id' => $installationId,
                'api_key' => $licenseData['api_key'] ?? '',
                'installation_token' => $licenseData['installation_token'] ?? '',
                'plan' => $licenseData['license']['plan'] ?? ($licenseData['plan'] ?? 'basic'),
                'features' => $licenseData['features'] ?? ($licenseData['license']['features'] ?? []),
                'features_map' => $licenseData['features_map'] ?? ($licenseData['license']['features_map'] ?? []),
                'grace_period_days' => $licenseData['grace_period_days'] ?? 30,
                'expires_at' => $licenseData['license']['expires_at'] ?? null,
                'raw_response' => $licenseData,
                'activated_at' => date('Y-m-d H:i:s')
            ];

            if (!headers_sent()) {
                header('Location: index.php?step=6');
            }
            echo '<script>window.location.href="index.php?step=6";</script>';
            exit;
        }
    }
}
?>

<div class="mb-4">
    <h4 class="fw-bold mb-1 text-white">Step 5: EduCore Live Activation</h4>
    <p class="text-muted small">Connect this school installation node to EduCore Live central control server using your purchased activation key.</p>
</div>

<?php if ($errorMsg): ?>
    <div class="alert alert-danger d-flex align-items-start mb-4 shadow-sm">
        <i class="bi bi-exclamation-octagon-fill fs-5 me-2 mt-1"></i>
        <div><?= $errorMsg ?></div>
    </div>
<?php endif; ?>

<form method="POST" action="index.php?step=5">
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label">Installation Domain</label>
            <input type="text" class="form-control" value="<?= htmlspecialchars($domain) ?>" readonly disabled>
            <div class="form-text text-muted">License will be cryptographically bound to this domain.</div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Stable Installation ID</label>
            <input type="text" class="form-control font-monospace" value="<?= htmlspecialchars($installationId) ?>" readonly disabled>
            <div class="form-text text-muted">Unique persistent identifier for this school node.</div>
        </div>
        <div class="col-md-12">
            <label class="form-label">EduCore Live Server URL</label>
            <input type="text" name="license_server_url" class="form-control" value="<?= htmlspecialchars($liveServerUrl) ?>" required placeholder="http://educore.skysaveings.com.ng or http://localhost/EduCore-LicenseServer">
            <div class="form-text text-muted">Central EduCore Live control endpoint:
                <ul class="mb-0 mt-1 ps-3 small text-muted">
                    <li>For Localhost: <code>http://localhost/EduCore-LicenseServer</code></li>
                    <li>For Subdomain / Live Server: <code>http://educore.skysaveings.com.ng</code> (or <code>https://...</code>)</li>
                </ul>
            </div>
        </div>
        <div class="col-12">
            <label class="form-label">EduCore Activation Key</label>
            <input type="text" name="license_key" class="form-control font-monospace" value="<?= htmlspecialchars($licenseKey) ?>" placeholder="EDC-XXXX-XXXX-XXXX or SKY-PRO-4444-5555-6666" required>
            <div class="form-text text-muted">Enter the activation key issued in your EduCore Live customer portal after purchase. (Demo keys: <code>SKY-BASIC-1111-2222-3333</code>, <code>SKY-PRO-4444-5555-6666</code>, <code>SKY-ENT-7777-8888-9999</code>)</div>
        </div>
    </div>

    <div class="installer-footer">
        <a href="index.php?step=4" class="btn btn-secondary-custom"><i class="bi bi-arrow-left me-1"></i> Back</a>
        <button type="submit" name="action" value="activate_license" class="btn btn-primary-custom">Verify & Register Node <i class="bi bi-shield-check ms-1"></i></button>
    </div>
</form>
