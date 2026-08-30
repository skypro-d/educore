<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/SmsService.php';
require_once __DIR__ . '/../config/AttendanceRules.php';
require_once __DIR__ . '/../models/AdminUser.php';
require_once __DIR__ . '/../models/Applicant.php';
require_once __DIR__ . '/../models/ClassModel.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/NotificationController.php';
require_once __DIR__ . '/../updater/MigrationRunner.php';

final class AdminController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
        MigrationRunner::ensureUpToDate($this->db);
    }

    public function login(): void
    {
        render('admin/login', [], 'auth');
    }

    public function authenticate(): void
    {
        verify_csrf();
        $email = trim($_POST['email'] ?? '');
        $admin = (new AdminUser($this->db))->findByEmail($email);
        if ($admin && password_verify((string) ($_POST['password'] ?? ''), $admin['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin'] = [
                'id' => $admin['id'],
                'name' => $admin['name'],
                'role' => $admin['role'],
                'school_id' => (int) ($admin['school_id'] ?? 1),
            ];
            SchoolContext::set((int) $_SESSION['admin']['school_id']);
            (new ActivityLog($this->db))->record('login', 'Admin logged in');
            Logger::info("Admin login successful", ['email' => $email, 'admin_id' => $admin['id']]);
            redirect('admin/dashboard');
        }
        Logger::warn("Admin login failed", ['email' => $email]);
        flash('danger', 'Invalid login details.');
        redirect('admin/login');
    }

    public function dashboard(): void
    {
        require_admin();
        $applicants = new Applicant($this->db);
        $stats = $applicants->stats();
        $recent = array_slice($applicants->all(), 0, 8);
        $byClass = $applicants->chartByClass();
        $byMonth = $applicants->chartByMonth();
        $logs = (new ActivityLog($this->db))->recent();

        // 1. Enrolled Students Count
        $totalStudents = (int) $this->db->query("SELECT COUNT(*) FROM applicants WHERE status='Enrolled'")->fetchColumn();

        // 2. Active Staff Count
        $totalStaff = (int) $this->db->query("SELECT COUNT(*) FROM staff WHERE status='Active'")->fetchColumn();

        // 3. School Fees Collection Stats
        $termFeesCollected = (float) $this->db->query("SELECT COALESCE(SUM(amount_paid),0) FROM student_fee_payments WHERE payment_status IN ('Paid','Partial','Manual')")->fetchColumn();
        $termFeesOutstanding = (float) $this->db->query("SELECT COALESCE(SUM(balance),0) FROM student_fee_payments WHERE payment_status IN ('Pending','Partial')")->fetchColumn();

        // 4. Classes Count
        $totalClasses = (int) $this->db->query("SELECT COUNT(*) FROM classes")->fetchColumn();

        // 5. Recent School Fee Transactions
        $recentFees = $this->db->query(
            "SELECT sfp.*, a.first_name, a.last_name, a.application_number, fs.fee_name 
             FROM student_fee_payments sfp 
             JOIN applicants a ON a.id=sfp.applicant_id 
             JOIN fee_structures fs ON fs.id=sfp.fee_structure_id 
             ORDER BY sfp.created_at DESC LIMIT 5"
        )->fetchAll();

        // 6. Recent Attendance Alerts
        $recentAttendance = $this->db->query(
            "SELECT att.*, a.first_name, a.last_name, c.name AS class_name 
             FROM attendance att 
             JOIN applicants a ON a.id=att.applicant_id 
             LEFT JOIN classes c ON c.id=a.class_id
             ORDER BY att.date DESC, att.created_at DESC LIMIT 5"
        )->fetchAll();

        // 7. Student Exit & Movement Today Metrics
        $todayDate = date('Y-m-d');
        try {
            $totalEntriesToday = (int) $this->db->query("SELECT COUNT(DISTINCT applicant_id) FROM attendance WHERE date = '{$todayDate}' AND status IN ('Present','Late')")->fetchColumn();
            $studentsExitedToday = (int) $this->db->query("SELECT COUNT(*) FROM student_exit_logs WHERE exit_date = '{$todayDate}'")->fetchColumn();
            $studentsInSchool = max(0, $totalEntriesToday - $studentsExitedToday);
            $earlyExitsToday = (int) $this->db->query("SELECT COUNT(*) FROM student_exit_logs WHERE exit_date = '{$todayDate}' AND exit_type = 'early'")->fetchColumn();
            $failedExitSms = (int) $this->db->query("SELECT COUNT(*) FROM sms_logs WHERE status = 'failed' AND (DATE(created_at) = '{$todayDate}' OR exit_log_id IS NOT NULL)")->fetchColumn();
        } catch (Throwable $e) {
            MigrationRunner::ensureUpToDate($this->db);
            $totalEntriesToday = 0;
            $studentsExitedToday = 0;
            $studentsInSchool = 0;
            $earlyExitsToday = 0;
            $failedExitSms = 0;
        }

        render('admin/dashboard', compact(
            'stats', 'recent', 'byClass', 'byMonth', 'logs',
            'totalStudents', 'totalStaff', 'termFeesCollected', 
            'termFeesOutstanding', 'totalClasses', 'recentFees', 'recentAttendance',
            'totalEntriesToday', 'studentsExitedToday', 'studentsInSchool', 'earlyExitsToday', 'failedExitSms'
        ), 'admin');
    }

    public function applications(): void
    {
        require_admin();
        $filters = ['q' => $_GET['q'] ?? '', 'class_id' => $_GET['class_id'] ?? '', 'status' => $_GET['status'] ?? ''];
        $classes = (new ClassModel($this->db))->all();
        $applications = (new Applicant($this->db))->all($filters);
        render('admin/applications', compact('applications', 'classes', 'filters'), 'admin');
    }

    public function showApplication(int $id): void
    {
        require_admin();
        $application = (new Applicant($this->db))->find($id);
        if (!$application) {
            flash('warning', 'Application not found.');
            redirect('admin/applications');
        }

        // Fetch student exit history
        $stmtExit = $this->db->prepare(
            "SELECT el.*, g.gate_name 
             FROM student_exit_logs el 
             LEFT JOIN school_gates g ON g.id = el.gate_id 
             WHERE el.student_id = ? 
             ORDER BY el.exited_at DESC LIMIT 30"
        );
        $stmtExit->execute([$id]);
        $exitLogs = $stmtExit->fetchAll();

        // Fetch authorized pickups
        $stmtPickups = $this->db->prepare(
            "SELECT * FROM student_authorized_pickups 
             WHERE student_id = ? 
             ORDER BY is_active DESC, name ASC"
        );
        $stmtPickups->execute([$id]);
        $authorizedPickups = $stmtPickups->fetchAll();

        render('admin/application_show', compact('application', 'exitLogs', 'authorizedPickups'), 'admin');
    }

    public function updateStatus(int $id, string $status): void
    {
        require_admin();
        verify_csrf();
        if (!in_array($status, ['Under Review', 'Awaiting Exam', 'Exam Completed', 'Interview Scheduled', 'Approved', 'Rejected', 'Enrolled', 'Terminated'], true)) {
            redirect('admin/applications');
        }
        $model = new Applicant($this->db);
        $application = $model->find($id);
        if ($application) {
            if ($status === 'Approved' || $status === 'Enrolled') {
                $creds = $this->autoEnrollStudent($id);
                if ($creds) {
                    flash('success', "Student approved and portal activated.<br><b>Student Username:</b> {$creds['student_user']} | <b>Temp Pass:</b> {$creds['student_pass']}<br><b>Parent Username:</b> {$creds['parent_user']} | <b>Temp Pass:</b> {$creds['parent_pass']}");
                } else {
                    flash('success', 'Student approved and portal activated.');
                }
            } else {
                $model->updateStatus($id, $status);
                (new ActivityLog($this->db))->record('application_' . strtolower($status), $application['application_number']);
                send_sms_notice($application['parent_phone'], 'Application ' . $application['application_number'] . ' has been ' . $status);
                (new NotificationController($this->db))->sendStatusUpdate($id, $status);
                flash('success', 'Application marked as ' . $status . '.');
            }
        }
        redirect('admin/applications');
    }

    private function autoEnrollStudent(int $applicantId): ?array
    {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT * FROM applicants WHERE id = ? FOR UPDATE");
            $stmt->execute([$applicantId]);
            $app = $stmt->fetch();
            if (!$app) {
                $this->db->rollBack();
                return null;
            }

            // 1. Generate school-branded Admission Number and Student ID.
            $year = date('Y');
            $schoolInfo = SchoolContext::info();
            $schoolCode = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) ($schoolInfo['school_code'] ?? 'SCH')));
            $schoolCode = substr($schoolCode !== '' ? $schoolCode : 'SCH', 0, 6);
            $stmtCount = $this->db->prepare("SELECT COUNT(*) + 1 FROM applicants WHERE admission_number LIKE ?");
            $stmtCount->execute([$schoolCode . $year . '%']);
            $nextNum = (int) $stmtCount->fetchColumn();
            $serial = str_pad((string) $nextNum, 3, '0', STR_PAD_LEFT);
            $admissionNumber = $schoolCode . $year . $serial;

            // 2. Generate Student Username / Student ID: GF-STD-2026-023
            $studentUsername = $schoolCode . "-STD-" . $year . "-" . $serial;
            // 3. Generate random student password and hash it
            $studentPass = generate_temp_password();
            $studentHash = password_hash($studentPass, PASSWORD_BCRYPT);

            // 4. Generate random parent password and hash it
            $parentPass = generate_temp_password();
            $parentHash = password_hash($parentPass, PASSWORD_BCRYPT);

            // 5. Generate QR code offline (safe, doesn't crash if GD or permissions missing)
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $qrToken = 'ATTENDANCE-STD-' . $applicantId . '-' . bin2hex(random_bytes(4));
            $schoolDomain = trim((string) ($schoolInfo['domain'] ?? ''));
            $portalHost = ($schoolDomain !== '' && $schoolDomain !== 'localhost') ? preg_replace('#^https?://#', '', $schoolDomain) : $host;
            $portalHost = preg_replace('#/.*$#', '', $portalHost);
            $qrData  = $scheme . '://' . $portalHost . BASE_URL . '/?route=attendance/scan&token=' . urlencode($qrToken);
            $qrPath  = 'qrcodes/std_' . $applicantId . '.png';
            $qrFullDir = UPLOAD_PATH . 'qrcodes/';
            
            $qrGenerated = false;
            if (extension_loaded('gd')) {
                try {
                    if (!is_dir($qrFullDir)) {
                        @mkdir($qrFullDir, 0755, true);
                    }
                    if (is_writable($qrFullDir) || is_writable(UPLOAD_PATH)) {
                        require_once __DIR__ . '/../config/phpqrcode.php';
                        @QRcode::png($qrData, $qrFullDir . 'std_' . $applicantId . '.png', 'L', 6, 2);
                        $qrGenerated = true;
                    }
                } catch (Throwable $qre) {
                    error_log("Offline QR generation failed: " . $qre->getMessage());
                }
            }
            if (!$qrGenerated) {
                $qrPath = null;
            }

            // 6. Update applicant record (Write short token qrToken to database)
            $stmtUpd = $this->db->prepare(
                "UPDATE applicants 
                 SET admission_number = ?, 
                     student_username = ?, 
                     qr_code = ?, 
                     qr_data = ?, 
                     student_login_created_at = NOW(),
                     status = 'Enrolled',
                     enrollment_status = 'Completed',
                     enrolled_at = NOW(),
                     updated_at = NOW() 
                 WHERE id = ?"
            );
            $stmtUpd->execute([$admissionNumber, $studentUsername, $qrPath, $qrToken, $applicantId]);

            // 7. Create student_accounts record
            $stmtStud = $this->db->prepare(
                "INSERT INTO student_accounts (applicant_id, username, password_hash, must_change_password) 
                 VALUES (?, ?, ?, 1) 
                 ON DUPLICATE KEY UPDATE username = VALUES(username), password_hash = VALUES(password_hash), must_change_password = 1"
            );
            $stmtStud->execute([$applicantId, $studentUsername, $studentHash]);

            // 8. Create parent_accounts record
            $stmtParent = $this->db->prepare(
                "INSERT INTO parent_accounts (applicant_id, phone, email, password_hash, must_change_password) 
                 VALUES (?, ?, ?, ?, 1) 
                 ON DUPLICATE KEY UPDATE phone = VALUES(phone), email = VALUES(email), password_hash = VALUES(password_hash), must_change_password = 1"
            );
            $stmtParent->execute([$applicantId, $app['parent_phone'], $app['parent_email'], $parentHash]);

            // Create initial welcome notifications
            $studentAccId = (int) $this->db->query("SELECT id FROM student_accounts WHERE applicant_id = $applicantId")->fetchColumn();
            $parentAccId = (int) $this->db->query("SELECT id FROM parent_accounts WHERE applicant_id = $applicantId")->fetchColumn();

            create_notification($this->db, 'student', $studentAccId, 'Welcome to Student Portal!', 'Welcome to your portal. Please configure your profile and change your temporary password.');
            create_notification($this->db, 'parent', $parentAccId, 'Welcome to Parent Portal!', 'Welcome to the parent portal. You can now monitor your child\'s results, fees, and attendance.');

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("autoEnrollStudent database transaction failed: " . $e->getMessage());
            flash('danger', 'Enrollment failed: ' . $e->getMessage() . '. Please verify database migrations have been fully run.');
            return null;
        }

        // 9. Send Email to Parent with credentials (done outside of the main DB transaction)
        try {
            $subject = 'Enrollment Completed - ' . $admissionNumber;
            $emailBody = "Dear Parent,\n\n"
                . "Congratulations! Your child " . trim($app['first_name'] . ' ' . $app['last_name']) . " has been successfully enrolled.\n\n"
                . "Here are the portal login details:\n\n"
                . "--- STUDENT PORTAL ---\n"
                . "URL: " . $this->absolutePortalUrl('student/login') . "\n"
                . "Username: " . $studentUsername . "\n"
                . "Temporary Password: " . $studentPass . "\n\n"
                . "--- PARENT PORTAL ---\n"
                . "URL: " . $this->absolutePortalUrl('parent/login') . "\n"
                . "Username / Email: " . $app['parent_email'] . "\n"
                . "Temporary Password: " . $parentPass . "\n\n"
                . "Please log in and update your passwords immediately.\n\n"
                . "Regards,\n"
                . setting('school_name', APP_NAME);

            (new NotificationController($this->db))->sendWelcomeNotice($applicantId, $app['parent_email'], $subject, $emailBody);
        } catch (Throwable $mailEx) {
            error_log("Enrollment welcome email failed to send: " . $mailEx->getMessage());
        }

        // Send SMS to Parent
        try {
            send_sms_notice($app['parent_phone'], 'Congratulations. Your child has been admitted. Student ID: ' . $studentUsername . '. Login: ' . $this->absolutePortalUrl('student/login') . '. Temporary password sent to parent email: ' . $app['parent_email']);
        } catch (Throwable $smsEx) {
            error_log("Enrollment SMS failed to send: " . $smsEx->getMessage());
        }

        // Log Activity
        try {
            (new ActivityLog($this->db))->record('student_enrolled', "Auto enrolled student: $admissionNumber (Username: $studentUsername)");
        } catch (Throwable $logEx) {
            error_log("Enrollment log record failed: " . $logEx->getMessage());
        }
        
        return [
            'student_user' => $studentUsername,
            'student_pass' => $studentPass,
            'parent_user' => $app['parent_email'],
            'parent_pass' => $parentPass
        ];
    }

    private function absolutePortalUrl(string $path): string
    {
        $school = SchoolContext::info();
        $domain = trim((string) ($school['domain'] ?? ''));
        $path = ltrim($path, '/');
        if ($domain !== '' && $domain !== 'localhost' && $domain !== '127.0.0.1') {
            $domain = preg_replace('#^https?://#', '', strtolower($domain));
            $domain = preg_replace('#/.*$#', '', $domain);
            $domain = preg_replace('/:\d+$/', '', $domain);
            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            return $scheme . '://' . rtrim($domain, '.') . '/' . $path;
        }
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . url($path);
    }

    public function classes(): void
    {
        require_admin();
        $classes = (new ClassModel($this->db))->all();
        render('admin/classes', compact('classes'), 'admin');
    }

    public function saveClass(): void
    {
        require_admin();
        verify_csrf();
        (new ClassModel($this->db))->save([
            'id' => $_POST['id'] ?? null,
            'name' => trim($_POST['name'] ?? ''),
            'sort_order' => (int) ($_POST['sort_order'] ?? 0),
        ]);
        flash('success', 'Class saved.');
        redirect('admin/classes');
    }

    public function deleteClass(int $id): void
    {
        require_admin();
        verify_csrf();
        (new ClassModel($this->db))->delete($id);
        flash('success', 'Class deleted.');
        redirect('admin/classes');
    }

    public function settings(): void
    {
        require_permission('settings');
        render('admin/settings', [], 'admin');
    }

    public function saveSettings(): void
    {
        require_permission('settings');
        verify_csrf();

        $postSettings = $_POST['settings'] ?? [];

        $stmtAppConfig = $this->db->prepare(
            'INSERT INTO app_configs (setting_key, setting_value) 
             VALUES (?, ?) 
             ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)'
        );

        foreach ($postSettings as $key => $value) {
            $stmtAppConfig->execute([$key, trim((string) $value)]);
        }

        $stmtSchool = $this->db->prepare(
            'UPDATE school_settings 
             SET school_name = COALESCE(NULLIF(?, ""), school_name),
                 email = COALESCE(NULLIF(?, ""), email),
                 phone = COALESCE(NULLIF(?, ""), phone),
                 address = COALESCE(NULLIF(?, ""), address),
                 principal_name = COALESCE(NULLIF(?, ""), principal_name),
                 website = COALESCE(NULLIF(?, ""), website),
                 academic_session = COALESCE(NULLIF(?, ""), academic_session)
             WHERE id = 1'
        );
        $stmtSchool->execute([
            $postSettings['school_name'] ?? '',
            $postSettings['school_email'] ?? '',
            $postSettings['school_phone'] ?? '',
            $postSettings['school_address'] ?? '',
            $postSettings['principal_name'] ?? '',
            $postSettings['school_website'] ?? '',
            $postSettings['academic_year'] ?? ''
        ]);

        $this->saveBrandFile('school_logo', 'branding');
        $this->saveBrandFile('favicon', 'branding');
        flash('success', 'Settings updated.');
        redirect('admin/settings');
    }

    public function formBuilder(): void
    {
        require_admin();
        render('admin/form_builder', [], 'admin');
    }

    public function saveFormBuilder(): void
    {
        require_admin();
        verify_csrf();

        // 1. Process basic settings
        foreach ($_POST['settings'] ?? [] as $key => $value) {
            $stmt = $this->db->prepare('INSERT INTO app_configs (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
            $stmt->execute([$key, trim((string) $value)]);
        }

        // 2. Process form fields JSON map
        if (isset($_POST['form_fields']) && is_array($_POST['form_fields'])) {
            $fieldsMap = [];
            foreach ($_POST['form_fields'] as $fieldKey => $fieldMeta) {
                $fieldsMap[$fieldKey] = $fieldMeta['status'] ?? 'optional';
            }
            $stmt = $this->db->prepare("INSERT INTO app_configs (setting_key, setting_value) VALUES ('admission_form_fields', ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
            $stmt->execute([json_encode($fieldsMap, JSON_UNESCAPED_SLASHES)]);
        }

        // 3. Process document requirements JSON map
        if (isset($_POST['doc_requirements']) && is_array($_POST['doc_requirements'])) {
            $docMap = [];
            foreach ($_POST['doc_requirements'] as $cat => $val) {
                $docMap[$cat] = trim((string) $val);
            }
            $stmt = $this->db->prepare("INSERT INTO app_configs (setting_key, setting_value) VALUES ('admission_doc_requirements', ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)");
            $stmt->execute([json_encode($docMap, JSON_UNESCAPED_SLASHES)]);
        }

        flash('success', 'Registration Portal & Form Builder settings saved successfully.');
        redirect('admin/form-builder');
    }

    private function saveBrandFile(string $key, string $folder): void
    {
        if (empty($_FILES[$key]['name'])) {
            return;
        }
        $path = upload_file($key, $folder, [
            'image/jpeg' => 'jpg',
            'image/pjpeg' => 'jpg',
            'image/png' => 'png',
            'image/x-png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            'image/x-icon' => 'ico',
            'image/vnd.microsoft.icon' => 'ico',
        ]);
        $stmt = $this->db->prepare('INSERT INTO app_configs (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
        $stmt->execute([$key, $path]);

        if ($key === 'school_logo') {
            $stmtLogo = $this->db->prepare('UPDATE school_settings SET logo = ? WHERE id = 1');
            $stmtLogo->execute([$path]);
        }
    }

    public function exportCsv(): void
    {
        require_admin();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="applicants.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Application Number', 'Name', 'Class', 'Parent Phone', 'Parent Email', 'Status', 'Date']);
        foreach ((new Applicant($this->db))->all() as $row) {
            fputcsv($out, [$row['application_number'], $row['first_name'] . ' ' . $row['last_name'], $row['class_name'], $row['parent_phone'], $row['parent_email'], $row['status'], $row['created_at']]);
        }
        exit;
    }

    public function payments(): void
    {
        require_permission('payments');
        $payments = $this->db->query("SELECT p.*, a.application_number, a.first_name, a.last_name FROM payments p JOIN applicants a ON a.id=p.applicant_id ORDER BY p.created_at DESC")->fetchAll();
        $revenue = (float) $this->db->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE payment_status='Paid'")->fetchColumn();
        render('admin/payments', compact('payments', 'revenue'), 'admin');
    }

    public function approvePayment(): void
    {
        require_permission('payments');
        verify_csrf();

        $paymentId = (int) ($_POST['payment_id'] ?? 0);
        $method = trim($_POST['method'] ?? 'manual_bank');
        $notes = trim($_POST['notes'] ?? '');
        $adminId = $_SESSION['admin']['id'];

        $stmt = $this->db->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch();

        if ($payment) {
            $stmtUpd = $this->db->prepare(
                "UPDATE payments 
                 SET payment_status = 'Paid', 
                     payment_date = NOW(), 
                     gateway = ?, 
                     approved_by = ?, 
                     approval_notes = ?, 
                     approved_at = NOW(), 
                     updated_at = NOW() 
                 WHERE id = ?"
            );
            $stmtUpd->execute([$method, $adminId, $notes, $paymentId]);

            (new NotificationController($this->db))->sendPaymentReceipt((int) $payment['applicant_id'], $payment['transaction_reference']);
            (new ActivityLog($this->db))->record('payment_approved', "Approved manual payment for ref: " . $payment['transaction_reference']);

            flash('success', 'Payment approved successfully.');
        } else {
            flash('danger', 'Payment record not found.');
        }

        redirect('admin/payments');
    }

    public function rejectPayment(): void
    {
        require_permission('payments');
        verify_csrf();

        $paymentId = (int) ($_POST['payment_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        $stmt = $this->db->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch();

        if ($payment) {
            $stmtUpd = $this->db->prepare(
                "UPDATE payments 
                 SET payment_status = 'Failed', 
                     gateway_response = ?, 
                     updated_at = NOW() 
                 WHERE id = ?"
            );
            $stmtUpd->execute([json_encode(['rejection_reason' => $reason]), $paymentId]);

            (new ActivityLog($this->db))->record('payment_rejected', "Rejected payment for ref: " . $payment['transaction_reference'] . ". Reason: " . $reason);

            flash('warning', 'Payment has been rejected/declined.');
        } else {
            flash('danger', 'Payment record not found.');
        }

        redirect('admin/payments');
    }

    public function reports(): void
    {
        require_permission('reports');
        $applicants = new Applicant($this->db);
        $stats = $applicants->stats();
        $byClass = $applicants->chartByClass();
        $byMonth = $applicants->chartByMonth();
        render('admin/reports', compact('stats', 'byClass', 'byMonth'), 'admin');
    }

    public function exams(): void
    {
        require_permission('exams');
        $subjects = $this->db->query('SELECT * FROM exam_subjects ORDER BY name')->fetchAll();
        $questions = $this->db->query('SELECT q.*, s.name AS subject_name FROM exam_questions q LEFT JOIN exam_subjects s ON s.id=q.subject_id ORDER BY q.id DESC LIMIT 50')->fetchAll();
        render('admin/exams', compact('subjects', 'questions'), 'admin');
    }

    public function saveExamQuestion(): void
    {
        require_permission('exams');
        verify_csrf();
        if (!empty($_POST['subject_name'])) {
            $stmt = $this->db->prepare('INSERT IGNORE INTO exam_subjects (name) VALUES (?)');
            $stmt->execute([trim($_POST['subject_name'])]);
        }
        if (!empty($_POST['question'])) {
            $stmt = $this->db->prepare('INSERT INTO exam_questions (subject_id, question, option_a, option_b, option_c, option_d, correct_option, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([(int) $_POST['subject_id'], trim($_POST['question']), trim($_POST['option_a']), trim($_POST['option_b']), trim($_POST['option_c']), trim($_POST['option_d']), $_POST['correct_option']]);
        }
        flash('success', 'Entrance exam setup updated.');
        redirect('admin/exams');
    }

    public function interviews(): void
    {
        require_permission('interviews');
        $applications = (new Applicant($this->db))->all();
        $interviews = $this->db->query("SELECT i.*, a.application_number, a.first_name, a.last_name FROM interviews i JOIN applicants a ON a.id=i.applicant_id ORDER BY i.scheduled_at DESC")->fetchAll();
        render('admin/interviews', compact('applications', 'interviews'), 'admin');
    }

    public function saveInterview(): void
    {
        require_permission('interviews');
        verify_csrf();
        $stmt = $this->db->prepare('INSERT INTO interviews (applicant_id, scheduled_at, score, remarks, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->execute([(int) $_POST['applicant_id'], $_POST['scheduled_at'], $_POST['score'] !== '' ? (int) $_POST['score'] : null, trim($_POST['remarks'] ?? '')]);
        flash('success', 'Interview scheduled.');
        redirect('admin/interviews');
    }

    public function roles(): void
    {
        require_admin();
        $roles = $this->db->query('SELECT * FROM roles ORDER BY name')->fetchAll();
        render('admin/roles', compact('roles'), 'admin');
    }

    public function logout(): never
    {
        session_destroy();
        redirect('admin/login');
    }

    /* â”€â”€â”€ Fee Structures â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public function feeStructures(): void
    {
        require_permission('fees');
        $classes = (new ClassModel($this->db))->all();
        $structures = $this->db->query(
            "SELECT fs.*, c.name AS class_name FROM fee_structures fs
             LEFT JOIN classes c ON c.id=fs.class_id
             ORDER BY fs.created_at DESC"
        )->fetchAll();

        // Get recent 10 payments
        $payments = $this->db->query(
            "SELECT sfp.*, fs.fee_name, fs.term, a.application_number, a.first_name, a.last_name
             FROM student_fee_payments sfp
             JOIN fee_structures fs ON fs.id=sfp.fee_structure_id
             JOIN applicants a ON a.id=sfp.applicant_id
             ORDER BY sfp.created_at DESC LIMIT 10"
        )->fetchAll();

        render('admin/fee_structures', compact('structures', 'classes', 'payments'), 'admin');
    }

    public function saveFeeStructure(): void
    {
        require_permission('fees');
        verify_csrf();
        
        $feeName = trim($_POST['fee_name'] ?? '');
        $amount = (float) ($_POST['amount'] ?? 0);
        $term = $_POST['term'] ?? 'First';
        $academicYear = trim($_POST['academic_year'] ?? current_academic_year());
        $classId = $_POST['class_id'] !== '' ? (int) $_POST['class_id'] : null;
        $isOptional = isset($_POST['is_optional']) ? 1 : 0;

        if ($feeName !== '') {
            $stmt = $this->db->prepare(
                "INSERT INTO fee_structures (class_id, term, academic_year, fee_name, amount, is_optional) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$classId, $term, $academicYear, $feeName, $amount, $isOptional]);
            flash('success', 'Fee structure item added.');
        } else {
            flash('danger', 'Fee name is required.');
        }
        redirect('admin/fee-structures');
    }

    public function deleteFeeStructure(int $id): void
    {
        require_permission('fees');
        verify_csrf();
        $this->db->prepare("DELETE FROM fee_structures WHERE id=?")->execute([$id]);
        flash('success', 'Fee structure item deleted.');
        redirect('admin/fee-structures');
    }

    /* â”€â”€â”€ Student Fees â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public function studentFees(): void
    {
        require_permission('fees');
        
        $filters = [
            'q' => $_GET['q'] ?? '',
            'class_id' => $_GET['class_id'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];

        // Process recording a manual payment (POST request to same endpoint)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $applicantId = (int) ($_POST['applicant_id'] ?? 0);
            $feeStructureId = (int) ($_POST['fee_structure_id'] ?? 0);
            $amountPaid = (float) ($_POST['amount_paid'] ?? 0);
            $method = $_POST['payment_method'] ?? 'bank_transfer';
            $date = $_POST['payment_date'] ?: date('Y-m-d H:i:s');
            $notes = trim($_POST['notes'] ?? '');

            // Get fee structure details to check total cost
            $stmt = $this->db->prepare("SELECT * FROM fee_structures WHERE id=?");
            $stmt->execute([$feeStructureId]);
            $fee = $stmt->fetch();

            if ($applicantId && $fee) {
                $ref = 'MAN-' . strtoupper(bin2hex(random_bytes(5)));
                $balance = max(0.00, (float)$fee['amount'] - $amountPaid);
                $status = ($balance <= 0) ? 'Paid' : 'Partial';
                $rcpt = generate_receipt_number($this->db);

                $ins = $this->db->prepare(
                    "INSERT INTO student_fee_payments 
                     (applicant_id, fee_structure_id, amount_paid, balance, payment_reference, payment_status, payment_method, payment_date, receipt_number, recorded_by, notes)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $ins->execute([
                    $applicantId,
                    $feeStructureId,
                    $amountPaid,
                    $balance,
                    $ref,
                    $status,
                    $method,
                    $date,
                    $rcpt,
                    $_SESSION['admin']['id'],
                    $notes
                ]);

                // Create Parent Portal Account if it doesn't exist
                $this->autoCreateParentAccount($applicantId);

                flash('success', 'Manual payment recorded successfully.');
            } else {
                flash('danger', 'Invalid student or fee item.');
            }
            redirect('admin/student-fees');
        }

        // Build list of payments
        $sql = "SELECT sfp.*, fs.fee_name, fs.term, fs.amount AS fee_amount, fs.academic_year,
                       a.application_number, a.first_name, a.last_name, c.name AS class_name
                FROM student_fee_payments sfp
                JOIN fee_structures fs ON fs.id=sfp.fee_structure_id
                JOIN applicants a ON a.id=sfp.applicant_id
                LEFT JOIN classes c ON c.id=a.class_id
                WHERE 1=1";
        
        $params = [];
        if ($filters['q'] !== '') {
            $sql .= " AND (a.first_name LIKE ? OR a.last_name LIKE ? OR a.application_number LIKE ?)";
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
            $params[] = '%' . $filters['q'] . '%';
        }
        if ($filters['class_id'] !== '') {
            $sql .= " AND a.class_id = ?";
            $params[] = (int) $filters['class_id'];
        }
        if ($filters['status'] !== '') {
            $sql .= " AND sfp.payment_status = ?";
            $params[] = $filters['status'];
        }
        $sql .= " ORDER BY sfp.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $payments = $stmt->fetchAll();

        // Enrolled students for drop down
        $students = $this->db->query(
            "SELECT a.id, a.first_name, a.last_name, a.application_number, a.class_id, c.name AS class_name
             FROM applicants a
             LEFT JOIN classes c ON c.id=a.class_id
             WHERE a.status='Enrolled'
             ORDER BY a.last_name, a.first_name"
        )->fetchAll();

        // Active fee structures for drop down
        $feeStructures = $this->db->query(
            "SELECT fs.*, c.name AS class_name FROM fee_structures fs
             LEFT JOIN classes c ON c.id=fs.class_id
             WHERE fs.is_active=1
             ORDER BY fs.fee_name"
        )->fetchAll();

        $classes = (new ClassModel($this->db))->all();

        render('admin/student_fees', compact('payments', 'students', 'feeStructures', 'classes', 'filters'), 'admin');
    }

    public function saveBalancePayment(): void
    {
        require_permission('fees');
        verify_csrf();
        $paymentId = (int) ($_POST['payment_id'] ?? 0);
        $amountPaid = (float) ($_POST['amount_paid'] ?? 0);
        $method = $_POST['payment_method'] ?? 'bank_transfer';
        $notes = trim($_POST['notes'] ?? '');

        $stmt = $this->db->prepare("SELECT * FROM student_fee_payments WHERE id=?");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch();

        if ($payment) {
            $newPaid = (float)$payment['amount_paid'] + $amountPaid;
            $newBalance = max(0.00, (float)$payment['balance'] - $amountPaid);
            $newStatus = ($newBalance <= 0) ? 'Paid' : 'Partial';

            $upd = $this->db->prepare(
                "UPDATE student_fee_payments 
                 SET amount_paid=?, balance=?, payment_status=?, payment_method=?, notes=CONCAT(COALESCE(notes,''), '\n', ?), payment_date=NOW()
                 WHERE id=?"
            );
            $upd->execute([$newPaid, $newBalance, $newStatus, $method, "Recorded â‚¦".number_format($amountPaid)." via $method. Notes: $notes", $paymentId]);
            flash('success', 'Balance payment applied.');
        } else {
            flash('danger', 'Payment record not found.');
        }
        redirect('admin/student-fees');
    }

    private function autoCreateParentAccount(int $applicantId): void
    {
        // Check if parent account exists for this student
        $stmt = $this->db->prepare("SELECT * FROM parent_accounts WHERE applicant_id=?");
        $stmt->execute([$applicantId]);
        if ($stmt->fetch()) {
            return;
        }

        // Get applicant info
        $stmt = $this->db->prepare("SELECT * FROM applicants WHERE id=?");
        $stmt->execute([$applicantId]);
        $app = $stmt->fetch();
        if ($app && $app['parent_email'] && $app['parent_phone']) {
            // Create parent account, default password is parent phone number
            $hash = password_hash($app['parent_phone'], PASSWORD_BCRYPT);
            $this->db->prepare(
                "INSERT INTO parent_accounts (applicant_id, phone, email, password_hash) VALUES (?, ?, ?, ?)"
            )->execute([$applicantId, $app['parent_phone'], $app['parent_email'], $hash]);
        }
    }

    /* â”€â”€â”€ Student Promotion â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public function promotion(): void
    {
        require_permission('promotion');
        $classes = (new ClassModel($this->db))->all();
        $classId = (int) ($_GET['class_id'] ?? 0);
        $students = [];
        if ($classId) {
            $stmt = $this->db->prepare(
                "SELECT id, first_name, last_name, application_number FROM applicants
                 WHERE class_id=? AND status='Enrolled'
                 ORDER BY last_name, first_name"
            );
            $stmt->execute([$classId]);
            $students = $stmt->fetchAll();
        }

        $history = $this->db->query(
            "SELECT ph.*, a.first_name, a.last_name, a.application_number,
                    c1.name AS from_class, c2.name AS to_class
             FROM promotion_history ph
             JOIN applicants a ON a.id=ph.applicant_id
             LEFT JOIN classes c1 ON c1.id=ph.from_class_id
             LEFT JOIN classes c2 ON c2.id=ph.to_class_id
             ORDER BY ph.promoted_at DESC LIMIT 50"
        )->fetchAll();

        render('admin/promotion', compact('classes', 'students', 'history', 'classId'), 'admin');
    }

    public function promoteStudents(): void
    {
        require_permission('promotion');
        verify_csrf();
        
        $fromClassId = (int) ($_POST['from_class_id'] ?? 0);
        $toClassId = $_POST['to_class_id'] !== '' ? (int) $_POST['to_class_id'] : null;
        $studentIds = $_POST['student_ids'] ?? [];
        $action = $_POST['action'] ?? 'Promoted'; // Promoted, Repeated, Graduated, etc.
        $academicYear = trim($_POST['academic_year'] ?? current_academic_year());
        $remarks = trim($_POST['remarks'] ?? '');

        if (empty($studentIds)) {
            flash('warning', 'Please select at least one student.');
            redirect('admin/promotion?class_id=' . $fromClassId);
        }

        $this->db->beginTransaction();
        try {
            foreach ($studentIds as $applicantId) {
                $applicantId = (int) $applicantId;
                
                // Record history
                $stmt = $this->db->prepare(
                    "INSERT INTO promotion_history (applicant_id, from_class_id, to_class_id, academic_year, action, remarks, promoted_by)
                     VALUES (?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $applicantId,
                    $fromClassId ?: null,
                    $toClassId,
                    $academicYear,
                    $action,
                    $remarks,
                    $_SESSION['admin']['id']
                ]);

                // Update applicant class or status
                if ($action === 'Graduated') {
                    $upd = $this->db->prepare("UPDATE applicants SET status='Graduated' WHERE id=?");
                    $upd->execute([$applicantId]);
                } else if ($action === 'Promoted' || $action === 'Repeated') {
                    if ($toClassId) {
                        $upd = $this->db->prepare("UPDATE applicants SET class_id=? WHERE id=?");
                        $upd->execute([$toClassId, $applicantId]);
                    }
                }
            }
            $this->db->commit();
            flash('success', 'Promotion process completed for selected students.');
        } catch (Throwable $e) {
            $this->db->rollBack();
            flash('danger', 'Error processing promotion: ' . $e->getMessage());
        }

        redirect('admin/promotion?class_id=' . $fromClassId);
    }

    /* â”€â”€â”€ Communication Center â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public function communications(): void
    {
        require_permission('communications');
        $classes = (new ClassModel($this->db))->all();
        
        $announcements = $this->db->query(
            "SELECT a.*, c.name AS class_name 
             FROM announcements a 
             LEFT JOIN classes c ON c.id=a.class_id
             ORDER BY a.created_at DESC LIMIT 50"
        )->fetchAll();

        $smsLogs = $this->db->query(
            "SELECT * FROM sms_logs ORDER BY created_at DESC LIMIT 100"
        )->fetchAll();

        render('admin/communications', compact('announcements', 'smsLogs', 'classes'), 'admin');
    }

    public function saveAnnouncement(): void
    {
        require_permission('communications');
        verify_csrf();
        
        $title = trim($_POST['title'] ?? '');
        $body = trim($_POST['body'] ?? '');
        $audience = $_POST['audience'] ?? 'all';
        $classId = $_POST['class_id'] !== '' ? (int) $_POST['class_id'] : null;
        $isPublished = isset($_POST['is_published']) ? 1 : 0;
        $pubDate = $isPublished ? date('Y-m-d H:i:s') : null;

        if ($title !== '' && $body !== '') {
            $stmt = $this->db->prepare(
                "INSERT INTO announcements (title, body, audience, class_id, is_published, published_at, created_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $title,
                $body,
                $audience,
                $classId,
                $isPublished,
                $pubDate,
                $_SESSION['admin']['id']
            ]);
            flash('success', 'Announcement saved.');
        } else {
            flash('danger', 'Title and Body are required.');
        }
        redirect('admin/communications');
    }

    public function sendBulkSms(): void
    {
        require_permission('communications');
        verify_csrf();

        $target = $_POST['target'] ?? 'all'; // all_parents, class_parents, individual
        $classId = (int) ($_POST['class_id'] ?? 0);
        $applicantId = (int) ($_POST['applicant_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');

        if ($message === '') {
            flash('danger', 'SMS message body cannot be empty.');
            redirect('admin/communications');
        }

        // Gather phone numbers
        $recipients = [];
        if ($target === 'all_parents') {
            $recipients = $this->db->query(
                "SELECT id, parent_phone AS phone, parent_name AS name FROM applicants WHERE status='Enrolled'"
            )->fetchAll();
        } else if ($target === 'class_parents' && $classId) {
            $stmt = $this->db->prepare(
                "SELECT id, parent_phone AS phone, parent_name AS name FROM applicants WHERE class_id=? AND status='Enrolled'"
            );
            $stmt->execute([$classId]);
            $recipients = $stmt->fetchAll();
        } else if ($target === 'individual' && $applicantId) {
            $stmt = $this->db->prepare(
                "SELECT id, parent_phone AS phone, parent_name AS name FROM applicants WHERE id=?"
            );
            $stmt->execute([$applicantId]);
            $recipients = $stmt->fetchAll();
        }

        if (empty($recipients)) {
            flash('warning', 'No recipients found matching criteria.');
            redirect('admin/communications');
        }

        $sentCount = 0;
        foreach ($recipients as $r) {
            if ($r['phone']) {
                send_sms_notice($r['phone'], $message);
                log_sms($this->db, $r['phone'], $r['name'] ?: 'Parent', $message, 'sent');
                $sentCount++;
            }
        }

        flash('success', "Bulk SMS queued/sent to $sentCount recipients.");
        redirect('admin/communications');
    }

    /* â”€â”€â”€ Staff Management â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public function staff(): void
    {
        require_permission('staff');
        $staff = $this->db->query("SELECT * FROM staff ORDER BY first_name, last_name")->fetchAll();
        render('admin/staff', compact('staff'), 'admin');
    }

    public function saveStaff(): void
    {
        require_permission('staff');
        verify_csrf();

        $id = (int) ($_POST['id'] ?? 0);
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $role = $_POST['role'] ?? 'Teacher';
        $status = $_POST['status'] ?? 'Active';
        $qualification = trim($_POST['qualification'] ?? '');
        $salary = $_POST['salary'] !== '' ? (float)$_POST['salary'] : null;

        if ($firstName === '' || $lastName === '' || $phone === '') {
            flash('danger', 'First Name, Last Name and Phone are required.');
            redirect('admin/staff');
        }

        if ($id > 0) {
            $stmt = $this->db->prepare(
                "UPDATE staff SET first_name=?, last_name=?, email=?, phone=?, role=?, status=?, qualification=?, salary=?, updated_at=NOW() WHERE id=?"
            );
            $stmt->execute([$firstName, $lastName, $email ?: null, $phone, $role, $status, $qualification, $salary, $id]);
            flash('success', 'Staff profile updated.');
        } else {
            $staffId = generate_staff_id($this->db);
            $stmt = $this->db->prepare(
                "INSERT INTO staff (staff_id, first_name, last_name, email, phone, role, status, qualification, salary)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$staffId, $firstName, $lastName, $email ?: null, $phone, $role, $status, $qualification, $salary]);

            // Auto-create Teacher Portal login account
            $newStaffRowId = (int) $this->db->lastInsertId();
            if ($newStaffRowId > 0) {
                // Build a clean username from staff_id, e.g. STF20260001
                $username = str_replace('-', '', $staffId); // STF-2026-0001 => STF20260001
                $tempPass = generate_temp_password();
                $hashPass = password_hash($tempPass, PASSWORD_BCRYPT);

                $stmtAcc = $this->db->prepare(
                    "INSERT INTO staff_accounts (staff_id, username, password_hash, must_change_password)
                     VALUES (?, ?, ?, 1)
                     ON DUPLICATE KEY UPDATE username = VALUES(username), password_hash = VALUES(password_hash), must_change_password = 1"
                );
                $stmtAcc->execute([$newStaffRowId, $username, $hashPass]);

                // Send credentials email if email is provided
                if ($email) {
                    $emailSubject = 'Your Teacher Portal Login Credentials';
                    $emailBody = "Dear $firstName $lastName,\n\n"
                        . "Your staff account has been created on " . setting('school_name', APP_NAME) . ".\n\n"
                        . "Teacher Portal URL: " . url('teacher/login') . "\n"
                        . "Username: $username\n"
                        . "Temporary Password: $tempPass\n\n"
                        . "Please log in and change your password immediately.\n\n"
                        . "Regards,\n" . setting('school_name', 'School Administration');
                    send_email_notice($email, $emailSubject, $emailBody);
                }

                // Send SMS with credentials
                if ($phone) {
                    send_sms_notice($phone, "Your teacher portal account has been created. Username: $username, Temp Password: $tempPass. Login: " . url('teacher/login'));
                }

                (new ActivityLog($this->db))->record('staff_account_created', "Teacher account created for $firstName $lastName ($username)");
                flash('success', "New staff member registered. Portal credentials: Username: $username, Password: $tempPass");
            } else {
                flash('success', 'New staff member registered.');
            }
        }

        redirect('admin/staff');
    }

    public function deleteStaff(int $id): void
    {
        require_permission('staff');
        verify_csrf();
        $this->db->prepare("DELETE FROM staff WHERE id=?")->execute([$id]);
        flash('success', 'Staff profile deleted.');
        redirect('admin/staff');
    }

    /* â”€â”€â”€ Student ID Card â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public function idCard(int $applicantId): void
    {
        require_admin();
        $student = (new Applicant($this->db))->find($applicantId);
        if (!$student) {
            flash('danger', 'Student not found.');
            redirect('admin/applications');
        }
        render('admin/id_card', compact('student'), 'auth');
    }

    /* Secondary Modules CRUD implementations are at the bottom of this file */

    /* â”€â”€â”€ Attendance Settings â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public function attendanceSettings(): void
    {
        require_permission('attendance');

        $map = settings_map();

        // SMS logs with optional filters
        $smsFilter    = $_GET['sms_type'] ?? '';
        $smsStatus    = $_GET['sms_status'] ?? '';
        $smsDate      = $_GET['sms_date'] ?? '';
        $smsSearch    = trim($_GET['sms_search'] ?? '');

        $sqlLogs = "SELECT * FROM sms_logs WHERE 1=1";
        $params  = [];

        if ($smsFilter !== '') {
            $sqlLogs .= " AND sms_type = ?";
            $params[] = $smsFilter;
        }
        if ($smsStatus !== '') {
            $sqlLogs .= " AND status = ?";
            $params[] = $smsStatus;
        }
        if ($smsDate !== '') {
            $sqlLogs .= " AND DATE(sent_at) = ?";
            $params[] = $smsDate;
        }
        if ($smsSearch !== '') {
            $sqlLogs .= " AND (recipient_phone LIKE ? OR recipient_name LIKE ?)";
            $params[] = "%{$smsSearch}%";
            $params[] = "%{$smsSearch}%";
        }

        $sqlLogs .= " ORDER BY created_at DESC LIMIT 200";
        $stmt = $this->db->prepare($sqlLogs);
        $stmt->execute($params);
        $smsLogs = $stmt->fetchAll();

        $lastAutoAbsent = $map['auto_absent_last_run'] ?? '';
        $times          = AttendanceRules::getTimes();

        render('admin/attendance_settings', compact(
            'map', 'smsLogs', 'lastAutoAbsent', 'times',
            'smsFilter', 'smsStatus', 'smsDate', 'smsSearch'
        ), 'admin');
    }

    public function saveAttendanceSettings(): void
    {
        require_permission('attendance');
        verify_csrf();

        $section = trim($_POST['section'] ?? '');
        $postSettings = $_POST['settings'] ?? [];
        $tabRedirect = 'time-rules';

        $stmt = $this->db->prepare(
            "INSERT INTO app_configs (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)"
        );

        if ($section === 'time-rules') {
            $tabRedirect = 'time-rules';
            $timeKeys = [
                'attendance_open_time', 'attendance_ontime_until', 'attendance_late_from',
                'attendance_close_time', 'school_close_time'
            ];
            foreach ($timeKeys as $key) {
                if (isset($postSettings[$key])) {
                    $stmt->execute([$key, trim((string) $postSettings[$key])]);
                }
            }
            flash('success', 'Time rules saved successfully.');
        } elseif ($section === 'sms') {
            $tabRedirect = 'sms';
            // Checkboxes: present in POST -> 1, absent -> 0
            $toggles = ['attendance_sms_enabled', 'checkin_sms_enabled', 'absent_sms_enabled'];
            foreach ($toggles as $key) {
                $val = isset($postSettings[$key]) ? '1' : '0';
                $stmt->execute([$key, $val]);
            }
            // Text / dropdown settings
            $smsFields = ['sms_gateway', 'sms_api_key', 'sms_sender_id'];
            foreach ($smsFields as $key) {
                if (isset($postSettings[$key])) {
                    $stmt->execute([$key, trim((string) $postSettings[$key])]);
                }
            }
            flash('success', 'SMS configuration saved successfully.');
        } elseif ($section === 'auto-absent') {
            $tabRedirect = 'auto-absent';
            $val = isset($postSettings['auto_absent_enabled']) ? '1' : '0';
            $stmt->execute(['auto_absent_enabled', $val]);
            flash('success', 'Auto-absent settings saved successfully.');
        } elseif ($section === 'exit') {
            $tabRedirect = 'exit';
            $toggles = [
                'exit_tracking_enabled', 'exit_sms_enabled', 'early_exit_sms_enabled',
                'exit_require_pickup_verification', 'exit_allow_manual', 'exit_require_entry_record'
            ];
            foreach ($toggles as $key) {
                $val = isset($postSettings[$key]) ? '1' : '0';
                $stmt->execute([$key, $val]);
            }
            $exitFields = ['exit_normal_time', 'exit_sms_template_normal', 'exit_sms_template_early'];
            foreach ($exitFields as $key) {
                if (isset($postSettings[$key])) {
                    $stmt->execute([$key, trim((string) $postSettings[$key])]);
                }
            }
            flash('success', 'Student Exit & Dismissal settings saved successfully.');
        } else {
            // Fallback for generic or all-in-one POST: only update keys that were explicitly submitted
            foreach ($postSettings as $key => $val) {
                $stmt->execute([$key, trim((string) $val)]);
            }
            flash('success', 'Attendance settings saved successfully.');
        }

        redirect('admin/attendance-settings?tab=' . urlencode($tabRedirect));
    }

    public function testSms(): void
    {
        require_permission('attendance');
        verify_csrf();

        $phone = trim($_POST['test_phone'] ?? '');
        if ($phone === '') {
            flash('danger', 'Please enter a phone number to test.');
            redirect('admin/attendance-settings#tab-sms');
        }

        $result = SmsService::test($phone);

        if ($result['success']) {
            flash('success', 'Test SMS sent successfully to ' . e($phone) . '.');
        } else {
            flash('danger', 'Test SMS failed: ' . e(substr($result['response'], 0, 200)));
        }

        redirect('admin/attendance-settings#tab-sms');
    }

    public function deleteSmsLog(int $id): void
    {
        require_permission('attendance');
        verify_csrf();
        $this->db->prepare("DELETE FROM sms_logs WHERE id = ?")->execute([$id]);
        flash('success', 'SMS log entry deleted.');
        redirect('admin/attendance-settings#tab-logs');
    }

    /* â”€â”€â”€ Android POS Devices â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

    public function devices(): void
    {
        require_permission('attendance');
        $schoolId = SchoolContext::id();

        // Query devices registered for current school
        $stmt = $this->db->prepare("SELECT * FROM attendance_devices WHERE school_id = ? ORDER BY created_at DESC");
        $stmt->execute([$schoolId]);
        $devices = $stmt->fetchAll();

        render('admin/devices', compact('devices'), 'admin');
    }

    public function saveDevice(): void
    {
        require_permission('attendance');
        verify_csrf();

        $schoolId = SchoolContext::id();
        $deviceId = (int) ($_POST['device_id'] ?? 0);
        $name     = trim($_POST['device_name'] ?? '');
        $location = trim($_POST['location'] ?? '');

        if ($name === '') {
            flash('danger', 'Device Name is required.');
            redirect('admin/devices');
        }

        if ($deviceId === 0) {
            // Pre-register device profile with Activation Code
            $activationCode = strtoupper(substr(md5(uniqid('', true)), 0, 8));
            $stmt = $this->db->prepare(
                "INSERT INTO attendance_devices (school_id, device_name, location, activation_code, status, created_at)
                 VALUES (?, ?, ?, ?, 'inactive', NOW())"
            );
            $stmt->execute([$schoolId, $name, $location, $activationCode]);
            flash('success', "Device profile pre-registered. Provide this Activation Code to the terminal installer: {$activationCode}");
        } else {
            // Update device profile settings
            $stmt = $this->db->prepare("UPDATE attendance_devices SET device_name = ?, location = ? WHERE id = ? AND school_id = ?");
            $stmt->execute([$name, $location, $deviceId, $schoolId]);
            flash('success', 'Device settings updated.');
        }

        redirect('admin/devices');
    }

    public function toggleDeviceStatus(int $id): void
    {
        require_permission('attendance');
        $schoolId = SchoolContext::id();

        $stmt = $this->db->prepare("SELECT status FROM attendance_devices WHERE id = ? AND school_id = ? LIMIT 1");
        $stmt->execute([$id, $schoolId]);
        $status = $stmt->fetchColumn();

        if ($status !== false) {
            $newStatus = $status === 'active' ? 'blocked' : 'active';
            $upd = $this->db->prepare("UPDATE attendance_devices SET status = ? WHERE id = ? AND school_id = ?");
            $upd->execute([$newStatus, $id, $schoolId]);
            flash('success', 'Device status updated to: ' . ucfirst($newStatus));
        }

        redirect('admin/devices');
    }

    public function resetDeviceToken(int $id): void
    {
        require_permission('attendance');
        verify_csrf();
        $schoolId = SchoolContext::id();

        // Reset token and force re-login by generating a new activation code
        $activationCode = strtoupper(substr(md5(uniqid('', true)), 0, 8));
        $stmt = $this->db->prepare(
            "UPDATE attendance_devices 
             SET device_token = NULL, activation_code = ?, status = 'inactive', last_seen = NULL, last_scan_time = NULL 
             WHERE id = ? AND school_id = ?"
        );
        $stmt->execute([$activationCode, $id, $schoolId]);

        flash('success', "Device token revoked. Device has been logged out. New Activation Code: {$activationCode}");
        redirect('admin/devices');
    }

    public function deleteDevice(int $id): void
    {
        require_permission('attendance');
        verify_csrf();
        $schoolId = SchoolContext::id();

        $stmt = $this->db->prepare("DELETE FROM attendance_devices WHERE id = ? AND school_id = ?");
        $stmt->execute([$id, $schoolId]);

        flash('success', 'Device record removed.');
        redirect('admin/devices');
    }

    public function subscriptionOverview(): void
    {
        require_admin();
        $schoolId = SchoolContext::id();

        // Fetch school details
        $schStmt = $this->db->prepare("SELECT * FROM schools WHERE id = ? LIMIT 1");
        $schStmt->execute([$schoolId]);
        $school = $schStmt->fetch();

        // Fetch active license
        $licStmt = $this->db->prepare("SELECT * FROM school_licenses WHERE school_id = ? AND is_active = 1 LIMIT 1");
        $licStmt->execute([$schoolId]);
        $lic = $licStmt->fetch();

        // Fetch invoices
        $invStmt = $this->db->prepare("SELECT * FROM school_subscriptions WHERE school_id = ? ORDER BY payment_date DESC");
        $invStmt->execute([$schoolId]);
        $invoices = $invStmt->fetchAll();

        // Fetch marketplace logs
        $mktStmt = $this->db->prepare("SELECT * FROM marketplace_transactions WHERE school_id = ? ORDER BY created_at DESC");
        $mktStmt->execute([$schoolId]);
        $marketplace = $mktStmt->fetchAll();

        render('admin/subscription_overview', compact('school', 'lic', 'invoices', 'marketplace'), 'admin');
    }

    public function purchaseSmsCredits(): void
    {
        require_admin();
        verify_csrf();
        $schoolId = SchoolContext::id();
        $credits = (int) ($_POST['sms_credits'] ?? 0);

        if ($credits <= 0) {
            flash('danger', 'Please enter a valid amount of SMS credits.');
            redirect('admin/billing');
        }

        $cost = $credits * 4.50; // Wholesale pricing N4.50 per SMS
        $ref = 'TXN-SMS-' . strtoupper(substr(md5(uniqid('', true)), 0, 10));

        // Create transaction log
        $ins = $this->db->prepare(
            "INSERT INTO marketplace_transactions (school_id, item_type, quantity, cost, payment_status, transaction_ref) 
             VALUES (?, 'sms', ?, ?, 'paid', ?)"
        );
        $ins->execute([$schoolId, $credits, $cost, $ref]);

        // Recharge school SMS balance
        $upd = $this->db->prepare("UPDATE schools SET sms_balance = sms_balance + ? WHERE id = ?");
        $upd->execute([$credits, $schoolId]);

        flash('success', "SMS package purchased successfully. {$credits} credits added to balance.");
        redirect('admin/billing');
    }

    public function purchaseDevice(): void
    {
        require_admin();
        verify_csrf();
        $schoolId = SchoolContext::id();
        $qty = (int) ($_POST['quantity'] ?? 1);

        if ($qty <= 0) {
            flash('danger', 'Please enter a valid quantity of POS terminals.');
            redirect('admin/billing');
        }

        $cost = $qty * 65000.00; // Wholesale device rate N65k NGN per terminal
        $ref = 'TXN-POS-' . strtoupper(substr(md5(uniqid('', true)), 0, 10));

        // Create transaction log
        $ins = $this->db->prepare(
            "INSERT INTO marketplace_transactions (school_id, item_type, quantity, cost, payment_status, transaction_ref) 
             VALUES (?, 'device', ?, ?, 'paid', ?)"
        );
        $ins->execute([$schoolId, $qty, $cost, $ref]);

        flash('success', "Purchase request submitted for {$qty} POS terminal(s). Reference: {$ref}. Technical support will dispatch hardware scanner shortly.");
        redirect('admin/billing');
    }

    public function library(): void
    {
        require_admin();
        $schoolId = SchoolContext::id();

        $books = $this->db->prepare("SELECT * FROM library_books WHERE school_id = ? ORDER BY title ASC");
        $books->execute([$schoolId]);
        $books = $books->fetchAll();

        $borrowings = $this->db->prepare(
            "SELECT b.*, a.first_name, a.last_name, a.application_number, bk.title AS book_title 
             FROM library_borrowings b 
             LEFT JOIN applicants a ON a.id = b.applicant_id 
             LEFT JOIN library_books bk ON bk.id = b.book_id
             WHERE b.school_id = ? 
             ORDER BY b.borrowed_at DESC"
        );
        $borrowings->execute([$schoolId]);
        $borrowings = $borrowings->fetchAll();

        render('admin/library', compact('books', 'borrowings'), 'admin');
    }

    public function saveBook(): void
    {
        require_admin();
        verify_csrf();
        $schoolId = SchoolContext::id();

        $id = (int) ($_POST['id'] ?? 0);
        $isbn = trim($_POST['isbn'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $publisher = trim($_POST['publisher'] ?? '');
        $year = (int) ($_POST['year_published'] ?? date('Y'));
        $category = trim($_POST['category'] ?? '');
        $total = (int) ($_POST['total_copies'] ?? 1);
        $location = trim($_POST['location'] ?? '');

        if ($title === '') {
            flash('danger', 'Book title is required.');
            redirect('admin/library');
        }

        if ($id === 0) {
            $stmt = $this->db->prepare(
                "INSERT INTO library_books (school_id, isbn, title, author, publisher, year_published, category, total_copies, available_copies, location) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$schoolId, $isbn, $title, $author, $publisher, $year, $category, $total, $total, $location]);
            flash('success', 'New book added to library catalog.');
        } else {
            $stmt = $this->db->prepare(
                "UPDATE library_books 
                 SET isbn = ?, title = ?, author = ?, publisher = ?, year_published = ?, category = ?, total_copies = ?, location = ? 
                 WHERE id = ? AND school_id = ?"
            );
            $stmt->execute([$isbn, $title, $author, $publisher, $year, $category, $total, $location, $id, $schoolId]);
            flash('success', 'Book details updated.');
        }

        redirect('admin/library');
    }

    public function deleteBook(int $id): void
    {
        require_admin();
        $schoolId = SchoolContext::id();
        $stmt = $this->db->prepare("DELETE FROM library_books WHERE id = ? AND school_id = ?");
        $stmt->execute([$id, $schoolId]);
        flash('success', 'Book removed from library catalog.');
        redirect('admin/library');
    }

    public function transport(): void
    {
        require_admin();
        $schoolId = SchoolContext::id();

        $routes = $this->db->prepare("SELECT * FROM transport_routes WHERE school_id = ? ORDER BY route_name ASC");
        $routes->execute([$schoolId]);
        $routes = $routes->fetchAll();

        render('admin/transport', compact('routes'), 'admin');
    }

    public function saveRoute(): void
    {
        require_admin();
        verify_csrf();
        $schoolId = SchoolContext::id();

        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['route_name'] ?? '');
        $points = trim($_POST['pickup_points'] ?? '');
        $fee = (float) ($_POST['fee'] ?? 0.00);

        if ($name === '') {
            flash('danger', 'Route name is required.');
            redirect('admin/transport');
        }

        if ($id === 0) {
            $stmt = $this->db->prepare("INSERT INTO transport_routes (school_id, route_name, pickup_points, fee) VALUES (?, ?, ?, ?)");
            $stmt->execute([$schoolId, $name, $points, $fee]);
            flash('success', 'Transport route added.');
        } else {
            $stmt = $this->db->prepare("UPDATE transport_routes SET route_name = ?, pickup_points = ?, fee = ? WHERE id = ? AND school_id = ?");
            $stmt->execute([$name, $points, $fee, $id, $schoolId]);
            flash('success', 'Route details updated.');
        }

        redirect('admin/transport');
    }

    public function deleteRoute(int $id): void
    {
        require_admin();
        $schoolId = SchoolContext::id();
        $stmt = $this->db->prepare("DELETE FROM transport_routes WHERE id = ? AND school_id = ?");
        $stmt->execute([$id, $schoolId]);
        flash('success', 'Transport route deleted.');
        redirect('admin/transport');
    }

    public function inventory(): void
    {
        require_admin();
        $schoolId = SchoolContext::id();

        $items = $this->db->prepare("SELECT * FROM inventory_items WHERE school_id = ? ORDER BY item_name ASC");
        $items->execute([$schoolId]);
        $items = $items->fetchAll();

        render('admin/inventory', compact('items'), 'admin');
    }

    public function saveAsset(): void
    {
        require_admin();
        verify_csrf();
        $schoolId = SchoolContext::id();

        $id = (int) ($_POST['id'] ?? 0);
        $name = trim($_POST['item_name'] ?? '');
        $category = $_POST['category'] ?? 'Other';
        $qty = (int) ($_POST['quantity'] ?? 0);
        $unit = trim($_POST['unit'] ?? 'pcs');
        $cost = (float) ($_POST['unit_cost'] ?? 0.00);
        $supplier = trim($_POST['supplier'] ?? '');
        $location = trim($_POST['location'] ?? '');

        if ($name === '') {
            flash('danger', 'Item name is required.');
            redirect('admin/inventory');
        }

        if ($id === 0) {
            $stmt = $this->db->prepare(
                "INSERT INTO inventory_items (school_id, item_name, category, quantity, unit, unit_cost, supplier, location) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([$schoolId, $name, $category, $qty, $unit, $cost, $supplier, $location]);
            flash('success', 'Inventory item added.');
        } else {
            $stmt = $this->db->prepare(
                "UPDATE inventory_items 
                 SET item_name = ?, category = ?, quantity = ?, unit = ?, unit_cost = ?, supplier = ?, location = ? 
                 WHERE id = ? AND school_id = ?"
            );
            $stmt->execute([$name, $category, $qty, $unit, $cost, $supplier, $location, $id, $schoolId]);
            flash('success', 'Inventory details updated.');
        }

        redirect('admin/inventory');
    }

    public function deleteAsset(int $id): void
    {
        require_admin();
        $schoolId = SchoolContext::id();
        $stmt = $this->db->prepare("DELETE FROM inventory_items WHERE id = ? AND school_id = ?");
        $stmt->execute([$id, $schoolId]);
        flash('success', 'Inventory item removed.');
        redirect('admin/inventory');
    }

    // ══════════════════════════════════════════════════════════════════════════
    // STUDENT EXIT VERIFICATION & GATE MANAGEMENT
    // ══════════════════════════════════════════════════════════════════════════

    public function exitScanner(): void
    {
        require_permission('exit_scanner');
        $schoolId = SchoolContext::id();

        // Active gates
        $stmtGates = $this->db->prepare("SELECT * FROM school_gates WHERE status = 'active' ORDER BY gate_name ASC");
        $stmtGates->execute();
        $gates = $stmtGates->fetchAll();

        // Today's recent exits
        $today = date('Y-m-d');
        $stmtRecent = $this->db->prepare(
            "SELECT el.*, a.first_name, a.last_name, a.application_number, a.admission_number,
                    a.passport_photo, c.name AS class_name, g.gate_name
             FROM student_exit_logs el
             JOIN applicants a ON a.id = el.student_id
             LEFT JOIN classes c ON c.id = a.class_id
             LEFT JOIN school_gates g ON g.id = el.gate_id
             WHERE el.exit_date = ?
             ORDER BY el.exited_at DESC LIMIT 15"
        );
        $stmtRecent->execute([$today]);
        $recentExits = $stmtRecent->fetchAll();

        $normalCloseTime = setting('exit_normal_time', setting('school_close_time', '14:30'));

        render('admin/exit_scanner', compact('gates', 'recentExits', 'normalCloseTime'), 'admin');
    }

    public function exitScanAjax(): void
    {
        header('Content-Type: application/json');
        require_permission('exit_scanner');

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $qrData = trim($input['qr_data'] ?? $input['token'] ?? '');
        $gateId = !empty($input['gate_id']) ? (int) $input['gate_id'] : null;
        $scanMethod = trim($input['scan_method'] ?? 'qr_usb');
        if (!in_array($scanMethod, ['qr_camera', 'qr_usb', 'manual', 'api_device'], true)) {
            $scanMethod = 'qr_usb';
        }

        if ($qrData === '') {
            echo json_encode(['success' => false, 'status' => 'error', 'message' => 'No QR code or student token provided.']);
            exit;
        }

        // Parse token if full URL was scanned
        if (str_contains($qrData, 'token=')) {
            if (preg_match('/[?&]token=([^&]+)/', $qrData, $m)) {
                $qrData = urldecode($m[1]);
            }
        }
        $token = trim($qrData);
        $schoolId = SchoolContext::id();

        // 1. Locate student by qr_data, admission_number, application_number, or ATTENDANCE-STD-{id}
        $stmt = $this->db->prepare(
            "SELECT a.*, c.name AS class_name
             FROM applicants a
             LEFT JOIN classes c ON c.id = a.class_id
             WHERE (a.qr_data = ? OR a.admission_number = ? OR a.application_number = ?)
             LIMIT 1"
        );
        $stmt->execute([$token, $token, $token]);
        $student = $stmt->fetch();

        if (!$student && str_starts_with($token, 'ATTENDANCE-STD-')) {
            $parts = explode('-', $token);
            $studentId = isset($parts[2]) ? (int) $parts[2] : 0;
            if ($studentId > 0) {
                $stmtLegacy = $this->db->prepare(
                    "SELECT a.*, c.name AS class_name
                     FROM applicants a
                     LEFT JOIN classes c ON c.id = a.class_id
                     WHERE a.id = ? LIMIT 1"
                );
                $stmtLegacy->execute([$studentId]);
                $student = $stmtLegacy->fetch();
            }
        }

        if (!$student) {
            echo json_encode([
                'success' => false,
                'status' => 'not_found',
                'message' => 'Student not found. The scanned card could not be verified in this school.'
            ]);
            exit;
        }

        // 2. Validate enrollment & status
        if ($student['status'] !== 'Enrolled') {
            echo json_encode([
                'success' => false,
                'status' => 'not_enrolled',
                'message' => "Student {$student['first_name']} {$student['last_name']} is currently {$student['status']} (Not Enrolled)."
            ]);
            exit;
        }

        if (($student['student_status'] ?? 'Active') !== 'Active') {
            echo json_encode([
                'success' => false,
                'status' => 'inactive',
                'message' => "Student {$student['first_name']} {$student['last_name']} is {$student['student_status']} and not permitted to exit."
            ]);
            exit;
        }

        $today = date('Y-m-d');
        $nowTime = date('H:i:s');
        $studentId = (int) $student['id'];

        // 3. Optional: check today's attendance entry if required
        $attendanceId = null;
        $attStmt = $this->db->prepare("SELECT id, status, time_in FROM attendance WHERE applicant_id = ? AND date = ? LIMIT 1");
        $attStmt->execute([$studentId, $today]);
        $attRow = $attStmt->fetch();
        if ($attRow) {
            $attendanceId = (int) $attRow['id'];
        } elseif (setting('exit_require_entry_record', '0') === '1') {
            echo json_encode([
                'success' => false,
                'status' => 'no_entry',
                'message' => "Student has no check-in record for today."
            ]);
            exit;
        }

        // 4. Duplicate Exit Protection (Backend check)
        $stmtExitCheck = $this->db->prepare(
            "SELECT el.*, g.gate_name, adm.name AS staff_name
             FROM student_exit_logs el
             LEFT JOIN school_gates g ON g.id = el.gate_id
             LEFT JOIN admins adm ON adm.id = el.scanned_by
             WHERE el.student_id = ? AND el.exit_date = ? LIMIT 1"
        );
        $stmtExitCheck->execute([$studentId, $today]);
        $existingExit = $stmtExitCheck->fetch();

        if ($existingExit) {
            $exitFmt = date('g:i A', strtotime($existingExit['exit_time']));
            $gateLabel = $existingExit['gate_name'] ?: 'Gate';
            $staffLabel = $existingExit['staff_name'] ?: ($existingExit['scanned_by_name'] ?: 'Staff');
            echo json_encode([
                'success' => false,
                'status' => 'already_exited',
                'message' => "Already Checked Out! {$student['first_name']} {$student['last_name']} exited today at {$exitFmt} via {$gateLabel} (Logged by: {$staffLabel}).",
                'student' => [
                    'id' => $student['id'],
                    'name' => trim($student['first_name'] . ' ' . $student['last_name']),
                    'admission_number' => $student['admission_number'] ?: $student['application_number'],
                    'class_name' => $student['class_name'] ?: 'General',
                    'photo' => $student['passport_photo'] ? url('uploads/' . $student['passport_photo']) : null,
                    'parent_name' => $student['parent_name'] ?: ($student['father_name'] ?: ($student['mother_name'] ?: 'Parent/Guardian')),
                    'parent_phone' => mask_phone($student['parent_phone']),
                    'exit_time' => $exitFmt,
                    'gate_name' => $gateLabel,
                    'exit_type' => ucfirst($existingExit['exit_type']),
                    'exit_reason' => $existingExit['exit_reason'] ?: '—'
                ]
            ]);
            exit;
        }

        // 5. Determine Early vs Normal Exit
        $normalCloseTime = setting('exit_normal_time', setting('school_close_time', '14:30'));
        $currentTimeShort = date('H:i');
        $isEarly = ($currentTimeShort < $normalCloseTime);
        $exitType = $isEarly ? 'early' : 'normal';

        // 6. Fetch Authorized Pickups
        $stmtPickups = $this->db->prepare("SELECT * FROM student_authorized_pickups WHERE student_id = ? AND is_active = 1 ORDER BY name ASC");
        $stmtPickups->execute([$studentId]);
        $pickups = $stmtPickups->fetchAll();

        // 7. If Early exit OR pickup verification required, prompt confirmation on UI
        $requirePickup = (setting('exit_require_pickup_verification', '0') === '1');
        $forceConfirm = !empty($input['auto_confirm']) ? false : ($isEarly || $requirePickup || count($pickups) > 0);

        if ($forceConfirm && empty($input['confirmed'])) {
            echo json_encode([
                'success' => true,
                'status' => 'confirm_required',
                'is_early' => $isEarly,
                'exit_type' => $exitType,
                'current_time' => date('g:i A', strtotime($nowTime)),
                'normal_closing_time' => date('g:i A', strtotime($normalCloseTime)),
                'student' => [
                    'id' => $student['id'],
                    'name' => trim($student['first_name'] . ' ' . $student['last_name']),
                    'first_name' => $student['first_name'],
                    'last_name' => $student['last_name'],
                    'admission_number' => $student['admission_number'] ?: $student['application_number'],
                    'class_name' => $student['class_name'] ?: 'General',
                    'photo' => $student['passport_photo'] ? url('uploads/' . $student['passport_photo']) : null,
                    'parent_name' => $student['parent_name'] ?: ($student['father_name'] ?: ($student['mother_name'] ?: 'Parent/Guardian')),
                    'parent_phone' => mask_phone($student['parent_phone']),
                ],
                'authorized_pickups' => array_map(function($p) {
                    return [
                        'id' => $p['id'],
                        'name' => $p['name'],
                        'relationship' => $p['relationship'],
                        'phone' => mask_phone($p['phone']),
                        'id_card_number' => $p['id_card_number']
                    ];
                }, $pickups)
            ]);
            exit;
        }

        // 8. Normal exit without required prompt -> Record immediately
        $adminUser = admin();
        $staffId = $adminUser ? (int)$adminUser['id'] : null;
        $staffName = $adminUser ? $adminUser['name'] : 'Gate Staff';

        $gateName = null;
        if ($gateId) {
            $stmtG = $this->db->prepare("SELECT gate_name FROM school_gates WHERE id = ? LIMIT 1");
            $stmtG->execute([$gateId]);
            $gateName = $stmtG->fetchColumn() ?: null;
        }

        $pickupPersonId = !empty($input['pickup_person_id']) ? (int) $input['pickup_person_id'] : null;
        $pickupPersonName = !empty($input['pickup_person_name']) ? trim($input['pickup_person_name']) : null;
        $exitReason = !empty($input['exit_reason']) ? trim($input['exit_reason']) : ($isEarly ? 'Early Dismissal' : null);
        $exitNotes = !empty($input['exit_notes']) ? trim($input['exit_notes']) : null;

        $ins = $this->db->prepare(
            "INSERT INTO student_exit_logs
                (school_id, student_id, attendance_id, pickup_person_id, pickup_person_name,
                 exit_type, exit_reason, exit_reason_notes, exit_date, exit_time, exited_at,
                 gate_id, gate_name, scanned_by, scanned_by_name, scan_method, qr_token,
                 verification_status, sms_status, created_at)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, 'verified', 'pending', NOW())"
        );
        $ins->execute([
            $schoolId, $studentId, $attendanceId, $pickupPersonId, $pickupPersonName,
            $exitType, $exitReason, $exitNotes, $today, $nowTime,
            $gateId, $gateName, $staffId, $staffName, $scanMethod, $token
        ]);
        $exitLogId = (int) $this->db->lastInsertId();

        // Fire SMS
        $exitData = [
            'exit_type' => $exitType,
            'exit_date' => $today,
            'exit_time' => $nowTime,
            'exit_reason' => $exitReason,
            'pickup_person_name' => $pickupPersonName
        ];
        $smsSent = send_exit_sms($this->db, $student, $exitData, $exitLogId);

        echo json_encode([
            'success' => true,
            'status' => 'success',
            'message' => 'Student Exit Verified & Recorded!',
            'exit_id' => $exitLogId,
            'is_early' => $isEarly,
            'exit_type' => $exitType,
            'exit_time' => date('g:i A', strtotime($nowTime)),
            'gate_name' => $gateName ?: 'Gate',
            'sms_status' => $smsSent ? 'Sent' : (setting('exit_sms_enabled', '1') === '0' ? 'Disabled' : 'Failed'),
            'student' => [
                'id' => $student['id'],
                'name' => trim($student['first_name'] . ' ' . $student['last_name']),
                'admission_number' => $student['admission_number'] ?: $student['application_number'],
                'class_name' => $student['class_name'] ?: 'General',
                'photo' => $student['passport_photo'] ? url('uploads/' . $student['passport_photo']) : null,
                'parent_name' => $student['parent_name'] ?: ($student['father_name'] ?: ($student['mother_name'] ?: 'Parent/Guardian')),
                'parent_phone' => mask_phone($student['parent_phone']),
                'pickup_person' => $pickupPersonName ?: '—'
            ]
        ]);
        exit;
    }

    public function exitConfirmAjax(): void
    {
        header('Content-Type: application/json');
        require_permission('exit_scanner');

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $studentId = (int) ($input['student_id'] ?? 0);
        $gateId = !empty($input['gate_id']) ? (int) $input['gate_id'] : null;
        $exitType = trim($input['exit_type'] ?? 'normal');
        if (!in_array($exitType, ['normal', 'early', 'manual'], true)) {
            $exitType = 'normal';
        }
        $exitReason = trim($input['exit_reason'] ?? '');
        $exitNotes = trim($input['exit_notes'] ?? '');
        $pickupPersonId = !empty($input['pickup_person_id']) ? (int) $input['pickup_person_id'] : null;
        $pickupPersonName = trim($input['pickup_person_name'] ?? '');
        $scanMethod = trim($input['scan_method'] ?? 'qr_usb');

        if ($studentId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid student ID.']);
            exit;
        }

        // Fetch student
        $stmt = $this->db->prepare(
            "SELECT a.*, c.name AS class_name 
             FROM applicants a 
             LEFT JOIN classes c ON c.id = a.class_id 
             WHERE a.id = ? LIMIT 1"
        );
        $stmt->execute([$studentId]);
        $student = $stmt->fetch();

        if (!$student) {
            echo json_encode(['success' => false, 'message' => 'Student record not found.']);
            exit;
        }

        $today = date('Y-m-d');
        $nowTime = date('H:i:s');
        $schoolId = SchoolContext::id();

        // Check duplicate
        $chk = $this->db->prepare("SELECT id FROM student_exit_logs WHERE student_id = ? AND exit_date = ? LIMIT 1");
        $chk->execute([$studentId, $today]);
        if ($chk->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Student has already been checked out today.']);
            exit;
        }

        // If pickupPersonId was given, resolve name
        if ($pickupPersonId && $pickupPersonName === '') {
            $stmtP = $this->db->prepare("SELECT name FROM student_authorized_pickups WHERE id = ? LIMIT 1");
            $stmtP->execute([$pickupPersonId]);
            $pickupPersonName = $stmtP->fetchColumn() ?: '';
        }

        $gateName = null;
        if ($gateId) {
            $stmtG = $this->db->prepare("SELECT gate_name FROM school_gates WHERE id = ? LIMIT 1");
            $stmtG->execute([$gateId]);
            $gateName = $stmtG->fetchColumn() ?: null;
        }

        $adminUser = admin();
        $staffId = $adminUser ? (int)$adminUser['id'] : null;
        $staffName = $adminUser ? $adminUser['name'] : 'Gate Staff';

        $ins = $this->db->prepare(
            "INSERT INTO student_exit_logs
                (school_id, student_id, attendance_id, pickup_person_id, pickup_person_name,
                 exit_type, exit_reason, exit_reason_notes, exit_date, exit_time, exited_at,
                 gate_id, gate_name, scanned_by, scanned_by_name, scan_method, qr_token,
                 verification_status, sms_status, created_at)
             VALUES
                (?, ?, NULL, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, 'verified', 'pending', NOW())"
        );
        $ins->execute([
            $schoolId, $studentId, $pickupPersonId, $pickupPersonName,
            $exitType, $exitReason, $exitNotes, $today, $nowTime,
            $gateId, $gateName, $staffId, $staffName, $scanMethod, $student['qr_data'] ?? null
        ]);
        $exitLogId = (int) $this->db->lastInsertId();

        // Dispatch SMS
        $exitData = [
            'exit_type' => $exitType,
            'exit_date' => $today,
            'exit_time' => $nowTime,
            'exit_reason' => $exitReason,
            'pickup_person_name' => $pickupPersonName
        ];
        $smsSent = send_exit_sms($this->db, $student, $exitData, $exitLogId);

        echo json_encode([
            'success' => true,
            'status' => 'success',
            'message' => 'Exit Confirmed & Recorded!',
            'exit_id' => $exitLogId,
            'exit_type' => $exitType,
            'exit_time' => date('g:i A', strtotime($nowTime)),
            'gate_name' => $gateName ?: 'Gate',
            'sms_status' => $smsSent ? 'Sent' : 'Failed',
            'student' => [
                'id' => $student['id'],
                'name' => trim($student['first_name'] . ' ' . $student['last_name']),
                'admission_number' => $student['admission_number'] ?: $student['application_number'],
                'class_name' => $student['class_name'] ?: 'General',
                'photo' => $student['passport_photo'] ? url('uploads/' . $student['passport_photo']) : null,
                'pickup_person' => $pickupPersonName ?: '—'
            ]
        ]);
        exit;
    }

    public function studentLookupAjax(): void
    {
        header('Content-Type: application/json');
        require_permission('exit_scanner');

        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            echo json_encode(['results' => []]);
            exit;
        }

        $stmt = $this->db->prepare(
            "SELECT a.id, a.first_name, a.last_name, a.application_number, a.admission_number,
                    a.passport_photo, c.name AS class_name
             FROM applicants a
             LEFT JOIN classes c ON c.id = a.class_id
             WHERE a.status = 'Enrolled' AND (
                 a.first_name LIKE ? OR a.last_name LIKE ? OR 
                 a.application_number LIKE ? OR a.admission_number LIKE ?
             )
             ORDER BY a.last_name, a.first_name LIMIT 10"
        );
        $term = "%{$q}%";
        $stmt->execute([$term, $term, $term, $term]);
        $rows = $stmt->fetchAll();

        $results = array_map(function($r) {
            return [
                'id' => $r['id'],
                'name' => trim($r['first_name'] . ' ' . $r['last_name']),
                'admission_number' => $r['admission_number'] ?: $r['application_number'],
                'class_name' => $r['class_name'] ?: 'General',
                'photo' => $r['passport_photo'] ? url('uploads/' . $r['passport_photo']) : null,
            ];
        }, $rows);

        echo json_encode(['results' => $results]);
        exit;
    }

    public function exitManualAjax(): void
    {
        header('Content-Type: application/json');
        require_permission('exit_scanner');

        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $studentId = (int) ($input['student_id'] ?? 0);
        $gateId = !empty($input['gate_id']) ? (int) $input['gate_id'] : null;
        $reason = trim($input['exit_reason'] ?? 'Manual Gate Authorization');
        $notes = trim($input['exit_notes'] ?? '');
        $pickupPersonName = trim($input['pickup_person_name'] ?? '');

        if ($studentId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Please select a student.']);
            exit;
        }

        $stmt = $this->db->prepare(
            "SELECT a.*, c.name AS class_name 
             FROM applicants a 
             LEFT JOIN classes c ON c.id = a.class_id 
             WHERE a.id = ? AND a.status = 'Enrolled' LIMIT 1"
        );
        $stmt->execute([$studentId]);
        $student = $stmt->fetch();

        if (!$student) {
            echo json_encode(['success' => false, 'message' => 'Active enrolled student not found.']);
            exit;
        }

        $today = date('Y-m-d');
        $nowTime = date('H:i:s');
        $schoolId = SchoolContext::id();

        // Check duplicate
        $chk = $this->db->prepare("SELECT id FROM student_exit_logs WHERE student_id = ? AND exit_date = ? LIMIT 1");
        $chk->execute([$studentId, $today]);
        if ($chk->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Student has already been checked out today.']);
            exit;
        }

        $gateName = null;
        if ($gateId) {
            $stmtG = $this->db->prepare("SELECT gate_name FROM school_gates WHERE id = ? LIMIT 1");
            $stmtG->execute([$gateId]);
            $gateName = $stmtG->fetchColumn() ?: null;
        }

        $adminUser = admin();
        $staffId = $adminUser ? (int)$adminUser['id'] : null;
        $staffName = $adminUser ? $adminUser['name'] : 'Gate Staff';

        $ins = $this->db->prepare(
            "INSERT INTO student_exit_logs
                (school_id, student_id, attendance_id, pickup_person_id, pickup_person_name,
                 exit_type, exit_reason, exit_reason_notes, exit_date, exit_time, exited_at,
                 gate_id, gate_name, scanned_by, scanned_by_name, scan_method, qr_token,
                 verification_status, sms_status, created_at)
             VALUES
                (?, ?, NULL, NULL, ?, 'manual', ?, ?, ?, ?, NOW(), ?, ?, ?, ?, 'manual', NULL, 'manual_override', 'pending', NOW())"
        );
        $ins->execute([
            $schoolId, $studentId, $pickupPersonName,
            $reason, $notes, $today, $nowTime,
            $gateId, $gateName, $staffId, $staffName
        ]);
        $exitLogId = (int) $this->db->lastInsertId();

        $exitData = [
            'exit_type' => 'manual',
            'exit_date' => $today,
            'exit_time' => $nowTime,
            'exit_reason' => $reason,
            'pickup_person_name' => $pickupPersonName
        ];
        $smsSent = send_exit_sms($this->db, $student, $exitData, $exitLogId);

        echo json_encode([
            'success' => true,
            'status' => 'success',
            'message' => 'Manual exit recorded successfully.',
            'exit_id' => $exitLogId,
            'exit_time' => date('g:i A', strtotime($nowTime)),
            'sms_status' => $smsSent ? 'Sent' : 'Failed',
            'student' => [
                'name' => trim($student['first_name'] . ' ' . $student['last_name']),
                'admission_number' => $student['admission_number'] ?: $student['application_number'],
                'class_name' => $student['class_name'] ?: 'General',
                'photo' => $student['passport_photo'] ? url('uploads/' . $student['passport_photo']) : null,
            ]
        ]);
        exit;
    }

    public function exitLogs(): void
    {
        require_permission('exit_logs');

        $classes = (new ClassModel($this->db))->all();
        $gates = $this->db->query("SELECT * FROM school_gates ORDER BY gate_name ASC")->fetchAll();

        $dateFrom  = trim($_GET['date_from'] ?? '');
        $dateTo    = trim($_GET['date_to'] ?? '');
        $classId   = (int) ($_GET['class_id'] ?? 0);
        $gateId    = (int) ($_GET['gate_id'] ?? 0);
        $exitType  = trim($_GET['exit_type'] ?? '');
        $smsStatus = trim($_GET['sms_status'] ?? '');
        $search    = trim($_GET['q'] ?? '');
        $page      = max(1, (int) ($_GET['page'] ?? 1));
        $perPage   = 25;
        $offset    = ($page - 1) * $perPage;

        $where = ["1=1"];
        $params = [];

        if ($dateFrom !== '') {
            $where[] = "el.exit_date >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo !== '') {
            $where[] = "el.exit_date <= ?";
            $params[] = $dateTo;
        }
        if ($classId > 0) {
            $where[] = "a.class_id = ?";
            $params[] = $classId;
        }
        if ($gateId > 0) {
            $where[] = "el.gate_id = ?";
            $params[] = $gateId;
        }
        if ($exitType !== '') {
            $where[] = "el.exit_type = ?";
            $params[] = $exitType;
        }
        if ($smsStatus !== '') {
            $where[] = "el.sms_status = ?";
            $params[] = $smsStatus;
        }
        if ($search !== '') {
            $where[] = "(a.first_name LIKE ? OR a.last_name LIKE ? OR a.admission_number LIKE ? OR a.application_number LIKE ? OR el.pickup_person_name LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereSql = implode(' AND ', $where);

        try {
            $stmtCount = $this->db->prepare("SELECT COUNT(*) FROM student_exit_logs el JOIN applicants a ON a.id = el.student_id WHERE {$whereSql}");
            $stmtCount->execute($params);
            $totalLogs = (int) $stmtCount->fetchColumn();
            $totalPages = max(1, (int) ceil($totalLogs / $perPage));

            $stmtLogs = $this->db->prepare($sql);
            $stmtLogs->execute($params);
            $logs = $stmtLogs->fetchAll();

            $today = date('Y-m-d');
            $totalToday = (int) $this->db->query("SELECT COUNT(*) FROM student_exit_logs WHERE exit_date = '{$today}'")->fetchColumn();
            $earlyToday = (int) $this->db->query("SELECT COUNT(*) FROM student_exit_logs WHERE exit_date = '{$today}' AND exit_type = 'early'")->fetchColumn();
            $smsFailed = (int) $this->db->query("SELECT COUNT(*) FROM student_exit_logs WHERE sms_status = 'failed'")->fetchColumn();
        } catch (Throwable $e) {
            MigrationRunner::ensureUpToDate($this->db);
            $totalLogs = 0;
            $totalPages = 1;
            $logs = [];
            $totalToday = 0;
            $earlyToday = 0;
            $smsFailed = 0;
        }

        render('admin/exit_logs', compact(
            'logs', 'classes', 'gates', 'totalLogs', 'totalPages', 'page', 'perPage',
            'dateFrom', 'dateTo', 'classId', 'gateId', 'exitType', 'smsStatus', 'search',
            'totalToday', 'earlyToday', 'smsFailed'
        ), 'admin');
    }

    public function exportExitLogsCsv(): void
    {
        require_permission('exit_logs');

        $dateFrom  = trim($_GET['date_from'] ?? '');
        $dateTo    = trim($_GET['date_to'] ?? '');
        $classId   = (int) ($_GET['class_id'] ?? 0);
        $gateId    = (int) ($_GET['gate_id'] ?? 0);
        $exitType  = trim($_GET['exit_type'] ?? '');
        $smsStatus = trim($_GET['sms_status'] ?? '');
        $search    = trim($_GET['q'] ?? '');

        $where = ["1=1"];
        $params = [];

        if ($dateFrom !== '') {
            $where[] = "el.exit_date >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo !== '') {
            $where[] = "el.exit_date <= ?";
            $params[] = $dateTo;
        }
        if ($classId > 0) {
            $where[] = "a.class_id = ?";
            $params[] = $classId;
        }
        if ($gateId > 0) {
            $where[] = "el.gate_id = ?";
            $params[] = $gateId;
        }
        if ($exitType !== '') {
            $where[] = "el.exit_type = ?";
            $params[] = $exitType;
        }
        if ($smsStatus !== '') {
            $where[] = "el.sms_status = ?";
            $params[] = $smsStatus;
        }
        if ($search !== '') {
            $where[] = "(a.first_name LIKE ? OR a.last_name LIKE ? OR a.admission_number LIKE ? OR a.application_number LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $whereSql = implode(' AND ', $where);

        $sql = "SELECT el.*, a.first_name, a.last_name, a.application_number, a.admission_number,
                       a.parent_phone, c.name AS class_name, g.gate_name, adm.name AS staff_name
                FROM student_exit_logs el
                JOIN applicants a ON a.id = el.student_id
                LEFT JOIN classes c ON c.id = a.class_id
                LEFT JOIN school_gates g ON g.id = el.gate_id
                LEFT JOIN admins adm ON adm.id = el.scanned_by
                WHERE {$whereSql}
                ORDER BY el.exited_at DESC LIMIT 5000";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Student_Exit_Logs_' . date('Y_m_d_His') . '.csv');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Excel compatibility
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($out, ['ID', 'Student Name', 'Admission No', 'Class', 'Exit Date', 'Exit Time', 'Exit Type', 'Reason', 'Pickup Person', 'Gate', 'Verified By', 'Method', 'Parent Phone', 'SMS Status']);

        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['first_name'] . ' ' . $r['last_name'],
                $r['admission_number'] ?: $r['application_number'],
                $r['class_name'] ?: '—',
                $r['exit_date'],
                date('g:i A', strtotime($r['exit_time'])),
                ucfirst($r['exit_type']),
                $r['exit_reason'] ?: '—',
                $r['pickup_person_name'] ?: '—',
                $r['gate_name'] ?: '—',
                $r['staff_name'] ?: ($r['scanned_by_name'] ?: '—'),
                $r['scan_method'],
                $r['parent_phone'],
                ucfirst($r['sms_status'])
            ]);
        }
        fclose($out);
        exit;
    }

    public function retryExitSms(): void
    {
        require_permission('exit_logs');
        verify_csrf();

        $exitLogId = (int) ($_POST['exit_log_id'] ?? 0);
        if ($exitLogId <= 0) {
            flash('danger', 'Invalid exit record reference.');
            redirect('admin/exit-logs');
        }

        $stmt = $this->db->prepare(
            "SELECT el.*, a.first_name, a.last_name, a.parent_phone, c.name AS class_name
             FROM student_exit_logs el
             JOIN applicants a ON a.id = el.student_id
             LEFT JOIN classes c ON c.id = a.class_id
             WHERE el.id = ? LIMIT 1"
        );
        $stmt->execute([$exitLogId]);
        $exitLog = $stmt->fetch();

        if (!$exitLog) {
            flash('danger', 'Exit log not found.');
            redirect('admin/exit-logs');
        }

        $student = [
            'first_name' => $exitLog['first_name'],
            'last_name' => $exitLog['last_name'],
            'parent_phone' => $exitLog['parent_phone'],
            'class_name' => $exitLog['class_name'] ?? ''
        ];

        $sent = send_exit_sms($this->db, $student, $exitLog, $exitLogId);

        if ($sent) {
            flash('success', 'Exit SMS re-sent successfully to parent.');
        } else {
            flash('warning', 'SMS retry attempted, but gateway reported an issue. Check SMS configuration.');
        }

        redirect('admin/exit-logs');
    }

    // ── School Gate Management ────────────────────────────────────────────────

    public function gates(): void
    {
        require_permission('gates');
        $gates = $this->db->query("SELECT * FROM school_gates ORDER BY id ASC")->fetchAll();
        render('admin/gates', compact('gates'), 'admin');
    }

    public function saveGate(): void
    {
        require_permission('gates');
        verify_csrf();

        $id       = (int) ($_POST['id'] ?? 0);
        $name     = trim($_POST['gate_name'] ?? '');
        $code     = trim($_POST['gate_code'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $status   = trim($_POST['status'] ?? 'active');
        $schoolId = SchoolContext::id();

        if ($name === '') {
            flash('danger', 'Gate name is required.');
            redirect('admin/gates');
        }

        if ($id === 0) {
            $ins = $this->db->prepare("INSERT INTO school_gates (school_id, gate_name, gate_code, location, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            $ins->execute([$schoolId, $name, $code, $location, $status]);
            flash('success', 'School Gate added successfully.');
        } else {
            $upd = $this->db->prepare("UPDATE school_gates SET gate_name = ?, gate_code = ?, location = ?, status = ?, updated_at = NOW() WHERE id = ? AND school_id = ?");
            $upd->execute([$name, $code, $location, $status, $id, $schoolId]);
            flash('success', 'Gate updated successfully.');
        }

        redirect('admin/gates');
    }

    public function deleteGate(int $id): void
    {
        require_permission('gates');
        $schoolId = SchoolContext::id();
        $stmt = $this->db->prepare("DELETE FROM school_gates WHERE id = ? AND school_id = ?");
        $stmt->execute([$id, $schoolId]);
        flash('success', 'Gate deleted.');
        redirect('admin/gates');
    }

    // ── Authorized Pickups Management ─────────────────────────────────────────

    public function authorizedPickups(): void
    {
        require_permission('authorized_pickups');

        $studentId = (int) ($_GET['student_id'] ?? 0);
        $students = $this->db->query("SELECT id, first_name, last_name, application_number, admission_number FROM applicants WHERE status = 'Enrolled' ORDER BY last_name, first_name")->fetchAll();

        $where = "1=1";
        $params = [];
        if ($studentId > 0) {
            $where .= " AND p.student_id = ?";
            $params[] = $studentId;
        }

        $sql = "SELECT p.*, a.first_name, a.last_name, a.admission_number, a.application_number, c.name AS class_name
                FROM student_authorized_pickups p
                JOIN applicants a ON a.id = p.student_id
                LEFT JOIN classes c ON c.id = a.class_id
                WHERE {$where}
                ORDER BY a.last_name, p.name ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $pickups = $stmt->fetchAll();

        render('admin/authorized_pickups', compact('pickups', 'students', 'studentId'), 'admin');
    }

    public function saveAuthorizedPickup(): void
    {
        require_permission('authorized_pickups');
        verify_csrf();

        $id           = (int) ($_POST['id'] ?? 0);
        $studentId    = (int) ($_POST['student_id'] ?? 0);
        $name         = trim($_POST['name'] ?? '');
        $relationship = trim($_POST['relationship'] ?? 'Guardian');
        $phone        = trim($_POST['phone'] ?? '');
        $idCardNumber = trim($_POST['id_card_number'] ?? '');
        $isActive     = isset($_POST['is_active']) ? 1 : 0;
        $schoolId     = SchoolContext::id();

        if ($studentId <= 0 || $name === '' || $phone === '') {
            flash('danger', 'Student, Guardian Name, and Phone Number are required.');
            redirect('admin/authorized-pickups');
        }

        if ($id === 0) {
            $ins = $this->db->prepare(
                "INSERT INTO student_authorized_pickups (school_id, student_id, name, relationship, phone, id_card_number, is_active, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
            );
            $ins->execute([$schoolId, $studentId, $name, $relationship, $phone, $idCardNumber, $isActive]);
            flash('success', 'Authorized pickup person registered.');
        } else {
            $upd = $this->db->prepare(
                "UPDATE student_authorized_pickups 
                 SET student_id = ?, name = ?, relationship = ?, phone = ?, id_card_number = ?, is_active = ?, updated_at = NOW()
                 WHERE id = ? AND school_id = ?"
            );
            $upd->execute([$studentId, $name, $relationship, $phone, $idCardNumber, $isActive, $id, $schoolId]);
            flash('success', 'Authorized pickup person updated.');
        }

        redirect('admin/authorized-pickups?student_id=' . $studentId);
    }

    public function deleteAuthorizedPickup(int $id): void
    {
        require_permission('authorized_pickups');
        $schoolId = SchoolContext::id();
        $studentId = (int) $this->db->query("SELECT student_id FROM student_authorized_pickups WHERE id = {$id}")->fetchColumn();
        $this->db->prepare("DELETE FROM student_authorized_pickups WHERE id = ? AND school_id = ?")->execute([$id, $schoolId]);
        flash('success', 'Authorized pickup person removed.');
        redirect('admin/authorized-pickups' . ($studentId ? '?student_id=' . $studentId : ''));
    }

    public function updates(): void
    {
        require_admin();
        require_once __DIR__ . '/../version.php';
        require_once __DIR__ . '/../config/ApiKeyService.php';
        require_once __DIR__ . '/../config/UpdaterService.php';
        require_once __DIR__ . '/../updater/MigrationRunner.php';
        require_once __DIR__ . '/../updater/BackupManager.php';
        require_once __DIR__ . '/../updater/UpdateInstaller.php';
        require_once __DIR__ . '/../updater/RollbackManager.php';
        require_once __DIR__ . '/../updater/UpdateChecker.php';

        $forceCheck = isset($_GET['check']) && $_GET['check'] === 'now';
        $updateInfo = UpdateChecker::check($forceCheck);

        // Handle POST actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
            $action = trim($_POST['action'] ?? '');

            if ($action === 'apply_update') {
                $targetVersion = trim($_POST['target_version'] ?? ($updateInfo['latest_version'] ?? ''));
                $downloadUrl = trim($_POST['download_url'] ?? ($updateInfo['download_url'] ?? ''));
                $sha256 = trim($_POST['sha256'] ?? ($updateInfo['sha256'] ?? ''));
                $signature = trim($_POST['signature'] ?? ($updateInfo['signature'] ?? ''));

                $adminName = $_SESSION['admin']['name'] ?? ($_SESSION['admin_name'] ?? 'System Administrator');
                $installer = new UpdateInstaller($this->db);
                $res = $installer->applyUpdate($targetVersion, $downloadUrl, $sha256, $signature, $adminName);

                if ($res['success']) {
                    flash('success', $res['message']);
                } else {
                    flash('danger', $res['message']);
                }
                redirect('admin/updates');
            }

            if ($action === 'run_migrations') {
                $runner = new MigrationRunner($this->db);
                $res = $runner->runPending();
                if ($res['success']) {
                    $executedList = !empty($res['executed']) ? implode(', ', $res['executed']) : 'All';
                    flash('success', "Database updated successfully! Applied: {$executedList}");
                } else {
                    flash('danger', $res['message'] ?? "Migration failed.");
                }
                redirect('admin/updates');
            }

            if ($action === 'create_backup') {
                $backupMgr = new BackupManager($this->db);
                $res = $backupMgr->createBackup(defined('EDUCORE_VERSION') ? EDUCORE_VERSION : '1.0.0');
                if ($res['success']) {
                    $sizeMb = round($res['total_size_bytes'] / (1024 * 1024), 2);
                    flash('success', "Full system snapshot created successfully (" . ($sizeMb > 0 ? $sizeMb . " MB" : round($res['total_size_bytes'] / 1024, 1) . " KB") . ") in storage/backups.");
                } else {
                    flash('danger', "Failed creating system backup: " . ($res['message'] ?? ''));
                }
                redirect('admin/updates');
            }

            if ($action === 'rollback') {
                $backupDir = trim($_POST['backup_dir'] ?? '');
                $rollbackMgr = new RollbackManager($this->db);
                $res = $rollbackMgr->rollback($backupDir);
                if ($res['success']) {
                    flash('success', "System rollback completed successfully from backup.");
                } else {
                    flash('danger', "Rollback failed: " . ($res['message'] ?? ''));
                }
                redirect('admin/updates');
            }
        }

        // Fetch Update History
        $history = [];
        try {
            $stmt = $this->db->query("SELECT * FROM `system_update_history` ORDER BY `id` DESC LIMIT 20");
            $history = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Throwable $t) {}

        // Fetch Pending Migrations
        $migrationRunner = new MigrationRunner($this->db);
        $pendingMigrations = $migrationRunner->getPendingMigrations();
        $executedMigrations = $migrationRunner->getExecutedMigrations();

        // License & System Info
        $licenseData = ApiKeyService::loadLocalLicense();
        $graceInfo = ApiKeyService::getGracePeriodInfo();

        render('admin/system_updates', compact(
            'updateInfo',
            'history',
            'pendingMigrations',
            'executedMigrations',
            'licenseData',
            'graceInfo'
        ), 'admin');
    }
}
