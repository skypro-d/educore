<?php
declare(strict_types=1);

/**
 * StaffAuth — Centralized Staff & Teacher Authorization Service
 *
 * Implements Role-Based and Permission-Based Access Control (RBAC & PBAC)
 * with strict resource-level scoping (Class, Subject, Student, Tenant).
 *
 * @package EduCore
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/SchoolContext.php';
require_once __DIR__ . '/helpers.php';

final class StaffAuth
{
    private static ?array $cachedUser = null;
    private static ?array $cachedPermissions = null;
    private static ?array $cachedClasses = null;
    private static ?array $cachedSubjects = null;

    /**
     * Clear in-memory caches.
     */
    public static function resetCache(): void
    {
        self::$cachedPermissions = null;
        self::$cachedClasses = null;
        self::$cachedSubjects = null;
    }

    /**
     * Get authenticated staff session details.
     */
    public static function user(): ?array
    {
        return $_SESSION['teacher'] ?? null;
    }

    /**
     * Check if a staff member is authenticated.
     */
    public static function check(): bool
    {
        return !empty($_SESSION['teacher']['id']);
    }

    /**
     * Get the primary staff table ID (staff.id).
     */
    public static function id(): int
    {
        return (int) ($_SESSION['teacher']['staff_table_id'] ?? 0);
    }

    /**
     * Get the staff account ID (staff_accounts.id).
     */
    public static function accountId(): int
    {
        return (int) ($_SESSION['teacher']['id'] ?? 0);
    }

    /**
     * Get current staff role name.
     */
    public static function role(): string
    {
        return (string) ($_SESSION['teacher']['role'] ?? 'Teacher');
    }

    /**
     * Check if staff has a specific role or one of multiple roles.
     *
     * @param string|array $roles
     */
    public static function hasRole(string|array $roles): bool
    {
        $currentRole = strtolower(str_replace(' ', '_', self::role()));
        $roleList = is_array($roles) ? $roles : [$roles];
        foreach ($roleList as $r) {
            $check = strtolower(str_replace(' ', '_', $r));
            if ($currentRole === $check) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if the authenticated staff member has school-wide administrative authority.
     */
    public static function isSchoolAdmin(): bool
    {
        return self::hasRole(['super_admin', 'proprietor', 'principal']);
    }

    /**
     * Check if staff has a specific granular permission.
     * Evaluates super_admin bypass, role default permissions, and staff_permissions overrides.
     */
    public static function can(string $permission): bool
    {
        if (!self::check()) {
            return false;
        }

        // Full system admin bypass
        if (self::hasRole(['super_admin', 'proprietor'])) {
            return true;
        }

        $permissions = self::permissions();
        return in_array($permission, $permissions, true);
    }

    /**
     * Enforce a granular permission. Aborts with 403 Forbidden if not allowed.
     */
    public static function requirePermission(string $permission): void
    {
        self::requireAuth();

        if (!self::can($permission)) {
            http_response_code(403);
            if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
                header('Content-Type: application/json');
                die(json_encode(['success' => false, 'message' => "Access Denied: Missing '{$permission}' permission."]));
            }
            flash('danger', "Access Denied: You do not have permission to perform this action ({$permission}).");
            redirect('teacher/dashboard');
        }
    }

    /**
     * Ensure staff is authenticated and does not have a pending forced password change.
     */
    public static function requireAuth(bool $checkPasswordForce = true): void
    {
        if (!self::check()) {
            redirect('teacher/login');
        }

        if ($checkPasswordForce) {
            try {
                $db = Database::connect();
                $stmt = $db->prepare("SELECT must_change_password FROM staff_accounts WHERE id = ?");
                $stmt->execute([self::accountId()]);
                if ((bool) $stmt->fetchColumn()) {
                    redirect('teacher/change-password');
                }
            } catch (Throwable $e) {}
        }
    }

    /**
     * Retrieve list of assigned class IDs for the logged in staff member.
     *
     * @return int[]
     */
    public static function assignedClassIds(): array
    {
        if (self::$cachedClasses !== null) {
            return self::$cachedClasses;
        }

        $staffId = self::id();
        if ($staffId <= 0) {
            return [];
        }

        // School-wide admins have access to all active classes
        if (self::isSchoolAdmin()) {
            try {
                $db = Database::connect();
                $classes = $db->query("SELECT id FROM classes ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_COLUMN);
                self::$cachedClasses = array_map('intval', $classes);
                return self::$cachedClasses;
            } catch (Throwable $e) {
                return [];
            }
        }

        // Check session cached classes
        if (isset($_SESSION['teacher']['assigned_classes']) && is_array($_SESSION['teacher']['assigned_classes'])) {
            self::$cachedClasses = array_map('intval', $_SESSION['teacher']['assigned_classes']);
            return self::$cachedClasses;
        }

        try {
            $db = Database::connect();
            $year = current_academic_year();
            $stmt = $db->prepare(
                "SELECT DISTINCT class_id FROM staff_class_assignments WHERE staff_id = ? AND academic_year = ?"
            );
            $stmt->execute([$staffId, $year]);
            $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            self::$cachedClasses = array_map('intval', $ids);
            return self::$cachedClasses;
        } catch (Throwable $e) {
            return [];
        }
    }

    /**
     * Check if staff is authorized to access a given class.
     */
    public static function canAccessClass(int $classId): bool
    {
        if ($classId <= 0) {
            return false;
        }
        if (self::isSchoolAdmin()) {
            return true;
        }
        return in_array($classId, self::assignedClassIds(), true);
    }

    /**
     * Require access to a class; terminates with 403 or redirect if unauthorized.
     */
    public static function requireClass(int $classId): void
    {
        self::requireAuth();

        if (!self::canAccessClass($classId)) {
            http_response_code(403);
            if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
                header('Content-Type: application/json');
                die(json_encode(['success' => false, 'message' => 'Access Denied: You are not assigned to this class.']));
            }
            flash('danger', 'Access Denied: You are not authorized to view or manage this class.');
            redirect('teacher/classes');
        }
    }

    /**
     * Check if staff is assigned to teach a specific subject in a specific class.
     */
    public static function canAccessSubject(int $classId, int $subjectId): bool
    {
        if ($classId <= 0 || $subjectId <= 0) {
            return false;
        }
        if (self::isSchoolAdmin()) {
            return true;
        }

        $staffId = self::id();
        $year = current_academic_year();

        try {
            $db = Database::connect();
            $stmt = $db->prepare(
                "SELECT 1 FROM staff_class_assignments 
                 WHERE staff_id = ? AND class_id = ? 
                   AND (subject_id = ? OR is_form_teacher = 1) 
                   AND academic_year = ? LIMIT 1"
            );
            $stmt->execute([$staffId, $classId, $subjectId, $year]);
            return (bool) $stmt->fetchColumn();
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Require assignment to a specific subject in a specific class.
     */
    public static function requireSubject(int $classId, int $subjectId): void
    {
        self::requireAuth();

        if (!self::canAccessSubject($classId, $subjectId)) {
            http_response_code(403);
            if (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json')) {
                header('Content-Type: application/json');
                die(json_encode(['success' => false, 'message' => 'Access Denied: You are not assigned to this subject.']));
            }
            flash('danger', 'Access Denied: You are not assigned to teach this subject in the selected class.');
            redirect('teacher/results');
        }
    }

    /**
     * Check if staff is authorized to access a specific student.
     * Verifies tenant isolation and class assignment.
     */
    public static function canAccessStudent(int $studentId): bool
    {
        if ($studentId <= 0) {
            return false;
        }

        try {
            $db = Database::connect();
            $stmt = $db->prepare(
                "SELECT class_id, status, student_status FROM applicants WHERE id = ? LIMIT 1"
            );
            $stmt->execute([$studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student) {
                return false;
            }

            if ($student['status'] !== 'Enrolled') {
                return false;
            }

            return self::canAccessClass((int) $student['class_id']);
        } catch (Throwable $e) {
            return false;
        }
    }

    /**
     * Require student access; returns student data or halts with 403.
     */
    public static function requireStudent(int $studentId): array
    {
        self::requireAuth();

        try {
            $db = Database::connect();
            $stmt = $db->prepare(
                "SELECT a.*, c.name AS class_name 
                 FROM applicants a 
                 LEFT JOIN classes c ON c.id = a.class_id 
                 WHERE a.id = ? LIMIT 1"
            );
            $stmt->execute([$studentId]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$student || $student['status'] !== 'Enrolled') {
                http_response_code(404);
                flash('danger', 'Student record not found.');
                redirect('teacher/students');
            }

            if (!self::canAccessClass((int) $student['class_id'])) {
                http_response_code(403);
                flash('danger', 'Access Denied: This student does not belong to your authorized classes.');
                redirect('teacher/students');
            }

            return $student;
        } catch (Throwable $e) {
            http_response_code(500);
            flash('danger', 'Error loading student profile: ' . $e->getMessage());
            redirect('teacher/students');
        }
    }

    /**
     * Retrieve all active permissions for the currently authenticated staff member.
     *
     * @return string[]
     */
    public static function permissions(): array
    {
        if (self::$cachedPermissions !== null) {
            return self::$cachedPermissions;
        }

        if (isset($_SESSION['teacher']['permissions']) && is_array($_SESSION['teacher']['permissions'])) {
            self::$cachedPermissions = $_SESSION['teacher']['permissions'];
            return self::$cachedPermissions;
        }

        $staffId = self::id();
        $roleName = self::role();

        self::$cachedPermissions = self::loadStaffPermissions($staffId, $roleName);
        $_SESSION['teacher']['permissions'] = self::$cachedPermissions;

        return self::$cachedPermissions;
    }

    /**
     * Compute permissions from role defaults plus custom staff overrides.
     *
     * @param int    $staffId
     * @param string $roleName
     * @return string[]
     */
    public static function loadStaffPermissions(int $staffId, string $roleName): array
    {
        try {
            $db = Database::connect();
            $normalizedRole = strtolower(str_replace(' ', '_', $roleName));

            // 1. Role Default Permissions
            $stmtRole = $db->prepare(
                "SELECT p.name 
                 FROM role_permissions rp
                 JOIN roles r ON r.id = rp.role_id
                 JOIN permissions p ON p.id = rp.permission_id
                 WHERE LOWER(REPLACE(r.name, ' ', '_')) = ?"
            );
            $stmtRole->execute([$normalizedRole]);
            $rolePerms = $stmtRole->fetchAll(PDO::FETCH_COLUMN);

            $permSet = array_flip($rolePerms);

            // 2. Staff Specific Overrides (granted = 1 adds, granted = 0 revokes)
            $stmtOverrides = $db->prepare(
                "SELECT p.name, sp.granted 
                 FROM staff_permissions sp
                 JOIN permissions p ON p.id = sp.permission_id
                 WHERE sp.staff_id = ?"
            );
            $stmtOverrides->execute([$staffId]);
            $overrides = $stmtOverrides->fetchAll(PDO::FETCH_ASSOC);

            foreach ($overrides as $ov) {
                $pName = $ov['name'];
                if ((int) $ov['granted'] === 1) {
                    $permSet[$pName] = true;
                } else {
                    unset($permSet[$pName]);
                }
            }

            return array_values(array_keys($permSet));
        } catch (Throwable $e) {
            error_log("StaffAuth::loadStaffPermissions failed: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Refresh the cached permissions and session.
     */
    public static function refreshSession(): void
    {
        self::$cachedPermissions = null;
        self::$cachedClasses = null;
        self::$cachedSubjects = null;
        if (self::check()) {
            $_SESSION['teacher']['permissions'] = self::loadStaffPermissions(self::id(), self::role());
        }
    }
}
