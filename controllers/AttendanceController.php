<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/SmsService.php';
require_once __DIR__ . '/../config/AttendanceRules.php';
require_once __DIR__ . '/../models/Applicant.php';
require_once __DIR__ . '/../models/ClassModel.php';
require_once __DIR__ . '/../services/AttendanceService.php';

final class AttendanceController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function index(): void
    {
        require_admin();
        $classes = (new ClassModel($this->db))->all();
        $classId = (int) ($_GET['class_id'] ?? 0);
        $date    = $_GET['date'] ?? date('Y-m-d');

        $students = [];
        $existing = [];

        if ($classId) {
            $stmt = $this->db->prepare(
                "SELECT a.id, a.first_name, a.last_name, a.application_number, a.passport_photo
                 FROM applicants a
                 WHERE a.class_id=? AND a.status='Enrolled'
                 ORDER BY a.last_name, a.first_name"
            );
            $stmt->execute([$classId]);
            $students = $stmt->fetchAll();

            $stmt2 = $this->db->prepare(
                "SELECT applicant_id, status, remark, time_in, time_out FROM attendance WHERE class_id=? AND date=?"
            );
            $stmt2->execute([$classId, $date]);
            foreach ($stmt2->fetchAll() as $row) {
                $existing[$row['applicant_id']] = $row;
            }
        }

        render('admin/attendance', compact('classes', 'classId', 'date', 'students', 'existing'), 'admin');
    }

    public function save(): void
    {
        require_admin();
        verify_csrf();
        $classId   = (int) ($_POST['class_id'] ?? 0);
        $date      = $_POST['date'] ?? date('Y-m-d');
        $statuses  = $_POST['status'] ?? [];
        $remarks   = $_POST['remark'] ?? [];
        $adminId   = (int) ($_SESSION['admin']['id'] ?? 0);

        // Determine the current time string for time-rule application
        $nowTime = date('H:i');

        foreach ($statuses as $applicantId => $status) {
            $applicantId = (int) $applicantId;
            $remark      = trim($remarks[$applicantId] ?? '');

            // Apply time rules to manually-marked Present statuses (for today only)
            if ($status === 'Present' && $date === date('Y-m-d')) {
                $resolved = AttendanceRules::resolveStatus($nowTime);
                if ($resolved === 'Late') {
                    $status = 'Late';
                }
                // If 'Denied' we still allow admin override — admin can force-mark
            }

            $this->db->prepare(
                "INSERT INTO attendance (applicant_id, class_id, date, time_in, status, remark, marked_by, alert_sent)
                 VALUES (?, ?, ?, ?, ?, ?, ?, 0)
                 ON DUPLICATE KEY UPDATE status=VALUES(status), remark=VALUES(remark),
                     marked_by=VALUES(marked_by), time_in=COALESCE(time_in, VALUES(time_in))"
            )->execute([$applicantId, $classId, $date, $nowTime, $status, $remark, $adminId]);

            // Get the attendance ID for SMS dedup
            $attRow = $this->db->prepare(
                "SELECT id, alert_sent FROM attendance WHERE applicant_id=? AND date=?"
            );
            $attRow->execute([$applicantId, $date]);
            $att = $attRow->fetch();

            if ($att && !$att['alert_sent']) {
                if ($status === 'Present' || $status === 'Late') {
                    // Check-in SMS (only for today — past dates don't need real-time alerts)
                    if ($date === date('Y-m-d')) {
                        $student = $this->fetchStudentForSms($applicantId);
                        if ($student) {
                            AttendanceService::dispatchCheckinNotification($student, $nowTime, $status, (int) $att['id']);
                        }
                    }
                } elseif ($status === 'Absent') {
                    $student = $this->fetchStudentForSms($applicantId);
                    if ($student) {
                        send_absent_sms($this->db, $student, $date, (int) $att['id']);
                    }
                }
            }
        }

        flash('success', 'Attendance saved for ' . date('M j, Y', strtotime($date)) . '.');
        redirect('admin/attendance?class_id=' . $classId . '&date=' . $date);
    }

    public function report(): void
    {
        require_admin();
        $classes = (new ClassModel($this->db))->all();
        $classId = (int) ($_GET['class_id'] ?? 0);
        $month   = (int) ($_GET['month'] ?? date('n'));
        $year    = (int) ($_GET['year'] ?? date('Y'));

        $report = [];
        if ($classId) {
            $stmt = $this->db->prepare(
                "SELECT a.id, a.first_name, a.last_name, a.application_number,
                        SUM(CASE WHEN att.status='Present' THEN 1 ELSE 0 END) AS present,
                        SUM(CASE WHEN att.status='Absent' THEN 1 ELSE 0 END) AS absent,
                        SUM(CASE WHEN att.status='Late' THEN 1 ELSE 0 END) AS late,
                        SUM(CASE WHEN att.status='Excused' THEN 1 ELSE 0 END) AS excused,
                        COUNT(att.id) AS total_days
                 FROM applicants a
                 LEFT JOIN attendance att ON att.applicant_id=a.id
                     AND MONTH(att.date)=? AND YEAR(att.date)=?
                 WHERE a.class_id=? AND a.status='Enrolled'
                 GROUP BY a.id ORDER BY a.last_name"
            );
            $stmt->execute([$month, $year, $classId]);
            $report = $stmt->fetchAll();
        }

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = date('F', mktime(0, 0, 0, $m, 1));
        }

        render('admin/attendance_report', compact('classes', 'classId', 'month', 'year', 'months', 'report'), 'admin');
    }

    /**
     * Auto-absent processing — called by admin panel "Run Now" or cron/Task Scheduler.
     * Marks all enrolled students with no attendance for today as Absent
     * and sends absent SMS to parents.
     *
     * Returns a summary array for display.
     */
    public function processAutoAbsent(?string $date = null): array
    {
        $date    = $date ?? date('Y-m-d');
        $today   = date('Y-m-d');
        $summary = ['processed' => 0, 'skipped' => 0, 'errors' => [], 'date' => $date];

        // Safety: only process today (or explicitly requested past dates)
        if ($date === $today && AttendanceRules::isWindowOpen()) {
            $summary['errors'][] = 'Attendance window is still open. Run after ' .
                AttendanceRules::format(setting('attendance_close_time', '09:00')) . '.';
            return $summary;
        }

        // Find enrolled students with NO attendance record for $date
        $stmt = $this->db->prepare(
            "SELECT a.id, a.first_name, a.last_name, a.parent_phone, a.class_id
             FROM applicants a
             WHERE a.status = 'Enrolled'
               AND a.id NOT IN (
                   SELECT att.applicant_id FROM attendance att WHERE att.date = ?
               )"
        );
        $stmt->execute([$date]);
        $missing = $stmt->fetchAll();

        foreach ($missing as $student) {
            try {
                // Insert absent record
                $ins = $this->db->prepare(
                    "INSERT IGNORE INTO attendance (applicant_id, class_id, date, status, alert_sent, marked_by, created_at)
                     VALUES (?, ?, ?, 'Absent', 0, NULL, NOW())"
                );
                $ins->execute([$student['id'], $student['class_id'] ?? null, $date]);
                $attendanceId = (int) $this->db->lastInsertId();

                // Send absent SMS
                if ($attendanceId > 0) {
                    send_absent_sms($this->db, $student, $date, $attendanceId);
                }

                $summary['processed']++;
            } catch (Throwable $e) {
                $summary['errors'][] = 'Student ID ' . $student['id'] . ': ' . $e->getMessage();
                $summary['skipped']++;
            }
        }

        // Record last run time in settings
        $this->db->prepare(
            "INSERT INTO app_configs (setting_key, setting_value) VALUES ('auto_absent_last_run', ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        )->execute([date('Y-m-d H:i:s')]);

        return $summary;
    }

    /**
     * Admin route handler: POST /admin/attendance-settings/run-auto-absent
     */
    public function runAutoAbsent(): void
    {
        require_permission('attendance');
        verify_csrf();

        $date    = trim($_POST['date'] ?? date('Y-m-d'));
        $summary = $this->processAutoAbsent($date);

        $msg = "Auto-absent run complete for {$date}. "
             . "Marked absent: {$summary['processed']}. "
             . "Skipped: {$summary['skipped']}.";

        if (!empty($summary['errors'])) {
            flash('warning', $msg . ' Errors: ' . implode(' | ', array_slice($summary['errors'], 0, 3)));
        } else {
            flash('success', $msg);
        }

        redirect('admin/attendance-settings#tab-auto-absent');
    }

    /**
     * HIPPOINT X7-1000 USB QR Attendance Kiosk Scanner Page
      /**
     * HIPPOINT X7-1000 USB QR Attendance Kiosk Scanner Page
     */
    public function scanner(): void
    {
        require_admin();
        $today = date('Y-m-d');
        $schoolId = SchoolContext::id() ?? 1;

        // Fetch today's summary stats
        $statsStmt = $this->db->prepare(
            "SELECT
                COUNT(DISTINCT a.id) as total_students,
                SUM(CASE WHEN att.id IS NOT NULL AND att.status IN ('Present', 'Late') THEN 1 ELSE 0 END) as checked_in,
                SUM(CASE WHEN att.id IS NOT NULL AND att.time_out IS NOT NULL THEN 1 ELSE 0 END) as checked_out,
                SUM(CASE WHEN att.status = 'Late' THEN 1 ELSE 0 END) as late_count
             FROM applicants a
             LEFT JOIN attendance att ON att.applicant_id = a.id AND att.date = ?
             WHERE a.status = 'Enrolled' AND (a.student_status IS NULL OR a.student_status = 'Active')"
        );
        $statsStmt->execute([$today]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_students' => 0,
            'checked_in'     => 0,
            'checked_out'    => 0,
            'late_count'     => 0,
        ];
        $stats['on_campus'] = max(0, ((int)$stats['checked_in']) - ((int)$stats['checked_out']));

        // Staff stats today
        $staffStatsStmt = $this->db->prepare(
            "SELECT
                COUNT(DISTINCT s.id) as total_staff,
                SUM(CASE WHEN sa.id IS NOT NULL AND sa.status IN ('Present', 'Late') THEN 1 ELSE 0 END) as staff_checked_in,
                SUM(CASE WHEN sa.id IS NOT NULL AND sa.time_out IS NOT NULL THEN 1 ELSE 0 END) as staff_checked_out,
                SUM(CASE WHEN sa.status = 'Late' THEN 1 ELSE 0 END) as staff_late_count
             FROM staff s
             LEFT JOIN staff_attendance sa ON sa.staff_id = s.id AND sa.date = ?
             WHERE s.status = 'Active'"
        );
        $staffStatsStmt->execute([$today]);
        $staffStats = $staffStatsStmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_staff'       => 0,
            'staff_checked_in'  => 0,
            'staff_checked_out' => 0,
            'staff_late_count'  => 0,
        ];

        // Recent student scans
        $studentScans = $this->db->prepare(
            "SELECT att.id as attendance_id, att.applicant_id, att.time_in, att.time_out, att.status, att.scan_method,
                    s.first_name, s.last_name, s.admission_number, s.application_number, s.passport_photo,
                    c.name as class_name, 'student' as person_type
             FROM attendance att
             JOIN applicants s ON s.id = att.applicant_id
             LEFT JOIN classes c ON c.id = s.class_id
             WHERE att.date = ?"
        );
        $studentScans->execute([$today]);
        $allStudentScans = $studentScans->fetchAll(PDO::FETCH_ASSOC);

        // Recent staff scans
        $staffScans = $this->db->prepare(
            "SELECT sa.id as attendance_id, sa.staff_id as applicant_id, sa.time_in, sa.time_out, sa.status, sa.scan_method,
                    s.first_name, s.last_name, s.staff_id as admission_number, s.staff_id as application_number, s.passport_photo,
                    COALESCE(s.department, 'Faculty') as class_name, 'staff' as person_type
             FROM staff_attendance sa
             JOIN staff s ON s.id = sa.staff_id
             WHERE sa.date = ?"
        );
        $staffScans->execute([$today]);
        $allStaffScans = $staffScans->fetchAll(PDO::FETCH_ASSOC);

        $merged = array_merge($allStudentScans, $allStaffScans);
        usort($merged, function ($a, $b) {
            $tA = max($a['time_out'] ?? '00:00:00', $a['time_in'] ?? '00:00:00');
            $tB = max($b['time_out'] ?? '00:00:00', $b['time_in'] ?? '00:00:00');
            return strcmp($tB, $tA);
        });
        $recentScans = array_slice($merged, 0, 10);

        // Fetch sample enrolled students
        $sampleStudentsStmt = $this->db->prepare(
            "SELECT a.id, a.first_name, a.last_name, a.admission_number, a.qr_data, c.name as class_name
             FROM applicants a
             LEFT JOIN classes c ON c.id = a.class_id
             WHERE a.status = 'Enrolled' AND (a.student_status IS NULL OR a.student_status = 'Active')
             ORDER BY a.id ASC
             LIMIT 4"
        );
        $sampleStudentsStmt->execute();
        $sampleStudents = $sampleStudentsStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch sample staff members
        $sampleStaffStmt = $this->db->prepare(
            "SELECT s.id, s.first_name, s.last_name, s.staff_id, s.qr_data, s.department, r.name as role_title, s.role
             FROM staff s
             LEFT JOIN roles r ON r.id = s.role_id
             WHERE s.status = 'Active'
             ORDER BY s.id ASC
             LIMIT 4"
        );
        $sampleStaffStmt->execute();
        $sampleStaff = $sampleStaffStmt->fetchAll(PDO::FETCH_ASSOC);

        $debounceMins = (int) setting('attendance_debounce_minutes', 15);
        $autoResetSeconds = (int) setting('attendance_scanner_auto_reset_seconds', 3);

        render('admin/attendance_scanner', compact('stats', 'staffStats', 'recentScans', 'sampleStudents', 'sampleStaff', 'today', 'debounceMins', 'autoResetSeconds'), 'admin');
    }

    /**
     * AJAX endpoint for HIPPOINT X7-1000 USB Scanner
     * Processes QR code scans, handles automatic IN/OUT transitions and duplicate scan protection
     */
    public function processScanAjax(): void
    {
        require_admin();
        header('Content-Type: application/json; charset=utf-8');

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $rawCode = trim($input['qr_data'] ?? $input['token'] ?? '');

        if ($rawCode === '') {
            echo json_encode([
                'success'      => false,
                'action'       => 'error',
                'badge_status' => 'danger',
                'title'        => 'No Input',
                'message'      => 'No QR code received from scanner.'
            ]);
            exit;
        }

        // Extract token if URL or mangled keyboard layout URL was scanned
        $token = trim($rawCode);
        if (preg_match('/[?&_\-\^]token[=0\):]([^\s&#\^?]+?)(?:http|$)/i', $rawCode, $matches)) {
            $token = urldecode(rtrim($matches[1], '/'));
        } elseif (preg_match('/[?&]token=([^&#\s]+)/i', $rawCode, $matches)) {
            $token = urldecode(rtrim($matches[1], '/'));
        }

        $token = trim($token);
        $normalizedToken = str_replace('/', '-', $token);

        // ── STEP 1: Check if the scanned token belongs to a STAFF MEMBER ──
        $stmtStaff = $this->db->prepare(
            "SELECT s.*, r.name AS role_title
             FROM staff s
             LEFT JOIN roles r ON r.id = s.role_id
             WHERE (s.qr_data = ? OR s.qr_data = ? OR s.staff_id = ?)
             LIMIT 1"
        );
        $stmtStaff->execute([$token, $normalizedToken, $token]);
        $staffMember = $stmtStaff->fetch(PDO::FETCH_ASSOC);

        if (!$staffMember && preg_match('/attendance[-_\/]stf[-_\/](\d+)/i', $rawCode, $mStaff)) {
            $staffId = (int)$mStaff[1];
            $stmtStaffId = $this->db->prepare(
                "SELECT s.*, r.name AS role_title FROM staff s LEFT JOIN roles r ON r.id = s.role_id WHERE s.id = ? LIMIT 1"
            );
            $stmtStaffId->execute([$staffId]);
            $staffMember = $stmtStaffId->fetch(PDO::FETCH_ASSOC);
        }

        if ($staffMember) {
            $staffId = (int) $staffMember['id'];
            $today = date('Y-m-d');
            $nowTime = date('H:i:s');
            $adminId = (int) ($_SESSION['admin']['id'] ?? 0);
            $staffName = trim($staffMember['first_name'] . ' ' . $staffMember['last_name']);
            $roleTitle = !empty($staffMember['role_title']) ? ucwords(str_replace('_', ' ', $staffMember['role_title'])) : ($staffMember['role'] ?? 'Staff Member');
            $debounceMins = (int) setting('attendance_debounce_minutes', 15);
            if ($debounceMins < 1) $debounceMins = 1;

            $staffInfo = [
                'id'          => $staffId,
                'name'        => $staffName,
                'staff_id'    => $staffMember['staff_id'],
                'role'        => $roleTitle,
                'department'  => $staffMember['department'] ?: 'Academics',
                'photo'       => !empty($staffMember['passport_photo']) ? url('uploads/' . $staffMember['passport_photo']) : null,
                'person_type' => 'Staff Member',
            ];

            if ($staffMember['status'] !== 'Active') {
                echo json_encode([
                    'success'      => false,
                    'person_type'  => 'staff',
                    'action'       => 'inactive_staff',
                    'badge_status' => 'danger',
                    'title'        => 'Staff Inactive',
                    'message'      => "Staff {$staffName} is currently marked as {$staffMember['status']}.",
                    'staff'        => $staffInfo,
                    'student'      => [
                        'id' => $staffId,
                        'name' => $staffName,
                        'admission_number' => $staffMember['staff_id'],
                        'class_name' => $roleTitle,
                        'photo' => $staffInfo['photo']
                    ]
                ]);
                exit;
            }

            // Check today's staff attendance record
            $chkStaff = $this->db->prepare(
                "SELECT id, staff_id, time_in, time_out, status FROM staff_attendance WHERE staff_id = ? AND date = ? LIMIT 1"
            );
            $chkStaff->execute([$staffId, $today]);
            $existingStaff = $chkStaff->fetch(PDO::FETCH_ASSOC);

            // CASE 1: No record today -> Check-in arrival
            if (!$existingStaff) {
                $resolvedStatus = AttendanceRules::resolveCurrentStatus();
                if ($resolvedStatus === 'Denied') {
                    $allowLateAfterClose = (bool) (int) setting('attendance_allow_late_after_close', 1);
                    if (!$allowLateAfterClose) {
                        echo json_encode([
                            'success'      => false,
                            'person_type'  => 'staff',
                            'action'       => 'denied',
                            'badge_status' => 'danger',
                            'title'        => 'Scan Denied (Window Closed)',
                            'message'      => "Attendance window is closed for today ({$nowTime}). Entry denied.",
                            'time'         => date('g:i A', strtotime($nowTime)),
                            'staff'        => $staffInfo,
                            'student'      => [
                                'id' => $staffId,
                                'name' => $staffName,
                                'admission_number' => $staffMember['staff_id'],
                                'class_name' => $roleTitle,
                                'photo' => $staffInfo['photo']
                            ]
                        ]);
                        exit;
                    }
                    $resolvedStatus = 'Late';
                }

                $this->db->prepare(
                    "INSERT INTO staff_attendance (staff_id, school_id, date, time_in, status, scan_method, marked_by, created_at)
                     VALUES (?, 1, ?, ?, ?, 'qr_usb', ?, NOW())"
                )->execute([$staffId, $today, $nowTime, $resolvedStatus, $adminId ?: null]);

                echo json_encode([
                    'success'      => true,
                    'person_type'  => 'staff',
                    'action'       => 'check_in',
                    'badge_status' => 'success',
                    'title'        => 'STAFF ARRIVAL RECORDED',
                    'message'      => "Welcome, {$staffName}! Arrival logged at " . date('g:i A', strtotime($nowTime)) . " ({$resolvedStatus}).",
                    'status'       => $resolvedStatus,
                    'time'         => date('g:i A', strtotime($nowTime)),
                    'staff'        => $staffInfo,
                    'student'      => [
                        'id' => $staffId,
                        'name' => $staffName,
                        'admission_number' => $staffMember['staff_id'],
                        'class_name' => $roleTitle,
                        'photo' => $staffInfo['photo']
                    ]
                ]);
                exit;
            }

            // CASE 2: Record exists, time_out IS NULL -> Debounce or Check-out
            if (empty($existingStaff['time_out'])) {
                $timeInSeconds = strtotime($today . ' ' . $existingStaff['time_in']);
                $diffMinutes = (int) round((time() - $timeInSeconds) / 60);

                if ($diffMinutes < $debounceMins) {
                    echo json_encode([
                        'success'      => false,
                        'person_type'  => 'staff',
                        'action'       => 'duplicate_in',
                        'badge_status' => 'warning',
                        'title'        => 'STAFF ALREADY CHECKED IN',
                        'message'      => "{$staffName} already checked in at " . date('g:i A', $timeInSeconds) . " ({$diffMinutes} min ago). Duplicate scan ignored.",
                        'status'       => $existingStaff['status'],
                        'time'         => date('g:i A', $timeInSeconds),
                        'staff'        => $staffInfo,
                        'student'      => [
                            'id' => $staffId,
                            'name' => $staffName,
                            'admission_number' => $staffMember['staff_id'],
                            'class_name' => $roleTitle,
                            'photo' => $staffInfo['photo']
                        ]
                    ]);
                    exit;
                }

                $this->db->prepare(
                    "UPDATE staff_attendance SET time_out = ?, scan_method = 'qr_usb' WHERE id = ?"
                )->execute([$nowTime, $existingStaff['id']]);

                echo json_encode([
                    'success'      => true,
                    'person_type'  => 'staff',
                    'action'       => 'check_out',
                    'badge_status' => 'primary',
                    'title'        => 'STAFF CHECK-OUT RECORDED',
                    'message'      => "Goodbye, {$staffName}! Departure logged at " . date('g:i A', strtotime($nowTime)) . ".",
                    'status'       => 'Checked Out',
                    'time'         => date('g:i A', strtotime($nowTime)),
                    'staff'        => $staffInfo,
                    'student'      => [
                        'id' => $staffId,
                        'name' => $staffName,
                        'admission_number' => $staffMember['staff_id'],
                        'class_name' => $roleTitle,
                        'photo' => $staffInfo['photo']
                    ]
                ]);
                exit;
            }

            // CASE 3: Already checked out
            $timeOutSeconds = strtotime($today . ' ' . $existingStaff['time_out']);
            echo json_encode([
                'success'      => false,
                'person_type'  => 'staff',
                'action'       => 'duplicate_out',
                'badge_status' => 'warning',
                'title'        => 'STAFF ALREADY CHECKED OUT',
                'message'      => "{$staffName} already checked out today at " . date('g:i A', $timeOutSeconds) . ".",
                'status'       => 'Checked Out',
                'time'         => date('g:i A', $timeOutSeconds),
                'staff'        => $staffInfo,
                'student'      => [
                    'id' => $staffId,
                    'name' => $staffName,
                    'admission_number' => $staffMember['staff_id'],
                    'class_name' => $roleTitle,
                    'photo' => $staffInfo['photo']
                ]
            ]);
            exit;
        }

        // ── STEP 2: Locate student by qr_data, normalized qr_data, admission number, or application number ──
        $stmt = $this->db->prepare(
            "SELECT a.*, c.name AS class_name
             FROM applicants a
             LEFT JOIN classes c ON c.id = a.class_id
             WHERE (a.qr_data = ? OR a.qr_data = ? OR a.admission_number = ? OR a.application_number = ?)
             LIMIT 1"
        );
        $stmt->execute([$token, $normalizedToken, $token, $token]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fallback A: Match by student ID from attendance token pattern (e.g. ATTENDANCE-STD-8845 or attendance/std/8845)
        if (!$student && preg_match('/attendance[-_\/]std[-_\/](\d+)/i', $rawCode, $m)) {
            $studentId = (int)$m[1];
            $stmtId = $this->db->prepare(
                "SELECT a.*, c.name AS class_name
                 FROM applicants a
                 LEFT JOIN classes c ON c.id = a.class_id
                 WHERE a.id = ? LIMIT 1"
            );
            $stmtId->execute([$studentId]);
            $student = $stmtId->fetch(PDO::FETCH_ASSOC);
        }

        // Fallback B: If token is directly an integer ID
        if (!$student && ctype_digit($token)) {
            $stmtId = $this->db->prepare(
                "SELECT a.*, c.name AS class_name
                 FROM applicants a
                 LEFT JOIN classes c ON c.id = a.class_id
                 WHERE a.id = ? LIMIT 1"
            );
            $stmtId->execute([(int)$token]);
            $student = $stmtId->fetch(PDO::FETCH_ASSOC);
        }

        // Student existence check
        if (!$student) {
            echo json_encode([
                'success'      => false,
                'action'       => 'not_found',
                'badge_status' => 'danger',
                'title'        => 'Record Not Found',
                'message'      => 'Unrecognized QR code or token. Not registered as student or staff.',
                'raw_code'     => $rawCode
            ]);
            exit;
        }

        // Multi-tenant school isolation check
        $currentSchoolId = SchoolContext::id();
        if ($currentSchoolId !== null && (int)$student['school_id'] !== (int)$currentSchoolId) {
            echo json_encode([
                'success'      => false,
                'action'       => 'cross_school_denied',
                'badge_status' => 'danger',
                'title'        => 'Cross-School Access Denied',
                'message'      => 'This QR code belongs to a member from another school.'
            ]);
            exit;
        }

        // Enrollment & Active status check
        if ($student['status'] !== 'Enrolled' || (!empty($student['student_status']) && $student['student_status'] !== 'Active')) {
            $statusLabel = $student['status'] !== 'Enrolled' ? $student['status'] : ($student['student_status'] ?? 'Inactive');
            echo json_encode([
                'success'      => false,
                'action'       => 'inactive_student',
                'badge_status' => 'danger',
                'title'        => 'Student Inactive',
                'message'      => "Student {$student['first_name']} {$student['last_name']} is currently {$statusLabel}.",
                'student'      => [
                    'id'               => (int) $student['id'],
                    'name'             => trim($student['first_name'] . ' ' . $student['last_name']),
                    'admission_number' => $student['admission_number'] ?: $student['application_number'],
                    'class_name'       => $student['class_name'] ?: 'N/A',
                    'photo'            => !empty($student['passport_photo']) ? url('uploads/' . $student['passport_photo']) : null
                ]
            ]);
            exit;
        }

        $studentId = (int) $student['id'];
        $classId   = (int) ($student['class_id'] ?? 0);
        $schoolId  = (int) ($student['school_id'] ?? 1);
        $today     = date('Y-m-d');
        $nowTime   = date('H:i:s');
        $adminId   = (int) ($_SESSION['admin']['id'] ?? 0);
        $debounceMins = (int) setting('attendance_debounce_minutes', 15);
        if ($debounceMins < 1) $debounceMins = 1;

        $studentName = trim($student['first_name'] . ' ' . $student['last_name']);
        $studentInfo = [
            'id'               => $studentId,
            'name'             => $studentName,
            'first_name'       => $student['first_name'],
            'last_name'        => $student['last_name'],
            'admission_number' => $student['admission_number'] ?: $student['application_number'],
            'class_name'       => $student['class_name'] ?: 'N/A',
            'photo'            => !empty($student['passport_photo']) ? url('uploads/' . $student['passport_photo']) : null,
            'parent_phone'     => mask_phone($student['parent_phone'] ?? ''),
        ];

        // Check today's attendance record
        $chkStmt = $this->db->prepare(
            "SELECT id, applicant_id, class_id, time_in, time_out, status, alert_sent, timeout_alert_sent
             FROM attendance
             WHERE applicant_id = ? AND date = ?
             LIMIT 1"
        );
        $chkStmt->execute([$studentId, $today]);
        $existing = $chkStmt->fetch(PDO::FETCH_ASSOC);

        // ── CASE 1: No attendance record today → Record CHECK-IN ──
        if (!$existing) {
            $resolvedStatus = AttendanceRules::resolveCurrentStatus();
            $isDenied = ($resolvedStatus === 'Denied');

            if ($isDenied) {
                $allowLateAfterClose = (bool) (int) setting('attendance_allow_late_after_close', 1);
                if (!$allowLateAfterClose) {
                    echo json_encode([
                        'success'      => false,
                        'action'       => 'denied',
                        'badge_status' => 'danger',
                        'title'        => 'Scan Denied (Window Closed)',
                        'message'      => "Attendance window is closed for today ({$nowTime}). Entry denied.",
                        'time'         => date('g:i A', strtotime($nowTime)),
                        'student'      => $studentInfo
                    ]);
                    exit;
                }
                $resolvedStatus = 'Late';
            }

            $this->db->beginTransaction();
            try {
                $ins = $this->db->prepare(
                    "INSERT INTO attendance (applicant_id, class_id, school_id, date, time_in, status, scan_method, alert_sent, marked_by, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, 'qr_usb', 0, ?, NOW())"
                );
                $ins->execute([$studentId, $classId, $schoolId, $today, $nowTime, $resolvedStatus, $adminId ?: null]);
                $attendanceId = (int) $this->db->lastInsertId();
                $this->db->commit();
            } catch (Throwable $e) {
                $this->db->rollBack();
                echo json_encode([
                    'success'      => false,
                    'action'       => 'error',
                    'badge_status' => 'danger',
                    'title'        => 'Database Error',
                    'message'      => 'Failed to save attendance: ' . $e->getMessage()
                ]);
                exit;
            }

            // Dispatch Check-in Notification asynchronously
            AttendanceService::dispatchCheckinNotification($student, $nowTime, $resolvedStatus, $attendanceId);

            echo json_encode([
                'success'      => true,
                'action'       => 'check_in',
                'badge_status' => 'success',
                'title'        => 'CHECK-IN RECORDED',
                'message'      => "Welcome, {$studentName}! Marked as {$resolvedStatus}.",
                'status'       => $resolvedStatus,
                'time'         => date('g:i A', strtotime($nowTime)),
                'student'      => $studentInfo
            ]);
            exit;
        }

        // ── CASE 2: Record exists, but time_out IS NULL ──
        if (empty($existing['time_out'])) {
            $timeInSeconds = strtotime($today . ' ' . $existing['time_in']);
            $nowSeconds    = time();
            $diffMinutes   = (int) round(($nowSeconds - $timeInSeconds) / 60);

            // Subcase 2A: Within debounce window → Duplicate check-in attempt
            if ($diffMinutes < $debounceMins) {
                echo json_encode([
                    'success'      => false,
                    'action'       => 'duplicate_in',
                    'badge_status' => 'warning',
                    'title'        => 'ALREADY CHECKED IN',
                    'message'      => "{$studentName} already checked in at " . date('g:i A', $timeInSeconds) . " ({$diffMinutes} min ago). Duplicate scan ignored.",
                    'status'       => $existing['status'],
                    'time'         => date('g:i A', $timeInSeconds),
                    'student'      => $studentInfo
                ]);
                exit;
            }

            // Subcase 2B: Outside debounce window → Record CHECK-OUT
            $this->db->beginTransaction();
            try {
                $upd = $this->db->prepare(
                    "UPDATE attendance
                     SET time_out = ?, scan_method = 'qr_usb'
                     WHERE id = ?"
                );
                $upd->execute([$nowTime, $existing['id']]);
                $this->db->commit();
            } catch (Throwable $e) {
                $this->db->rollBack();
                echo json_encode([
                    'success'      => false,
                    'action'       => 'error',
                    'badge_status' => 'danger',
                    'title'        => 'Database Error',
                    'message'      => 'Failed to update check-out: ' . $e->getMessage()
                ]);
                exit;
            }

            // Dispatch Check-out Notification asynchronously
            if ((int) setting('attendance_checkout_sms_enabled', 1) || (int) setting('attendance_checkout_email_enabled', 1)) {
                $exitData = [
                    'exit_date'          => $today,
                    'exit_time'          => $nowTime,
                    'exit_type'          => 'normal',
                    'exit_reason'        => 'School Departure',
                    'pickup_person_name' => 'Self / Guardian'
                ];
                AttendanceService::dispatchCheckoutNotification($student, $exitData, (int)$existing['id']);
            }

            echo json_encode([
                'success'      => true,
                'action'       => 'check_out',
                'badge_status' => 'primary',
                'title'        => 'CHECK-OUT RECORDED',
                'message'      => "Goodbye, {$studentName}! Departure logged at " . date('g:i A', strtotime($nowTime)) . ".",
                'status'       => 'Checked Out',
                'time'         => date('g:i A', strtotime($nowTime)),
                'student'      => $studentInfo
            ]);
            exit;
        }

        // ── CASE 3: Record exists and time_out IS ALREADY RECORDED ──
        $timeOutSeconds = strtotime($today . ' ' . $existing['time_out']);
        echo json_encode([
            'success'      => false,
            'action'       => 'duplicate_out',
            'badge_status' => 'warning',
            'title'        => 'ALREADY CHECKED OUT',
            'message'      => "{$studentName} already checked out today at " . date('g:i A', $timeOutSeconds) . ". No further scan required.",
            'status'       => 'Checked Out',
            'time'         => date('g:i A', $timeOutSeconds),
            'student'      => $studentInfo
        ]);
        exit;
    }

    /**
     * AJAX polling endpoint to refresh stats and recent scan feed
     */
    public function recentScansAjax(): void
    {
        require_admin();
        header('Content-Type: application/json; charset=utf-8');
        $today = date('Y-m-d');

        $statsStmt = $this->db->prepare(
            "SELECT
                COUNT(DISTINCT a.id) as total_students,
                SUM(CASE WHEN att.id IS NOT NULL AND att.status IN ('Present', 'Late') THEN 1 ELSE 0 END) as checked_in,
                SUM(CASE WHEN att.id IS NOT NULL AND att.time_out IS NOT NULL THEN 1 ELSE 0 END) as checked_out,
                SUM(CASE WHEN att.status = 'Late' THEN 1 ELSE 0 END) as late_count
             FROM applicants a
             LEFT JOIN attendance att ON att.applicant_id = a.id AND att.date = ?
             WHERE a.status = 'Enrolled' AND (a.student_status IS NULL OR a.student_status = 'Active')"
        );
        $statsStmt->execute([$today]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_students' => 0, 'checked_in' => 0, 'checked_out' => 0, 'late_count' => 0
        ];
        $stats['on_campus'] = max(0, ((int)$stats['checked_in']) - ((int)$stats['checked_out']));

        // Student scans
        $studentScans = $this->db->prepare(
            "SELECT att.id as attendance_id, att.applicant_id, att.time_in, att.time_out, att.status, att.scan_method,
                    s.first_name, s.last_name, s.admission_number, s.application_number, s.passport_photo,
                    c.name as class_name, 'student' as person_type
             FROM attendance att
             JOIN applicants s ON s.id = att.applicant_id
             LEFT JOIN classes c ON c.id = s.class_id
             WHERE att.date = ?"
        );
        $studentScans->execute([$today]);
        $allStudentScans = $studentScans->fetchAll(PDO::FETCH_ASSOC);

        // Staff scans
        $staffScans = $this->db->prepare(
            "SELECT sa.id as attendance_id, sa.staff_id as applicant_id, sa.time_in, sa.time_out, sa.status, sa.scan_method,
                    s.first_name, s.last_name, s.staff_id as admission_number, s.staff_id as application_number, s.passport_photo,
                    COALESCE(s.department, 'Faculty') as class_name, 'staff' as person_type
             FROM staff_attendance sa
             JOIN staff s ON s.id = sa.staff_id
             WHERE sa.date = ?"
        );
        $staffScans->execute([$today]);
        $allStaffScans = $staffScans->fetchAll(PDO::FETCH_ASSOC);

        $merged = array_merge($allStudentScans, $allStaffScans);
        usort($merged, function ($a, $b) {
            $tA = max($a['time_out'] ?? '00:00:00', $a['time_in'] ?? '00:00:00');
            $tB = max($b['time_out'] ?? '00:00:00', $b['time_in'] ?? '00:00:00');
            return strcmp($tB, $tA);
        });
        $recentScans = array_slice($merged, 0, 10);

        foreach ($recentScans as &$r) {
            $r['name'] = trim($r['first_name'] . ' ' . $r['last_name']);
            $r['formatted_time'] = !empty($r['time_out']) ? date('g:i A', strtotime($r['time_out'])) : (!empty($r['time_in']) ? date('g:i A', strtotime($r['time_in'])) : '—');
            $r['scan_type'] = !empty($r['time_out']) ? 'OUT' : 'IN';
            $r['photo_url'] = !empty($r['passport_photo']) ? url('uploads/' . $r['passport_photo']) : null;
        }

        echo json_encode([
            'success'     => true,
            'stats'       => $stats,
            'recentScans' => $recentScans
        ]);
        exit;
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function fetchStudentForSms(int $applicantId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT a.*, c.name AS class_name FROM applicants a LEFT JOIN classes c ON c.id = a.class_id WHERE a.id = ?"
        );
        $stmt->execute([$applicantId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
