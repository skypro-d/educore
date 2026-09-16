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

    public function resetRequest(): void
    {
        render('student/reset_request', [], 'student_auth');
    }

    public function resetSend(): void
    {
        verify_csrf();
        $identifier = trim((string) ($_POST['identifier'] ?? ''));

        if ($identifier !== '') {
            $stmt = $this->db->prepare(
                "SELECT sa.id, sa.username, a.first_name, a.last_name, a.parent_email, a.parent_name
                 FROM student_accounts sa
                 JOIN applicants a ON a.id = sa.applicant_id
                 WHERE sa.username = ? OR a.admission_number = ? OR a.application_number = ? OR a.parent_email = ?
                 LIMIT 1"
            );
            $stmt->execute([$identifier, $identifier, $identifier, $identifier]);
            $account = $stmt->fetch();

            if ($account && !empty($account['parent_email']) && filter_var($account['parent_email'], FILTER_VALIDATE_EMAIL)) {
                $token = bin2hex(random_bytes(24));
                $this->db->prepare("UPDATE student_accounts SET reset_token = ?, reset_expires = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE id = ?")
                    ->execute([$token, $account['id']]);

                $schoolName = (string) setting('school_name', 'School Portal');
                $resetLink = url('student/reset?token=' . $token);
                $subject = "Student Portal Password Reset - {$schoolName}";
                $body = "Hello {$account['first_name']},\n\n"
                    . "A request was received to reset the password for your Student Portal account ({$account['username']}).\n\n"
                    . "Please click the link below to set a new password:\n"
                    . "{$resetLink}\n\n"
                    . "This link will expire in 30 minutes. If you did not request this, you can safely ignore this email.\n\n"
                    . "Regards,\n"
                    . $schoolName;

                send_email_notice($account['parent_email'], $subject, $body);
            }
        }

        flash('success', 'If a matching student account was found, password reset instructions have been sent to the registered email address.');
        redirect('student/login');
    }

    public function resetForm(): void
    {
        $token = trim((string) ($_GET['token'] ?? ''));
        $stmt = $this->db->prepare("SELECT id, username FROM student_accounts WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $account = $stmt->fetch();

        if (!$account) {
            flash('danger', 'Invalid or expired password reset link. Please request a new link.');
            redirect('student/login');
        }

        render('student/reset_form', compact('token', 'account'), 'student_auth');
    }

    public function resetSave(): void
    {
        verify_csrf();
        $token = trim((string) ($_POST['token'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirmation'] ?? '');

        if ($token === '') {
            flash('danger', 'Invalid password reset token.');
            redirect('student/login');
        }

        if (strlen($password) < 6) {
            flash('danger', 'Password must be at least 6 characters.');
            redirect('student/reset?token=' . urlencode($token));
        }

        if ($password !== $passwordConfirm) {
            flash('danger', 'Password confirmation does not match.');
            redirect('student/reset?token=' . urlencode($token));
        }

        $stmt = $this->db->prepare("SELECT id FROM student_accounts WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1");
        $stmt->execute([$token]);
        $account = $stmt->fetch();

        if (!$account) {
            flash('danger', 'This password reset link is invalid or has expired. Please request a new one.');
            redirect('student/login');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->db->prepare("UPDATE student_accounts SET password_hash = ?, reset_token = NULL, reset_expires = NULL, must_change_password = 0 WHERE id = ?")
            ->execute([$hash, $account['id']]);

        flash('success', 'Password reset successfully! You can now sign in with your new password.');
        redirect('student/login');
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

    public function fees(): void
    {
        $this->requireStudent();
        $applicantId = (int) $_SESSION['student']['applicant_id'];
        $student = (new Applicant($this->db))->find($applicantId);
        $classId = (int) ($student['class_id'] ?? 0);
        $year = $_GET['year'] ?? setting('academic_year', date('Y') . '/' . (date('Y') + 1));
        $term = $_GET['term'] ?? setting('current_term', 'First');

        $stmtFs = $this->db->prepare(
            "SELECT fs.*, c.name AS class_name,
                    sfp.id AS payment_id, sfp.amount_paid, sfp.balance, sfp.payment_status, sfp.receipt_number, sfp.payment_date, sfp.payment_method
             FROM fee_structures fs
             LEFT JOIN classes c ON c.id = fs.class_id
             LEFT JOIN student_fee_payments sfp ON sfp.fee_structure_id = fs.id AND sfp.applicant_id = ?
             WHERE fs.is_active = 1 
               AND (fs.class_id IS NULL OR fs.class_id = 0 OR fs.class_id = ?)
             ORDER BY fs.term ASC, fs.fee_name ASC"
        );
        $stmtFs->execute([$applicantId, $classId]);
        $feeSchedule = $stmtFs->fetchAll();

        $outstanding = $this->outstandingBalance($applicantId);

        render('student/fees', compact('feeSchedule', 'outstanding', 'student', 'year', 'term'), 'student');
    }

    public function paymentHistory(): void
    {
        $this->requireStudent();
        $applicantId = (int) $_SESSION['student']['applicant_id'];
        $year = $_GET['year'] ?? '';

        $sql = "SELECT sfp.*, fs.fee_name, fs.term, fs.amount AS fee_amount, fs.academic_year
                FROM student_fee_payments sfp
                JOIN fee_structures fs ON fs.id = sfp.fee_structure_id
                WHERE sfp.applicant_id = ?";
        $params = [$applicantId];
        if ($year !== '') {
            $sql .= " AND fs.academic_year = ?";
            $params[] = $year;
        }
        $sql .= " ORDER BY sfp.created_at DESC, sfp.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $payments = $stmt->fetchAll();

        $outstanding = $this->outstandingBalance($applicantId);
        $student = (new Applicant($this->db))->find($applicantId);

        render('student/payment_history', compact('payments', 'outstanding', 'student', 'year'), 'student');
    }

    public function receipt(): void
    {
        $this->requireStudent();
        $applicantId = (int) $_SESSION['student']['applicant_id'];
        $paymentId = (int) ($_GET['id'] ?? ($_GET['receipt'] ?? 0));

        $stmt = $this->db->prepare(
            "SELECT sfp.*, fs.fee_name, fs.term, fs.amount AS fee_amount, fs.academic_year,
                    a.first_name, a.last_name, a.application_number, a.admission_number, a.parent_name, a.guardian_name,
                    c.name AS class_name
             FROM student_fee_payments sfp
             JOIN fee_structures fs ON fs.id = sfp.fee_structure_id
             JOIN applicants a ON a.id = sfp.applicant_id
             LEFT JOIN classes c ON c.id = a.class_id
             WHERE sfp.id = ? AND sfp.applicant_id = ? LIMIT 1"
        );
        $stmt->execute([$paymentId, $applicantId]);
        $payment = $stmt->fetch();

        if (!$payment) {
            flash('danger', 'Receipt not found or you do not have permission to view it.');
            redirect('student/payment-history');
        }

        $backUrl = url('student/payment-history');
        render('shared/fee_receipt', compact('payment', 'backUrl'), 'none');
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
