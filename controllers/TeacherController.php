<?php
declare(strict_types=1);

// controllers/TeacherController.php — Production Staff & Teacher Management Controller

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/StaffAuth.php';
require_once __DIR__ . '/../config/StaffAudit.php';
require_once __DIR__ . '/../config/GradingService.php';
require_once __DIR__ . '/../models/Applicant.php';
require_once __DIR__ . '/../models/ClassModel.php';

final class TeacherController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /* ─── 1. Authentication & Session Handling ───────────────────────────── */

    public function login(): void
    {
        if (StaffAuth::check()) {
            redirect('teacher/dashboard');
        }
        render('teacher/login', [], 'teacher_auth');
    }

    public function authenticate(): void
    {
        verify_csrf();
        $username = trim($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            flash('danger', 'Please enter your username and password.');
            redirect('teacher/login');
        }

        $stmt = $this->db->prepare(
            "SELECT sa.*, s.first_name, s.last_name, s.role, s.role_id, s.staff_id AS public_staff_id, 
                    s.passport_photo, s.status, s.department,
                    COALESCE(r.name, NULLIF(s.role, ''), 'class_teacher') AS resolved_role
             FROM staff_accounts sa
             JOIN staff s ON s.id = sa.staff_id
             LEFT JOIN roles r ON r.id = s.role_id
             WHERE sa.username = ? LIMIT 1"
        );
        $stmt->execute([$username]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($account) {
            if ($account['status'] !== 'Active') {
                Logger::warn("Staff login attempt on inactive account", ['username' => $username]);
                flash('danger', 'Your staff account is currently inactive or on leave. Please contact administration.');
                redirect('teacher/login');
            }

            if (password_verify($password, $account['password_hash'])) {
                session_regenerate_id(true);

                $staffId = (int) $account['staff_id'];
                $roleKey = strtolower(str_replace(' ', '_', (string) $account['resolved_role']));

                // Clear any lingering static cache
                StaffAuth::resetCache();

                // Load permissions & assignments
                $permissions = StaffAuth::loadStaffPermissions($staffId, $roleKey);

                // Safe fallback for teachers
                if (empty($permissions) && stripos($roleKey, 'teacher') !== false) {
                    $permissions = StaffAuth::loadStaffPermissions($staffId, 'class_teacher');
                }

                $academicYear = current_academic_year();
                $stmtClass = $this->db->prepare(
                    "SELECT DISTINCT class_id FROM staff_class_assignments WHERE staff_id = ? AND academic_year = ?"
                );
                $stmtClass->execute([$staffId, $academicYear]);
                $assignedClasses = array_map('intval', $stmtClass->fetchAll(PDO::FETCH_COLUMN));

                $stmtSubj = $this->db->prepare(
                    "SELECT DISTINCT subject_id FROM staff_class_assignments WHERE staff_id = ? AND subject_id IS NOT NULL AND academic_year = ?"
                );
                $stmtSubj->execute([$staffId, $academicYear]);
                $assignedSubjects = array_map('intval', $stmtSubj->fetchAll(PDO::FETCH_COLUMN));

                $_SESSION['teacher'] = [
                    'id'                => (int) $account['id'],
                    'staff_table_id'    => $staffId,
                    'username'          => $account['username'],
                    'name'              => $account['first_name'] . ' ' . $account['last_name'],
                    'first_name'        => $account['first_name'],
                    'last_name'         => $account['last_name'],
                    'staff_id'          => $account['public_staff_id'],
                    'role'              => $roleKey,
                    'role_title'        => ucwords(str_replace('_', ' ', $roleKey)),
                    'role_id'           => $account['role_id'] ? (int) $account['role_id'] : null,
                    'department'        => $account['department'] ?? '',
                    'photo'             => $account['passport_photo'],
                    'permissions'       => $permissions,
                    'assigned_classes'  => $assignedClasses,
                    'assigned_subjects' => $assignedSubjects,
                ];

                $this->db->prepare("UPDATE staff_accounts SET last_login = NOW() WHERE id = ?")->execute([$account['id']]);

                StaffAudit::log('auth.login', 'staff_accounts', (int) $account['id'], "Staff logged in successfully ({$username})");

                if (!empty($account['must_change_password'])) {
                    redirect('teacher/change-password');
                }

                flash('success', "Welcome back, {$account['first_name']}!");
                redirect('teacher/dashboard');
            }
        }

        StaffAudit::log('auth.login_failed', 'staff_accounts', null, "Failed login attempt for username '{$username}'");
        flash('danger', 'Invalid username or password.');
        redirect('teacher/login');
    }

    public function logout(): never
    {
        if (StaffAuth::check()) {
            StaffAudit::log('auth.logout', 'staff_accounts', StaffAuth::accountId(), 'Staff logged out');
            unset($_SESSION['teacher']);
        }
        redirect('teacher/login');
    }

    public function changePasswordForm(): void
    {
        StaffAuth::requireAuth(false);
        render('teacher/change_password', ['pageTitle' => 'Change Password'], 'teacher_auth');
    }

    public function changePasswordSave(): void
    {
        StaffAuth::requireAuth(false);
        verify_csrf();

        $password = (string) ($_POST['password'] ?? '');
        $confirm  = (string) ($_POST['password_confirmation'] ?? '');

        if (strlen($password) < 6) {
            flash('danger', 'Password must be at least 6 characters.');
            redirect('teacher/change-password');
        }

        if ($password !== $confirm) {
            flash('danger', 'Password confirmation does not match.');
            redirect('teacher/change-password');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->db->prepare("UPDATE staff_accounts SET password_hash = ?, must_change_password = 0 WHERE id = ?")
            ->execute([$hash, StaffAuth::accountId()]);

        StaffAudit::log('auth.password_changed', 'staff_accounts', StaffAuth::accountId(), 'Staff updated account password');

        flash('success', 'Your password has been updated successfully. Welcome to your dashboard.');
        redirect('teacher/dashboard');
    }

    /* ─── 2. Staff Dashboard ─────────────────────────────────────────────── */

    public function dashboard(): void
    {
        StaffAuth::requireAuth();
        $staffId = StaffAuth::id();
        $academicYear = current_academic_year();
        $term = current_term();
        $today = date('Y-m-d');

        // 1. My Classes Count
        $classIds = StaffAuth::assignedClassIds();
        $classCount = count($classIds);

        // 2. My Students Count
        $studentCount = 0;
        $presentToday = 0;
        $absentToday  = 0;
        $lateToday    = 0;
        $attendancePercentage = 0.0;

        if (!empty($classIds)) {
            $inClasses = implode(',', array_map('intval', $classIds));
            
            $stmtStud = $this->db->query(
                "SELECT COUNT(*) FROM applicants WHERE class_id IN ({$inClasses}) AND status = 'Enrolled' AND student_status = 'Active'"
            );
            $studentCount = (int) $stmtStud->fetchColumn();

            // Attendance today in assigned classes
            $stmtAtt = $this->db->prepare(
                "SELECT status, COUNT(*) AS cnt 
                 FROM attendance 
                 WHERE class_id IN ({$inClasses}) AND date = ? 
                 GROUP BY status"
            );
            $stmtAtt->execute([$today]);
            $attRows = $stmtAtt->fetchAll(PDO::FETCH_KEY_PAIR);

            $presentToday = (int) ($attRows['Present'] ?? 0);
            $lateToday    = (int) ($attRows['Late'] ?? 0);
            $absentToday  = (int) ($attRows['Absent'] ?? 0);

            $markedTotal = $presentToday + $lateToday + $absentToday;
            if ($markedTotal > 0) {
                $attendancePercentage = round((($presentToday + $lateToday) / $markedTotal) * 100, 1);
            }
        }

        // 3. Pending Assignments (Assignments with ungraded submissions)
        $pendingAssignments = 0;
        try {
            $stmtAssign = $this->db->prepare(
                "SELECT COUNT(DISTINCT sub.id) 
                 FROM assignment_submissions sub
                 JOIN assignments a ON a.id = sub.assignment_id
                 WHERE a.teacher_id = ? AND sub.score IS NULL"
            );
            $stmtAssign->execute([$staffId]);
            $pendingAssignments = (int) $stmtAssign->fetchColumn();
        } catch (Throwable $e) {}

        // 4. Pending Results (Draft results entered by this teacher)
        $pendingResults = 0;
        try {
            $stmtRes = $this->db->prepare(
                "SELECT COUNT(*) FROM student_results 
                 WHERE class_id IN (" . (!empty($classIds) ? implode(',', $classIds) : '0') . ") 
                 AND term = ? AND academic_year = ? AND status IN ('draft', 'submitted')"
            );
            $stmtRes->execute([$term, $academicYear]);
            $pendingResults = (int) $stmtRes->fetchColumn();
        } catch (Throwable $e) {}

        // Form Teacher info
        $stmtForm = $this->db->prepare(
            "SELECT c.name FROM staff_class_assignments sca
             JOIN classes c ON c.id = sca.class_id
             WHERE sca.staff_id = ? AND sca.is_form_teacher = 1 AND sca.academic_year = ? LIMIT 1"
        );
        $stmtForm->execute([$staffId, $academicYear]);
        $formClassName = $stmtForm->fetchColumn() ?: 'None';

        // Today's Timetable Schedule
        $dayOfWeek = date('l');
        $stmtTime = $this->db->prepare(
            "SELECT t.*, s.name AS subject_name, c.name AS class_name
             FROM timetables t
             JOIN subjects s ON s.id = t.subject_id
             JOIN classes c ON c.id = t.class_id
             WHERE (t.teacher_id = ? OR t.class_id IN (" . (!empty($classIds) ? implode(',', $classIds) : '0') . ")) 
             AND t.day_of_week = ?
             ORDER BY t.start_time ASC"
        );
        $stmtTime->execute([$staffId, $dayOfWeek]);
        $todaySchedule = $stmtTime->fetchAll(PDO::FETCH_ASSOC);

        // Staff Announcements
        $announcements = $this->db->query(
            "SELECT * FROM announcements 
             WHERE is_published = 1 AND audience IN ('all', 'staff')
             AND (expires_at IS NULL OR expires_at > NOW())
             ORDER BY published_at DESC LIMIT 5"
        )->fetchAll(PDO::FETCH_ASSOC);

        render('teacher/dashboard', compact(
            'classCount', 'studentCount', 'presentToday', 'absentToday', 'lateToday',
            'attendancePercentage', 'pendingAssignments', 'pendingResults',
            'formClassName', 'todaySchedule', 'announcements', 'academicYear', 'term'
        ), 'teacher');
    }

    /* ─── 3. My Classes Module ───────────────────────────────────────────── */

    public function classList(): void
    {
        StaffAuth::requirePermission('classes.view');
        $staffId = StaffAuth::id();
        $academicYear = current_academic_year();

        if (StaffAuth::isSchoolAdmin()) {
            $stmt = $this->db->query(
                "SELECT c.id AS class_id, c.name AS class_name, c.sort_order,
                        (SELECT COUNT(*) FROM applicants a WHERE a.class_id = c.id AND a.status = 'Enrolled' AND a.student_status = 'Active') AS student_count
                 FROM classes c
                 ORDER BY c.sort_order ASC"
            );
            $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Group assignments
            $assignments = [];
            foreach ($classes as $cls) {
                $assignments[] = [
                    'class_id' => $cls['class_id'],
                    'class_name' => $cls['class_name'],
                    'subject_name' => 'All Subjects (Admin)',
                    'subject_code' => 'ALL',
                    'is_form_teacher' => 1,
                    'student_count' => $cls['student_count']
                ];
            }
        } else {
            $stmt = $this->db->prepare(
                "SELECT sca.*, c.name AS class_name, s.name AS subject_name, s.code AS subject_code,
                        (SELECT COUNT(*) FROM applicants a WHERE a.class_id = c.id AND a.status = 'Enrolled' AND a.student_status = 'Active') AS student_count
                 FROM staff_class_assignments sca
                 JOIN classes c ON c.id = sca.class_id
                 LEFT JOIN subjects s ON s.id = sca.subject_id
                 WHERE sca.staff_id = ? AND sca.academic_year = ?
                 ORDER BY c.sort_order ASC, s.name ASC"
            );
            $stmt->execute([$staffId, $academicYear]);
            $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        render('teacher/classes', compact('assignments', 'academicYear'), 'teacher');
    }

    public function classView(int $classId): void
    {
        StaffAuth::requirePermission('classes.view');
        StaffAuth::requireClass($classId);

        $stmtClass = $this->db->prepare("SELECT * FROM classes WHERE id = ? LIMIT 1");
        $stmtClass->execute([$classId]);
        $class = $stmtClass->fetch(PDO::FETCH_ASSOC);

        if (!$class) {
            flash('danger', 'Class not found.');
            redirect('teacher/classes');
        }

        // Fetch students in this class
        $stmtStud = $this->db->prepare(
            "SELECT id, first_name, last_name, admission_number, gender, date_of_birth, passport_photo, student_status
             FROM applicants
             WHERE class_id = ? AND status = 'Enrolled' AND student_status = 'Active'
             ORDER BY last_name ASC, first_name ASC"
        );
        $stmtStud->execute([$classId]);
        $students = $stmtStud->fetchAll(PDO::FETCH_ASSOC);

        // Class assignments for this class
        $stmtAssign = $this->db->prepare(
            "SELECT * FROM assignments WHERE class_id = ? ORDER BY due_date DESC LIMIT 5"
        );
        $stmtAssign->execute([$classId]);
        $assignments = $stmtAssign->fetchAll(PDO::FETCH_ASSOC);

        render('teacher/class_view', compact('class', 'students', 'assignments'), 'teacher');
    }

    /* ─── 4. My Students Module ──────────────────────────────────────────── */

    public function students(): void
    {
        StaffAuth::requirePermission('students.view');
        $classIds = StaffAuth::assignedClassIds();

        $students = [];
        $search = trim($_GET['q'] ?? '');
        $classFilter = (int) ($_GET['class_id'] ?? 0);
        $genderFilter = trim($_GET['gender'] ?? '');
        $statusFilter = trim($_GET['status'] ?? 'Active');

        $classes = [];
        if (!empty($classIds)) {
            $inClasses = implode(',', array_map('intval', $classIds));

            $stmtCls = $this->db->query("SELECT id, name FROM classes WHERE id IN ({$inClasses}) ORDER BY sort_order ASC");
            $classes = $stmtCls->fetchAll(PDO::FETCH_ASSOC);

            $sql = "SELECT a.id, a.first_name, a.last_name, a.admission_number, a.gender, a.date_of_birth, a.passport_photo, a.student_status,
                           c.name AS class_name, c.id AS class_id
                    FROM applicants a
                    JOIN classes c ON c.id = a.class_id
                    WHERE a.class_id IN ({$inClasses}) AND a.status = 'Enrolled'";
            $params = [];

            if ($search !== '') {
                $sql .= " AND (a.first_name LIKE ? OR a.last_name LIKE ? OR a.admission_number LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }

            if ($classFilter > 0 && in_array($classFilter, $classIds, true)) {
                $sql .= " AND a.class_id = ?";
                $params[] = $classFilter;
            }

            if ($genderFilter !== '') {
                $sql .= " AND a.gender = ?";
                $params[] = $genderFilter;
            }

            if ($statusFilter !== '') {
                $sql .= " AND a.student_status = ?";
                $params[] = $statusFilter;
            }

            $sql .= " ORDER BY c.sort_order ASC, a.last_name ASC, a.first_name ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        render('teacher/students', compact('students', 'classes', 'search', 'classFilter', 'genderFilter', 'statusFilter'), 'teacher');
    }

    public function studentProfile(int $studentId): void
    {
        StaffAuth::requirePermission('students.view');
        $student = StaffAuth::requireStudent($studentId);

        $academicYear = current_academic_year();
        $term = current_term();

        // 1. Academic Results
        $stmtResults = $this->db->prepare(
            "SELECT sr.*, s.name AS subject_name, s.code AS subject_code
             FROM student_results sr
             JOIN subjects s ON s.id = sr.subject_id
             WHERE sr.applicant_id = ?
             ORDER BY sr.academic_year DESC, sr.term ASC, s.name ASC"
        );
        $stmtResults->execute([$studentId]);
        $results = $stmtResults->fetchAll(PDO::FETCH_ASSOC);

        // 2. Attendance Summary
        $stmtAtt = $this->db->prepare(
            "SELECT status, COUNT(*) AS count FROM attendance WHERE applicant_id = ? GROUP BY status"
        );
        $stmtAtt->execute([$studentId]);
        $attSummary = $stmtAtt->fetchAll(PDO::FETCH_KEY_PAIR);

        $presentCount = (int) ($attSummary['Present'] ?? 0);
        $absentCount  = (int) ($attSummary['Absent'] ?? 0);
        $lateCount    = (int) ($attSummary['Late'] ?? 0);
        $totalDays    = $presentCount + $absentCount + $lateCount;
        $attendanceRate = $totalDays > 0 ? round((($presentCount + $lateCount) / $totalDays) * 100, 1) : 0;

        // 3. Assignment Submissions
        $stmtSub = $this->db->prepare(
            "SELECT sub.*, a.title AS assignment_title, a.max_score, a.due_date, s.name AS subject_name
             FROM assignment_submissions sub
             JOIN assignments a ON a.id = sub.assignment_id
             JOIN subjects s ON s.id = a.subject_id
             WHERE sub.applicant_id = ?
             ORDER BY sub.submitted_at DESC"
        );
        $stmtSub->execute([$studentId]);
        $submissions = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

        // 4. Behaviour / Teacher Remarks
        $stmtRemarks = $this->db->prepare(
            "SELECT tr.*, c.name AS class_name 
             FROM term_remarks tr 
             JOIN classes c ON c.id = tr.class_id 
             WHERE tr.applicant_id = ? 
             ORDER BY tr.academic_year DESC, tr.term DESC"
        );
        $stmtRemarks->execute([$studentId]);
        $remarks = $stmtRemarks->fetchAll(PDO::FETCH_ASSOC);

        render('teacher/student_profile', compact(
            'student', 'results', 'presentCount', 'absentCount', 'lateCount',
            'attendanceRate', 'totalDays', 'submissions', 'remarks', 'academicYear', 'term'
        ), 'teacher');
    }

    /* ─── 5. Attendance Management & QR Scanning ─────────────────────────── */

    public function attendanceForm(): void
    {
        StaffAuth::requirePermission('attendance.view');
        $classIds = StaffAuth::assignedClassIds();

        $classes = [];
        if (!empty($classIds)) {
            $inClasses = implode(',', array_map('intval', $classIds));
            $classes = $this->db->query("SELECT id, name FROM classes WHERE id IN ({$inClasses}) ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
        }

        $classId = (int) ($_GET['class_id'] ?? ($classes[0]['id'] ?? 0));
        $date    = $_GET['date'] ?? date('Y-m-d');

        $students = [];
        $existing = [];

        if ($classId > 0) {
            StaffAuth::requireClass($classId);

            $stmtStud = $this->db->prepare(
                "SELECT id, first_name, last_name, admission_number, passport_photo 
                 FROM applicants 
                 WHERE class_id = ? AND status = 'Enrolled' AND student_status = 'Active'
                 ORDER BY last_name ASC, first_name ASC"
            );
            $stmtStud->execute([$classId]);
            $students = $stmtStud->fetchAll(PDO::FETCH_ASSOC);

            $stmtAtt = $this->db->prepare("SELECT applicant_id, status, remark, time_in FROM attendance WHERE class_id = ? AND date = ?");
            $stmtAtt->execute([$classId, $date]);
            foreach ($stmtAtt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $existing[$row['applicant_id']] = $row;
            }
        }

        render('teacher/attendance', compact('classes', 'classId', 'date', 'students', 'existing'), 'teacher');
    }

    public function attendanceSave(): void
    {
        StaffAuth::requirePermission('attendance.mark');
        verify_csrf();

        $classId   = (int) ($_POST['class_id'] ?? 0);
        $date      = $_POST['date'] ?? date('Y-m-d');
        $statusMap = $_POST['status'] ?? [];
        $remarkMap = $_POST['remarks'] ?? [];
        $staffId   = StaffAuth::id();

        StaffAuth::requireClass($classId);

        if ($classId <= 0) {
            flash('danger', 'Invalid class selected.');
            redirect('teacher/attendance');
        }

        $stmtInsert = $this->db->prepare(
            "INSERT INTO attendance (applicant_id, class_id, date, status, remark, marked_by, time_in)
             VALUES (?, ?, ?, ?, ?, NULL, NULL)
             ON DUPLICATE KEY UPDATE status = VALUES(status), remark = VALUES(remark)"
        );

        foreach ($statusMap as $appId => $status) {
            $appId = (int) $appId;
            $remark = trim($remarkMap[$appId] ?? '');
            $stmtInsert->execute([$appId, $classId, $date, $status, $remark]);

            if (in_array($status, ['Absent', 'Late'], true)) {
                $this->notifyParentOfAttendance($appId, $status, $date);
            }
        }

        StaffAudit::log('attendance.marked', 'attendance', $classId, "Marked attendance for class #{$classId} on {$date} (" . count($statusMap) . " students)");

        flash('success', 'Attendance record saved successfully.');
        redirect("teacher/attendance?class_id={$classId}&date={$date}");
    }

    public function attendanceScanQRAjax(): void
    {
        StaffAuth::requirePermission('attendance.mark');

        $input = json_decode(file_get_contents('php://input'), true);
        $qrData = trim($input['qr_data'] ?? '');

        if ($qrData === '') {
            echo json_encode(['success' => false, 'message' => 'No QR code payload received.']);
            exit;
        }

        $token = $qrData;
        if (str_contains($qrData, 'token=')) {
            if (preg_match('/[?&]token=([^&]+)/', $qrData, $matches)) {
                $token = urldecode($matches[1]);
            }
        }
        $token = trim($token);

        if (!str_starts_with($token, 'ATTENDANCE-STD-')) {
            echo json_encode(['success' => false, 'message' => 'Invalid QR code signature. Not an EduCore student ID code.']);
            exit;
        }

        $parts = explode('-', $token);
        $studentId = isset($parts[2]) ? (int) $parts[2] : 0;

        if ($studentId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid student identifier in QR code.']);
            exit;
        }

        $stmtStud = $this->db->prepare(
            "SELECT a.id, a.first_name, a.last_name, a.class_id, a.status, a.student_status, a.qr_data, c.name AS class_name
             FROM applicants a
             LEFT JOIN classes c ON c.id = a.class_id
             WHERE a.id = ? LIMIT 1"
        );
        $stmtStud->execute([$studentId]);
        $student = $stmtStud->fetch(PDO::FETCH_ASSOC);

        if (!$student || $student['status'] !== 'Enrolled' || $student['student_status'] !== 'Active') {
            echo json_encode(['success' => false, 'message' => 'Student is not currently an active enrolled student.']);
            exit;
        }

        $classId = (int) $student['class_id'];

        // Strict Server-Side Access Control Check
        if (!StaffAuth::canAccessClass($classId)) {
            StaffAudit::log('security.idor_attempt', 'attendance', $studentId, "Unauthorized QR attendance scan attempt for student #{$studentId} in class #{$classId}");
            echo json_encode([
                'success' => false,
                'message' => "Access Denied: Student {$student['first_name']} belongs to {$student['class_name']}, which is not among your authorized classes."
            ]);
            exit;
        }

        $date = date('Y-m-d');
        $timeIn = date('H:i:s');
        $status = ($timeIn > '08:00:00') ? 'Late' : 'Present';
        $staffId = StaffAuth::id();

        // Prevent duplicate attendance for today
        $stmtChk = $this->db->prepare("SELECT status, time_in FROM attendance WHERE applicant_id = ? AND date = ?");
        $stmtChk->execute([$studentId, $date]);
        $existing = $stmtChk->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            echo json_encode([
                'success' => false,
                'message' => "{$student['first_name']} has already been checked in as {$existing['status']} today at " . date('h:i A', strtotime($existing['time_in'] ?? 'now'))
            ]);
            exit;
        }

        $stmtIns = $this->db->prepare(
            "INSERT INTO attendance (applicant_id, class_id, date, status, marked_by, time_in)
             VALUES (?, ?, ?, ?, NULL, ?)
             ON DUPLICATE KEY UPDATE status = VALUES(status), time_in = VALUES(time_in)"
        );
        $stmtIns->execute([$studentId, $classId, $date, $status, $timeIn]);

        $studentName = $student['first_name'] . ' ' . $student['last_name'];
        $formattedTime = date('h:i A', strtotime($timeIn));

        $this->notifyParentOfScan($studentId, $studentName, $status, $formattedTime, $date);
        StaffAudit::log('attendance.qr_scan', 'attendance', $studentId, "QR scanned {$studentName} as {$status} at {$formattedTime}");

        echo json_encode([
            'success'      => true,
            'message'      => "Attendance Confirmed: {$studentName} marked {$status}",
            'student_name' => $studentName,
            'status'       => $status,
            'time_in'      => $formattedTime,
            'class_name'   => $student['class_name']
        ]);
        exit;
    }

    public function attendanceReport(): void
    {
        StaffAuth::requirePermission('attendance.view');
        $classIds = StaffAuth::assignedClassIds();

        $month = $_GET['month'] ?? date('Y-m');
        $classId = (int) ($_GET['class_id'] ?? ($classIds[0] ?? 0));

        if ($classId > 0) {
            StaffAuth::requireClass($classId);
        }

        $classes = [];
        if (!empty($classIds)) {
            $inClasses = implode(',', array_map('intval', $classIds));
            $classes = $this->db->query("SELECT id, name FROM classes WHERE id IN ({$inClasses}) ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
        }

        $records = [];
        if ($classId > 0) {
            $stmt = $this->db->prepare(
                "SELECT a.date, a.status, COUNT(*) as count 
                 FROM attendance a 
                 WHERE a.class_id = ? AND a.date LIKE ? 
                 GROUP BY a.date, a.status 
                 ORDER BY a.date ASC"
            );
            $stmt->execute([$classId, "{$month}%"]);
            $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        render('teacher/attendance_report', compact('classes', 'classId', 'month', 'records'), 'teacher');
    }

    /* ─── 6. Results Management & Workflow ───────────────────────────────── */

    public function resultsForm(): void
    {
        StaffAuth::requirePermission('results.view');
        $staffId = StaffAuth::id();

        // 1. Session & Term
        $academicYear = $_GET['academic_year'] ?? current_academic_year();
        $term = $_GET['term'] ?? current_term();

        // 2. Fetch permitted classes for this teacher
        if (StaffAuth::isSchoolAdmin()) {
            $classes = (new ClassModel($this->db))->all();
        } else {
            $stmtClasses = $this->db->prepare(
                "SELECT DISTINCT c.id, c.name, c.sort_order 
                 FROM staff_class_assignments sca
                 JOIN classes c ON c.id = sca.class_id
                 WHERE sca.staff_id = ? AND sca.academic_year = ?
                 ORDER BY c.sort_order ASC, c.name ASC"
            );
            $stmtClasses->execute([$staffId, $academicYear]);
            $classes = $stmtClasses->fetchAll(PDO::FETCH_ASSOC);
        }

        $classId = (int) ($_GET['class_id'] ?? ($classes[0]['id'] ?? 0));

        // 3. Verify class access
        $isFormTeacher = false;
        if ($classId > 0) {
            StaffAuth::requireClass($classId);

            // Check if teacher is Form Teacher of this class
            $stmtFT = $this->db->prepare(
                "SELECT 1 FROM staff_class_assignments WHERE staff_id = ? AND class_id = ? AND is_form_teacher = 1 AND academic_year = ? LIMIT 1"
            );
            $stmtFT->execute([$staffId, $classId, $academicYear]);
            $isFormTeacher = (bool) $stmtFT->fetchColumn() || StaffAuth::isSchoolAdmin();
        }

        // 4. Fetch permitted subjects for selected class (dynamic from class_subjects)
        $subjects = [];
        if ($classId > 0) {
            $classAllSubjects = ResultService::getSubjectsForClass($classId);
            if ($isFormTeacher || StaffAuth::isSchoolAdmin()) {
                // Form Teachers & Admins get ALL subjects configured for this class
                $subjects = $classAllSubjects;
            } else {
                // Subject teachers only get their assigned subjects that are ALSO offered by this class
                $classSubjectIds = array_column($classAllSubjects, 'id');
                if (!empty($classSubjectIds)) {
                    $inList = implode(',', array_map('intval', $classSubjectIds));
                    $stmtSubjs = $this->db->prepare(
                        "SELECT DISTINCT s.id, s.name, s.code 
                         FROM staff_class_assignments sca
                         JOIN subjects s ON s.id = sca.subject_id
                         WHERE sca.staff_id = ? AND sca.class_id = ? AND sca.academic_year = ? AND s.id IN ({$inList})
                         ORDER BY s.name ASC"
                    );
                    $stmtSubjs->execute([$staffId, $classId, $academicYear]);
                    $subjects = $stmtSubjs->fetchAll(PDO::FETCH_ASSOC);
                }
            }
        }

        $subjectId = (int) ($_GET['subject_id'] ?? ($subjects[0]['id'] ?? 0));

        // 5. Selected class & subject info
        $selectedClass = null;
        foreach ($classes as $c) {
            if ((int)$c['id'] === $classId) {
                $selectedClass = $c;
                break;
            }
        }

        $selectedSubject = null;
        foreach ($subjects as $sb) {
            if ((int)$sb['id'] === $subjectId) {
                $selectedSubject = $sb;
                break;
            }
        }

        $students = [];
        $existingResults = [];
        $workflowStatus = 'draft';

        // Analytics
        $totalStudents = 0;
        $gradedCount = 0;
        $missingCount = 0;
        $classAverage = 0.0;
        $highestScore = 0.0;
        $lowestScore = 0.0;
        $passRate = 0.0;

        if ($classId > 0 && $subjectId > 0) {
            StaffAuth::requireSubject($classId, $subjectId);

            // Fetch students enrolled in this class
            $stmtStud = $this->db->prepare(
                "SELECT id, first_name, last_name, admission_number, passport_photo, gender 
                 FROM applicants 
                 WHERE class_id = ? AND status = 'Enrolled' AND student_status = 'Active'
                 ORDER BY last_name ASC, first_name ASC"
            );
            $stmtStud->execute([$classId]);
            $students = $stmtStud->fetchAll(PDO::FETCH_ASSOC);
            $totalStudents = count($students);

            // Fetch existing results
            $stmtRes = $this->db->prepare(
                "SELECT * FROM student_results 
                 WHERE class_id = ? AND subject_id = ? AND term = ? AND academic_year = ?"
            );
            $stmtRes->execute([$classId, $subjectId, $term, $academicYear]);
            $resRows = $stmtRes->fetchAll(PDO::FETCH_ASSOC);

            $validTotals = [];
            foreach ($resRows as $row) {
                $existingResults[$row['applicant_id']] = $row;
                if (!empty($row['status'])) {
                    $workflowStatus = $row['status'];
                }

                if ($row['total'] !== null) {
                    $validTotals[] = (float) $row['total'];
                }
            }

            // Compute missing & graded counts
            foreach ($students as $st) {
                $r = $existingResults[$st['id']] ?? null;
                if (!$r || ($r['ca1'] === null && $r['ca2'] === null && $r['assignment'] === null && $r['exam'] === null)) {
                    $missingCount++;
                } else {
                    $gradedCount++;
                }
            }

            if (!empty($validTotals)) {
                $classAverage = round(array_sum($validTotals) / count($validTotals), 1);
                $highestScore = max($validTotals);
                $lowestScore = min($validTotals);
                $passCount = count(array_filter($validTotals, fn($sc) => $sc >= 50.0));
                $passRate = round(($passCount / count($validTotals)) * 100, 1);
            }
        }

        $components = ResultService::getAssessmentComponents();
        $classOverview = $classId > 0 ? ResultService::getClassResultOverview($classId, $term, $academicYear) : [];

        render('teacher/results', compact(
            'classes', 'classId', 'selectedClass',
            'subjects', 'subjectId', 'selectedSubject',
            'isFormTeacher', 'term', 'academicYear',
            'students', 'existingResults', 'workflowStatus',
            'totalStudents', 'gradedCount', 'missingCount',
            'classAverage', 'highestScore', 'lowestScore', 'passRate',
            'components', 'classOverview'
        ), 'teacher');
    }

    public function resultsSave(): void
    {
        StaffAuth::requirePermission('results.enter');
        verify_csrf();

        $classId      = (int) ($_POST['class_id'] ?? 0);
        $subjectId    = (int) ($_POST['subject_id'] ?? 0);
        $term         = $_POST['term'] ?? current_term();
        $academicYear = $_POST['academic_year'] ?? current_academic_year();
        $staffId      = StaffAuth::id();

        StaffAuth::requireClass($classId);
        StaffAuth::requireSubject($classId, $subjectId);

        // Verify result is not locked
        $stmtCheckLock = $this->db->prepare(
            "SELECT status FROM student_results 
             WHERE class_id = ? AND subject_id = ? AND term = ? AND academic_year = ? LIMIT 1"
        );
        $stmtCheckLock->execute([$classId, $subjectId, $term, $academicYear]);
        $currentStatus = $stmtCheckLock->fetchColumn();

        if (in_array($currentStatus, ['approved', 'published'], true) && !StaffAuth::can('results.approve')) {
            flash('danger', "These results are currently {$currentStatus} and locked against modifications. Contact an administrator for authorized corrections.");
            redirect("teacher/results?class_id={$classId}&subject_id={$subjectId}&term={$term}&academic_year={$academicYear}");
        }

        $ca1Map        = $_POST['ca1'] ?? [];
        $ca2Map        = $_POST['ca2'] ?? [];
        $assignmentMap = $_POST['assignment'] ?? [];
        $midTermMap    = $_POST['mid_term'] ?? [];
        $examMap       = $_POST['exam'] ?? [];
        $remarkMap     = $_POST['teacher_remark'] ?? [];

        $stmtInsert = $this->db->prepare(
            "INSERT INTO student_results (applicant_id, subject_id, class_id, term, academic_year, ca1, ca2, assignment, mid_term, exam, total, grade, remark, teacher_remark, status, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', NOW())
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

        $components = ResultService::getAssessmentComponents();
        $maxScores = [];
        foreach ($components as $c) {
            $maxScores[$c['code']] = $c['max_score'];
        }

        foreach ($ca1Map as $appId => $ca1) {
            $appId = (int) $appId;
            $ca1Val    = $ca1 !== '' ? (float) $ca1 : null;
            $ca2Val    = ($ca2Map[$appId] ?? '') !== '' ? (float) $ca2Map[$appId] : null;
            $assignVal = ($assignmentMap[$appId] ?? '') !== '' ? (float) $assignmentMap[$appId] : null;
            $midVal    = ($midTermMap[$appId] ?? '') !== '' ? (float) $midTermMap[$appId] : null;
            $examVal   = ($examMap[$appId] ?? '') !== '' ? (float) $examMap[$appId] : null;

            // Enforce component bounds
            if ($ca1Val !== null && $ca1Val > ($maxScores['ca1'] ?? 15.0)) $ca1Val = $maxScores['ca1'] ?? 15.0;
            if ($ca2Val !== null && $ca2Val > ($maxScores['ca2'] ?? 10.0)) $ca2Val = $maxScores['ca2'] ?? 10.0;
            if ($assignVal !== null && $assignVal > ($maxScores['assignment'] ?? 10.0)) $assignVal = $maxScores['assignment'] ?? 10.0;
            if ($midVal !== null && $midVal > ($maxScores['mid_term'] ?? 10.0)) $midVal = $maxScores['mid_term'] ?? 10.0;
            if ($examVal !== null && $examVal > ($maxScores['exam'] ?? 55.0)) $examVal = $maxScores['exam'] ?? 55.0;

            $totalVal = null;
            $grade    = null;
            $remark   = null;

            if ($ca1Val !== null || $ca2Val !== null || $assignVal !== null || $midVal !== null || $examVal !== null) {
                $totalVal = ($ca1Val ?? 0) + ($ca2Val ?? 0) + ($assignVal ?? 0) + ($midVal ?? 0) + ($examVal ?? 0);
                $eval     = GradingService::evaluate($totalVal);
                $grade    = $eval['grade'];
                $remark   = $eval['remark'];
            }

            $teacherRemark = trim($remarkMap[$appId] ?? '');

            $stmtInsert->execute([
                $appId, $subjectId, $classId, $term, $academicYear,
                $ca1Val, $ca2Val, $assignVal, $midVal, $examVal,
                $totalVal, $grade, $remark, $teacherRemark
            ]);
        }

        // Recalculate true class averages and positions based on actual student subjects
        ResultService::recalculateClassPositions($classId, $term, $academicYear);

        if (in_array($currentStatus, ['approved', 'published'], true)) {
            StaffAudit::log('results.corrected', 'student_results', $classId, "Authorized correction applied to {$currentStatus} results for Class #{$classId} Subject #{$subjectId}");
            flash('success', 'Authorized corrections saved and class rankings recalculated.');
        } else {
            StaffAudit::log('results.draft_saved', 'student_results', $classId, "Saved draft results for class #{$classId} subject #{$subjectId} ({$term} Term, {$academicYear})");
            flash('success', 'Draft results and grades saved successfully. You can continue editing or submit for review when finished.');
        }

        redirect("teacher/results?class_id={$classId}&subject_id={$subjectId}&term={$term}&academic_year={$academicYear}");
    }

    public function resultsSubmit(): void
    {
        StaffAuth::requirePermission('results.submit');
        verify_csrf();

        $classId      = (int) ($_POST['class_id'] ?? 0);
        $subjectId    = (int) ($_POST['subject_id'] ?? 0);
        $term         = $_POST['term'] ?? current_term();
        $academicYear = $_POST['academic_year'] ?? current_academic_year();
        $staffId      = StaffAuth::id();

        StaffAuth::requireClass($classId);
        StaffAuth::requireSubject($classId, $subjectId);

        $stmt = $this->db->prepare(
            "UPDATE student_results 
             SET status = 'submitted', submitted_at = NOW(), submitted_by = ? 
             WHERE class_id = ? AND subject_id = ? AND term = ? AND academic_year = ?"
        );
        $stmt->execute([$staffId, $classId, $subjectId, $term, $academicYear]);

        StaffAudit::log('results.submitted', 'student_results', $classId, "Submitted results for approval: Class #{$classId} Subject #{$subjectId} ({$term} Term, {$academicYear})");

        flash('success', 'Results submitted successfully for administrative approval. The Principal / Administrator has been notified.');
        redirect("teacher/results?class_id={$classId}&subject_id={$subjectId}&term={$term}&academic_year={$academicYear}");
    }

    public function resultsApprove(): void
    {
        StaffAuth::requirePermission('results.approve');
        verify_csrf();

        $classId      = (int) ($_POST['class_id'] ?? 0);
        $subjectId    = (int) ($_POST['subject_id'] ?? 0);
        $term         = $_POST['term'] ?? current_term();
        $academicYear = $_POST['academic_year'] ?? current_academic_year();
        $staffId      = StaffAuth::id();

        $stmt = $this->db->prepare(
            "UPDATE student_results 
             SET status = 'approved', approved_at = NOW(), approved_by = ? 
             WHERE class_id = ? AND subject_id = ? AND term = ? AND academic_year = ?"
        );
        $stmt->execute([$staffId, $classId, $subjectId, $term, $academicYear]);

        StaffAudit::log('results.approved', 'student_results', $classId, "Approved academic results: Class #{$classId} Subject #{$subjectId} ({$term} Term)");

        flash('success', 'Results approved successfully. Grades are now locked from regular teacher modifications.');
        redirect("teacher/results?class_id={$classId}&subject_id={$subjectId}&term={$term}&academic_year={$academicYear}");
    }

    public function resultsPublish(): void
    {
        StaffAuth::requirePermission('results.publish');
        verify_csrf();

        $classId      = (int) ($_POST['class_id'] ?? 0);
        $subjectId    = (int) ($_POST['subject_id'] ?? 0);
        $term         = $_POST['term'] ?? current_term();
        $academicYear = $_POST['academic_year'] ?? current_academic_year();
        $staffId      = StaffAuth::id();

        $stmt = $this->db->prepare(
            "UPDATE student_results 
             SET status = 'published', published_at = NOW(), published_by = ? 
             WHERE class_id = ? AND subject_id = ? AND term = ? AND academic_year = ?"
        );
        $stmt->execute([$staffId, $classId, $subjectId, $term, $academicYear]);

        StaffAudit::log('results.published', 'student_results', $classId, "Published results to students and parents for Class #{$classId} Subject #{$subjectId} ({$term} Term)");

        flash('success', 'Results published successfully! Students and parents can now view report cards on their portals.');
        redirect("teacher/results?class_id={$classId}&subject_id={$subjectId}&term={$term}&academic_year={$academicYear}");
    }

    public function resultsPrint(): void
    {
        StaffAuth::requirePermission('results.view');

        $classId      = (int) ($_GET['class_id'] ?? 0);
        $subjectId    = (int) ($_GET['subject_id'] ?? 0);
        $term         = $_GET['term'] ?? current_term();
        $academicYear = $_GET['academic_year'] ?? current_academic_year();

        StaffAuth::requireClass($classId);
        StaffAuth::requireSubject($classId, $subjectId);

        $stmtClass = $this->db->prepare("SELECT * FROM classes WHERE id = ? LIMIT 1");
        $stmtClass->execute([$classId]);
        $class = $stmtClass->fetch(PDO::FETCH_ASSOC);

        $stmtSubj = $this->db->prepare("SELECT * FROM subjects WHERE id = ? LIMIT 1");
        $stmtSubj->execute([$subjectId]);
        $subject = $stmtSubj->fetch(PDO::FETCH_ASSOC);

        $stmtStud = $this->db->prepare(
            "SELECT id, first_name, last_name, admission_number, gender 
             FROM applicants 
             WHERE class_id = ? AND status = 'Enrolled' AND student_status = 'Active'
             ORDER BY last_name ASC, first_name ASC"
        );
        $stmtStud->execute([$classId]);
        $students = $stmtStud->fetchAll(PDO::FETCH_ASSOC);

        $stmtRes = $this->db->prepare(
            "SELECT * FROM student_results 
             WHERE class_id = ? AND subject_id = ? AND term = ? AND academic_year = ?"
        );
        $stmtRes->execute([$classId, $subjectId, $term, $academicYear]);
        $results = [];
        foreach ($stmtRes->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $results[$r['applicant_id']] = $r;
        }

        render('teacher/results_print', compact('class', 'subject', 'term', 'academicYear', 'students', 'results'), 'print');
    }

    /* ─── 7. Assignments Module ──────────────────────────────────────────── */

    public function assignmentsList(): void
    {
        StaffAuth::requirePermission('assignments.view');
        $staffId = StaffAuth::id();
        $classIds = StaffAuth::assignedClassIds();

        $assignments = [];
        if (!empty($classIds)) {
            $inClasses = implode(',', array_map('intval', $classIds));
            $stmt = $this->db->prepare(
                "SELECT a.*, c.name AS class_name, s.name AS subject_name,
                        (SELECT COUNT(*) FROM assignment_submissions sub WHERE sub.assignment_id = a.id) AS submission_count,
                        (SELECT COUNT(*) FROM assignment_submissions sub WHERE sub.assignment_id = a.id AND sub.score IS NOT NULL) AS graded_count
                 FROM assignments a
                 JOIN classes c ON c.id = a.class_id
                 JOIN subjects s ON s.id = a.subject_id
                 WHERE a.class_id IN ({$inClasses}) AND (a.teacher_id = ? OR ? = 1)
                 ORDER BY a.created_at DESC"
            );
            $stmt->execute([$staffId, StaffAuth::isSchoolAdmin() ? 1 : 0]);
            $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        render('teacher/assignments', compact('assignments'), 'teacher');
    }

    public function assignmentCreate(): void
    {
        StaffAuth::requirePermission('assignments.create');
        $staffId = StaffAuth::id();
        $academicYear = current_academic_year();

        // Get classes and subjects assigned to this teacher
        if (StaffAuth::isSchoolAdmin()) {
            $classes = (new ClassModel($this->db))->all();
            $subjects = $this->db->query("SELECT * FROM subjects WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmtCls = $this->db->prepare(
                "SELECT DISTINCT c.id, c.name 
                 FROM staff_class_assignments sca
                 JOIN classes c ON c.id = sca.class_id
                 WHERE sca.staff_id = ? AND sca.academic_year = ?
                 ORDER BY c.sort_order ASC"
            );
            $stmtCls->execute([$staffId, $academicYear]);
            $classes = $stmtCls->fetchAll(PDO::FETCH_ASSOC);

            $stmtSubj = $this->db->prepare(
                "SELECT DISTINCT s.id, s.name, s.code 
                 FROM staff_class_assignments sca
                 JOIN subjects s ON s.id = sca.subject_id
                 WHERE sca.staff_id = ? AND sca.academic_year = ?
                 ORDER BY s.name ASC"
            );
            $stmtSubj->execute([$staffId, $academicYear]);
            $subjects = $stmtSubj->fetchAll(PDO::FETCH_ASSOC);
        }

        render('teacher/assignment_create', compact('classes', 'subjects'), 'teacher');
    }

    public function assignmentSave(): void
    {
        StaffAuth::requirePermission('assignments.create');
        verify_csrf();

        $classId      = (int) ($_POST['class_id'] ?? 0);
        $subjectId    = (int) ($_POST['subject_id'] ?? 0);
        $title        = trim($_POST['title'] ?? '');
        $instructions = trim($_POST['instructions'] ?? '');
        $dueDate      = $_POST['due_date'] ?? date('Y-m-d', strtotime('+7 days'));
        $maxScore     = (float) ($_POST['max_score'] ?? 100);
        $staffId      = StaffAuth::id();

        StaffAuth::requireClass($classId);

        if ($title === '' || $classId <= 0 || $subjectId <= 0) {
            flash('danger', 'Please enter a title, and select a valid class and subject.');
            redirect('teacher/assignments/create');
        }

        $attachmentPath = null;
        if (!empty($_FILES['attachment']['name'])) {
            try {
                $allowed = ['pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
                $attachmentPath = upload_file('attachment', 'assignments', $allowed);
            } catch (Throwable $e) {}
        }

        $stmt = $this->db->prepare(
            "INSERT INTO assignments (class_id, subject_id, teacher_id, title, instructions, due_date, attachment, max_score, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())"
        );
        $stmt->execute([$classId, $subjectId, $staffId, $title, $instructions, $dueDate, $attachmentPath, $maxScore]);
        $assignmentId = (int) $this->db->lastInsertId();

        StaffAudit::log('assignment.created', 'assignments', $assignmentId, "Created assignment '{$title}' for Class #{$classId}");

        flash('success', 'Assignment published successfully.');
        redirect('teacher/assignments');
    }

    public function assignmentView(int $assignmentId): void
    {
        StaffAuth::requirePermission('assignments.view');

        $stmt = $this->db->prepare(
            "SELECT a.*, c.name AS class_name, s.name AS subject_name 
             FROM assignments a 
             JOIN classes c ON c.id = a.class_id 
             JOIN subjects s ON s.id = a.subject_id 
             WHERE a.id = ? LIMIT 1"
        );
        $stmt->execute([$assignmentId]);
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$assignment) {
            flash('danger', 'Assignment not found.');
            redirect('teacher/assignments');
        }

        StaffAuth::requireClass((int) $assignment['class_id']);

        // Fetch submissions
        $stmtSub = $this->db->prepare(
            "SELECT sub.*, a.first_name, a.last_name, a.admission_number, a.passport_photo 
             FROM assignment_submissions sub
             JOIN applicants a ON a.id = sub.applicant_id
             WHERE sub.assignment_id = ?
             ORDER BY sub.submitted_at DESC"
        );
        $stmtSub->execute([$assignmentId]);
        $submissions = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

        render('teacher/assignment_view', compact('assignment', 'submissions'), 'teacher');
    }

    public function assignmentGrade(int $assignmentId): void
    {
        StaffAuth::requirePermission('assignments.grade');
        verify_csrf();

        $submissionId = (int) ($_POST['submission_id'] ?? 0);
        $score        = $_POST['score'] !== '' ? (float) $_POST['score'] : null;
        $feedback     = trim($_POST['feedback'] ?? '');
        $staffId      = StaffAuth::id();

        $stmt = $this->db->prepare("SELECT a.class_id FROM assignments a WHERE a.id = ? LIMIT 1");
        $stmt->execute([$assignmentId]);
        $classId = (int) $stmt->fetchColumn();

        StaffAuth::requireClass($classId);

        $this->db->prepare(
            "UPDATE assignment_submissions 
             SET score = ?, feedback = ?, graded_by = ?, graded_at = NOW() 
             WHERE id = ? AND assignment_id = ?"
        )->execute([$score, $feedback, $staffId, $submissionId, $assignmentId]);

        StaffAudit::log('assignment.graded', 'assignment_submissions', $submissionId, "Graded submission for assignment #{$assignmentId} with score {$score}");

        flash('success', 'Student submission graded.');
        redirect("teacher/assignments/{$assignmentId}");
    }

    /* ─── 8. Timetable Module ────────────────────────────────────────────── */

    public function timetable(): void
    {
        StaffAuth::requirePermission('timetable.view');
        $staffId = StaffAuth::id();
        $classIds = StaffAuth::assignedClassIds();

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $scheduleByDay = array_fill_keys($days, []);

        if (!empty($classIds)) {
            $inClasses = implode(',', array_map('intval', $classIds));
            $stmt = $this->db->prepare(
                "SELECT t.*, s.name AS subject_name, s.code AS subject_code, c.name AS class_name
                 FROM timetables t
                 JOIN subjects s ON s.id = t.subject_id
                 JOIN classes c ON c.id = t.class_id
                 WHERE (t.teacher_id = ? OR t.class_id IN ({$inClasses}))
                 ORDER BY t.start_time ASC"
            );
            $stmt->execute([$staffId]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $r) {
                $day = ucfirst(strtolower($r['day_of_week']));
                if (isset($scheduleByDay[$day])) {
                    $scheduleByDay[$day][] = $r;
                }
            }
        }

        render('teacher/timetable', compact('scheduleByDay', 'days'), 'teacher');
    }

    /* ─── 9. Parent Messages & Communications ────────────────────────────── */

    public function messages(): void
    {
        StaffAuth::requirePermission('messages.view');
        $classIds = StaffAuth::assignedClassIds();

        $students = [];
        if (!empty($classIds)) {
            $inClasses = implode(',', array_map('intval', $classIds));
            $stmt = $this->db->query(
                "SELECT a.id, a.first_name, a.last_name, a.admission_number, a.parent_name, a.parent_phone, a.parent_email, c.name as class_name
                 FROM applicants a
                 JOIN classes c ON c.id = a.class_id
                 WHERE a.class_id IN ({$inClasses}) AND a.status = 'Enrolled'
                 ORDER BY c.sort_order ASC, a.last_name ASC"
            );
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        render('teacher/messages', compact('students'), 'teacher');
    }

    public function sendMessage(): void
    {
        StaffAuth::requirePermission('messages.send');
        verify_csrf();

        $studentId = (int) ($_POST['student_id'] ?? 0);
        $message   = trim($_POST['message'] ?? '');
        $channel   = $_POST['channel'] ?? 'sms'; // 'sms' or 'email'

        if ($studentId <= 0 || $message === '') {
            flash('danger', 'Please select a student and provide a message.');
            redirect('teacher/messages');
        }

        StaffAuth::requireStudent($studentId);

        $stmt = $this->db->prepare("SELECT first_name, last_name, parent_name, parent_phone, parent_email FROM applicants WHERE id = ? LIMIT 1");
        $stmt->execute([$studentId]);
        $stud = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stud) {
            $teacherName = StaffAuth::user()['name'] ?? 'Class Teacher';
            $msgBody = "EduCore Notice from {$teacherName} regarding {$stud['first_name']}: {$message}";

            if ($channel === 'sms' && !empty($stud['parent_phone'])) {
                send_sms_notice($stud['parent_phone'], $msgBody);
                log_sms($this->db, $stud['parent_phone'], $stud['parent_name'] ?? 'Parent', $msgBody);
            } elseif ($channel === 'email' && !empty($stud['parent_email'])) {
                send_email_notice($stud['parent_email'], "Teacher Notice — {$stud['first_name']}", $msgBody);
            }

            StaffAudit::log('message.sent', 'applicants', $studentId, "Sent parent {$channel} regarding student #{$studentId}");
            flash('success', 'Message dispatched to parent successfully.');
        }

        redirect('teacher/messages');
    }

    /* ─── 10. Announcements Module ───────────────────────────────────────── */

    public function announcements(): void
    {
        StaffAuth::requirePermission('announcements.view');
        $classIds = StaffAuth::assignedClassIds();

        $announcements = $this->db->query(
            "SELECT a.*, c.name AS class_name 
             FROM announcements a
             LEFT JOIN classes c ON c.id = a.class_id
             WHERE a.is_published = 1 AND a.audience IN ('all', 'staff', 'class')
             ORDER BY a.published_at DESC LIMIT 20"
        )->fetchAll(PDO::FETCH_ASSOC);

        $classes = [];
        if (!empty($classIds)) {
            $inClasses = implode(',', array_map('intval', $classIds));
            $classes = $this->db->query("SELECT id, name FROM classes WHERE id IN ({$inClasses}) ORDER BY sort_order ASC")->fetchAll(PDO::FETCH_ASSOC);
        }

        render('teacher/announcements', compact('announcements', 'classes'), 'teacher');
    }

    public function createAnnouncement(): void
    {
        StaffAuth::requirePermission('announcements.create');
        verify_csrf();

        $title    = trim($_POST['title'] ?? '');
        $body     = trim($_POST['body'] ?? '');
        $audience = $_POST['audience'] ?? 'class';
        $classId  = !empty($_POST['class_id']) ? (int) $_POST['class_id'] : null;
        $staffId  = StaffAuth::id();

        if ($classId !== null) {
            StaffAuth::requireClass($classId);
        }

        if ($title === '' || $body === '') {
            flash('danger', 'Please enter an announcement title and message.');
            redirect('teacher/announcements');
        }

        $stmt = $this->db->prepare(
            "INSERT INTO announcements (title, body, audience, class_id, is_published, published_at, created_by, created_at)
             VALUES (?, ?, ?, ?, 1, NOW(), ?, NOW())"
        );
        $stmt->execute([$title, $body, $audience, $classId, $staffId]);

        StaffAudit::log('announcement.created', 'announcements', (int) $this->db->lastInsertId(), "Created announcement '{$title}' for audience '{$audience}'");

        flash('success', 'Announcement published.');
        redirect('teacher/announcements');
    }

    /* ─── 11. Staff Profile Module ───────────────────────────────────────── */

    public function profile(): void
    {
        StaffAuth::requireAuth();
        $staffId = StaffAuth::id();
        $academicYear = current_academic_year();

        $stmt = $this->db->prepare(
            "SELECT s.*, sa.username, sa.last_login 
             FROM staff s 
             LEFT JOIN staff_accounts sa ON sa.staff_id = s.id 
             WHERE s.id = ? LIMIT 1"
        );
        $stmt->execute([$staffId]);
        $staff = $stmt->fetch(PDO::FETCH_ASSOC);

        // Assigned classes & subjects
        $stmtAssigned = $this->db->prepare(
            "SELECT sca.*, c.name AS class_name, s.name AS subject_name 
             FROM staff_class_assignments sca
             JOIN classes c ON c.id = sca.class_id
             LEFT JOIN subjects s ON s.id = sca.subject_id
             WHERE sca.staff_id = ? AND sca.academic_year = ?
             ORDER BY c.sort_order ASC"
        );
        $stmtAssigned->execute([$staffId, $academicYear]);
        $assignments = $stmtAssigned->fetchAll(PDO::FETCH_ASSOC);

        render('teacher/profile', compact('staff', 'assignments'), 'teacher');
    }

    public function profileSave(): void
    {
        StaffAuth::requireAuth();
        verify_csrf();
        $staffId = StaffAuth::id();

        $phone = trim($_POST['phone'] ?? '');
        $newPass = (string) ($_POST['new_password'] ?? '');

        if ($phone !== '') {
            $this->db->prepare("UPDATE staff SET phone = ?, updated_at = NOW() WHERE id = ?")->execute([$phone, $staffId]);
        }

        // Photo Upload
        if (!empty($_FILES['photo']['name'])) {
            try {
                $allowed = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png'];
                $photoPath = upload_file('photo', 'staff', $allowed);
                if ($photoPath) {
                    $this->db->prepare("UPDATE staff SET passport_photo = ?, updated_at = NOW() WHERE id = ?")->execute([$photoPath, $staffId]);
                    $_SESSION['teacher']['photo'] = $photoPath;
                }
            } catch (Throwable $e) {
                flash('danger', 'Photo upload failed: ' . $e->getMessage());
            }
        }

        // Password change
        if ($newPass !== '') {
            if (strlen($newPass) < 6) {
                flash('danger', 'New password must be at least 6 characters.');
                redirect('teacher/profile');
            }
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            $this->db->prepare("UPDATE staff_accounts SET password_hash = ? WHERE staff_id = ?")->execute([$hash, $staffId]);
        }

        StaffAudit::log('profile.updated', 'staff', $staffId, 'Staff updated profile contact information and/or password');

        flash('success', 'Profile updated successfully.');
        redirect('teacher/profile');
    }

    /* ─── 12. Helper Methods ─────────────────────────────────────────────── */

    private function notifyParentOfAttendance(int $studentId, string $status, string $date): void
    {
        try {
            $stmt = $this->db->prepare("SELECT first_name, last_name, parent_phone, parent_email, parent_name FROM applicants WHERE id = ? LIMIT 1");
            $stmt->execute([$studentId]);
            $stud = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$stud) return;

            $studentName = $stud['first_name'] . ' ' . $stud['last_name'];
            $formattedDate = date('M d, Y', strtotime($date));
            $msg = "Dear Parent, your child {$studentName} was marked {$status} on {$formattedDate}.";

            if (!empty($stud['parent_phone'])) {
                send_sms_notice($stud['parent_phone'], $msg, 'attendance');
                log_sms($this->db, $stud['parent_phone'], $stud['parent_name'] ?? ($studentName . ' Parent'), $msg);
            }
            if (!empty($stud['parent_email'])) {
                send_email_notice($stud['parent_email'], "Attendance Alert — {$status}", $msg);
            }
        } catch (Throwable $e) {}
    }

    private function notifyParentOfScan(int $studentId, string $studentName, string $status, string $timeIn, string $date): void
    {
        try {
            $stmt = $this->db->prepare("SELECT parent_phone, parent_email, parent_name FROM applicants WHERE id = ? LIMIT 1");
            $stmt->execute([$studentId]);
            $stud = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$stud) return;

            $statusUpper = strtoupper($status);
            $msg = "Dear Parent, your child {$studentName} checked in at {$timeIn} today ({$date}) as {$statusUpper}.";

            if (!empty($stud['parent_phone'])) {
                send_sms_notice($stud['parent_phone'], $msg, 'checkin');
                log_sms($this->db, $stud['parent_phone'], $stud['parent_name'] ?? ($studentName . ' Parent'), $msg);
            }
            if (!empty($stud['parent_email'])) {
                send_email_notice($stud['parent_email'], "School Attendance Check-In Alert", $msg);
            }
        } catch (Throwable $e) {}
    }

    public function studentReportCard(int $applicantId): void
    {
        StaffAuth::requirePermission('results.view');
        $year    = $_GET['academic_year'] ?? current_academic_year();
        $term    = $_GET['term'] ?? current_term();

        $student = (new Applicant($this->db))->find($applicantId);
        if (!$student) {
            flash('warning', 'Student not found.');
            redirect('teacher/results');
        }

        $classId = (int) ($student['class_id'] ?? 0);
        StaffAuth::requireClass($classId);

        $resultData = ResultService::calculateStudentResult($applicantId, $classId, $term, $year);

        render('admin/result_sheet', compact('student', 'resultData', 'year', 'term', 'classId'), 'none');
    }

    private function updateTermRemarks(int $classId, string $term, string $academicYear): void
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT sr.applicant_id, SUM(sr.total) AS grand_total, COUNT(sr.total) AS sub_count
                 FROM student_results sr
                 WHERE sr.class_id = ? AND sr.term = ? AND sr.academic_year = ?
                 GROUP BY sr.applicant_id
                 ORDER BY grand_total DESC"
            );
            $stmt->execute([$classId, $term, $academicYear]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $classSize = count($rows);

            foreach ($rows as $pos => $row) {
                $position = $pos + 1;
                $avg = ($row['sub_count'] > 0) ? round((float)$row['grand_total'] / $row['sub_count'], 2) : 0;

                $this->db->prepare(
                    "INSERT INTO term_remarks (applicant_id, class_id, term, academic_year, total_score, average, position, class_size, updated_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
                     ON DUPLICATE KEY UPDATE total_score = VALUES(total_score), average = VALUES(average), position = VALUES(position), class_size = VALUES(class_size), updated_at = NOW()"
                )->execute([$row['applicant_id'], $classId, $term, $academicYear, $row['grand_total'], $avg, $position, $classSize]);
            }
        } catch (Throwable $e) {}
    }
}
