<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/SchoolContext.php';
require_once __DIR__ . '/../models/ActivityLog.php';

final class StudentEnrollmentService
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Directly enrol a student (old/continuing student or direct admission).
     *
     * @param array $data Input form data
     * @param array|null $passportFile Uploaded file array ($_FILES['passport_photo'])
     * @param bool $sendEmail Whether to send credentials email to parent
     * @param bool $sendSms Whether to send credentials SMS to parent
     * @return array Result containing created student data and temporary credentials
     * @throws InvalidArgumentException|RuntimeException
     */
    public function enrolDirect(
        array $data,
        ?array $passportFile = null,
        bool $sendEmail = true,
        bool $sendSms = true
    ): array {
        // 1. Validation of required core fields
        $firstName = trim((string) ($data['first_name'] ?? ''));
        $middleName = trim((string) ($data['middle_name'] ?? ''));
        $lastName = trim((string) ($data['last_name'] ?? ''));
        $gender = trim((string) ($data['gender'] ?? ''));
        $dob = trim((string) ($data['date_of_birth'] ?? ''));
        $classId = (int) ($data['class_id'] ?? 0);
        $parentName = trim((string) ($data['parent_name'] ?? ''));
        $parentPhone = trim((string) ($data['parent_phone'] ?? ''));
        $parentEmail = trim((string) ($data['parent_email'] ?? ''));
        $homeAddress = trim((string) ($data['home_address'] ?? ''));
        $admissionType = trim((string) ($data['admission_type'] ?? 'Old Student'));

        if ($firstName === '' || $lastName === '') {
            throw new InvalidArgumentException('Student first name and last name are required.');
        }
        if (!in_array($gender, ['Male', 'Female'], true)) {
            throw new InvalidArgumentException('Please select a valid gender (Male or Female).');
        }
        if ($dob === '' || !strtotime($dob)) {
            throw new InvalidArgumentException('Please provide a valid date of birth (YYYY-MM-DD).');
        }
        if ($classId <= 0) {
            throw new InvalidArgumentException('Please select a valid class.');
        }
        if ($parentName === '') {
            $parentName = trim(($data['father_name'] ?? '') ?: ($data['mother_name'] ?? '') ?: ($data['guardian_name'] ?? '') ?: ($firstName . ' ' . $lastName . ' Guardian'));
        }
        if ($parentPhone === '') {
            throw new InvalidArgumentException('Parent phone number is required.');
        }
        if ($parentEmail === '' || !filter_var($parentEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('A valid parent email address is required for portal communication.');
        }

        // Verify class exists
        $stmtClass = $this->db->prepare('SELECT id, name FROM classes WHERE id = ? LIMIT 1');
        $stmtClass->execute([$classId]);
        $classRow = $stmtClass->fetch();
        if (!$classRow) {
            throw new InvalidArgumentException('The selected class does not exist in the school system.');
        }

        // 2. Resolve or generate Admission Number and Student Username
        $schoolInfo = SchoolContext::info();
        $schoolCode = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) ($schoolInfo['school_code'] ?? 'SCH')));
        $schoolCode = substr($schoolCode !== '' ? $schoolCode : 'SCH', 0, 6);
        $year = date('Y');

        // Check custom or existing admission number
        $customAdmissionNumber = trim((string) ($data['admission_number'] ?? ''));
        if ($customAdmissionNumber !== '') {
            $stmtCheck = $this->db->prepare('SELECT id FROM applicants WHERE admission_number = ? LIMIT 1');
            $stmtCheck->execute([$customAdmissionNumber]);
            if ($stmtCheck->fetch()) {
                throw new InvalidArgumentException("Admission number '{$customAdmissionNumber}' is already assigned to another student.");
            }
            $admissionNumber = $customAdmissionNumber;
        } else {
            $admissionNumber = $this->generateAdmissionNumber($schoolCode, $year);
        }

        // Check custom or auto-generated student username
        $customUsername = trim((string) ($data['student_username'] ?? ''));
        if ($customUsername !== '') {
            $stmtCheckUser = $this->db->prepare('SELECT id FROM student_accounts WHERE username = ? LIMIT 1');
            $stmtCheckUser->execute([$customUsername]);
            if ($stmtCheckUser->fetch()) {
                throw new InvalidArgumentException("Student username '{$customUsername}' is already taken.");
            }
            $studentUsername = $customUsername;
        } else {
            // Auto generate username based on admission number or standard pattern
            $studentUsername = $this->generateStudentUsername($schoolCode, $year, $admissionNumber);
        }

        // Generate application number
        $applicationNumber = 'DIR-' . $year . '-' . strtoupper(bin2hex(random_bytes(3)));

        // Passwords (use custom if provided, otherwise auto-generate secure temp password)
        $studentPass = trim((string) ($data['student_password'] ?? '')) ?: generate_temp_password();
        $parentPass = trim((string) ($data['parent_password'] ?? '')) ?: generate_temp_password();
        $studentHash = password_hash($studentPass, PASSWORD_BCRYPT);
        $parentHash = password_hash($parentPass, PASSWORD_BCRYPT);

        // Passport photograph upload
        $passportPath = null;
        if ($passportFile !== null && !empty($passportFile['name'])) {
            $passportPath = $this->uploadPassport($passportFile);
        }

        // Enrollment date (custom/historical or current)
        $customEnrolledAt = trim((string) ($data['enrolled_at'] ?? ''));
        $enrolledAtDate = ($customEnrolledAt !== '' && strtotime($customEnrolledAt)) ? date('Y-m-d H:i:s', strtotime($customEnrolledAt)) : date('Y-m-d H:i:s');

        // Execute atomic database insertion
        try {
            $this->db->beginTransaction();

            // 1. Insert into applicants
            $sqlApp = "INSERT INTO applicants (
                application_number, admission_type, admission_number, student_username,
                first_name, middle_name, last_name, gender, date_of_birth,
                state_of_origin, local_government, nationality, religion, home_address,
                parent_name, parent_phone, parent_email, father_name, mother_name, guardian_name, parent_occupation,
                class_id, previous_school, previous_class, blood_group, allergies, special_needs,
                emergency_name, emergency_relationship, emergency_phone,
                passport_photo, status, admission_status, enrollment_status,
                student_status, enrolled_at, created_at, updated_at
            ) VALUES (
                :application_number, :admission_type, :admission_number, :student_username,
                :first_name, :middle_name, :last_name, :gender, :date_of_birth,
                :state_of_origin, :local_government, :nationality, :religion, :home_address,
                :parent_name, :parent_phone, :parent_email, :father_name, :mother_name, :guardian_name, :parent_occupation,
                :class_id, :previous_school, :previous_class, :blood_group, :allergies, :special_needs,
                :emergency_name, :emergency_relationship, :emergency_phone,
                :passport_photo, 'Enrolled', 'Enrolled', 'Completed',
                'Active', :enrolled_at, NOW(), NOW()
            )";

            $stmtApp = $this->db->prepare($sqlApp);
            $stmtApp->execute([
                ':application_number' => $applicationNumber,
                ':admission_type' => $admissionType !== '' ? $admissionType : 'Old Student',
                ':admission_number' => $admissionNumber,
                ':student_username' => $studentUsername,
                ':first_name' => $firstName,
                ':middle_name' => $middleName !== '' ? $middleName : null,
                ':last_name' => $lastName,
                ':gender' => $gender,
                ':date_of_birth' => $dob,
                ':state_of_origin' => trim((string) ($data['state_of_origin'] ?? 'State')),
                ':local_government' => trim((string) ($data['local_government'] ?? '')) ?: null,
                ':nationality' => trim((string) ($data['nationality'] ?? 'Nigerian')),
                ':religion' => trim((string) ($data['religion'] ?? '')) ?: null,
                ':home_address' => $homeAddress,
                ':parent_name' => $parentName,
                ':parent_phone' => $parentPhone,
                ':parent_email' => $parentEmail,
                ':father_name' => trim((string) ($data['father_name'] ?? '')) ?: null,
                ':mother_name' => trim((string) ($data['mother_name'] ?? '')) ?: null,
                ':guardian_name' => trim((string) ($data['guardian_name'] ?? '')) ?: null,
                ':parent_occupation' => trim((string) ($data['parent_occupation'] ?? '')) ?: null,
                ':class_id' => $classId,
                ':previous_school' => trim((string) ($data['previous_school'] ?? '')) ?: null,
                ':previous_class' => trim((string) ($data['previous_class'] ?? '')) ?: null,
                ':blood_group' => trim((string) ($data['blood_group'] ?? '')) ?: null,
                ':allergies' => trim((string) ($data['allergies'] ?? '')) ?: null,
                ':special_needs' => trim((string) ($data['special_needs'] ?? '')) ?: null,
                ':emergency_name' => trim((string) ($data['emergency_name'] ?? '')) ?: null,
                ':emergency_relationship' => trim((string) ($data['emergency_relationship'] ?? '')) ?: null,
                ':emergency_phone' => trim((string) ($data['emergency_phone'] ?? '')) ?: null,
                ':passport_photo' => $passportPath,
                ':enrolled_at' => $enrolledAtDate,
            ]);

            $applicantId = (int) $this->db->lastInsertId();

            // 2. Generate QR code for Attendance & Exit verification
            $qrData = $this->generateAttendanceQr($applicantId);
            $stmtQr = $this->db->prepare("UPDATE applicants SET qr_code = ?, qr_data = ?, student_login_created_at = NOW() WHERE id = ?");
            $stmtQr->execute([$qrData['path'], $qrData['token'], $applicantId]);

            // 3. Create student_accounts login record
            $stmtStud = $this->db->prepare(
                "INSERT INTO student_accounts (applicant_id, username, password_hash, must_change_password) 
                 VALUES (?, ?, ?, 1) 
                 ON DUPLICATE KEY UPDATE username = VALUES(username), password_hash = VALUES(password_hash), must_change_password = 1"
            );
            $stmtStud->execute([$applicantId, $studentUsername, $studentHash]);

            // 4. Create parent_accounts login record
            $stmtParent = $this->db->prepare(
                "INSERT INTO parent_accounts (applicant_id, phone, email, password_hash, must_change_password) 
                 VALUES (?, ?, ?, ?, 1) 
                 ON DUPLICATE KEY UPDATE phone = VALUES(phone), email = VALUES(email), password_hash = VALUES(password_hash), must_change_password = 1"
            );
            $stmtParent->execute([$applicantId, $parentPhone, $parentEmail, $parentHash]);

            // 5. Ensure admission_letters entry exists
            $stmtLetter = $this->db->prepare("INSERT INTO admission_letters (applicant_id, admission_number, generated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE admission_number = VALUES(admission_number)");
            $stmtLetter->execute([$applicantId, $admissionNumber]);

            // 6. In-app welcome notifications
            $studentAccId = (int) $this->db->query("SELECT id FROM student_accounts WHERE applicant_id = {$applicantId}")->fetchColumn();
            $parentAccId = (int) $this->db->query("SELECT id FROM parent_accounts WHERE applicant_id = {$applicantId}")->fetchColumn();
            if ($studentAccId > 0) {
                create_notification($this->db, 'student', $studentAccId, 'Welcome to Student Portal!', 'Welcome to your portal. Your account is active.');
            }
            if ($parentAccId > 0) {
                create_notification($this->db, 'parent', $parentAccId, 'Welcome to Parent Portal!', 'Welcome to the parent portal. You can now monitor your child\'s attendance, results, and fees.');
            }

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Student direct enrollment failed: ' . $e->getMessage());
            throw new RuntimeException('Direct enrollment failed: ' . $e->getMessage(), (int) $e->getCode(), $e);
        }

        // 7. Activity Log
        try {
            $fullName = trim($firstName . ' ' . $lastName);
            (new ActivityLog($this->db))->record(
                'student_enrolled_direct',
                "Direct enrolled student: {$admissionNumber} ({$fullName} - Class: {$classRow['name']})"
            );
        } catch (Throwable $logEx) {
            error_log('Enrollment log recording error: ' . $logEx->getMessage());
        }

        // 8. Deliver credentials to Parent via Email & SMS if requested
        $schoolName = (string) setting('school_name', APP_NAME);
        if ($sendEmail && filter_var($parentEmail, FILTER_VALIDATE_EMAIL)) {
            try {
                $subject = "Student Portal Credentials - {$admissionNumber}";
                $body = "Dear Parent,\n\n"
                    . "Student " . trim($firstName . ' ' . $lastName) . " has been successfully enrolled into {$classRow['name']}.\n\n"
                    . "Here are your portal access details:\n\n"
                    . "--- STUDENT PORTAL ---\n"
                    . "URL: " . url('student/login') . "\n"
                    . "Username / ID: " . $studentUsername . "\n"
                    . "Temporary Password: " . $studentPass . "\n\n"
                    . "--- PARENT PORTAL ---\n"
                    . "URL: " . url('parent/login') . "\n"
                    . "Username / Email: " . $parentEmail . "\n"
                    . "Temporary Password: " . $parentPass . "\n\n"
                    . "Please log in and update your passwords immediately.\n\n"
                    . "Regards,\n"
                    . $schoolName;

                send_email_notice($parentEmail, $subject, $body);
            } catch (Throwable $mailEx) {
                error_log('Direct enrollment credentials email error: ' . $mailEx->getMessage());
            }
        }

        if ($sendSms && $parentPhone !== '') {
            try {
                $smsMsg = "Welcome to {$schoolName}. {$firstName} enrolled in {$classRow['name']}. Student ID: {$studentUsername}. Check parent email ({$parentEmail}) for portal credentials.";
                send_sms_notice($parentPhone, $smsMsg);
            } catch (Throwable $smsEx) {
                error_log('Direct enrollment credentials SMS error: ' . $smsEx->getMessage());
            }
        }

        return [
            'applicant_id' => $applicantId,
            'admission_number' => $admissionNumber,
            'student_name' => trim($firstName . ' ' . ($middleName ? $middleName . ' ' : '') . $lastName),
            'class_name' => (string) $classRow['name'],
            'class_id' => $classId,
            'student_username' => $studentUsername,
            'student_password' => $studentPass,
            'parent_email' => $parentEmail,
            'parent_phone' => $parentPhone,
            'parent_password' => $parentPass,
            'admission_type' => $admissionType,
            'qr_code' => $qrData['path'] ?? null,
        ];
    }

    /**
     * Batch import old students from CSV file.
     *
     * @param string $csvPath Path to uploaded CSV file
     * @param bool $sendNotices Whether to dispatch SMS/Email during bulk import
     * @param int|null $defaultClassId Fallback class if not specified in row
     * @return array Summary of import operation
     */
    public function importCsv(string $csvPath, bool $sendNotices = false, ?int $defaultClassId = null): array
    {
        if (!file_exists($csvPath) || !is_readable($csvPath)) {
            throw new InvalidArgumentException('CSV file could not be read or does not exist.');
        }

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            throw new RuntimeException('Unable to open CSV file.');
        }

        // Fetch all classes and index by lowercase trimmed name
        $classes = $this->db->query('SELECT id, name FROM classes')->fetchAll();
        $classMap = [];
        foreach ($classes as $c) {
            $classMap[strtolower(trim($c['name']))] = (int) $c['id'];
        }

        // Read header row
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            throw new InvalidArgumentException('The uploaded CSV file is empty.');
        }

        $normalizedHeader = array_map(fn($h) => strtolower(trim(preg_replace('/[^a-zA-Z0-9_]/', '_', (string) $h))), $header);

        $enrolledCount = 0;
        $failedCount = 0;
        $errors = [];
        $rowNumber = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            // Skip empty rows
            if (empty(array_filter($row, fn($v) => trim((string)$v) !== ''))) {
                continue;
            }

            $rowData = [];
            foreach ($normalizedHeader as $idx => $key) {
                $rowData[$key] = trim((string) ($row[$idx] ?? ''));
            }

            // Map fields
            $firstName = $rowData['first_name'] ?? $rowData['firstname'] ?? '';
            $middleName = $rowData['middle_name'] ?? $rowData['middlename'] ?? '';
            $lastName = $rowData['last_name'] ?? $rowData['lastname'] ?? $rowData['surname'] ?? '';
            $gender = ucfirst(strtolower($rowData['gender'] ?? 'Male'));
            $dob = $rowData['date_of_birth'] ?? $rowData['dob'] ?? '2015-01-01';
            $className = strtolower($rowData['class_name'] ?? $rowData['class'] ?? '');
            $admissionNumber = $rowData['admission_number'] ?? $rowData['admission_no'] ?? $rowData['student_id'] ?? '';
            $parentName = $rowData['parent_name'] ?? $rowData['guardian_name'] ?? ($firstName . ' Guardian');
            $parentPhone = $rowData['parent_phone'] ?? $rowData['phone'] ?? '';
            $parentEmail = $rowData['parent_email'] ?? $rowData['email'] ?? '';
            $homeAddress = $rowData['home_address'] ?? $rowData['address'] ?? 'School Residence';
            $enrolledAt = $rowData['enrollment_date'] ?? $rowData['enrolled_at'] ?? '';

            // Resolve class
            $classId = $classMap[$className] ?? $defaultClassId;
            if (!$classId) {
                $failedCount++;
                $errors[] = "Row {$rowNumber}: Unknown class '{$className}'. Please map to an existing school class.";
                continue;
            }

            if ($parentEmail === '' || !filter_var($parentEmail, FILTER_VALIDATE_EMAIL)) {
                $parentEmail = 'parent_' . strtolower(preg_replace('/[^a-z0-9]/', '', $firstName . $lastName)) . '_' . rand(100, 999) . '@school.test';
            }
            if ($parentPhone === '') {
                $parentPhone = '08000000000';
            }

            $enrolData = [
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'gender' => in_array($gender, ['Male', 'Female'], true) ? $gender : 'Male',
                'date_of_birth' => $dob,
                'class_id' => $classId,
                'admission_type' => 'Old Student',
                'admission_number' => $admissionNumber,
                'parent_name' => $parentName,
                'parent_phone' => $parentPhone,
                'parent_email' => $parentEmail,
                'home_address' => $homeAddress,
                'enrolled_at' => $enrolledAt,
            ];

            try {
                $this->enrolDirect($enrolData, null, $sendNotices, $sendNotices);
                $enrolledCount++;
            } catch (Throwable $e) {
                $failedCount++;
                $errors[] = "Row {$rowNumber} ({$firstName} {$lastName}): " . $e->getMessage();
            }
        }

        fclose($handle);

        return [
            'total_processed' => $enrolledCount + $failedCount,
            'enrolled_count' => $enrolledCount,
            'failed_count' => $failedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Generate ready-to-use Sample CSV string for Old Students migration.
     */
    public function generateSampleCsv(): string
    {
        $classes = $this->db->query('SELECT name FROM classes ORDER BY sort_order, name LIMIT 3')->fetchAll(PDO::FETCH_COLUMN);
        $c1 = $classes[0] ?? 'Primary 1';
        $c2 = $classes[1] ?? 'Primary 2';

        $output = fopen('php://temp', 'r+');
        fputcsv($output, [
            'first_name',
            'middle_name',
            'last_name',
            'gender',
            'date_of_birth',
            'class_name',
            'admission_number',
            'parent_name',
            'parent_phone',
            'parent_email',
            'home_address',
            'enrollment_date'
        ]);

        // Sample Row 1: Old student with established ID
        fputcsv($output, [
            'Emmanuel',
            'Tunde',
            'Adeyemi',
            'Male',
            '2016-04-12',
            $c1,
            'SCH/2023/045',
            'Dr. Victor Adeyemi',
            '08031234567',
            'victor.adeyemi@example.com',
            '14 Victoria Island Road, Lagos',
            '2023-09-11'
        ]);

        // Sample Row 2: Continuing student (let system auto-generate admission number)
        fputcsv($output, [
            'Fatima',
            'Zainab',
            'Bello',
            'Female',
            '2015-08-23',
            $c2,
            '',
            'Alhaji Bello Musa',
            '08029876543',
            'bello.musa@example.com',
            '8 Ahmadu Bello Way, Abuja',
            '2024-01-08'
        ]);

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return (string) $csv;
    }

    private function generateAdmissionNumber(string $schoolCode, string $year): string
    {
        $prefix = $schoolCode . $year;
        $stmtCount = $this->db->prepare('SELECT COUNT(*) + 1 FROM applicants WHERE admission_number LIKE ?');
        $stmtCount->execute([$prefix . '%']);
        $nextNum = (int) $stmtCount->fetchColumn();
        $serial = str_pad((string) $nextNum, 4, '0', STR_PAD_LEFT);
        $candidate = $prefix . $serial;

        // Verify uniqueness
        $stmtCheck = $this->db->prepare('SELECT id FROM applicants WHERE admission_number = ? LIMIT 1');
        $stmtCheck->execute([$candidate]);
        if ($stmtCheck->fetch()) {
            return $prefix . str_pad((string) ($nextNum + rand(10, 99)), 4, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    private function generateStudentUsername(string $schoolCode, string $year, string $admissionNumber): string
    {
        // Try school standard GF-STD-YYYY-XXXX
        $stmtCount = $this->db->prepare('SELECT COUNT(*) + 1 FROM student_accounts WHERE username LIKE ?');
        $stmtCount->execute([$schoolCode . '-STD-' . $year . '-%']);
        $next = (int) $stmtCount->fetchColumn();
        $serial = str_pad((string) $next, 4, '0', STR_PAD_LEFT);
        $candidate = $schoolCode . '-STD-' . $year . '-' . $serial;

        $stmtCheck = $this->db->prepare('SELECT id FROM student_accounts WHERE username = ? LIMIT 1');
        $stmtCheck->execute([$candidate]);
        if ($stmtCheck->fetch()) {
            $candidate = $schoolCode . '-STD-' . $year . '-' . str_pad((string) ($next + rand(10, 99)), 4, '0', STR_PAD_LEFT);
        }

        return $candidate;
    }

    private function generateAttendanceQr(int $applicantId): array
    {
        $schoolInfo = SchoolContext::info();
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $qrToken = 'ATTENDANCE-STD-' . $applicantId . '-' . bin2hex(random_bytes(4));

        $schoolDomain = trim((string) ($schoolInfo['domain'] ?? ''));
        $portalHost = ($schoolDomain !== '' && $schoolDomain !== 'localhost') ? preg_replace('#^https?://#', '', $schoolDomain) : $host;
        $portalHost = preg_replace('#/.*$#', '', $portalHost);
        $qrData = $scheme . '://' . $portalHost . BASE_URL . '/?route=attendance/scan&token=' . urlencode($qrToken);

        $qrPath = 'qrcodes/std_' . $applicantId . '.png';
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
                error_log('Offline QR generation in StudentEnrollmentService failed: ' . $qre->getMessage());
            }
        }

        return [
            'token' => $qrToken,
            'path' => $qrGenerated ? $qrPath : null,
            'url' => $qrData,
        ];
    }

    private function uploadPassport(array $file): ?string
    {
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/pjpeg' => 'jpg',
            'image/png' => 'png',
            'image/x-png' => 'png',
            'image/webp' => 'webp',
        ];

        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return null;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($file['tmp_name']);
        if (!isset($allowed[$mime])) {
            return null;
        }

        $folder = UPLOAD_PATH . 'passports/';
        if (!is_dir($folder)) {
            @mkdir($folder, 0755, true);
        }

        $ext = $allowed[$mime];
        $filename = bin2hex(random_bytes(12)) . '.' . $ext;
        if (move_uploaded_file($file['tmp_name'], $folder . $filename)) {
            return 'passports/' . $filename;
        }

        return null;
    }
}
