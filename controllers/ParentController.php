<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Applicant.php';

final class ParentController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /* ─── Auth ─────────────────────────────────────────── */

    public function login(): void
    {
        if ($this->parentSession()) {
            redirect('parent/dashboard');
        }
        render('parent/login', [], 'parent');
    }

    public function authenticate(): void
    {
        verify_csrf();
        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        $stmt = $this->db->prepare(
            "SELECT pa.*, a.first_name, a.last_name, a.application_number, a.status
             FROM parent_accounts pa
             JOIN applicants a ON a.id = pa.applicant_id
             WHERE pa.email = ? LIMIT 1"
         );
        $stmt->execute([$email]);
        $account = $stmt->fetch();

        if ($account && password_verify($password, $account['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['parent'] = [
                'id'             => $account['id'],
                'applicant_id'   => $account['applicant_id'],
                'name'           => $account['first_name'] . ' ' . $account['last_name'],
                'email'          => $account['email'],
                'app_number'     => $account['application_number'],
            ];
            $this->db->prepare("UPDATE parent_accounts SET last_login=NOW() WHERE id=?")->execute([$account['id']]);
            
            Logger::info("Parent login successful", ['email' => $email, 'account_id' => $account['id']]);
            if ($account['must_change_password']) {
                redirect('parent/change-password');
            }
            redirect('parent/dashboard');
        }

        Logger::warn("Parent login failed", ['email' => $email]);
        flash('danger', 'Invalid email or password.');
        redirect('parent/login');
    }

    public function logout(): never
    {
        unset($_SESSION['parent']);
        redirect('parent/login');
    }

    public function resetRequest(): void
    {
        render('parent/reset_request', [], 'parent');
    }

    public function resetSend(): void
    {
        verify_csrf();
        $email = trim($_POST['email'] ?? '');
        $stmt = $this->db->prepare("SELECT * FROM parent_accounts WHERE email=? LIMIT 1");
        $stmt->execute([$email]);
        $account = $stmt->fetch();
        if ($account) {
            $token = bin2hex(random_bytes(20));
            $this->db->prepare("UPDATE parent_accounts SET reset_token=?, reset_expires=DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE id=?")
                ->execute([$token, $account['id']]);
            $link = url('parent/reset?token=' . $token);
            send_email_notice($email, 'Password Reset', "Click this link to reset your password: $link");
        }
        flash('success', 'If that email exists, a reset link has been sent.');
        redirect('parent/login');
    }

    public function resetForm(): void
    {
        $token = trim($_GET['token'] ?? '');
        $stmt = $this->db->prepare("SELECT * FROM parent_accounts WHERE reset_token=? AND reset_expires > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $account = $stmt->fetch();
        if (!$account) {
            flash('danger', 'Invalid or expired reset link.');
            redirect('parent/login');
        }
        render('parent/reset_form', compact('token'), 'parent');
    }

    public function resetSave(): void
    {
        verify_csrf();
        $token = trim($_POST['token'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        if (strlen($password) < 6) {
            flash('danger', 'Password must be at least 6 characters.');
            redirect('parent/reset?token=' . $token);
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->db->prepare("UPDATE parent_accounts SET password_hash=?, reset_token=NULL, reset_expires=NULL WHERE reset_token=?")
            ->execute([$hash, $token]);
        flash('success', 'Password updated. Please login.');
        redirect('parent/login');
    }

    /* ─── Dashboard ─────────────────────────────────────── */

    public function dashboard(): void
    {
        $this->requireParent();
        $applicantId = (int) $_SESSION['parent']['applicant_id'];
        $student     = (new Applicant($this->db))->find($applicantId);
        $announcements = $this->db->query(
            "SELECT * FROM announcements WHERE is_published=1 AND audience IN ('all','parents')
             AND (expires_at IS NULL OR expires_at > NOW())
             ORDER BY published_at DESC LIMIT 5"
        )->fetchAll();

        // Attendance summary this month
        $attSummary = $this->db->prepare(
            "SELECT status, COUNT(*) AS cnt FROM attendance
             WHERE applicant_id=? AND MONTH(date)=MONTH(NOW()) AND YEAR(date)=YEAR(NOW())
             GROUP BY status"
        );
        $attSummary->execute([$applicantId]);
        $attendance = [];
        foreach ($attSummary->fetchAll() as $row) {
            $attendance[$row['status']] = $row['cnt'];
        }

        // Outstanding fees
        $feeBalance = $this->outstandingBalance($applicantId);

        // Latest results
        $lastResults = $this->db->prepare(
            "SELECT sr.*, s.name AS subject_name
             FROM student_results sr
             JOIN subjects s ON s.id=sr.subject_id
             WHERE sr.applicant_id=?
             ORDER BY sr.updated_at DESC, sr.created_at DESC LIMIT 6"
        );
        $lastResults->execute([$applicantId]);
        $results = $lastResults->fetchAll();

        // Attendance calendar dates this month
        $stmtCal = $this->db->prepare(
            "SELECT date, status FROM attendance 
             WHERE applicant_id = ? AND MONTH(date) = MONTH(NOW()) AND YEAR(date) = YEAR(NOW())
             ORDER BY date ASC"
        );
        $stmtCal->execute([$applicantId]);
        $calendar = $stmtCal->fetchAll();

        // Timetable today
        $classId = (int) $student['class_id'];
        $dayOfWeek = date('l');
        $stmtTime = $this->db->prepare(
            "SELECT t.*, s.name AS subject_name, st.first_name, st.last_name 
             FROM timetables t
             JOIN subjects s ON s.id = t.subject_id
             LEFT JOIN staff st ON st.id = t.teacher_id
             WHERE t.class_id = ? AND t.day_of_week = ?
             ORDER BY t.start_time ASC"
        );
        $stmtTime->execute([$classId, $dayOfWeek]);
        $timetableToday = $stmtTime->fetchAll();

        // Notifications
        $stmtNotif = $this->db->prepare(
            "SELECT * FROM notifications 
             WHERE user_type = 'parent' AND user_id = ? 
             ORDER BY created_at DESC LIMIT 5"
        );
        $stmtNotif->execute([$_SESSION['parent']['id']]);
        $notifications = $stmtNotif->fetchAll();

        // Today's Campus Arrival & Gate Departure Status
        $today = date('Y-m-d');
        $stmtTodayAtt = $this->db->prepare("SELECT * FROM attendance WHERE applicant_id = ? AND date = ? LIMIT 1");
        $stmtTodayAtt->execute([$applicantId, $today]);
        $todayAttendance = $stmtTodayAtt->fetch() ?: null;

        $stmtTodayExit = $this->db->prepare(
            "SELECT el.*, g.gate_name 
             FROM student_exit_logs el 
             LEFT JOIN school_gates g ON g.id = el.gate_id 
             WHERE el.student_id = ? AND el.exit_date = ? LIMIT 1"
        );
        $stmtTodayExit->execute([$applicantId, $today]);
        $todayExit = $stmtTodayExit->fetch() ?: null;

        render('parent/dashboard', compact(
            'student', 'announcements', 'attendance', 'feeBalance', 'results',
            'calendar', 'timetableToday', 'notifications', 'todayAttendance', 'todayExit'
        ), 'parent');
    }

    public function child(): void
    {
        $this->requireParent();
        $student = (new Applicant($this->db))->find((int) $_SESSION['parent']['applicant_id']);
        render('parent/child', compact('student'), 'parent');
    }

    public function attendance(): void
    {
        $this->requireParent();
        $applicantId = (int) $_SESSION['parent']['applicant_id'];
        $month = (int) ($_GET['month'] ?? date('n'));
        $year  = (int) ($_GET['year'] ?? date('Y'));

        $stmt = $this->db->prepare(
            "SELECT * FROM attendance WHERE applicant_id=? AND MONTH(date)=? AND YEAR(date)=? ORDER BY date ASC"
        );
        $stmt->execute([$applicantId, $month, $year]);
        $records = $stmt->fetchAll();

        // Fetch Gate Exit Logs for the month
        $stmtExits = $this->db->prepare(
            "SELECT el.*, g.gate_name 
             FROM student_exit_logs el 
             LEFT JOIN school_gates g ON g.id = el.gate_id 
             WHERE el.student_id = ? AND MONTH(el.exit_date) = ? AND YEAR(el.exit_date) = ? 
             ORDER BY el.exit_date ASC, el.exited_at ASC"
        );
        $stmtExits->execute([$applicantId, $month, $year]);
        $exitRecords = $stmtExits->fetchAll();

        $dailyExits = [];
        $totalExits = count($exitRecords);
        $earlyExits = 0;
        foreach ($exitRecords as $er) {
            $dailyExits[$er['exit_date']] = $er;
            if ($er['exit_type'] === 'early') {
                $earlyExits++;
            }
        }

        $summary = ['Present' => 0, 'Absent' => 0, 'Late' => 0, 'Excused' => 0];
        foreach ($records as $r) {
            $summary[$r['status']] = ($summary[$r['status']] ?? 0) + 1;
        }

        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = date('F', mktime(0, 0, 0, $m, 1));
        }

        render('parent/attendance', compact('records', 'exitRecords', 'dailyExits', 'totalExits', 'earlyExits', 'summary', 'months', 'month', 'year'), 'parent');
    }

    public function results(): void
    {
        $this->requireParent();
        $applicantId = (int) $_SESSION['parent']['applicant_id'];
        $year  = $_GET['year'] ?? setting('academic_year', date('Y') . '/' . (date('Y') + 1));
        $term  = $_GET['term'] ?? setting('current_term', 'First');

        $stmt = $this->db->prepare(
            "SELECT sr.*, s.name AS subject_name, s.code
             FROM student_results sr
             JOIN subjects s ON s.id=sr.subject_id
             WHERE sr.applicant_id=? AND sr.academic_year=? AND sr.term=?
             ORDER BY s.name ASC"
        );
        $stmt->execute([$applicantId, $year, $term]);
        $results = $stmt->fetchAll();

        $remark = $this->db->prepare(
            "SELECT * FROM term_remarks WHERE applicant_id=? AND academic_year=? AND term=? LIMIT 1"
        );
        $remark->execute([$applicantId, $year, $term]);
        $termRemark = $remark->fetch() ?: [];

        $student = (new Applicant($this->db))->find($applicantId);
        $terms   = ['First', 'Second', 'Third'];

        render('parent/results', compact('results', 'termRemark', 'student', 'year', 'term', 'terms'), 'parent');
    }

    public function fees(): void
    {
        $this->requireParent();
        $applicantId = (int) $_SESSION['parent']['applicant_id'];
        $year = $_GET['year'] ?? setting('academic_year', '');

        $stmt = $this->db->prepare(
            "SELECT sfp.*, fs.fee_name, fs.term, fs.amount AS fee_amount, fs.academic_year
             FROM student_fee_payments sfp
             JOIN fee_structures fs ON fs.id=sfp.fee_structure_id
             WHERE sfp.applicant_id=? AND fs.academic_year=?
             ORDER BY sfp.created_at DESC"
        );
        $stmt->execute([$applicantId, $year]);
        $payments = $stmt->fetchAll();

        // Outstanding
        $outstanding = $this->outstandingBalance($applicantId);
        $student = (new Applicant($this->db))->find($applicantId);

        render('parent/fees', compact('payments', 'outstanding', 'student', 'year'), 'parent');
    }

    public function announcements(): void
    {
        $this->requireParent();
        $announcements = $this->db->query(
            "SELECT * FROM announcements WHERE is_published=1
             AND audience IN ('all','parents')
             AND (expires_at IS NULL OR expires_at > NOW())
             ORDER BY published_at DESC"
        )->fetchAll();
        render('parent/announcements', compact('announcements'), 'parent');
    }

    public function timetable(): void
    {
        $this->requireParent();
        $applicantId = (int) $_SESSION['parent']['applicant_id'];
        $student = (new Applicant($this->db))->find($applicantId);
        $classId = (int) $student['class_id'];
        
        $stmt = $this->db->prepare(
            "SELECT t.*, s.name AS subject_name, s.code AS subject_code, st.first_name, st.last_name 
             FROM timetables t
             JOIN subjects s ON s.id = t.subject_id
             LEFT JOIN staff st ON st.id = t.teacher_id
             WHERE t.class_id = ?
             ORDER BY FIELD(t.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'), t.start_time ASC"
        );
        $stmt->execute([$classId]);
        $schedule = $stmt->fetchAll();

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $timetable = [];
        foreach ($days as $day) {
            $timetable[$day] = [];
        }
        foreach ($schedule as $row) {
            if (in_array($row['day_of_week'], $days)) {
                $timetable[$row['day_of_week']][] = $row;
            }
        }

        render('parent/timetable', compact('timetable', 'days', 'student'), 'parent');
    }

    public function idCard(): void
    {
        $this->requireParent();
        $student = (new Applicant($this->db))->find((int)$_SESSION['parent']['applicant_id']);
        render('parent/id_card', compact('student'), 'parent');
    }

    public function notifications(): void
    {
        $this->requireParent();
        $parentAccId = $_SESSION['parent']['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $notifId = (int) ($_POST['notification_id'] ?? 0);
            $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ? AND user_type = 'parent'")
                ->execute([$notifId, $parentAccId]);
            flash('success', 'Notification marked as read.');
            redirect('parent/notifications');
        }

        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_type = 'parent' AND user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$parentAccId]);
        $notifications = $stmt->fetchAll();

        render('parent/notifications', compact('notifications'), 'parent');
    }

    public function changePasswordForm(): void
    {
        $this->requireParent(false);
        render('parent/change_password', [], 'parent');
    }

    public function changePasswordSave(): void
    {
        $this->requireParent(false);
        verify_csrf();

        $password = (string) ($_POST['password'] ?? '');
        if (strlen($password) < 6) {
            flash('danger', 'Password must be at least 6 characters.');
            redirect('parent/change-password');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->db->prepare("UPDATE parent_accounts SET password_hash=?, must_change_password=0 WHERE id=?")
            ->execute([$hash, $_SESSION['parent']['id']]);

        flash('success', 'Password updated successfully. Welcome to your dashboard.');
        redirect('parent/dashboard');
    }

    /* ─── Helpers ────────────────────────────────────────── */

    private function requireParent(bool $checkPasswordForce = true): void
    {
        if (!$this->parentSession()) {
            redirect('parent/login');
        }

        if ($checkPasswordForce) {
            $stmt = $this->db->prepare("SELECT must_change_password FROM parent_accounts WHERE id = ?");
            $stmt->execute([$_SESSION['parent']['id']]);
            $mustChange = (bool) $stmt->fetchColumn();
            if ($mustChange) {
                redirect('parent/change-password');
            }
        }
    }

    private function parentSession(): ?array
    {
        return $_SESSION['parent'] ?? null;
    }

    private function outstandingBalance(int $applicantId): float
    {
        $stmt = $this->db->prepare(
            "SELECT COALESCE(SUM(balance), 0) FROM student_fee_payments
             WHERE applicant_id=? AND payment_status IN ('Pending','Partial')"
        );
        $stmt->execute([$applicantId]);
        return (float) $stmt->fetchColumn();
    }
}
