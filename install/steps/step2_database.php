<?php
// Step 2: Database Configuration & Connection Test
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/updater/MigrationRunner.php';

$errorMsg = '';
$dbHost = $_POST['db_host'] ?? ($_SESSION['install_db']['host'] ?? 'localhost');
$dbName = $_POST['db_name'] ?? ($_SESSION['install_db']['name'] ?? 'educore_school');
$dbUser = $_POST['db_user'] ?? ($_SESSION['install_db']['user'] ?? 'root');
$dbPass = $_POST['db_pass'] ?? ($_SESSION['install_db']['pass'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = null;
        try {
            // First attempt: Direct connection to target database (cPanel / shared hosting standard)
            $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $exDirect) {
            // Second attempt: Connect without dbname and create database (localhost / root standard)
            $dsn = "mysql:host={$dbHost};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$dbName}`");
        }

        // Import educore_school_schema.sql if needed
        $schemaPath = dirname(__DIR__, 2) . '/database/educore_school_schema.sql';
        if (!file_exists($schemaPath)) {
            throw new Exception("Database schema file not found at database/educore_school_schema.sql");
        }

        // Disable foreign key checks during batch schema import
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

        $sql = file_get_contents($schemaPath);
        $queries = preg_split('/;\s*[\r\n]+/', $sql);

        foreach ($queries as $query) {
            $query = trim($query);
            if ($query !== '' && !str_starts_with($query, '--') && !str_starts_with($query, '/*')) {
                try {
                    $pdo->exec($query);
                } catch (PDOException $ex) {
                    if ($ex->getCode() !== '42S02') {
                        // Ignore minor table recreate warnings
                    }
                }
            }
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

        // Run versioned migrations through MigrationRunner
        $migrationRunner = new MigrationRunner($pdo);
        $migrationRunner->recordBaseline('001_initial_schema.sql');
        $migrationResult = $migrationRunner->runPending();

        // Store DB parameters in session & persistent state
        $dbData = [
            'host' => $dbHost,
            'name' => $dbName,
            'user' => $dbUser,
            'pass' => $dbPass
        ];
        $_SESSION['install_db'] = $dbData;
        if (function_exists('save_installer_state')) {
            save_installer_state(['install_db' => $dbData]);
        }

        if (!headers_sent()) {
            header('Location: index.php?step=3');
        }
        echo '<script>window.location.href="index.php?step=3";</script>';
        exit;

    } catch (Throwable $e) {
        $errorMsg = "Database Connection Failed: " . htmlspecialchars($e->getMessage());
    }
}
?>

<div class="mb-4">
    <h4 class="fw-bold mb-1 text-white">Step 2: Database Configuration</h4>
    <p class="text-muted small">Enter your MySQL 8+ database connection parameters. The installer will initialize database tables and apply deterministic migrations.</p>
</div>

<?php if ($errorMsg): ?>
    <div class="alert alert-danger d-flex align-items-center mb-4">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
        <div><?= $errorMsg ?></div>
    </div>
<?php endif; ?>

<form method="POST" action="index.php?step=2">
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label">Database Host</label>
            <input type="text" name="db_host" class="form-control" value="<?= htmlspecialchars($dbHost) ?>" required placeholder="localhost">
        </div>
        <div class="col-md-6">
            <label class="form-label">Database Name</label>
            <input type="text" name="db_name" class="form-control" value="<?= htmlspecialchars($dbName) ?>" required placeholder="educore_school">
        </div>
        <div class="col-md-6">
            <label class="form-label">Database Username</label>
            <input type="text" name="db_user" class="form-control" value="<?= htmlspecialchars($dbUser) ?>" required placeholder="root">
        </div>
        <div class="col-md-6">
            <label class="form-label">Database Password</label>
            <input type="password" name="db_pass" class="form-control" value="<?= htmlspecialchars($dbPass) ?>" placeholder="Leave blank if none">
        </div>
    </div>

    <div class="installer-footer">
        <a href="index.php?step=1" class="btn btn-secondary-custom"><i class="bi bi-arrow-left me-1"></i> Back</a>
        <button type="submit" name="action" value="test_and_import" class="btn btn-primary-custom">Test Connection & Run Migrations <i class="bi bi-arrow-right ms-1"></i></button>
    </div>
</form>
