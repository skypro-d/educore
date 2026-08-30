<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/SmsService.php';
require_once __DIR__ . '/../config/AttendanceRules.php';
require_once __DIR__ . '/../models/Applicant.php';
require_once __DIR__ . '/../models/ClassModel.php';

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
                "SELECT applicant_id, status, remark, time_in FROM attendance WHERE class_id=? AND date=?"
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
                            send_checkin_sms($this->db, $student, $nowTime, $status, (int) $att['id']);
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

    // ── Private helpers ───────────────────────────────────────────────────────

    private function fetchStudentForSms(int $applicantId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT id, first_name, last_name, parent_phone, parent_email FROM applicants WHERE id = ?"
        );
        $stmt->execute([$applicantId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
