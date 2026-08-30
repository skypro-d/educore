<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/SchoolContext.php';

/**
 * TenantPDO — Custom PDO subclass that automatically isolates queries by school_id.
 */
class TenantPDO extends PDO
{
    #[\ReturnTypeWillChange]
    public function prepare($statement, $options = null)
    {
        $options = is_array($options) ? $options : [];
        $rewritten = $this->rewriteSql($statement);
        try {
            return parent::prepare($rewritten, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage() . " | Raw SQL: " . $statement . " | Rewritten SQL: " . $rewritten, (int)$e->getCode(), $e);
        }
    }

    #[\ReturnTypeWillChange]
    public function query($statement, $mode = null, ...$fetchModeArgs)
    {
        $rewritten = $this->rewriteSql($statement);
        try {
            if ($mode === null) {
                return parent::query($rewritten);
            }
            return parent::query($rewritten, $mode, ...$fetchModeArgs);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage() . " | Raw SQL: " . $statement . " | Rewritten SQL: " . $rewritten, (int)$e->getCode(), $e);
        }
    }

    #[\ReturnTypeWillChange]
    public function exec($statement)
    {
        $rewritten = $this->rewriteSql($statement);
        try {
            return parent::exec($rewritten);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage() . " | Raw SQL: " . $statement . " | Rewritten SQL: " . $rewritten, (int)$e->getCode(), $e);
        }
    }

    /**
     * Rewrites SQL to automatically append school_id = context_school_id for multi-tenant isolation.
     */
    private function rewriteSql(string $sql): string
    {
        // SaaS registry and permission tables that should not be isolated by school_id
        $excludePatterns = [
            // Core SaaS / registry tables
            'superadmins', 'schools', 'school_licenses', 'school_subscriptions', 
            'super_audit_logs', 'system_updates', 'school_update_log', 
            'support_tickets', 'support_ticket_replies', 'roles', 'permissions', 
            'role_permissions',
            // Customer Portal tables
            'customer_accounts', 'customer_invoices', 'customer_domains',
            'customer_downloads_log', 'customer_marketplace_orders',
            // Platform / SaaS settings
            'platform_settings', 'app_configs', 'school_settings', 'system_settings',
            // Software release center
            'software_releases',
            // Marketplace
            'marketplace_products', 'marketplace_transactions',
            // Subscription reminders
            'subscription_reminder_log',
            // Coupons (global)
            'coupons',
        ];

        foreach ($excludePatterns as $pattern) {
            if (stripos($sql, $pattern) !== false) {
                return $sql;
            }
        }

        // List of database tables that contain a school_id column
        $tenantTables = [
            'activity_logs', 'admins', 'admission_letters', 'admissions', 'announcements', 
            'applicants', 'attendance', 'audit_logs', 'classes', 'documents', 'email_logs', 
            'exam_questions', 'exam_results', 'exam_subjects', 'fee_structures', 'interviews', 
            'inventory_items', 'inventory_transactions', 'library_books', 'library_borrowings', 
            'notifications', 'parent_accounts', 'parents', 'payments', 'promotion_history', 
            'school_gates', 'sms_logs', 'staff', 'staff_accounts', 'staff_attendance', 'staff_class_assignments', 
            'student_accounts', 'student_authorized_pickups', 'student_exit_logs', 'student_fee_payments', 'student_results', 'student_transport', 
            'subjects', 'term_remarks', 'timetables', 'transport_buses', 'transport_routes'
        ];

        // If the query already explicitly mentions school_id, do not modify it
        if (stripos($sql, 'school_id') !== false) {
            return $sql;
        }

        $schoolId = SchoolContext::id();
        $trimmed = trim($sql);

        // ── 1. SELECT Query ──
        if (stripos($trimmed, 'SELECT') === 0) {
            $fromPos = stripos($trimmed, 'FROM');
            if ($fromPos !== false) {
                $afterFrom = substr($trimmed, $fromPos + 4);
                $endPos = strlen($afterFrom);
                $splitKeywords = ['JOIN', 'LEFT', 'RIGHT', 'INNER', 'WHERE', 'ORDER BY', 'GROUP BY', 'LIMIT', 'UNION', ')'];
                foreach ($splitKeywords as $kw) {
                    $pos = stripos($afterFrom, $kw);
                    if ($pos !== false && $pos < $endPos) {
                        $endPos = $pos;
                    }
                }
                
                $tableRef = trim(substr($afterFrom, 0, $endPos));
                $parts = preg_split('/\s+/', $tableRef);
                if (!empty($parts[0])) {
                    $table = strtolower(str_replace('`', '', $parts[0]));
                    if (in_array($table, $tenantTables, true)) {
                        $alias = $parts[0];
                        if (count($parts) > 1) {
                            $second = strtolower($parts[1]);
                            if ($second === 'as' && count($parts) > 2) {
                                $alias = $parts[2];
                            } else if ($second !== 'join' && $second !== 'left' && $second !== 'right' && $second !== 'inner') {
                                $alias = $parts[1];
                            }
                        }
                        
                        $alias = str_replace('`', '', $alias);
                        $qualifier = "`{$alias}`.school_id = " . $schoolId;
                        
                        if (stripos($trimmed, 'WHERE') !== false) {
                            return preg_replace('/WHERE/i', 'WHERE ' . $qualifier . ' AND ', $trimmed, 1);
                        } else {
                            $insertPos = strlen($trimmed);
                            $keywords = ['ORDER BY', 'GROUP BY', 'LIMIT', 'UNION'];
                            foreach ($keywords as $kw) {
                                        $pos = stripos($trimmed, $kw);
                                if ($pos !== false && $pos < $insertPos) {
                                    $insertPos = $pos;
                                }
                            }
                            return substr($trimmed, 0, $insertPos) . ' WHERE ' . $qualifier . ' ' . substr($trimmed, $insertPos);
                        }
                    }
                }
            }
        }

        // ── 2. INSERT Query ──
        if (stripos($trimmed, 'INSERT') === 0) {
            if (preg_match('/INSERT\s+INTO\s+`?([a-zA-Z0-9_]+)`?\s*\(([^)]+)\)\s*VALUES\s*\((.+)\)/is', $trimmed, $matches)) {
                $table = strtolower($matches[1]);
                if (in_array($table, $tenantTables, true)) {
                    $cols = $matches[2];
                    $vals = $matches[3];
                    return "INSERT INTO `{$matches[1]}` (school_id, {$cols}) VALUES ({$schoolId}, {$vals})";
                }
            }
        }

        // ── 3. UPDATE Query ──
        if (stripos($trimmed, 'UPDATE') === 0) {
            if (preg_match('/UPDATE\s+`?([a-zA-Z0-9_]+)`?/i', $trimmed, $matches)) {
                $table = strtolower($matches[1]);
                if (in_array($table, $tenantTables, true)) {
                    if (stripos($trimmed, 'WHERE') !== false) {
                        return preg_replace('/WHERE/i', 'WHERE school_id = ' . $schoolId . ' AND ', $trimmed, 1);
                    } else {
                        return $trimmed . ' WHERE school_id = ' . $schoolId;
                    }
                }
            }
        }

        // ── 4. DELETE Query ──
        if (stripos($trimmed, 'DELETE') === 0) {
            if (preg_match('/DELETE\s+FROM\s+`?([a-zA-Z0-9_]+)`?/i', $trimmed, $matches)) {
                $table = strtolower($matches[1]);
                if (in_array($table, $tenantTables, true)) {
                    if (stripos($trimmed, 'WHERE') !== false) {
                        return preg_replace('/WHERE/i', 'WHERE school_id = ' . $schoolId . ' AND ', $trimmed, 1);
                    } else {
                        return $trimmed . ' WHERE school_id = ' . $schoolId;
                    }
                }
            }
        }

        return $sql;
    }
}

final class Database
{
    private static ?TenantPDO $pdo = null;

    public static function connect(): TenantPDO
    {
        if (self::$pdo === null) {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            try {
                self::$pdo = new TenantPDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                ]);
            } catch (PDOException $e) {
                // If on localhost and connection to cPanel db fails, try fallback to local DB
                $hostName = strtolower($_SERVER['HTTP_HOST'] ?? 'localhost');
                $isLocalHost = str_starts_with($hostName, 'localhost')
                    || str_starts_with($hostName, '127.0.0.1')
                    || str_starts_with($hostName, '[::1]');
                
                if ($isLocalHost && DB_NAME !== 'school_admission_portal') {
                    try {
                        $fallbackDsn = 'mysql:host=localhost;dbname=' . DB_NAME . ';charset=utf8mb4';
                        self::$pdo = new TenantPDO($fallbackDsn, 'root', '', [
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                            PDO::ATTR_EMULATE_PREPARES => false,
                            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                        ]);
                    } catch (PDOException $fallbackException) {
                        throw $e; // Throw original exception if fallback also fails
                    }
                } else {
                    throw $e;
                }
            }
        }

        return self::$pdo;
    }
}
