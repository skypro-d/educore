<?php
declare(strict_types=1);

/**
 * StaffAudit — Centralized Audit Logging Service for Staff Operations
 *
 * Records all critical staff actions, data modifications, attendance markings,
 * grade updates, and administrative assignments with full context.
 *
 * @package EduCore
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/SchoolContext.php';

final class StaffAudit
{
    /**
     * Record an audit log entry for staff actions.
     *
     * @param string      $action        Action identifier (e.g. 'attendance.marked', 'results.submitted')
     * @param string      $resourceType  Type of resource (e.g. 'attendance', 'student_results', 'assignment')
     * @param int|null    $resourceId    ID of the affected resource
     * @param string|null $details       Human-readable summary of the action
     * @param string|null $previousValue Serialized or text previous state
     * @param string|null $newValue      Serialized or text new state
     * @param int|null    $staffId       Staff table ID (defaults to current session staff)
     * @return bool
     */
    public static function log(
        string $action,
        string $resourceType,
        ?int $resourceId = null,
        ?string $details = null,
        ?string $previousValue = null,
        ?string $newValue = null,
        ?int $staffId = null
    ): bool {
        try {
            $db = Database::connect();
            $schoolId = SchoolContext::id();

            if ($staffId === null && isset($_SESSION['teacher']['staff_table_id'])) {
                $staffId = (int) $_SESSION['teacher']['staff_table_id'];
            }

            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            $stmt = $db->prepare(
                "INSERT INTO staff_audit_logs (school_id, staff_id, action, resource_type, resource_id, details, previous_value, new_value, ip_address, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
            );

            return $stmt->execute([
                $schoolId,
                $staffId,
                $action,
                $resourceType,
                $resourceId,
                $details,
                $previousValue,
                $newValue,
                $ip,
            ]);
        } catch (Throwable $e) {
            error_log("StaffAudit::log failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch recent audit logs for a staff member or school.
     *
     * @param int|null $staffId
     * @param int      $limit
     * @return array
     */
    public static function getRecent(?int $staffId = null, int $limit = 50): array
    {
        try {
            $db = Database::connect();
            if ($staffId !== null && $staffId > 0) {
                $stmt = $db->prepare(
                    "SELECT sal.*, s.first_name, s.last_name, s.staff_id AS public_staff_id
                     FROM staff_audit_logs sal
                     LEFT JOIN staff s ON s.id = sal.staff_id
                     WHERE sal.staff_id = ?
                     ORDER BY sal.created_at DESC LIMIT ?"
                );
                $stmt->bindValue(1, $staffId, PDO::PARAM_INT);
                $stmt->bindValue(2, $limit, PDO::PARAM_INT);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            $stmt = $db->prepare(
                "SELECT sal.*, s.first_name, s.last_name, s.staff_id AS public_staff_id
                 FROM staff_audit_logs sal
                 LEFT JOIN staff s ON s.id = sal.staff_id
                 ORDER BY sal.created_at DESC LIMIT ?"
            );
            $stmt->bindValue(1, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("StaffAudit::getRecent failed: " . $e->getMessage());
            return [];
        }
    }
}
