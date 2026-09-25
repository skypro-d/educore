<?php
declare(strict_types=1);

/**
 * ScannerController — Dedicated Scanner Officer Kiosk Controller
 *
 * Provides dedicated, locked-down scanning portal interface for Scanner Officers.
 * Prevents unauthorized access to administrative modules.
 *
 * @package EduCore
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/SchoolContext.php';
require_once __DIR__ . '/../config/StaffAuth.php';
require_once __DIR__ . '/../config/StaffAudit.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../services/ScannerService.php';

final class ScannerController
{
    private PDO $db;
    private ScannerService $scannerService;

    public function __construct(?PDO $db = null, ?ScannerService $scannerService = null)
    {
        $this->db = $db ?? Database::connect();
        $this->scannerService = $scannerService ?? new ScannerService($this->db);
    }

    /**
     * Dedicated Scanner Officer Station Dashboard
     */
    public function dashboard(): void
    {
        $assignment = StaffAuth::requireScannerAccess();
        $schoolId = SchoolContext::id() ?? 1;
        $staffId = StaffAuth::id();
        $user = StaffAuth::user() ?? (admin() ?: ['name' => 'Administrator', 'first_name' => 'Admin']);

        $stats = $this->scannerService->getScannerStats($schoolId, (int) ($assignment['station_id'] ?? 0), $staffId);
        $recentLogs = $this->scannerService->getTodayLogs($staffId, $schoolId, 'all', 20);

        render('scanner/dashboard', compact('assignment', 'stats', 'recentLogs', 'user'), null);
    }

    /**
     * AJAX endpoint: Process scanned QR / ID Card token
     */
    public function processScanAjax(): void
    {
        $assignment = StaffAuth::requireScannerAccess();
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $rawCode = trim((string) ($input['qr_data'] ?? $input['token'] ?? $input['code'] ?? ''));
        $requestedMode = strtolower(trim((string) ($input['mode'] ?? 'auto')));

        if (!in_array($requestedMode, ['auto', 'in', 'out'], true)) {
            $requestedMode = 'auto';
        }

        $user = StaffAuth::user();
        $officerName = $user ? trim($user['first_name'] . ' ' . $user['last_name']) : (admin()['username'] ?? 'Scanner Officer');

        $officerContext = [
            'school_id'                => SchoolContext::id() ?? 1,
            'staff_id'                 => StaffAuth::id(),
            'assignment_id'            => (int) ($assignment['id'] ?? 0),
            'station_id'               => !empty($assignment['station_id']) ? (int) $assignment['station_id'] : null,
            'station_name'             => $assignment['station_name'] ?? 'Gate Scanner',
            'can_scan_in'              => (bool) ($assignment['can_scan_in'] ?? 1),
            'can_scan_out'             => (bool) ($assignment['can_scan_out'] ?? 1),
            'can_view_today_logs'      => (bool) ($assignment['can_view_today_logs'] ?? 1),
            'can_view_student_details' => (bool) ($assignment['can_view_student_details'] ?? 1),
            'officer_name'             => $officerName,
            'ip_address'               => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ];

        $response = $this->scannerService->processScan($rawCode, $officerContext, $requestedMode);

        echo json_encode($response);
        exit;
    }

    /**
     * AJAX endpoint: Retrieve today's scan activity logs
     */
    public function todayLogsAjax(): void
    {
        $assignment = StaffAuth::requireScannerAccess();
        header('Content-Type: application/json; charset=utf-8');

        $filter = strtolower(trim((string) ($_GET['filter'] ?? 'all')));
        $schoolId = SchoolContext::id() ?? 1;
        $staffId = StaffAuth::id();

        $logs = $this->scannerService->getTodayLogs($staffId, $schoolId, $filter, 50);

        echo json_encode([
            'success' => true,
            'logs'    => $logs,
            'count'   => count($logs)
        ]);
        exit;
    }

    /**
     * AJAX endpoint: Real-time stats refresh
     */
    public function statsAjax(): void
    {
        $assignment = StaffAuth::requireScannerAccess();
        header('Content-Type: application/json; charset=utf-8');

        $schoolId = SchoolContext::id() ?? 1;
        $staffId = StaffAuth::id();
        $stationId = (int) ($assignment['station_id'] ?? 0);

        $stats = $this->scannerService->getScannerStats($schoolId, $stationId, $staffId);

        echo json_encode([
            'success' => true,
            'stats'   => $stats
        ]);
        exit;
    }

    /**
     * Logout Scanner Officer
     */
    public function logout(): never
    {
        if (StaffAuth::check()) {
            StaffAudit::log('auth.scanner_logout', 'staff_accounts', StaffAuth::accountId(), 'Scanner Officer logged out');
            unset($_SESSION['teacher']);
        }
        redirect('teacher/login');
    }
}
