<?php
// controllers/TeacherController.php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Applicant.php';

final class TeacherController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /* ─── Auth ─────────────────────────────────────────── */

    public function login(): void
    {
        if ($this->teacherSession()) {
            redirect('teacher/dashboard');
        }
        render('teacher/login', [], 'teacher_auth');
    }

    public function authenticate(): void
    {
        verify_csrf();
        $username = trim($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        $stmt = $this->db->prepare(
            "SELECT sa.*, s.first_name, s.last_name, s.role, s.staff_id AS public_staff_id, s.passport_photo, s.status
             FROM staff_accounts sa
             JOIN staff s ON s.id = sa.staff_id
             WHERE sa.username = ? LIMIT 1"
        );
        $stmt->execute([$username]);
        $account = $stmt->fetch();

        if ($account) {
            if ($account['status'] !== 'Active') {
                Logger::warn("Teacher login inactive account attempt", ['username' => $username]);
                flash('danger', 'Your staff account is currently inactive.');
                redirect('teacher/login');
            }

            if (password_verify($password, $account['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['teacher'] = [
                    'id'             => $account['id'],
                    'staff_table_id' => $account['staff_id'],
                    'username'       => $account['username'],
                    'name'           => $account['first_name'] . ' ' . $account['last_name'],
                    'staff_id'       => $account['public_staff_id'],
                    'role'           => $account['role'],
                    'photo'          => $account['passport_photo']
                ];
                $this->db->prepare("UPDATE staff_accounts SET last_login=NOW() WHERE id=?")->execute([$account['id']]);
                
                Logger::info("Teacher login successful", ['username' => $username, 'account_id' => $account['id']]);
                if ($account['must_change_password']) {
                    redirect('teacher/change-password');
                }
                redirect('teacher/dashboard');
            }
        }

        Logger::warn("Teacher login failed", ['username' => $username]);
        flash('danger', 'Invalid username or password.');
        redirect('teacher/login');
    }

    public function logout(): never
    {
        unset($_SESSION['teacher']);
        redirect('teacher/login');
    }

    public function changePasswordForm(): void
    {
        $this->requireTeacher(false); // don't check must_change_password flag yet to prevent infinite redirect
        render('teacher/change_password', [], 'teacher_auth');
    }

    public function changePasswordSave(): void
    {
        $this->requireTeacher(false);
        verify_csrf();

        $password = (string) ($_POST['password'] ?? '');
        if (strlen($password) < 6) {
            flash('danger', 'Password must be at least 6 characters.');
            redirect('teacher/change-password');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->db->prepare("UPDATE staff_accounts SET password_hash=?, must_change_password=0 WHERE id=?")
            ->execute([$hash, $_SESSION['teacher']['id']]);

        flash('success', 'Password updated successfully. Welcome to your dashboard.');
        redirect('teacher/dashboard');
    }

    /* ─── Pages ────────────────────────────────────────── */

    public function dashboard(): void
    {
        $this->requireTeacher();
        $staffId = (int) $_SESSION['teacher']['staff_table_id'];

        // Get class assignments count
        $stmtClasses = $this->db->prepare(
            "SELECT COUNT(DISTINCT class_id) FROM staff_class_assignments WHERE staff_id = ?"
        );
        $stmtClasses->execute([$staffId]);
        $classCount = (int) $stmtClasses->fetchColumn();

        // Get subject assignments count
        $stmtSubj = $this->db->prepare(
            "SELECT COUNT(DISTINCT subject_id) FROM staff_class_assignments WHERE staff_id = ?"
        );
        $stmtSubj->execute([$staffId]);
        $subjectCount = (int) $stmtSubj->fetchColumn();

        // Check if form teacher of any class
        $stmtForm = $this->db->prepare(
            "SELECT c.name FROM staff_class_assignments sca
             JOIN classes c ON c.id = sca.class_id
             WHERE sca.staff_id = ? AND sca.is_form_teacher = 1 LIMIT 1"
        );
        $stmtForm->execute([$staffId]);
        $formClassName = $stmtForm->fetchColumn() ?: 'None';

        // Today's schedule
        $dayOfWeek = date('l');
        $stmtTime = $this->db->prepare(
            "SELECT t.*, s.name AS subject_name, c.name AS class_name
             FROM timetables t
             JOIN subjects s ON s.id = t.subject_id
             JOIN classes c ON c.id = t.class_id
             WHERE t.teacher_id = ? AND t.day_of_week = ?
             ORDER BY t.start_time ASC"
        );
        $stmtTime->execute([$staffId, $dayOfWeek]);
        $todaySchedule = $stmtTime->fetchAll();

        // General Announcements for staff
        $announcements = $this->db->query(
            "SELECT * FROM announcements 
             WHERE is_published = 1 AND audience IN ('all', 'staff')
             AND (expires_at IS NULL OR expires_at > NOW())
             ORDER BY published_at DESC LIMIT 5"
        )->fetchAll();

        render('teacher/dashboard', compact('classCount', 'subjectCount', 'formClassName', 'todaySchedule', 'announcements'), 'teacher');
    }

    public function classList(): void
    {
        $this->requireTeacher();
        $staffId = (int) $_SESSION['teacher']['staff_table_id'];
        $academicYear = current_academic_year();

        // Get all unique classes and subjects assigned
        $stmt = $this->db->prepare(
            "SELECT sca.*, c.name AS class_name, s.name AS subject_name, s.code AS subject_code
             FROM staff_class_assignments sca
             JOIN classes c ON c.id = sca.class_id
             LEFT JOIN subjects s ON s.id = sca.subject_id
             WHERE sca.staff_id = ? AND sca.academic_year = ?
             ORDER BY c.sort_order ASC, s.name ASC"
        );
        $stmt->execute([$staffId, $academicYear]);
        $assignments = $stmt->fetchAll();

        render('teacher/classList', compact('assignments', 'academicYear'), 'teacher');
    }

    public function attendanceForm(): void
    {
        $this->requireTeacher();
        $staffId = (int) $_SESSION['teacher']['staff_table_id'];
        $academicYear = current_academic_year();

        // Get list of classes where this teacher is assigned (either form teacher or subject teacher)
        $stmt = $this->db->prepare(
            "SELECT DISTINCT c.id, c.name 
             FROM staff_class_assignments sca
             JOIN classes c ON c.id = sca.class_id
             WHERE sca.staff_id = ? AND sca.academic_year = ?"
        );
        $stmt->execute([$staffId, $academicYear]);
        $classes = $stmt->fetchAll();

        $classId = (int) ($_GET['class_id'] ?? ($classes[0]['id'] ?? 0));
        $date = $_GET['date'] ?? date('Y-m-d');

        $students = [];
        $existing = [];

        if ($classId > 0) {
            // Verify permission (must be assigned to this class)
            $chk = $this->db->prepare("SELECT 1 FROM staff_class_assignments WHERE staff_id=? AND class_id=? AND academic_year=? LIMIT 1");
            $chk->execute([$staffId, $classId, $academicYear]);
            if (!$chk->fetch()) {
                flash('danger', 'Access denied to this class.');
                redirect('teacher/attendance');
            }

            // Get students in this class
            $stmtStud = $this->db->prepare(
                "SELECT id, first_name, last_name, admission_number, passport_photo 
                 FROM applicants 
                 WHERE class_id = ? AND status = 'Enrolled' AND student_status = 'Active'
                 ORDER BY last_name ASC, first_name ASC"
            );
            $stmtStud->execute([$classId]);
            $students = $stmtStud->fetchAll();

            // Get existing attendance for this class and date
            $stmtAtt = $this->db->prepare("SELECT applicant_id, status, remark, time_in FROM attendance WHERE class_id = ? AND date = ?");
            $stmtAtt->execute([$classId, $date]);
            foreach ($stmtAtt->fetchAll() as $row) {
                $existing[$row['applicant_id']] = $row;
            }
        }

        render('teacher/attendance', compact('classes', 'classId', 'date', 'students', 'existing'), 'teacher');
    }

    public function attendanceSave(): void
    {
        $this->requireTeacher();
        verify_csrf();

        $classId = (int) ($_POST['class_id'] ?? 0);
        $date = $_POST['date'] ?? date('Y-m-d');
        $statusMap = $_POST['status'] ?? []; // applicant_id => status
        $remarkMap = $_POST['remarks'] ?? []; // applicant_id => remark
        $staffId = (int) $_SESSION['teacher']['staff_table_id'];

        if ($classId <= 0) {
            flash('danger', 'Invalid class selection.');
            redirect('teacher/attendance');
        }

        // Attendance marking enabled for all staff
        // Save attendance
        $stmtInsert = $this->db->prepare(
            "INSERT INTO attendance (applicant_id, class_id, date, status, remark, marked_by, time_in)
             VALUES (?, ?, ?, ?, ?, ?, NULL)
             ON DUPLICATE KEY UPDATE status = VALUES(status), remark = VALUES(remark), marked_by = VALUES(marked_by)"
        );

        foreach ($statusMap as $appId => $status) {
            $remark = $remarkMap[$appId] ?? '';
            $stmtInsert->execute([$appId, $classId, $date, $status, $remark, $staffId]);

            // Notify parents if absent or late (optional enhancement)
            if (in_array($status, ['Absent', 'Late'])) {
                $this->notifyParentOfAttendance($appId, $status, $date);
            }
        }

        flash('success', 'Attendance saved successfully.');
        redirect("teacher/attendance?class_id={$classId}&date={$date}");
    }

    public function attendanceScanQRAjax(): void
    {
        // Must be AJAX / POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        $this->requireTeacher();
        $staffId = (int) $_SESSION['teacher']['staff_table_id'];

        // Get POST data
        $input = json_decode(file_get_contents('php://input'), true);
        $qrData = trim($input['qr_data'] ?? '');

        if (empty($qrData)) {
            echo json_encode(['success' => false, 'message' => 'No QR data received']);
            exit;
        }

        $token = $qrData;
        if (str_contains($qrData, 'token=')) {
            if (preg_match('/[?&]token=([^&]+)/', $qrData, $matches)) {
                $token = urldecode($matches[1]);
            } else {
                $parts = parse_url($qrData);
                if (isset($parts['query'])) {
                    parse_str($parts['query'], $queryVars);
                    if (isset($queryVars['token'])) {
                        $token = trim($queryVars['token']);
                    }
                }
            }
        }
        $token = trim($token);

        // Now validate token format: it must start with ATTENDANCE-STD-
        if (!str_starts_with($token, 'ATTENDANCE-STD-')) {
            echo json_encode(['success' => false, 'message' => 'Invalid QR code signature']);
            exit;
        }

        // Extract student ID from the token (e.g., ATTENDANCE-STD-1 or ATTENDANCE-STD-1-abcdef)
        $tokenParts = explode('-', $token);
        $studentId = isset($tokenParts[2]) ? (int)$tokenParts[2] : 0;

        if ($studentId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid student ID in QR code']);
            exit;
        }

        // Fetch student details
        $stmtStud = $this->db->prepare(
            "SELECT id, first_name, last_name, class_id, student_status, status, qr_data 
             FROM applicants WHERE id = ? LIMIT 1"
        );
        $stmtStud->execute([$studentId]);
        $student = $stmtStud->fetch();

        if (!$student) {
            echo json_encode(['success' => false, 'message' => 'Student not found']);
            exit;
        }

        // If the student has a registered qr_data token, verify that the scanned token matches it.
        // For legacy students whose qr_data is empty, we allow matching the legacy format ATTENDANCE-STD-{id}.
        $dbQrData = trim($student['qr_data'] ?? '');
        if ($dbQrData !== '') {
            if ($token !== $dbQrData) {
                echo json_encode(['success' => false, 'message' => 'QR code token mismatch (invalid or outdated QR code)']);
                exit;
            }
        } else {
            // Legacy check
            $legacyToken = 'ATTENDANCE-STD-' . $student['id'];
            if ($token !== $legacyToken) {
                echo json_encode(['success' => false, 'message' => 'Invalid QR code token for this student']);
                exit;
            }
        }

        if ($student['status'] !== 'Enrolled' || $student['student_status'] !== 'Active') {
            echo json_encode(['success' => false, 'message' => 'Student is not currently Active/Enrolled']);
            exit;
        }

        $classId = (int) $student['class_id'];
        $academicYear = current_academic_year();

        // Verify this teacher is assigned to this student's class
        $chk = $this->db->prepare("SELECT 1 FROM staff_class_assignments WHERE staff_id=? AND class_id=? AND academic_year=? LIMIT 1");
        $chk->execute([$staffId, $classId, $academicYear]);
        if (!$chk->fetch()) {
            echo json_encode(['success' => false, 'message' => 'You are not assigned to this student\'s class']);
            exit;
        }

        $date = date('Y-m-d');
        $timeIn = date('H:i:s');
        
        // Define school start time (e.g., 08:00:00)
        $lateThreshold = '08:00:00';
        $status = ($timeIn > $lateThreshold) ? 'Late' : 'Present';

        // Check if already marked today
        $stmtChk = $this->db->prepare("SELECT status, time_in FROM attendance WHERE applicant_id = ? AND date = ?");
        $stmtChk->execute([$studentId, $date]);
        $existing = $stmtChk->fetch();

        if ($existing) {
            echo json_encode([
                'success' => false, 
                'message' => "{$student['first_name']} has already been marked {$existing['status']} today at " . date('h:i A', strtotime($existing['time_in'] ?? 'now'))
            ]);
            exit;
        }

        // Insert/update attendance
        $stmtInsert = $this->db->prepare(
            "INSERT INTO attendance (applicant_id, class_id, date, status, marked_by, time_in)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE status = VALUES(status), marked_by = VALUES(marked_by), time_in = VALUES(time_in)"
        );
        $stmtInsert->execute([$studentId, $classId, $date, $status, $staffId, $timeIn]);

        // Send real-time parent alert
        $studentName = $student['first_name'] . ' ' . $student['last_name'];
        $formattedTime = date('h:i A', strtotime($timeIn));
        $this->notifyParentOfScan($studentId, $studentName, $status, $formattedTime, $date);

        echo json_encode([
            'success' => true,
            'message' => "Success! Marked {$studentName} as {$status}",
            'student_name' => $studentName,
            'status' => $status,
            'time_in' => $formattedTime
        ]);
        exit;
    }

    public function resultsForm(): void
    {
        $this->requireTeacher();
        $staffId = (int) $_SESSION['teacher']['staff_table_id'];
        $academicYear = current_academic_year();

        // Get class & subject assignments for this teacher
        $stmt = $this->db->prepare(
            "SELECT sca.*, c.name AS class_name, s.name AS subject_name 
             FROM staff_class_assignments sca
             JOIN classes c ON c.id = sca.class_id
             JOIN subjects s ON s.id = sca.subject_id
             WHERE sca.staff_id = ? AND sca.academic_year = ?
             ORDER BY c.sort_order ASC, s.name ASC"
        );
        $stmt->execute([$staffId, $academicYear]);
        $assignments = $stmt->fetchAll();

        // Selected Assignment (Default to first)
        $selectedIdx = (int) ($_GET['assignment_idx'] ?? 0);
        $selected = $assignments[$selectedIdx] ?? null;

        $students = [];
        $existingResults = [];
        $term = $_GET['term'] ?? current_term();

        if ($selected) {
            $classId = (int) $selected['class_id'];
            $subjectId = (int) $selected['subject_id'];

            // Fetch students in class
            $stmtStud = $this->db->prepare(
                "SELECT id, first_name, last_name, admission_number 
                 FROM applicants 
                 WHERE class_id = ? AND status = 'Enrolled' AND student_status = 'Active'
                 ORDER BY last_name ASC, first_name ASC"
            );
            $stmtStud->execute([$classId]);
            $students = $stmtStud->fetchAll();

            // Fetch existing results for this subject/term/year
            $stmtRes = $this->db->prepare(
                "SELECT * FROM student_results 
                 WHERE class_id = ? AND subject_id = ? AND term = ? AND academic_year = ?"
            );
            $stmtRes->execute([$classId, $subjectId, $term, $academicYear]);
            foreach ($stmtRes->fetchAll() as $row) {
                $existingResults[$row['applicant_id']] = $row;
            }
        }

        render('teacher/results', compact('assignments', 'selectedIdx', 'selected', 'students', 'existingResults', 'term', 'academicYear'), 'teacher');
    }

    public function resultsSave(): void
    {
        $this->requireTeacher();
        verify_csrf();

        $classId = (int) ($_POST['class_id'] ?? 0);
        $subjectId = (int) ($_POST['subject_id'] ?? 0);
        $term = $_POST['term'] ?? current_term();
        $academicYear = $_POST['academic_year'] ?? current_academic_year();
        $staffId = (int) $_SESSION['teacher']['staff_table_id'];

        // Verify teacher assignment
        $chk = $this->db->prepare(
            "SELECT 1 FROM staff_class_assignments 
             WHERE staff_id = ? AND class_id = ? AND subject_id = ? AND academic_year = ? LIMIT 1"
        );
        $chk->execute([$staffId, $classId, $subjectId, $academicYear]);
        if (!$chk->fetch()) {
            flash('danger', 'Access denied to edit results.');
            redirect('teacher/results');
        }

        $ca1Map = $_POST['ca1'] ?? [];
        $ca2Map = $_POST['ca2'] ?? [];
        $assignmentMap = $_POST['assignment'] ?? [];
        $midTermMap = $_POST['mid_term'] ?? [];
        $examMap = $_POST['exam'] ?? [];
        $remarkMap = $_POST['teacher_remark'] ?? [];

        $stmtInsert = $this->db->prepare(
            "INSERT INTO student_results (applicant_id, subject_id, class_id, term, academic_year, ca1, ca2, assignment, mid_term, exam, total, grade, remark, teacher_remark, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
               ca1 = VALUES(ca1),
               ca2 = VALUES(ca2),
               assignment = VALUES(assignment),
               mid_term = VALUES(mid_term),
               exam = VALUES(exam),
               total = VALUES(total),
               grade = VALUES(grade),
               remark = VALUES(remark),
               teacher_remark = VALUES(teacher_remark),
               updated_at = NOW()"
        );

        foreach ($ca1Map as $appId => $ca1) {
            $ca1Val = $ca1 !== '' ? (float) $ca1 : null;
            $ca2Val = ($ca2Map[$appId] ?? '') !== '' ? (float) $ca2Map[$appId] : null;
            $assignVal = ($assignmentMap[$appId] ?? '') !== '' ? (float) $assignmentMap[$appId] : null;
            $midVal = ($midTermMap[$appId] ?? '') !== '' ? (float) $midTermMap[$appId] : null;
            $examVal = ($examMap[$appId] ?? '') !== '' ? (float) $examMap[$appId] : null;
            
            $totalVal = null;
            $grade = null;
            $remark = null;

            // Calculate total if at least one score is entered
            if ($ca1Val !== null || $ca2Val !== null || $assignVal !== null || $midVal !== null || $examVal !== null) {
                $totalVal = ($ca1Val ?? 0) + ($ca2Val ?? 0) + ($assignVal ?? 0) + ($midVal ?? 0) + ($examVal ?? 0);
                $eval = $this->getGradeAndRemark($totalVal);
                $grade = $eval['grade'];
                $remark = $eval['remark'];
            }

            $teacherRemark = $remarkMap[$appId] ?? '';

            $stmtInsert->execute([
                $appId, $subjectId, $classId, $term, $academicYear,
                $ca1Val, $ca2Val, $assignVal, $midVal, $examVal,
                $totalVal, $grade, $remark, $teacherRemark
            ]);
        }

        // Recalculate class sizes, positions, totals, and averages in background
        $this->updateTermRemarks($classId, $term, $academicYear);

        flash('success', 'Grades and academic results updated successfully.');
        // Redirect back with details
        $assignmentIdx = (int) ($_POST['selected_idx'] ?? 0);
        redirect("teacher/results?assignment_idx={$assignmentIdx}&term={$term}");
    }

    /* ─── Helpers ────────────────────────────────────────── */

    private function requireTeacher(bool $checkPasswordForce = true): void
    {
        if (!$this->teacherSession()) {
            redirect('teacher/login');
        }

        if ($checkPasswordForce) {
            $stmt = $this->db->prepare("SELECT must_change_password FROM staff_accounts WHERE id = ?");
            $stmt->execute([$_SESSION['teacher']['id']]);
            $mustChange = (bool) $stmt->fetchColumn();
            if ($mustChange) {
                redirect('teacher/change-password');
            }
        }
    }

    private function teacherSession(): ?array
    {
        return $_SESSION['teacher'] ?? null;
    }

    private function getGradeAndRemark(float $total): array
    {
        if ($total >= 70) return ['grade' => 'A', 'remark' => 'Excellent'];
        if ($total >= 60) return ['grade' => 'B', 'remark' => 'Very Good'];
        if ($total >= 50) return ['grade' => 'C', 'remark' => 'Good'];
        if ($total >= 45) return ['grade' => 'D', 'remark' => 'Pass'];
        if ($total >= 40) return ['grade' => 'E', 'remark' => 'Pass'];
        return ['grade' => 'F', 'remark' => 'Fail'];
    }

    private function notifyParentOfAttendance(int $studentId, string $status, string $date): void
    {
        try {
            $stmt = $this->db->prepare("SELECT first_name, last_name FROM applicants WHERE id = ?");
            $stmt->execute([$studentId]);
            $stud = $stmt->fetch();
            if (!$stud) return;
            $studentName = $stud['first_name'] . ' ' . $stud['last_name'];

            $parentStmt = $this->db->prepare("SELECT id, phone, email, parent_name FROM parent_accounts WHERE applicant_id = ? LIMIT 1");
            $parentStmt->execute([$studentId]);
            $parent = $parentStmt->fetch();

            if ($parent) {
                $formattedDate = date('M d, Y', strtotime($date));
                $msg = "Dear Parent, your child {$studentName} was marked {$status} for school on {$formattedDate}.";
                
                // Send alerts
                send_sms_notice($parent['phone'], $msg);
                log_sms($this->db, $parent['phone'], $parent['parent_name'] ?? ($studentName . ' Parent'), $msg);
                send_email_notice($parent['email'], "Child Attendance Alert — {$status}", $msg);
                create_notification($this->db, 'parent', $parent['id'], 'Attendance Alert', $msg);
            }
        } catch (Throwable $e) {
            error_log("Failed parent attendance alert: " . $e->getMessage());
        }
    }

    private function notifyParentOfScan(int $studentId, string $studentName, string $status, string $timeIn, string $date): void
    {
        try {
            $parentStmt = $this->db->prepare("SELECT id, phone, email, parent_name FROM parent_accounts WHERE applicant_id = ? LIMIT 1");
            $parentStmt->execute([$studentId]);
            $parent = $parentStmt->fetch();

            if ($parent) {
                $statusUpper = strtoupper($status);
                $msg = "Dear Parent, your child {$studentName} was marked {$statusUpper} at {$timeIn} today ({$date}).";
                
                // Send alerts
                send_sms_notice($parent['phone'], $msg);
                log_sms($this->db, $parent['phone'], $parent['parent_name'] ?? ($studentName . ' Parent'), $msg);
                send_email_notice($parent['email'], "School Attendance Check-In Alert", $msg);
                create_notification($this->db, 'parent', $parent['id'], 'School Check-In Alert', $msg);
            }

            // Also send notification to the student account
            $studAccStmt = $this->db->prepare("SELECT id FROM student_accounts WHERE applicant_id = ? LIMIT 1");
            $studAccStmt->execute([$studentId]);
            $studentAccId = $studAccStmt->fetchColumn();
            if ($studentAccId) {
                create_notification($this->db, 'student', $studentAccId, 'Attendance Marked', "You have been marked {$status} at {$timeIn} today.");
            }

        } catch (Throwable $e) {
            error_log("Failed parent scan alert: " . $e->getMessage());
        }
    }

    /**
     * Recalculates averages, total scores, class size, and rank positions for the term
     */
    private function updateTermRemarks(int $classId, string $term, string $academicYear): void
    {
        try {
            // Get all students enrolled in this class
            $stmtStudents = $this->db->prepare(
                "SELECT id FROM applicants WHERE class_id = ? AND status = 'Enrolled' AND student_status = 'Active'"
            );
            $stmtStudents->execute([$classId]);
            $studentIds = $stmtStudents->fetchAll(PDO::FETCH_COLUMN);
            $classSize = count($studentIds);

            if ($classSize === 0) return;

            $totals = [];
            foreach ($studentIds as $appId) {
                // Get sum of results for this student
                $stmtSum = $this->db->prepare(
                    "SELECT SUM(total) AS total_score, COUNT(total) AS subject_count 
                     FROM student_results 
                     WHERE applicant_id = ? AND term = ? AND academic_year = ?"
                );
                $stmtSum->execute([$appId, $term, $academicYear]);
                $sumData = $stmtSum->fetch();

                if ($sumData && $sumData['subject_count'] > 0) {
                    $totalScore = (float) $sumData['total_score'];
                    $average = round($totalScore / $sumData['subject_count'], 2);
                    $totals[$appId] = [
                        'total_score' => $totalScore,
                        'average' => $average
                    ];
                } else {
                    $totals[$appId] = [
                        'total_score' => 0,
                        'average' => 0
                    ];
                }
            }

            // Sort students by total score descending to determine position rank
            uasort($totals, function($a, $b) {
                return $b['total_score'] <=> $a['total_score'];
            });

            // Insert/update positions
            $rank = 1;
            $prevScore = -1;
            $actualRank = 1;

            $stmtUpsert = $this->db->prepare(
                "INSERT INTO term_remarks (applicant_id, class_id, term, academic_year, total_score, average, position, class_size, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE 
                   total_score = VALUES(total_score), 
                   average = VALUES(average), 
                   position = VALUES(position), 
                   class_size = VALUES(class_size),
                   updated_at = NOW()"
            );

            foreach ($totals as $appId => $data) {
                if ($data['total_score'] < $prevScore) {
                    $actualRank = $rank;
                }
                $prevScore = $data['total_score'];

                $stmtUpsert->execute([
                    $appId, $classId, $term, $academicYear,
                    $data['total_score'], $data['average'], $actualRank, $classSize
                ]);

                // Update results with position too
                $stmtResultUpdate = $this->db->prepare(
                    "UPDATE student_results SET class_size = ? WHERE applicant_id = ? AND class_id = ? AND term = ? AND academic_year = ?"
                );
                $stmtResultUpdate->execute([$classSize, $appId, $classId, $term, $academicYear]);

                $rank++;
            }

        } catch (Throwable $e) {
            error_log("Failed updating term remarks / rankings: " . $e->getMessage());
        }
    }
}
