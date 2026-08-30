<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Applicant.php';

final class StudentController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /* ─── Auth ─────────────────────────────────────────── */

    public function login(): void
    {
        if ($this->studentSession()) {
            redirect('student/dashboard');
        }
        render('student/login', [], 'student_auth');
    }

    public function authenticate(): void
    {
        verify_csrf();
        $username = trim($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        $stmt = $this->db->prepare(
            "SELECT sa.*, a.first_name, a.last_name, a.application_number, a.admission_number, a.class_id, a.passport_photo
             FROM student_accounts sa
             JOIN applicants a ON a.id = sa.applicant_id
             WHERE sa.username = ? LIMIT 1"
        );
        $stmt->execute([$username]);
        $account = $stmt->fetch();

        if ($account && password_verify($password, $account['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['student'] = [
                'id'             => $account['id'],
                'applicant_id'   => $account['applicant_id'],
                'username'       => $account['username'],
                'name'           => $account['first_name'] . ' ' . $account['last_name'],
                'admission_no'   => $account['admission_number'],
                'class_id'       => $account['class_id'],
                'photo'          => $account['passport_photo']
            ];
            $this->db->prepare("UPDATE student_accounts SET last_login=NOW() WHERE id=?")->execute([$account['id']]);
            
            Logger::info("Student login successful", ['username' => $username, 'account_id' => $account['id']]);
            if ($account['must_change_password']) {
                redirect('student/change-password');
            }
            redirect('student/dashboard');
        }

        Logger::warn("Student login failed", ['username' => $username]);
        flash('danger', 'Invalid username or password.');
        redirect('student/login');
    }

    public function logout(): never
    {
        unset($_SESSION['student']);
        redirect('student/login');
    }

    public function changePasswordForm(): void
    {
        $this->requireStudent(false); // don't check must_change_password flag yet to prevent infinite redirect
        render('student/change_password', [], 'student_auth');
    }

    public function changePasswordSave(): void
    {
        $this->requireStudent(false);
        verify_csrf();

        $password = (string) ($_POST['password'] ?? '');
        if (strlen($password) < 6) {
            flash('danger', 'Password must be at least 6 characters.');
            redirect('student/change-password');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->db->prepare("UPDATE student_accounts SET password_hash=?, must_change_password=0 WHERE id=?")
            ->execute([$hash, $_SESSION['student']['id']]);

        flash('success', 'Password updated successfully. Welcome to your dashboard.');
        redirect('student/dashboard');
    }

    /* ─── Pages ────────────────────────────────────────── */

    public function dashboard(): void
    {
        $this->requireStudent();
        $applicantId = (int) $_SESSION['student']['applicant_id'];
        $classId = (int) $_SESSION['student']['class_id'];
        
        $student = (new Applicant($this->db))->find($applicantId);
        
        // 1. Attendance rate this term / year
        $stmtAtt = $this->db->prepare(
            "SELECT 
                COALESCE(SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END), 0) AS present,
                COALESCE(SUM(CASE WHEN status='Late' THEN 1 ELSE 0 END), 0) AS late,
                COUNT(*) as total 
             FROM attendance WHERE applicant_id = ?"
        );
        $stmtAtt->execute([$applicantId]);
        $attData = $stmtAtt->fetch();
        $attendanceRate = $attData['total'] > 0 
            ? round((($attData['present'] + ($attData['late'] * 0.5)) / $attData['total']) * 100) 
            : 100;

        // Daily calendar status for this month
        $stmtCal = $this->db->prepare(
            "SELECT date, status FROM attendance 
             WHERE applicant_id = ? AND MONTH(date) = MONTH(NOW()) AND YEAR(date) = YEAR(NOW())
             ORDER BY date ASC"
        );
        $stmtCal->execute([$applicantId]);
        $calendar = $stmtCal->fetchAll();

        // 2. School fees outstanding
        $feeBalance = $this->outstandingBalance($applicantId);

        // 3. Latest Results (last 5 entries)
        $stmtRes = $this->db->prepare(
            "SELECT r.*, s.name AS subject_name 
             FROM student_results r 
             JOIN subjects s ON s.id = r.subject_id 
             WHERE r.applicant_id = ? 
             ORDER BY r.updated_at DESC, r.created_at DESC LIMIT 5"
        );
        $stmtRes->execute([$applicantId]);
        $results = $stmtRes->fetchAll();

        // 4. Timetable preview (today's schedule)
        $dayOfWeek = date('l'); // e.g. Monday
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

        // 5. Announcements
        $announcements = $this->db->query(
            "SELECT * FROM announcements 
             WHERE is_published = 1 AND audience IN ('all', 'class') 
             AND (class_id IS NULL OR class_id = $classId)
             AND (expires_at IS NULL OR expires_at > NOW())
             ORDER BY published_at DESC LIMIT 5"
        )->fetchAll();

        // 6. Recent unread notifications
        $stmtNotif = $this->db->prepare(
            "SELECT * FROM notifications 
             WHERE user_type = 'student' AND user_id = ? 
             ORDER BY created_at DESC LIMIT 4"
        );
        $stmtNotif->execute([$_SESSION['student']['id']]);
        $notifications = $stmtNotif->fetchAll();

        render('student/dashboard', compact('student', 'attendanceRate', 'calendar', 'feeBalance', 'results', 'timetableToday', 'announcements', 'notifications'), 'student');
    }

    public function timetable(): void
    {
        $this->requireStudent();
        $classId = (int) $_SESSION['student']['class_id'];
        
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
            $timetable[$row['day_of_week']][] = $row;
        }

        render('student/timetable', compact('timetable', 'days'), 'student');
    }

    public function idCard(): void
    {
        $this->requireStudent();
        $student = (new Applicant($this->db))->find((int)$_SESSION['student']['applicant_id']);
        render('student/id_card', compact('student'), 'student');
    }

    public function notifications(): void
    {
        $this->requireStudent();
        $studentAccId = $_SESSION['student']['id'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $notifId = (int) ($_POST['notification_id'] ?? 0);
            $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ? AND user_type = 'student'")
                ->execute([$notifId, $studentAccId]);
            flash('success', 'Notification marked as read.');
            redirect('student/notifications');
        }

        $stmt = $this->db->prepare("SELECT * FROM notifications WHERE user_type = 'student' AND user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$studentAccId]);
        $notifications = $stmt->fetchAll();

        render('student/notifications', compact('notifications'), 'student');
    }

    /* ─── Helpers ────────────────────────────────────────── */

    private function requireStudent(bool $checkPasswordForce = true): void
    {
        if (!$this->studentSession()) {
            redirect('student/login');
        }

        if ($checkPasswordForce) {
            // Check if must change password is true
            $stmt = $this->db->prepare("SELECT must_change_password FROM student_accounts WHERE id = ?");
            $stmt->execute([$_SESSION['student']['id']]);
            $mustChange = (bool) $stmt->fetchColumn();
            if ($mustChange) {
                redirect('student/change-password');
            }
        }
    }

    private function studentSession(): ?array
    {
        return $_SESSION['student'] ?? null;
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
