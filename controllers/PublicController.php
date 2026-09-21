<?php
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../models/Applicant.php';
require_once __DIR__ . '/../models/ClassModel.php';
require_once __DIR__ . '/../models/Payment.php';
require_once __DIR__ . '/../config/PaymentConfig.php';
require_once __DIR__ . '/NotificationController.php';
require_once __DIR__ . '/../services/AttendanceService.php';
require_once __DIR__ . '/../updater/MigrationRunner.php';

final class PublicController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
        MigrationRunner::ensureUpToDate($this->db);
    }

    public function home(): void
    {
        if (SchoolContext::isResolved()) {
            $classes = (new ClassModel($this->db))->all();
            render('public/home', compact('classes'), 'landing');
        } else {
            $classes = (new ClassModel($this->db))->all();
            render('public/landing', compact('classes'), 'landing');
        }
    }

    public function registerSchool(): void
    {
        require __DIR__ . '/../views/public/register_school.php';
    }

    public function submitSchoolRegistration(): void
    {
        $name = trim($_POST['school_name'] ?? '');
        $code = trim(strtoupper($_POST['school_code'] ?? ''));
        $domain = $this->normalizeDomain($_POST['domain'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = (string) ($_POST['password'] ?? '');
        $plan = $_POST['plan'] ?? 'professional';
        if ($plan === 'starter') { $plan = 'basic'; }
        if ($plan === 'selfhosted') { $plan = 'self_hosted'; }
        $couponCode = trim(strtoupper($_POST['coupon'] ?? ''));
        $address = trim($_POST['address'] ?? '');
        $principal = trim($_POST['principal_name'] ?? '');

        if ($name === '' || $code === '' || $email === '' || $password === '' || $domain === '') {
            flash('danger', 'Please complete all required fields.');
            redirect('register-school');
        }

        // Check if school_code, domain, or login email already exists before creating records.
        $stmt = $this->db->prepare("SELECT id, school_code, domain FROM schools WHERE school_code = ? OR LOWER(TRIM(domain)) = ? LIMIT 1");
        $stmt->execute([$code, $domain]);
        $existing = $stmt->fetch();
        if ($existing) {
            if (strcasecmp($existing['school_code'], $code) === 0) {
                flash('danger', 'School code is already taken.');
            } else {
                flash('danger', 'Domain/Subdomain is already registered.');
            }
            redirect('register-school');
        }

        $emailStmt = $this->db->prepare(
            "SELECT 'admin' AS source FROM admins WHERE email = ?
             UNION
             SELECT 'customer' AS source FROM customer_accounts WHERE email = ?
             LIMIT 1"
        );
        $emailStmt->execute([$email, $email]);
        if ($emailStmt->fetch()) {
            flash('danger', 'This email address already has an EduCore account. Please log in or use a different email.');
            redirect('register-school');
        }

        // Determine plan pricing
        switch ($plan) {
            case 'basic':
                $price = 95000.00;
                break;
            case 'professional':
                $price = 180000.00;
                break;
            case 'enterprise':
                $price = 350000.00;
                break;
            case 'self_hosted':
                $price = 500000.00;
                break;
            default:
                $price = 180000.00;
                break;
        }

        $schoolBillingPlan = ($plan === 'self_hosted') ? 'enterprise' : $plan;

        // Validate coupon code
        $discount = 0;
        if ($couponCode !== '') {
            $cpStmt = $this->db->prepare("SELECT * FROM coupons WHERE code = ? AND expires_at >= CURRENT_DATE() AND used_count < max_uses LIMIT 1");
            $cpStmt->execute([$couponCode]);
            $cp = $cpStmt->fetch();
            if ($cp) {
                $discount = (int) $cp['discount_percent'];
                $price = $price - ($price * ($discount / 100));
                // Increment coupon usage
                $this->db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?")->execute([$cp['id']]);
            }
        }

        // Generate License, API Key, and Installation ID
        $licKey = 'EDUCORE-' . strtoupper(substr($plan, 0, 3)) . '-' . date('Y') . '-' . strtoupper(substr(md5(uniqid('', true)), 0, 8));
        $apiKey = 'edu_key_' . bin2hex(random_bytes(32));
        $installId = 'INST-' . strtoupper(substr(md5(uniqid('', true)), 0, 12));

        // 1. Create School Record
        $schStmt = $this->db->prepare(
            "INSERT INTO schools (school_code, school_name, domain, principal_name, phone, email, address, status, api_key, installation_id, registered_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, 'active', ?, ?, NOW())"
        );
        $schStmt->execute([$code, $name, $domain, $principal, $phone, $email, $address, $apiKey, $installId]);
        $schoolId = (int) $this->db->lastInsertId();

        // 2. Provision default school settings
        $this->db->prepare(
            "INSERT INTO school_settings (school_id, setting_key, setting_value) VALUES 
             (?, 'school_name', ?),
             (?, 'school_motto', 'Smart Learning for a Brighter Future'),
             (?, 'primary_color', '#0b3d91'),
             (?, 'secondary_color', '#f4b942'),
             (?, 'attendance_sms_enabled', '1'),
             (?, 'checkin_sms_enabled', '1'),
             (?, 'absent_sms_enabled', '1')"
        )->execute([$schoolId, $name, $schoolId, $schoolId, $schoolId, $schoolId, $schoolId, $schoolId]);

        // 3. Create administrator account for the school
        $passHash = password_hash($password, PASSWORD_BCRYPT);
        $admStmt = $this->db->prepare("INSERT INTO admins (school_id, name, email, password_hash, role) VALUES (?, 'School Administrator', ?, ?, 'super_admin')");
        $admStmt->execute([$schoolId, $email, $passHash]);
        $adminId = (int) $this->db->lastInsertId();

        // 4. Generate subscription invoice
        $invNo = 'INV-' . date('Ymd') . '-' . rand(1000, 9999);
        $subStmt = $this->db->prepare(
            "INSERT INTO school_subscriptions (school_id, invoice_number, plan, amount, payment_method, transaction_ref, payment_date, period_start, period_end, status) 
             VALUES (?, ?, ?, ?, 'paystack', ?, CURRENT_DATE(), CURRENT_DATE(), DATE_ADD(CURRENT_DATE(), INTERVAL 1 YEAR), 'paid')"
        );
        $subStmt->execute([$schoolId, $invNo, $schoolBillingPlan, $price, 'TXN-' . strtoupper(substr(md5(uniqid('', true)), 0, 16))]);

        // 5. Generate active license
        $licStmt = $this->db->prepare(
            "INSERT INTO school_licenses (school_id, license_key, plan, activated_at, expires_at, is_active, last_verified, grace_days) 
             VALUES (?, ?, ?, NOW(), DATE_ADD(CURRENT_DATE(), INTERVAL 1 YEAR), 1, NOW(), 15)"
        );
        $licStmt->execute([$schoolId, $licKey, $schoolBillingPlan]);

        // 6. Create Customer Portal account (B2B SaaS layer)
        $custStmt = $this->db->prepare(
            "INSERT INTO customer_accounts 
              (school_id, contact_name, email, password_hash, phone, company_name, plan, status, 
               subscription_expires_at, sms_balance, api_key, installation_id, email_verified_at, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'active', DATE_ADD(CURRENT_DATE(), INTERVAL 1 YEAR), 0, ?, ?, NOW(), NOW())"
        );
        $custStmt->execute([$schoolId, $principal ?: 'School Admin', $email, $passHash, $phone, $name, $plan, $apiKey, $installId]);
        $customerId = (int) $this->db->lastInsertId();

        // 7. Create customer invoice record
        $invStmt = $this->db->prepare(
            "INSERT INTO customer_invoices 
              (customer_id, invoice_number, description, subtotal, vat_percent, vat_amount, total, currency, status, payment_method, payment_ref, paid_at, period_start, period_end, created_at)
             VALUES (?, ?, ?, ?, 7.5, ?, ?, 'NGN', 'paid', 'paystack', ?, NOW(), CURRENT_DATE(), DATE_ADD(CURRENT_DATE(), INTERVAL 1 YEAR), NOW())"
        );
        $vat = $price * 0.075;
        $total = $price + $vat;
        $invStmt->execute([
            $customerId, $invNo, 
            ucfirst($plan) . " Plan Subscription — Annual", 
            $price, $vat, $total, 
            'TXN-' . strtoupper(substr(md5(uniqid('', true)), 0, 16))
        ]);

        // 8. Map initial installation domain (default to localhost/sandbox during dev)
        $this->db->prepare(
            "INSERT INTO customer_domains (customer_id, domain, is_primary, verified, added_at)
             VALUES (?, ?, 1, 1, NOW())"
        )->execute([$customerId, $domain]);

        // Send email/notification (simulated in audit logs)
        $this->db->prepare("INSERT INTO super_audit_logs (school_id, action, entity, new_value) VALUES (?, 'self_register', 'schools', ?)")
            ->execute([$schoolId, "School self-registered. Provisioned Admin: {$email}, Plan: {$plan}, License: {$licKey}"]);

        // Send welcome notifications
        try {
            send_sms_notice($phone, "Welcome to EduCore! Your license key: {$licKey}. Portal: /portal");
            send_email_notice($email, "Welcome to EduCore!", "Hi {$principal},\n\nYour school registration is active.\nLicense Key: {$licKey}\nAPI Key: {$apiKey}\n\nLog in to your Customer Portal to manage downloads and billing: /portal");
        } catch (Throwable $e) {
            error_log("SaaS Welcome Notification failed: " . $e->getMessage());
        }

        session_regenerate_id(true);
        $_SESSION['admin'] = [
            'id' => $adminId,
            'name' => 'School Administrator',
            'role' => 'super_admin',
            'school_id' => $schoolId,
        ];
        SchoolContext::set($schoolId);

        flash('success', "Congratulations! Your school portal is activated. License Key: {$licKey} | API Key: {$apiKey}.");
        redirect('admin/dashboard');
    }

    private function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = preg_replace('#/.*$#', '', $domain);
        $domain = preg_replace('/:\d+$/', '', $domain);
        return rtrim($domain, '.');
    }
    public function apply(string $admissionType = ''): void
    {
        $classes = (new ClassModel($this->db))->all();
        $admissionType = $this->normalizeAdmissionType($admissionType ?: ($_GET['type'] ?? ''));
        render('public/apply', compact('classes', 'admissionType'));
    }

    private function normalizeAdmissionType(string $type): string
    {
        $type = strtolower(trim($type));
        $map = [
            'nursery' => 'Nursery',
            'primary' => 'Primary',
            'junior-secondary' => 'Junior Secondary',
            'junior_secondary' => 'Junior Secondary',
            'jss' => 'Junior Secondary',
            'secondary' => 'Senior Secondary',
            'senior-secondary' => 'Senior Secondary',
            'senior_secondary' => 'Senior Secondary',
            'sss' => 'Senior Secondary',
        ];
        return $map[$type] ?? 'General';
    }
    public function submitApplication(): void
    {
        verify_csrf();
        $required = ['first_name', 'last_name', 'gender', 'date_of_birth', 'state_of_origin', 'nationality', 'home_address', 'parent_name', 'parent_phone', 'parent_email', 'class_id'];
        foreach ($required as $field) {
            if (trim((string) ($_POST[$field] ?? '')) === '') {
                flash('danger', 'Please complete all required fields.');
                redirect('apply');
            }
        }

        if (!filter_var($_POST['parent_email'], FILTER_VALIDATE_EMAIL)) {
            flash('danger', 'Please enter a valid parent email address.');
            redirect('apply');
        }

        try {
            $imageTypes = [
                'image/jpeg' => 'jpg',
                'image/pjpeg' => 'jpg',
                'image/png' => 'png',
                'image/x-png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
            ];
            $docTypes = [
                'application/pdf' => 'pdf',
                'image/jpeg' => 'jpg',
                'image/pjpeg' => 'jpg',
                'image/png' => 'png',
                'image/x-png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            ];
            // Duplicate submission guard (e.g. accidental double-click / touch bounce within 5 minutes)
            $stmtDup = $this->db->prepare("SELECT id, application_number FROM applicants WHERE first_name = :fname AND last_name = :lname AND parent_phone = :phone AND class_id = :class_id AND created_at >= NOW() - INTERVAL 5 MINUTE ORDER BY id DESC LIMIT 1");
            $stmtDup->execute([
                ':fname' => trim($_POST['first_name']),
                ':lname' => trim($_POST['last_name']),
                ':phone' => trim($_POST['parent_phone']),
                ':class_id' => (int) $_POST['class_id'],
            ]);
            $existingApp = $stmtDup->fetch();
            if ($existingApp) {
                flash('info', 'Your application is already registered with Application Number: ' . $existingApp['application_number'] . '. Please complete payment to finalize.');
                redirect('payment/process.php?applicant_id=' . $existingApp['id']);
                return;
            }

            $applicationNumber = generate_application_number($this->db);

            $data = [
                'application_number' => $applicationNumber,
                'admission_type' => $this->normalizeAdmissionType($_POST['admission_type'] ?? ''),
                'first_name' => trim($_POST['first_name']),
                'middle_name' => trim($_POST['middle_name'] ?? ''),
                'last_name' => trim($_POST['last_name']),
                'gender' => $_POST['gender'],
                'date_of_birth' => $_POST['date_of_birth'],
                'state_of_origin' => trim($_POST['state_of_origin']),
                'local_government' => trim($_POST['local_government'] ?? ''),
                'nationality' => trim($_POST['nationality']),
                'religion' => trim($_POST['religion'] ?? ''),
                'home_address' => trim($_POST['home_address']),
                'parent_name' => trim($_POST['parent_name'] ?? $_POST['guardian_name'] ?? $_POST['father_name'] ?? $_POST['mother_name'] ?? ''),
                'parent_phone' => trim($_POST['parent_phone']),
                'parent_email' => trim($_POST['parent_email']),
                'father_name' => trim($_POST['father_name'] ?? ''),
                'mother_name' => trim($_POST['mother_name'] ?? ''),
                'guardian_name' => trim($_POST['guardian_name'] ?? ''),
                'parent_occupation' => trim($_POST['parent_occupation'] ?? ''),
                'class_id' => (int) $_POST['class_id'],
                'previous_school' => trim($_POST['previous_school'] ?? ''),
                'previous_class' => trim($_POST['previous_class'] ?? ''),
                'blood_group' => trim($_POST['blood_group'] ?? ''),
                'allergies' => trim($_POST['allergies'] ?? ''),
                'special_needs' => trim($_POST['special_needs'] ?? ''),
                'emergency_name' => trim($_POST['emergency_name'] ?? ''),
                'emergency_relationship' => trim($_POST['emergency_relationship'] ?? ''),
                'emergency_phone' => trim($_POST['emergency_phone'] ?? ''),
                'passport_photo' => upload_file('passport_photo', 'passports', $imageTypes),
                'birth_certificate' => upload_file('birth_certificate', 'birth_certificates', $docTypes),
                'previous_result' => upload_file('previous_result', 'results', $docTypes),
                'testimonial' => upload_file('testimonial', 'testimonials', $docTypes),
                'recommendation_letter' => upload_file('recommendation_letter', 'recommendations', $docTypes),
            ];

            $applicantId = (new Applicant($this->db))->create($data);
            $this->storeDocuments($applicantId, $data);
            $reference = 'PAY' . date('YmdHis') . random_int(1000, 9999);
            (new Payment($this->db))->create($applicantId, $reference, PaymentConfig::admissionFee(), PaymentConfig::defaultGateway(), 'admission_fee');

            send_sms_notice($data['parent_phone'], 'Application submitted. Number: ' . $applicationNumber);
            (new NotificationController($this->db))->sendApplicationConfirmation($applicantId);
            flash('success', 'Application submitted successfully. Your application number is ' . $applicationNumber . '. Please complete payment to finalize your application.');
            redirect('payment/process.php?applicant_id=' . $applicantId);
        } catch (Throwable $e) {
            flash('danger', $e->getMessage());
            redirect('apply');
        }
    }

    private function storeDocuments(int $applicantId, array $data): void
    {
        $documents = [
            'Passport Photograph' => $data['passport_photo'] ?? null,
            'Birth Certificate' => $data['birth_certificate'] ?? null,
            'Previous School Result' => $data['previous_result'] ?? null,
            'Testimonial' => $data['testimonial'] ?? null,
            'Recommendation Letter' => $data['recommendation_letter'] ?? null,
        ];
        foreach ($documents as $type => $path) {
            if ($path) {
                $stmt = $this->db->prepare('INSERT INTO documents (applicant_id, document_type, file_path, uploaded_at) VALUES (?, ?, ?, NOW())');
                $stmt->execute([$applicantId, $type, $path]);
            }
        }
    }

    public function track(): void
    {
        $applicant = null;
        $payment = null;
        $number = trim($_GET['number'] ?? $_POST['application_number'] ?? '');
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf();
        }
        if ($number !== '') {
            $applicant = (new Applicant($this->db))->findByNumber($number);
            if ($applicant) {
                $payment = (new Payment($this->db))->findForApplicant((int) $applicant['id'], 'admission_fee');
            }
            if (!$applicant) {
                flash('warning', 'No application was found with that number.');
            }
        }
        render('public/track', compact('applicant', 'payment', 'number'));
    }

    public function verifyPayment(): void
    {
        $reference = trim($_GET['reference'] ?? '');
        $number = trim($_GET['number'] ?? '');
        if ($reference !== '' && (new Payment($this->db))->verifyWithPaystack($reference)) {
            (new Payment($this->db))->markPaid($reference, 'paystack');
            flash('success', 'Payment verified successfully.');
        } else {
            flash('danger', 'Payment verification failed.');
        }
        redirect('track?number=' . urlencode($number));
    }

    public function attendanceScan(): void
    {
        $token = trim($_GET['token'] ?? '');

        if ($token === '') {
            $student = ['scan_status' => 'error', 'status' => '', 'time_in' => ''];
            require __DIR__ . '/../views/public/attendance_scan.php';
            exit;
        }

        // 1. Check if token belongs to a Staff member
        $stmtStaff = $this->db->prepare(
            "SELECT s.*, r.name AS role_title
             FROM staff s
             LEFT JOIN roles r ON r.id = s.role_id
             WHERE (s.qr_data = ? OR s.staff_id = ?)
             LIMIT 1"
        );
        $stmtStaff->execute([$token, $token]);
        $staffMember = $stmtStaff->fetch();

        if (!$staffMember && str_starts_with($token, 'ATTENDANCE-STF-')) {
            $parts = explode('-', $token);
            $staffId = isset($parts[2]) ? (int)$parts[2] : 0;
            if ($staffId > 0) {
                $stmtStaffLegacy = $this->db->prepare(
                    "SELECT s.*, r.name AS role_title FROM staff s LEFT JOIN roles r ON r.id = s.role_id WHERE s.id = ? LIMIT 1"
                );
                $stmtStaffLegacy->execute([$staffId]);
                $staffMember = $stmtStaffLegacy->fetch();
            }
        }

        if ($staffMember) {
            $today = date('Y-m-d');
            $nowTime = date('H:i');
            $staffId = (int)$staffMember['id'];
            $staffMember['is_staff'] = true;

            if ($staffMember['status'] !== 'Active') {
                $student = [
                    'scan_status' => 'error',
                    'first_name' => $staffMember['first_name'],
                    'last_name' => $staffMember['last_name'],
                    'error_message' => 'Staff account is currently inactive.'
                ];
                require __DIR__ . '/../views/public/attendance_scan.php';
                exit;
            }

            // Check if already checked in today
            $checkStaffAtt = $this->db->prepare(
                "SELECT id, status, time_in, time_out FROM staff_attendance WHERE staff_id = ? AND date = ? LIMIT 1"
            );
            $checkStaffAtt->execute([$staffId, $today]);
            $existingStaffAtt = $checkStaffAtt->fetch();

            if ($existingStaffAtt) {
                if (empty($existingStaffAtt['time_out'])) {
                    $timeInSeconds = !empty($existingStaffAtt['time_in']) ? strtotime($today . ' ' . $existingStaffAtt['time_in']) : (time() - 3600);
                    $diffMinutes = (int) round((time() - $timeInSeconds) / 60);

                    // If debounce window passed OR dismissal time, record checkout
                    if ($diffMinutes >= 15 || AttendanceRules::isDismissalTime()) {
                        $this->db->prepare(
                            "UPDATE staff_attendance SET time_out = ?, scan_method = 'qr_web' WHERE id = ?"
                        )->execute([date('H:i:s'), $existingStaffAtt['id']]);

                        $staffMember['scan_status'] = 'checkout';
                        $staffMember['status'] = 'Checked Out';
                        $staffMember['time_out'] = $nowTime;
                        $staffMember['time_in'] = $existingStaffAtt['time_in'];
                    } else {
                        $staffMember['scan_status'] = 'already';
                        $staffMember['status'] = $existingStaffAtt['status'];
                        $staffMember['time_in'] = $existingStaffAtt['time_in'];
                    }
                } else {
                    $staffMember['scan_status'] = 'already_out';
                    $staffMember['status'] = 'Checked Out';
                    $staffMember['time_out'] = $existingStaffAtt['time_out'];
                }
                $student = $staffMember;
            } else {
                // If scanned during dismissal time, record departure
                if (AttendanceRules::isDismissalTime()) {
                    $this->db->prepare(
                        "INSERT INTO staff_attendance (staff_id, school_id, date, time_in, time_out, status, scan_method, created_at)
                         VALUES (?, 1, ?, ?, ?, 'Present', 'qr_web', NOW())"
                    )->execute([$staffId, $today, date('H:i:s'), date('H:i:s')]);

                    $staffMember['scan_status'] = 'checkout';
                    $staffMember['status'] = 'Checked Out';
                    $staffMember['time_out'] = $nowTime;
                    $student = $staffMember;
                } else {
                    $resolvedStatus = AttendanceRules::resolveCurrentStatus();
                    if ($resolvedStatus === 'Denied') {
                        $staffMember['scan_status'] = 'denied';
                        $staffMember['status'] = 'Denied';
                        $staffMember['time_in'] = $nowTime;
                        $student = $staffMember;
                        require __DIR__ . '/../views/public/attendance_scan.php';
                        exit;
                    }

                    $this->db->prepare(
                        "INSERT INTO staff_attendance (staff_id, school_id, date, time_in, status, scan_method, created_at)
                         VALUES (?, 1, ?, ?, ?, 'qr_web', NOW())"
                    )->execute([$staffId, $today, date('H:i:s'), $resolvedStatus]);

                    $staffMember['scan_status'] = 'success';
                    $staffMember['status'] = $resolvedStatus;
                    $staffMember['time_in'] = $nowTime;
                    $student = $staffMember;
                }
            }

            require __DIR__ . '/../views/public/attendance_scan.php';
            exit;
        }

        // 2. Find student by qr_data token. Explicitly include a.school_id to bypass TenantPDO auto-scoping.
        $stmt = $this->db->prepare(
            "SELECT a.*, c.name AS class_name
             FROM applicants a
             LEFT JOIN classes c ON c.id = a.class_id
             WHERE a.school_id IS NOT NULL AND a.qr_data = ?"
        );
        $stmt->execute([$token]);
        $applicant = $stmt->fetch();

        if ($applicant) {
            SchoolContext::set((int)$applicant['school_id']);
        }

        if (!$applicant && str_starts_with($token, 'ATTENDANCE-STD-')) {
            $parts = explode('-', $token);
            $studentId = isset($parts[2]) ? (int)$parts[2] : 0;
            if ($studentId > 0) {
                $stmtLegacy = $this->db->prepare(
                    "SELECT a.*, c.name AS class_name
                     FROM applicants a
                     LEFT JOIN classes c ON c.id = a.class_id
                     WHERE a.school_id IS NOT NULL AND a.id = ?"
                );
                $stmtLegacy->execute([$studentId]);
                $applicant = $stmtLegacy->fetch();
                if ($applicant) {
                    SchoolContext::set((int)$applicant['school_id']);
                }
            }
        }

        if (!$applicant) {
            $student = ['scan_status' => 'error', 'status' => '', 'time_in' => ''];
            require __DIR__ . '/../views/public/attendance_scan.php';
            exit;
        }

        if ($applicant['status'] !== 'Enrolled') {
            $student = ['scan_status' => 'error', 'status' => '', 'time_in' => ''];
            require __DIR__ . '/../views/public/attendance_scan.php';
            exit;
        };

        $today       = date('Y-m-d');
        $nowTime     = date('H:i');
        $nowTimeFull = date('H:i:s');
        $applicantId = (int) $applicant['id'];
        $schoolId    = (int) ($applicant['school_id'] ?? 1);

        // Check if already marked today
        $checkStmt = $this->db->prepare(
            "SELECT id, status, time_in, time_out FROM attendance WHERE applicant_id = ? AND date = ? LIMIT 1"
        );
        $checkStmt->execute([$applicantId, $today]);
        $existing = $checkStmt->fetch();

        if ($existing) {
            if (empty($existing['time_out'])) {
                $timeInSeconds = !empty($existing['time_in']) ? strtotime($today . ' ' . $existing['time_in']) : (time() - 3600);
                $diffMinutes = (int) round((time() - $timeInSeconds) / 60);

                if ($diffMinutes >= 15 || AttendanceRules::isDismissalTime()) {
                    // Record Check-out
                    $this->db->prepare(
                        "UPDATE attendance SET time_out = ?, time_in = COALESCE(time_in, ?), scan_method = 'qr_web' WHERE id = ?"
                    )->execute([$nowTimeFull, $nowTimeFull, $existing['id']]);

                    // Insert exit log
                    $chkExit = $this->db->prepare("SELECT id FROM student_exit_logs WHERE student_id = ? AND exit_date = ? LIMIT 1");
                    $chkExit->execute([$applicantId, $today]);
                    if (!$chkExit->fetch()) {
                        $isEarly = (date('H:i') < AttendanceRules::getDismissalTime());
                        $exitType = $isEarly ? 'early' : 'normal';
                        $exitReason = $isEarly ? 'Early Departure' : 'Normal Dismissal';
                        $this->db->prepare(
                            "INSERT INTO student_exit_logs
                                (school_id, student_id, attendance_id, exit_type, exit_reason, exit_date, exit_time, exited_at, scan_method, qr_token, verification_status, sms_status, created_at)
                             VALUES
                                (?, ?, ?, ?, ?, ?, ?, NOW(), 'qr_camera', ?, 'verified', 'pending', NOW())"
                        )->execute([$schoolId, $applicantId, (int)$existing['id'], $exitType, $exitReason, $today, $nowTimeFull, $token]);
                    }

                    // Dispatch Check-out Notification
                    if ((int) setting('attendance_checkout_sms_enabled', 1) || (int) setting('attendance_checkout_email_enabled', 1)) {
                        $exitData = [
                            'exit_date'          => $today,
                            'exit_time'          => $nowTimeFull,
                            'exit_type'          => 'normal',
                            'exit_reason'        => 'School Departure',
                            'pickup_person_name' => 'Self / Guardian'
                        ];
                        AttendanceService::dispatchCheckoutNotification($applicant, $exitData, (int)$existing['id']);
                    }

                    $applicant['scan_status'] = 'checkout';
                    $applicant['status']      = 'Checked Out';
                    $applicant['time_out']    = $nowTime;
                    $applicant['time_in']     = $existing['time_in'];
                } else {
                    $applicant['scan_status'] = 'already';
                    $applicant['status']      = $existing['status'];
                    $applicant['time_in']     = $existing['time_in'];
                }
            } else {
                $applicant['scan_status'] = 'already_out';
                $applicant['status']      = 'Checked Out';
                $applicant['time_out']    = $existing['time_out'];
            }
            $student = $applicant;
        } else {
            // If scanned during dismissal period, record as dismissal check-out
            if (AttendanceRules::isDismissalTime()) {
                $ins = $this->db->prepare(
                    "INSERT INTO attendance (applicant_id, class_id, school_id, date, time_in, time_out, status, scan_method, alert_sent, timeout_alert_sent, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, 'Present', 'qr_web', 1, 0, NOW())"
                );
                $ins->execute([
                    $applicantId,
                    $applicant['class_id'] ?? null,
                    $schoolId,
                    $today,
                    $nowTimeFull,
                    $nowTimeFull,
                ]);
                $attendanceId = (int) $this->db->lastInsertId();

                $isEarly = (date('H:i') < AttendanceRules::getDismissalTime());
                $exitType = $isEarly ? 'early' : 'normal';
                $exitReason = $isEarly ? 'Early Departure' : 'Normal Dismissal';
                $this->db->prepare(
                    "INSERT INTO student_exit_logs
                        (school_id, student_id, attendance_id, exit_type, exit_reason, exit_date, exit_time, exited_at, scan_method, qr_token, verification_status, sms_status, created_at)
                     VALUES
                        (?, ?, ?, ?, ?, ?, ?, NOW(), 'qr_camera', ?, 'verified', 'pending', NOW())"
                )->execute([$schoolId, $applicantId, $attendanceId, $exitType, $exitReason, $today, $nowTimeFull, $token]);

                if ((int) setting('attendance_checkout_sms_enabled', 1) || (int) setting('attendance_checkout_email_enabled', 1)) {
                    $exitData = [
                        'exit_date'          => $today,
                        'exit_time'          => $nowTimeFull,
                        'exit_type'          => 'normal',
                        'exit_reason'        => 'School Dismissal',
                        'pickup_person_name' => 'Self / Guardian'
                    ];
                    AttendanceService::dispatchCheckoutNotification($applicant, $exitData, $attendanceId);
                }

                $applicant['scan_status'] = 'checkout';
                $applicant['status']      = 'Checked Out';
                $applicant['time_out']    = $nowTime;
                $student = $applicant;
            } else {
                // Determine status via time rules
                $resolvedStatus = AttendanceRules::resolveCurrentStatus();

                if ($resolvedStatus === 'Denied') {
                    $allowLateAfterClose = (bool) (int) setting('attendance_allow_late_after_close', 1);
                    if (!$allowLateAfterClose) {
                        $applicant['scan_status'] = 'denied';
                        $applicant['status']      = 'Denied';
                        $applicant['time_in']     = $nowTime;
                        $student = $applicant;
                        require __DIR__ . '/../views/public/attendance_scan.php';
                        exit;
                    }
                    $resolvedStatus = 'Late';
                }

                // Insert attendance record
                $ins = $this->db->prepare(
                    "INSERT INTO attendance (applicant_id, class_id, school_id, date, time_in, status, scan_method, alert_sent, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, 'qr_web', 0, NOW())"
                );
                $ins->execute([
                    $applicantId,
                    $applicant['class_id'] ?? null,
                    $schoolId,
                    $today,
                    $nowTimeFull,
                    $resolvedStatus,
                ]);
                $attendanceId = (int) $this->db->lastInsertId();

                $applicant['scan_status'] = 'success';
                $applicant['status']      = $resolvedStatus;
                $applicant['time_in']     = $nowTime;
                $student = $applicant;

                // ── Dispatch Attendance Notifications (SMS, Email, WhatsApp) ────────
                AttendanceService::dispatchCheckinNotification($applicant, $nowTimeFull, $resolvedStatus, $attendanceId);
            }
        }

        require __DIR__ . '/../views/public/attendance_scan.php';
        exit;
    }
}

