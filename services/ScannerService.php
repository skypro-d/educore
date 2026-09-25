<?php
declare(strict_types=1);

/**
 * ScannerService — Enterprise Scanner Assignment & Scanner Station Operations Service
 *
 * Handles staff scanner assignments, station telemetry, student verification,
 * attendance check-in/check-out processing, multi-tenant RBAC enforcement,
 * parent notification triggering, and scanner audit logs.
 *
 * @package EduCore
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/SchoolContext.php';
require_once __DIR__ . '/../config/StaffAuth.php';
require_once __DIR__ . '/../config/StaffAudit.php';
require_once __DIR__ . '/../config/AttendanceRules.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/AttendanceService.php';

final class ScannerService
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    /* ═══════════════════════════════════════════════════════════════════════
       1. SCANNER ASSIGNMENT MANAGEMENT
       ═══════════════════════════════════════════════════════════════════════ */

    /**
     * Get active scanner assignment for a specific staff member.
     */
    public function getActiveAssignmentForStaff(int $staffId, ?int $schoolId = null): ?array
    {
        if ($staffId <= 0) {
            return null;
        }
        $schoolId = $schoolId ?? SchoolContext::id() ?? 1;

        $stmt = $this->db->prepare(
            "SELECT sa.*, st.station_name, st.station_code, st.scanner_type, st.location AS station_location,
                    s.first_name, s.last_name, s.staff_id AS public_staff_id, s.status AS staff_status,
                    s.passport_photo, s.department
             FROM scanner_assignments sa
             JOIN staff s ON s.id = sa.staff_id
             LEFT JOIN scanner_stations st ON st.id = sa.station_id
             WHERE sa.staff_id = ? AND sa.school_id = ? AND sa.status = 'active' AND sa.deleted_at IS NULL
             LIMIT 1"
        );
        $stmt->execute([$staffId, $schoolId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }

    /**
     * Get all scanner assignments (with optional filters).
     */
    public function getAllAssignments(?int $schoolId = null, array $filters = []): array
    {
        $schoolId = $schoolId ?? SchoolContext::id() ?? 1;
        $sql = "SELECT sa.*, st.station_name, st.station_code, st.location AS station_location,
                       s.first_name, s.last_name, s.staff_id AS public_staff_id, s.status AS staff_status,
                       s.department, s.passport_photo,
                       adm.name AS assigned_by_username
                FROM scanner_assignments sa
                JOIN staff s ON s.id = sa.staff_id
                LEFT JOIN scanner_stations st ON st.id = sa.station_id
                LEFT JOIN admins adm ON adm.id = sa.assigned_by
                WHERE sa.school_id = :school_id AND sa.deleted_at IS NULL";
        
        $params = ['school_id' => $schoolId];

        if (!empty($filters['status'])) {
            $sql .= " AND sa.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['station_id'])) {
            $sql .= " AND sa.station_id = :station_id";
            $params['station_id'] = (int) $filters['station_id'];
        }

        $sql .= " ORDER BY sa.status = 'active' DESC, sa.assigned_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Assign a staff member to a scanner station with specific permissions.
     */
    public function assignStaff(
        int $staffId,
        ?int $stationId,
        array $permissions,
        ?int $assignedBy = null,
        ?string $notes = null,
        ?int $schoolId = null
    ): int {
        $schoolId = $schoolId ?? SchoolContext::id() ?? 1;

        // Verify staff exists and is active
        $stmtStaff = $this->db->prepare("SELECT * FROM staff WHERE id = ? AND school_id = ? LIMIT 1");
        $stmtStaff->execute([$staffId, $schoolId]);
        $staff = $stmtStaff->fetch(PDO::FETCH_ASSOC);

        if (!$staff) {
            throw new InvalidArgumentException("Selected staff record does not exist in this school.");
        }
        if ($staff['status'] !== 'Active') {
            throw new InvalidArgumentException("Only active staff members can be assigned as Scanner Officers.");
        }

        // Deactivate any existing active assignments for this staff to prevent conflicts
        $this->db->prepare(
            "UPDATE scanner_assignments 
             SET status = 'inactive', updated_at = NOW() 
             WHERE staff_id = ? AND school_id = ? AND status = 'active'"
        )->execute([$staffId, $schoolId]);

        $canScanIn       = !empty($permissions['can_scan_in']) ? 1 : 0;
        $canScanOut      = !empty($permissions['can_scan_out']) ? 1 : 0;
        $canViewLogs     = !empty($permissions['can_view_today_logs']) ? 1 : 0;
        $canViewDetails  = !empty($permissions['can_view_student_details']) ? 1 : 0;

        $stmt = $this->db->prepare(
            "INSERT INTO scanner_assignments
                (school_id, staff_id, station_id, assigned_by, status, can_scan_in, can_scan_out, can_view_today_logs, can_view_student_details, notes, assigned_at, updated_at)
             VALUES
                (?, ?, ?, ?, 'active', ?, ?, ?, ?, ?, NOW(), NOW())"
        );
        $stmt->execute([
            $schoolId,
            $staffId,
            $stationId ?: null,
            $assignedBy,
            $canScanIn,
            $canScanOut,
            $canViewLogs,
            $canViewDetails,
            $notes
        ]);

        $assignmentId = (int) $this->db->lastInsertId();

        // Ensure granular permissions exist in staff_permissions table
        $this->syncStaffScannerPermissions($staffId, $permissions, $schoolId);

        // Audit logging
        $stationTitle = $stationId ? "station #{$stationId}" : "all stations";
        $staffName = trim($staff['first_name'] . ' ' . $staff['last_name']);
        StaffAudit::log(
            'scanner.assigned',
            'scanner_assignments',
            $assignmentId,
            "Assigned {$staffName} as Scanner Officer to {$stationTitle}"
        );

        return $assignmentId;
    }

    /**
     * Update an existing scanner assignment.
     */
    public function updateAssignment(
        int $assignmentId,
        ?int $stationId,
        string $status,
        array $permissions,
        ?string $notes = null,
        ?int $schoolId = null
    ): bool {
        $schoolId = $schoolId ?? SchoolContext::id() ?? 1;

        $stmtPrev = $this->db->prepare("SELECT * FROM scanner_assignments WHERE id = ? AND school_id = ? LIMIT 1");
        $stmtPrev->execute([$assignmentId, $schoolId]);
        $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);

        if (!$prev) {
            throw new InvalidArgumentException("Scanner assignment #{$assignmentId} not found.");
        }

        $canScanIn      = !empty($permissions['can_scan_in']) ? 1 : 0;
        $canScanOut     = !empty($permissions['can_scan_out']) ? 1 : 0;
        $canViewLogs    = !empty($permissions['can_view_today_logs']) ? 1 : 0;
        $canViewDetails = !empty($permissions['can_view_student_details']) ? 1 : 0;

        $stmt = $this->db->prepare(
            "UPDATE scanner_assignments
             SET station_id = ?, status = ?, can_scan_in = ?, can_scan_out = ?,
                 can_view_today_logs = ?, can_view_student_details = ?, notes = ?, updated_at = NOW()
             WHERE id = ? AND school_id = ?"
        );
        $res = $stmt->execute([
            $stationId ?: null,
            $status,
            $canScanIn,
            $canScanOut,
            $canViewLogs,
            $canViewDetails,
            $notes,
            $assignmentId,
            $schoolId
        ]);

        $this->syncStaffScannerPermissions((int) $prev['staff_id'], $permissions, $schoolId);

        StaffAudit::log(
            'scanner.assignment_updated',
            'scanner_assignments',
            $assignmentId,
            "Updated scanner assignment #{$assignmentId} (Status: {$status})"
        );

        return $res;
    }

    /**
     * Toggle assignment active / inactive status.
     */
    public function toggleAssignmentStatus(int $assignmentId, string $status, ?int $schoolId = null): bool
    {
        $schoolId = $schoolId ?? SchoolContext::id() ?? 1;
        $validStatuses = ['active', 'inactive'];
        if (!in_array($status, $validStatuses, true)) {
            throw new InvalidArgumentException("Invalid assignment status.");
        }

        $stmt = $this->db->prepare(
            "UPDATE scanner_assignments SET status = ?, updated_at = NOW() WHERE id = ? AND school_id = ?"
        );
        $res = $stmt->execute([$status, $assignmentId, $schoolId]);

        StaffAudit::log(
            'scanner.assignment_status_changed',
            'scanner_assignments',
            $assignmentId,
            "Changed assignment #{$assignmentId} status to {$status}"
        );

        return $res;
    }

    /**
     * Remove / soft-delete scanner assignment (preserving historical records).
     */
    public function removeAssignment(int $assignmentId, ?int $schoolId = null): bool
    {
        $schoolId = $schoolId ?? SchoolContext::id() ?? 1;

        $stmt = $this->db->prepare(
            "UPDATE scanner_assignments 
             SET status = 'revoked', deleted_at = NOW(), updated_at = NOW() 
             WHERE id = ? AND school_id = ?"
        );
        $res = $stmt->execute([$assignmentId, $schoolId]);

        StaffAudit::log(
            'scanner.assignment_removed',
            'scanner_assignments',
            $assignmentId,
            "Revoked and archived scanner assignment #{$assignmentId}"
        );

        return $res;
    }

    /**
     * Get assignment history for a specific staff member.
     */
    public function getAssignmentHistory(int $staffId, ?int $schoolId = null): array
    {
        $schoolId = $schoolId ?? SchoolContext::id() ?? 1;

        $stmt = $this->db->prepare(
            "SELECT sa.*, st.station_name, st.station_code, st.location AS station_location,
                    adm.name AS assigned_by_username
             FROM scanner_assignments sa
             LEFT JOIN scanner_stations st ON st.id = sa.station_id
             LEFT JOIN admins adm ON adm.id = sa.assigned_by
             WHERE sa.staff_id = ? AND sa.school_id = ?
             ORDER BY sa.assigned_at DESC"
        );
        $stmt->execute([$staffId, $schoolId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ═══════════════════════════════════════════════════════════════════════
       2. SCANNER STATION MANAGEMENT
       ═══════════════════════════════════════════════════════════════════════ */

    /**
     * List all scanner stations.
     */
    public function getAllStations(?int $schoolId = null): array
    {
        $schoolId = $schoolId ?? SchoolContext::id() ?? 1;

        $stmt = $this->db->prepare(
            "SELECT st.*,
                    (SELECT COUNT(*) FROM scanner_assignments sa WHERE sa.station_id = st.id AND sa.status = 'active' AND sa.deleted_at IS NULL) AS active_officers_count
             FROM scanner_stations st
             WHERE st.school_id = ?
             ORDER BY st.status = 'active' DESC, st.station_name ASC"
        );
        $stmt->execute([$schoolId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get single scanner station.
     */
    public function getStationById(int $stationId, ?int $schoolId = null): ?array
    {
        $schoolId = $schoolId ?? SchoolContext::id() ?? 1;

        $stmt = $this->db->prepare("SELECT * FROM scanner_stations WHERE id = ? AND school_id = ? LIMIT 1");
        $stmt->execute([$stationId, $schoolId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Save (create or update) a scanner station.
     */
    public function saveStation(array $data, ?int $schoolId = null): int
    {
        $schoolId    = $schoolId ?? SchoolContext::id() ?? 1;
        $id          = (int) ($data['id'] ?? 0);
        $stationName = trim($data['station_name'] ?? '');
        $stationCode = strtoupper(trim($data['station_code'] ?? ''));
        $scannerType = $data['scanner_type'] ?? 'usb_hid';
        $location    = trim($data['location'] ?? '');
        $status      = $data['status'] ?? 'active';
        $notes       = trim($data['notes'] ?? '');

        if ($stationName === '' || $stationCode === '') {
            throw new InvalidArgumentException("Station Name and Station Code are required.");
        }

        if ($id > 0) {
            $stmt = $this->db->prepare(
                "UPDATE scanner_stations 
                 SET station_name = ?, station_code = ?, scanner_type = ?, location = ?, status = ?, notes = ?, updated_at = NOW()
                 WHERE id = ? AND school_id = ?"
            );
            $stmt->execute([$stationName, $stationCode, $scannerType, $location ?: null, $status, $notes ?: null, $id, $schoolId]);
            return $id;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO scanner_stations (school_id, station_name, station_code, scanner_type, location, status, notes, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->execute([$schoolId, $stationName, $stationCode, $scannerType, $location ?: null, $status, $notes ?: null]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Delete scanner station (if no active assignments).
     */
    public function deleteStation(int $stationId, ?int $schoolId = null): bool
    {
        $schoolId = $schoolId ?? SchoolContext::id() ?? 1;

        // Unlink or deactivate assignments on this station
        $this->db->prepare(
            "UPDATE scanner_assignments SET station_id = NULL WHERE station_id = ? AND school_id = ?"
        )->execute([$stationId, $schoolId]);

        $stmt = $this->db->prepare("DELETE FROM scanner_stations WHERE id = ? AND school_id = ?");
        return $stmt->execute([$stationId, $schoolId]);
    }

    /* ═══════════════════════════════════════════════════════════════════════
       3. STUDENT SCAN PROCESSING ENGINE
       ═══════════════════════════════════════════════════════════════════════ */

    /**
     * Process scanned identifier from USB QR / Card Reader.
     *
     * @param string $rawCode        Raw scanner input string / token / QR URL
     * @param array  $officerContext Staff officer authentication & assignment details
     * @param string $requestedMode  'auto' | 'in' | 'out'
     * @return array Standardized structured response for the Scanner Station UI
     */
    public function processScan(string $rawCode, array $officerContext, string $requestedMode = 'auto'): array
    {
        $rawCode = trim($rawCode);
        $schoolId = (int) ($officerContext['school_id'] ?? SchoolContext::id() ?? 1);
        $staffId = (int) ($officerContext['staff_id'] ?? 0);
        $stationId = !empty($officerContext['station_id']) ? (int) $officerContext['station_id'] : null;
        $assignmentId = !empty($officerContext['assignment_id']) ? (int) $officerContext['assignment_id'] : null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $today = date('Y-m-d');
        $nowTime = date('H:i:s');

        if ($rawCode === '') {
            return [
                'success'      => false,
                'action'       => 'error',
                'badge_status' => 'danger',
                'title'        => 'NO INPUT',
                'message'      => 'No barcode or QR code received from scanner.',
                'raw_code'     => ''
            ];
        }

        // 1. Permission checks for requested mode
        if ($requestedMode === 'in' && empty($officerContext['can_scan_in']) && !StaffAuth::isSchoolAdmin()) {
            return [
                'success'      => false,
                'action'       => 'denied',
                'badge_status' => 'danger',
                'title'        => 'PERMISSION DENIED',
                'message'      => 'Your scanner assignment does not permit Entry Check-In operations.'
            ];
        }
        if ($requestedMode === 'out' && empty($officerContext['can_scan_out']) && !StaffAuth::isSchoolAdmin()) {
            return [
                'success'      => false,
                'action'       => 'denied',
                'badge_status' => 'danger',
                'title'        => 'PERMISSION DENIED',
                'message'      => 'Your scanner assignment does not permit Departure Check-Out operations.'
            ];
        }

        // 2. Sanitize & Normalize Token
        $token = $rawCode;
        if (preg_match('/[?&_\-\^]token[=0\):]([^\s&#\^?]+?)(?:http|$)/i', $rawCode, $matches)) {
            $token = urldecode(rtrim($matches[1], '/'));
        } elseif (preg_match('/[?&]token=([^&#\s]+)/i', $rawCode, $matches)) {
            $token = urldecode(rtrim($matches[1], '/'));
        }
        $token = trim($token);
        $normalizedToken = str_replace('/', '-', $token);

        // 3. Locate Student Record
        $stmt = $this->db->prepare(
            "SELECT a.*, c.name AS class_name
             FROM applicants a
             LEFT JOIN classes c ON c.id = a.class_id
             WHERE (a.qr_data = ? OR a.qr_data = ? OR a.admission_number = ? OR a.application_number = ?)
             LIMIT 1"
        );
        $stmt->execute([$token, $normalizedToken, $token, $token]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fallback A: Token ID pattern match
        if (!$student && preg_match('/attendance[-_\/]std[-_\/](\d+)/i', $rawCode, $m)) {
            $studentId = (int) $m[1];
            $stmtId = $this->db->prepare("SELECT a.*, c.name AS class_name FROM applicants a LEFT JOIN classes c ON c.id = a.class_id WHERE a.id = ? LIMIT 1");
            $stmtId->execute([$studentId]);
            $student = $stmtId->fetch(PDO::FETCH_ASSOC);
        }

        // Fallback B: Numeric ID fallback
        if (!$student && ctype_digit($token)) {
            $stmtId = $this->db->prepare("SELECT a.*, c.name AS class_name FROM applicants a LEFT JOIN classes c ON c.id = a.class_id WHERE a.id = ? LIMIT 1");
            $stmtId->execute([(int) $token]);
            $student = $stmtId->fetch(PDO::FETCH_ASSOC);
        }

        // 4. Handle Invalid Student Scan
        if (!$student) {
            $this->logScanEvent([
                'school_id'          => $schoolId,
                'assignment_id'      => $assignmentId,
                'staff_id'           => $staffId,
                'station_id'         => $stationId,
                'student_id'         => null,
                'person_type'        => 'unknown',
                'scan_mode'          => $requestedMode,
                'scan_action'        => 'invalid',
                'status'             => 'danger',
                'identifier_scanned' => $rawCode,
                'response_message'   => 'Scanned student ID or QR code could not be found.',
                'ip_address'         => $ipAddress,
                'date'               => $today
            ]);

            StaffAudit::log('scanner.scan_invalid', 'applicants', null, "Invalid scan attempt: {$rawCode}");

            return [
                'success'      => false,
                'action'       => 'invalid',
                'badge_status' => 'danger',
                'title'        => '✕ INVALID STUDENT',
                'message'      => 'The scanned student ID could not be found. Please verify the ID card and try again.',
                'raw_code'     => $rawCode
            ];
        }

        // 5. Multi-Tenant School Check
        if ((int)$student['school_id'] !== $schoolId) {
            return [
                'success'      => false,
                'action'       => 'cross_school_denied',
                'badge_status' => 'danger',
                'title'        => '✕ UNAUTHORIZED TENANT',
                'message'      => 'This student card belongs to a different school branch.'
            ];
        }

        // 6. Active & Enrolled Status Check
        $studentName = trim($student['first_name'] . ' ' . $student['last_name']);
        $studentPhoto = !empty($student['passport_photo']) ? url('uploads/' . $student['passport_photo']) : null;
        $studentInfo = [
            'id'               => (int) $student['id'],
            'name'             => $studentName,
            'first_name'       => $student['first_name'],
            'last_name'        => $student['last_name'],
            'admission_number' => $student['admission_number'] ?: $student['application_number'],
            'class_name'       => $student['class_name'] ?: 'N/A',
            'photo'            => $studentPhoto,
            'parent_phone'     => mask_phone($student['parent_phone'] ?? ''),
        ];

        if ($student['status'] !== 'Enrolled' || (!empty($student['student_status']) && $student['student_status'] !== 'Active')) {
            $statusLabel = $student['status'] !== 'Enrolled' ? $student['status'] : ($student['student_status'] ?? 'Inactive');
            
            $this->logScanEvent([
                'school_id'          => $schoolId,
                'assignment_id'      => $assignmentId,
                'staff_id'           => $staffId,
                'station_id'         => $stationId,
                'student_id'         => (int) $student['id'],
                'person_type'        => 'student',
                'scan_mode'          => $requestedMode,
                'scan_action'        => 'inactive',
                'status'             => 'danger',
                'identifier_scanned' => $rawCode,
                'student_name'       => $studentName,
                'student_admission_no'=> $studentInfo['admission_number'],
                'class_name'         => $studentInfo['class_name'],
                'response_message'   => "Student is currently {$statusLabel}.",
                'ip_address'         => $ipAddress,
                'date'               => $today
            ]);

            return [
                'success'      => false,
                'action'       => 'inactive_student',
                'badge_status' => 'danger',
                'title'        => '✕ STUDENT INACTIVE',
                'message'      => "Student {$studentName} is marked as {$statusLabel} in the system.",
                'student'      => $studentInfo
            ];
        }

        $studentId   = (int) $student['id'];
        $classId     = (int) ($student['class_id'] ?? 0);
        $debounceMins = (int) setting('attendance_debounce_minutes', 15);
        if ($debounceMins < 1) $debounceMins = 1;

        // 7. Check Today's Attendance State
        $chkStmt = $this->db->prepare(
            "SELECT id, applicant_id, class_id, time_in, time_out, status, alert_sent, timeout_alert_sent
             FROM attendance
             WHERE applicant_id = ? AND date = ?
             LIMIT 1"
        );
        $chkStmt->execute([$studentId, $today]);
        $existing = $chkStmt->fetch(PDO::FETCH_ASSOC);

        // ═══════════════════════════════════════════════════════════════════
        // DETERMINATION: SCAN IN vs SCAN OUT vs AUTO
        // ═══════════════════════════════════════════════════════════════════

        // Decide operation type
        $operation = 'IN';
        if ($requestedMode === 'out') {
            $operation = 'OUT';
        } elseif ($requestedMode === 'in') {
            $operation = 'IN';
        } else {
            // Auto Mode
            if (!$existing) {
                $operation = AttendanceRules::isDismissalTime() ? 'OUT' : 'IN';
            } elseif (empty($existing['time_out'])) {
                $timeInSec = !empty($existing['time_in']) ? strtotime($today . ' ' . $existing['time_in']) : (time() - 3600);
                $diffMins  = (int) round((time() - $timeInSec) / 60);
                if ($diffMins < $debounceMins && !AttendanceRules::isDismissalTime()) {
                    $operation = 'DUPLICATE_IN';
                } else {
                    $operation = 'OUT';
                }
            } else {
                $operation = 'DUPLICATE_OUT';
            }
        }

        // ── EXECUTE SCAN IN ────────────────────────────────────────────────
        if ($operation === 'IN') {
            if ($existing && !empty($existing['time_in'])) {
                // Already checked in
                $timeInFormatted = date('g:i A', strtotime($today . ' ' . $existing['time_in']));
                
                $this->logScanEvent([
                    'school_id'          => $schoolId,
                    'assignment_id'      => $assignmentId,
                    'staff_id'           => $staffId,
                    'station_id'         => $stationId,
                    'student_id'         => $studentId,
                    'person_type'        => 'student',
                    'scan_mode'          => $requestedMode,
                    'scan_action'        => 'duplicate_in',
                    'status'             => 'warning',
                    'identifier_scanned' => $rawCode,
                    'attendance_id'      => (int) $existing['id'],
                    'student_name'       => $studentName,
                    'student_admission_no'=> $studentInfo['admission_number'],
                    'class_name'         => $studentInfo['class_name'],
                    'response_message'   => "Student is already checked in at {$timeInFormatted}.",
                    'ip_address'         => $ipAddress,
                    'date'               => $today
                ]);

                return [
                    'success'      => false,
                    'action'       => 'duplicate_in',
                    'badge_status' => 'warning',
                    'title'        => '⚠ ALREADY CHECKED IN',
                    'message'      => "{$studentName} is already checked in today at {$timeInFormatted}.",
                    'status'       => $existing['status'],
                    'time'         => $timeInFormatted,
                    'student'      => $studentInfo
                ];
            }

            // Record Check-in
            $resolvedStatus = AttendanceRules::resolveCurrentStatus();
            if ($resolvedStatus === 'Denied') {
                $allowLate = (bool) (int) setting('attendance_allow_late_after_close', 1);
                $resolvedStatus = $allowLate ? 'Late' : 'Denied';
            }

            if ($resolvedStatus === 'Denied') {
                return [
                    'success'      => false,
                    'action'       => 'denied',
                    'badge_status' => 'danger',
                    'title'        => '✕ ENTRY DENIED',
                    'message'      => "Attendance gate window is closed for today ({$nowTime}).",
                    'time'         => date('g:i A', strtotime($nowTime)),
                    'student'      => $studentInfo
                ];
            }

            $this->db->beginTransaction();
            $adminMarkedBy = !empty($officerContext['is_admin']) ? (int)$officerContext['admin_id'] : null;
            try {
                if ($existing) {
                    $upd = $this->db->prepare(
                        "UPDATE attendance 
                         SET time_in = ?, status = ?, scan_method = 'qr_usb', marked_by = ?, updated_at = NOW() 
                         WHERE id = ?"
                    );
                    $upd->execute([$nowTime, $resolvedStatus, $adminMarkedBy, $existing['id']]);
                    $attendanceId = (int) $existing['id'];
                } else {
                    $ins = $this->db->prepare(
                        "INSERT INTO attendance (applicant_id, class_id, school_id, date, time_in, status, scan_method, alert_sent, marked_by, created_at)
                         VALUES (?, ?, ?, ?, ?, ?, 'qr_usb', 0, ?, NOW())"
                    );
                    $ins->execute([$studentId, $classId, $schoolId, $today, $nowTime, $resolvedStatus, $adminMarkedBy]);
                    $attendanceId = (int) $this->db->lastInsertId();
                }
                $this->db->commit();
            } catch (Throwable $e) {
                $this->db->rollBack();
                return [
                    'success'      => false,
                    'action'       => 'error',
                    'badge_status' => 'danger',
                    'title'        => 'DATABASE ERROR',
                    'message'      => 'Failed to save attendance: ' . $e->getMessage()
                ];
            }

            // Dispatch Check-in Notification
            AttendanceService::dispatchCheckinNotification($student, $nowTime, $resolvedStatus, $attendanceId);

            $smsEnabled = (int) setting('attendance_sms_enabled', 1);
            $emailEnabled = (int) setting('attendance_email_enabled', 1);

            $this->logScanEvent([
                'school_id'          => $schoolId,
                'assignment_id'      => $assignmentId,
                'staff_id'           => $staffId,
                'station_id'         => $stationId,
                'student_id'         => $studentId,
                'person_type'        => 'student',
                'scan_mode'          => $requestedMode,
                'scan_action'        => 'check_in',
                'status'             => 'success',
                'identifier_scanned' => $rawCode,
                'attendance_id'      => $attendanceId,
                'student_name'       => $studentName,
                'student_admission_no'=> $studentInfo['admission_number'],
                'class_name'         => $studentInfo['class_name'],
                'sms_status'         => $smsEnabled ? 'sent' : 'skipped',
                'email_status'       => $emailEnabled ? 'sent' : 'skipped',
                'response_message'   => "Student checked in as {$resolvedStatus}.",
                'ip_address'         => $ipAddress,
                'date'               => $today
            ]);

            return [
                'success'             => true,
                'action'              => 'check_in',
                'badge_status'        => 'success',
                'title'               => '✓ ENTRY RECORDED',
                'message'             => "The student has been successfully checked in ({$resolvedStatus}).",
                'status'              => $resolvedStatus,
                'time'                => date('g:i:s A', strtotime($nowTime)),
                'student'             => $studentInfo,
                'notifications'       => [
                    'sms'   => $smsEnabled ? 'SMS Sent' : 'SMS Disabled',
                    'email' => $emailEnabled ? 'Email Sent' : 'Email Disabled'
                ]
            ];
        }

        // ── EXECUTE SCAN OUT ───────────────────────────────────────────────
        if ($operation === 'OUT') {
            if ($existing && !empty($existing['time_out'])) {
                // Already checked out
                $timeOutFormatted = date('g:i A', strtotime($today . ' ' . $existing['time_out']));

                $this->logScanEvent([
                    'school_id'          => $schoolId,
                    'assignment_id'      => $assignmentId,
                    'staff_id'           => $staffId,
                    'station_id'         => $stationId,
                    'student_id'         => $studentId,
                    'person_type'        => 'student',
                    'scan_mode'          => $requestedMode,
                    'scan_action'        => 'duplicate_out',
                    'status'             => 'warning',
                    'identifier_scanned' => $rawCode,
                    'attendance_id'      => (int) $existing['id'],
                    'student_name'       => $studentName,
                    'student_admission_no'=> $studentInfo['admission_number'],
                    'class_name'         => $studentInfo['class_name'],
                    'response_message'   => "Student already checked out today at {$timeOutFormatted}.",
                    'ip_address'         => $ipAddress,
                    'date'               => $today
                ]);

                return [
                    'success'      => false,
                    'action'       => 'duplicate_out',
                    'badge_status' => 'warning',
                    'title'        => '⚠ ALREADY CHECKED OUT',
                    'message'      => "{$studentName} already checked out today at {$timeOutFormatted}. No further exit scan required.",
                    'status'       => 'Checked Out',
                    'time'         => $timeOutFormatted,
                    'student'      => $studentInfo
                ];
            }

            $this->db->beginTransaction();
            $adminMarkedBy = !empty($officerContext['is_admin']) ? (int)$officerContext['admin_id'] : null;
            $exitLogId = 0;
            try {
                if ($existing) {
                    $upd = $this->db->prepare(
                        "UPDATE attendance
                         SET time_out = ?,
                             time_in = COALESCE(time_in, ?),
                             status = CASE WHEN status = 'Absent' THEN 'Present' ELSE status END,
                             scan_method = 'qr_usb'
                         WHERE id = ?"
                    );
                    $upd->execute([$nowTime, $nowTime, $existing['id']]);
                    $attendanceId = (int) $existing['id'];
                } else {
                    $ins = $this->db->prepare(
                        "INSERT INTO attendance (applicant_id, class_id, school_id, date, time_in, time_out, status, scan_method, alert_sent, timeout_alert_sent, marked_by, created_at)
                         VALUES (?, ?, ?, ?, ?, ?, 'Present', 'qr_usb', 1, 0, ?, NOW())"
                    );
                    $ins->execute([$studentId, $classId, $schoolId, $today, $nowTime, $nowTime, $adminMarkedBy]);
                    $attendanceId = (int) $this->db->lastInsertId();
                }

                // Student exit logs entry
                $isEarly = (date('H:i') < AttendanceRules::getDismissalTime());
                $exitType = $isEarly ? 'early' : 'normal';
                $exitReason = $isEarly ? 'Early Departure' : 'School Dismissal';

                $insExit = $this->db->prepare(
                    "INSERT INTO student_exit_logs
                        (school_id, student_id, attendance_id, exit_type, exit_reason, exit_date, exit_time, exited_at, scanned_by, scanned_by_name, scan_method, qr_token, verification_status, sms_status, created_at)
                     VALUES
                        (?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, 'qr_usb', ?, 'verified', 'pending', NOW())"
                );
                $officerName = $officerContext['officer_name'] ?? 'Scanner Officer';
                $insExit->execute([$schoolId, $studentId, $attendanceId, $exitType, $exitReason, $today, $nowTime, $adminMarkedBy, $officerName, $token]);
                $exitLogId = (int) $this->db->lastInsertId();

                $this->db->commit();
            } catch (Throwable $e) {
                $this->db->rollBack();
                return [
                    'success'      => false,
                    'action'       => 'error',
                    'badge_status' => 'danger',
                    'title'        => 'DATABASE ERROR',
                    'message'      => 'Failed to save exit checkout: ' . $e->getMessage()
                ];
            }

            // Dispatch Checkout Notification
            $smsEnabled = (int) setting('attendance_checkout_sms_enabled', 1);
            $emailEnabled = (int) setting('attendance_checkout_email_enabled', 1);

            if ($smsEnabled || $emailEnabled) {
                $exitData = [
                    'exit_date'          => $today,
                    'exit_time'          => $nowTime,
                    'exit_type'          => $exitType,
                    'exit_reason'        => $exitReason,
                    'pickup_person_name' => 'Self / Guardian'
                ];
                AttendanceService::dispatchCheckoutNotification($student, $exitData, $attendanceId);
            }

            $this->logScanEvent([
                'school_id'          => $schoolId,
                'assignment_id'      => $assignmentId,
                'staff_id'           => $staffId,
                'station_id'         => $stationId,
                'student_id'         => $studentId,
                'person_type'        => 'student',
                'scan_mode'          => $requestedMode,
                'scan_action'        => 'check_out',
                'status'             => 'success',
                'identifier_scanned' => $rawCode,
                'attendance_id'      => $attendanceId,
                'exit_log_id'        => $exitLogId,
                'student_name'       => $studentName,
                'student_admission_no'=> $studentInfo['admission_number'],
                'class_name'         => $studentInfo['class_name'],
                'sms_status'         => $smsEnabled ? 'sent' : 'skipped',
                'email_status'       => $emailEnabled ? 'sent' : 'skipped',
                'response_message'   => "Student departure checked out at " . date('g:i A', strtotime($nowTime)),
                'ip_address'         => $ipAddress,
                'date'               => $today
            ]);

            return [
                'success'             => true,
                'action'              => 'check_out',
                'badge_status'        => 'primary',
                'title'               => '✓ EXIT RECORDED',
                'message'             => "The student departure has been successfully logged.",
                'status'              => 'Checked Out',
                'time'                => date('g:i:s A', strtotime($nowTime)),
                'student'             => $studentInfo,
                'notifications'       => [
                    'sms'   => $smsEnabled ? 'SMS Sent' : 'SMS Disabled',
                    'email' => $emailEnabled ? 'Email Sent' : 'Email Disabled'
                ]
            ];
        }

        // Duplicate fallbacks
        if ($operation === 'DUPLICATE_IN') {
            $timeInFormatted = date('g:i A', strtotime($today . ' ' . $existing['time_in']));
            return [
                'success'      => false,
                'action'       => 'duplicate_in',
                'badge_status' => 'warning',
                'title'        => '⚠ ALREADY CHECKED IN',
                'message'      => "{$studentName} is already checked in today at {$timeInFormatted}.",
                'status'       => $existing['status'],
                'time'         => $timeInFormatted,
                'student'      => $studentInfo
            ];
        }

        if ($operation === 'DUPLICATE_OUT') {
            $timeOutFormatted = date('g:i A', strtotime($today . ' ' . $existing['time_out']));
            return [
                'success'      => false,
                'action'       => 'duplicate_out',
                'badge_status' => 'warning',
                'title'        => '⚠ ALREADY CHECKED OUT',
                'message'      => "{$studentName} already checked out today at {$timeOutFormatted}.",
                'status'       => 'Checked Out',
                'time'         => $timeOutFormatted,
                'student'      => $studentInfo
            ];
        }

        return [
            'success'      => false,
            'action'       => 'error',
            'badge_status' => 'danger',
            'title'        => 'UNKNOWN STATE',
            'message'      => 'Attendance scan could not be resolved.'
        ];
    }

    /* ═══════════════════════════════════════════════════════════════════════
       4. SCANNER LOGS & REPORTING
       ═══════════════════════════════════════════════════════════════════════ */

    /**
     * Record a scan event into `scanner_logs`.
     */
    public function logScanEvent(array $data): void
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO scanner_logs
                    (school_id, assignment_id, staff_id, station_id, student_id, person_type, scan_mode, scan_action,
                     status, identifier_scanned, attendance_id, exit_log_id, student_name, student_admission_no,
                     class_name, sms_status, email_status, response_message, ip_address, date, scanned_at)
                 VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $stmt->execute([
                $data['school_id'] ?? 1,
                $data['assignment_id'] ?? null,
                $data['staff_id'] ?? null,
                $data['station_id'] ?? null,
                $data['student_id'] ?? null,
                $data['person_type'] ?? 'student',
                $data['scan_mode'] ?? 'auto',
                $data['scan_action'] ?? 'check_in',
                $data['status'] ?? 'success',
                $data['identifier_scanned'] ?? null,
                $data['attendance_id'] ?? null,
                $data['exit_log_id'] ?? null,
                $data['student_name'] ?? null,
                $data['student_admission_no'] ?? null,
                $data['class_name'] ?? null,
                $data['sms_status'] ?? 'skipped',
                $data['email_status'] ?? 'skipped',
                $data['response_message'] ?? null,
                $data['ip_address'] ?? null,
                $data['date'] ?? date('Y-m-d')
            ]);
        } catch (Throwable $e) {
            error_log("ScannerService::logScanEvent error: " . $e->getMessage());
        }
    }

    /**
     * Get today's scans for Scanner Officer portal.
     */
    public function getTodayLogs(int $staffId, ?int $schoolId = null, string $filter = 'all', int $limit = 50): array
    {
        $schoolId = $schoolId ?? SchoolContext::id() ?? 1;
        $today = date('Y-m-d');

        $sql = "SELECT sl.*, st.station_name 
                FROM scanner_logs sl
                LEFT JOIN scanner_stations st ON st.id = sl.station_id
                WHERE sl.school_id = :school_id AND sl.date = :date";
        
        $params = ['school_id' => $schoolId, 'date' => $today];

        // Filter by action
        if ($filter === 'entry' || $filter === 'in') {
            $sql .= " AND sl.scan_action = 'check_in'";
        } elseif ($filter === 'exit' || $filter === 'out') {
            $sql .= " AND sl.scan_action = 'check_out'";
        } elseif ($filter === 'failed' || $filter === 'warning') {
            $sql .= " AND sl.status IN ('warning', 'danger')";
        }

        $sql .= " ORDER BY sl.scanned_at DESC LIMIT " . (int) $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all scanner logs with full audit trail for Admin portal.
     */
    public function getAllScannerLogs(?int $schoolId = null, array $filters = [], int $limit = 100, int $offset = 0): array
    {
        $schoolId = $schoolId ?? SchoolContext::id() ?? 1;

        $sql = "SELECT sl.*, st.station_name, st.station_code,
                       s.first_name AS officer_first_name, s.last_name AS officer_last_name,
                       s.staff_id AS officer_staff_id
                FROM scanner_logs sl
                LEFT JOIN scanner_stations st ON st.id = sl.station_id
                LEFT JOIN staff s ON s.id = sl.staff_id
                WHERE sl.school_id = :school_id";
        
        $params = ['school_id' => $schoolId];

        if (!empty($filters['date_from'])) {
            $sql .= " AND sl.date >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND sl.date <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        if (!empty($filters['station_id'])) {
            $sql .= " AND sl.station_id = :station_id";
            $params['station_id'] = (int) $filters['station_id'];
        }
        if (!empty($filters['staff_id'])) {
            $sql .= " AND sl.staff_id = :staff_id";
            $params['staff_id'] = (int) $filters['staff_id'];
        }
        if (!empty($filters['scan_action'])) {
            $sql .= " AND sl.scan_action = :scan_action";
            $params['scan_action'] = $filters['scan_action'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (sl.student_name LIKE :search OR sl.student_admission_no LIKE :search2 OR sl.identifier_scanned LIKE :search3)";
            $params['search'] = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
            $params['search3'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY sl.scanned_at DESC LIMIT " . (int) $limit . " OFFSET " . (int) $offset;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get scanner metrics and real-time counts.
     */
    public function getScannerStats(?int $schoolId = null, ?int $stationId = null, ?int $staffId = null): array
    {
        $schoolId = $schoolId ?? SchoolContext::id() ?? 1;
        $today = date('Y-m-d');

        // Entries & Exits from attendance
        $attStmt = $this->db->prepare(
            "SELECT 
                SUM(CASE WHEN att.time_in IS NOT NULL THEN 1 ELSE 0 END) AS total_entries,
                SUM(CASE WHEN att.time_out IS NOT NULL THEN 1 ELSE 0 END) AS total_exits
             FROM attendance att
             JOIN applicants a ON a.id = att.applicant_id
             WHERE att.school_id = ? AND att.date = ? AND a.status = 'Enrolled'"
        );
        $attStmt->execute([$schoolId, $today]);
        $attRow = $attStmt->fetch(PDO::FETCH_ASSOC) ?: ['total_entries' => 0, 'total_exits' => 0];

        $entries = (int) ($attRow['total_entries'] ?? 0);
        $exits   = (int) ($attRow['total_exits'] ?? 0);
        $onCampus = max(0, $entries - $exits);

        // Total scans logged today
        $logStmt = $this->db->prepare(
            "SELECT 
                COUNT(*) AS total_scans,
                SUM(CASE WHEN status IN ('warning', 'danger') THEN 1 ELSE 0 END) AS failed_scans
             FROM scanner_logs
             WHERE school_id = ? AND date = ?"
        );
        $logStmt->execute([$schoolId, $today]);
        $logRow = $logStmt->fetch(PDO::FETCH_ASSOC) ?: ['total_scans' => 0, 'failed_scans' => 0];

        return [
            'entries_today' => $entries,
            'exits_today'   => $exits,
            'on_campus'     => $onCampus,
            'total_scans'   => (int) ($logRow['total_scans'] ?? 0),
            'failed_scans'  => (int) ($logRow['failed_scans'] ?? 0),
            'date'          => $today
        ];
    }

    /* ═══════════════════════════════════════════════════════════════════════
       5. HELPER UTILITIES
       ═══════════════════════════════════════════════════════════════════════ */

    /**
     * Sync granular permissions into staff_permissions table.
     */
    private function syncStaffScannerPermissions(int $staffId, array $permissions, int $schoolId): void
    {
        $map = [
            'scanner.access'               => 1,
            'scanner.scan_in'              => !empty($permissions['can_scan_in']) ? 1 : 0,
            'scanner.scan_out'             => !empty($permissions['can_scan_out']) ? 1 : 0,
            'scanner.view_today_logs'      => !empty($permissions['can_view_today_logs']) ? 1 : 0,
            'scanner.view_student_details' => !empty($permissions['can_view_student_details']) ? 1 : 0,
        ];

        foreach ($map as $permName => $granted) {
            $permId = $this->db->query("SELECT id FROM permissions WHERE name = '{$permName}' LIMIT 1")->fetchColumn();
            if ($permId) {
                $this->db->prepare(
                    "INSERT INTO staff_permissions (school_id, staff_id, permission_id, granted, created_at)
                     VALUES (?, ?, ?, ?, NOW())
                     ON DUPLICATE KEY UPDATE granted = VALUES(granted)"
                )->execute([$schoolId, $staffId, (int) $permId, $granted]);
            }
        }
    }
}
