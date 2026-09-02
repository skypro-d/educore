<?php
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../controllers/AcademicController.php';
require_once __DIR__ . '/../controllers/AttendanceController.php';

$route = trim($_GET['route'] ?? 'dashboard', '/');
$route = preg_replace('#^admin/?#', '', $route);
$controller = new AdminController();
$academic = new AcademicController();
$attendance = new AttendanceController();

// Dynamic / Parameterized routes
if (preg_match('#^applications/(\d+)$#', $route, $m)) {
    $controller->showApplication((int) $m[1]);
    exit;
}
if (preg_match('#^applications/(\d+)/id-card$#', $route, $m)) {
    $controller->idCard((int) $m[1]);
    exit;
}
if (preg_match('#^applications/(\d+)/(approve|reject|terminate)$#', $route, $m)) {
    $statusMap = ['approve' => 'Approved', 'reject' => 'Rejected', 'terminate' => 'Terminated'];
    $controller->updateStatus((int) $m[1], $statusMap[$m[2]]);
    exit;
}
if (preg_match('#^applications/(\d+)/(review|exam|exam-completed|interview|enroll)$#', $route, $m)) {
    $statusMap = ['review' => 'Under Review', 'exam' => 'Awaiting Exam', 'exam-completed' => 'Exam Completed', 'interview' => 'Interview Scheduled', 'enroll' => 'Enrolled'];
    $controller->updateStatus((int) $m[1], $statusMap[$m[2]]);
    exit;
}
if (preg_match('#^classes/(\d+)/delete$#', $route, $m)) {
    require_post();
    $controller->deleteClass((int) $m[1]);
    exit;
}
if (preg_match('#^letter/(\d+)$#', $route, $m)) {
    $controller->letter((int) $m[1]);
    exit;
}
if (preg_match('#^fee-structures/(\d+)/delete$#', $route, $m)) {
    require_post();
    $controller->deleteFeeStructure((int) $m[1]);
    exit;
}
if (preg_match('#^staff/(\d+)/delete$#', $route, $m)) {
    require_post();
    $controller->deleteStaff((int) $m[1]);
    exit;
}
if (preg_match('#^staff/(\d+)/toggle-status$#', $route, $m)) {
    require_post();
    $controller->toggleStaffStatus((int) $m[1]);
    exit;
}
if (preg_match('#^staff/(\d+)/reset-password$#', $route, $m)) {
    require_post();
    $controller->resetStaffPassword((int) $m[1]);
    exit;
}
if (preg_match('#^staff/(\d+)/assignments$#', $route, $m)) {
    $_SERVER['REQUEST_METHOD'] === 'POST'
        ? $controller->saveStaffAssignments((int) $m[1])
        : $controller->staffAssignments((int) $m[1]);
    exit;
}
if (preg_match('#^staff/(\d+)/permissions$#', $route, $m)) {
    $_SERVER['REQUEST_METHOD'] === 'POST'
        ? $controller->saveStaffPermissions((int) $m[1])
        : $controller->staffPermissions((int) $m[1]);
    exit;
}
if (preg_match('#^staff/(\d+)/activity$#', $route, $m)) {
    $controller->staffActivity((int) $m[1]);
    exit;
}
if (preg_match('#^staff/(\d+)/students$#', $route, $m)) {
    $controller->staffStudents((int) $m[1]);
    exit;
}
if (preg_match('#^subjects/(\d+)/delete$#', $route, $m)) {
    require_post();
    $academic->deleteSubject((int) $m[1]);
    exit;
}
if (preg_match('#^result-sheet/(\d+)$#', $route, $m)) {
    $academic->resultSheet((int) $m[1]);
    exit;
}
if (preg_match('#^attendance-settings/sms-log/(\d+)/delete$#', $route, $m)) {
    require_post();
    $controller->deleteSmsLog((int) $m[1]);
    exit;
}
if (preg_match('#^devices/status/(\d+)$#', $route, $m)) {
    require_post();
    $controller->toggleDeviceStatus((int) $m[1]);
    exit;
}
if (preg_match('#^devices/reset-token/(\d+)$#', $route, $m)) {
    require_post();
    $controller->resetDeviceToken((int) $m[1]);
    exit;
}
if (preg_match('#^devices/delete/(\d+)$#', $route, $m)) {
    require_post();
    $controller->deleteDevice((int) $m[1]);
    exit;
}
if (preg_match('#^library/delete/(\d+)$#', $route, $m)) {
    require_post();
    $controller->deleteBook((int) $m[1]);
    exit;
}
if (preg_match('#^transport/delete/(\d+)$#', $route, $m)) {
    require_post();
    $controller->deleteRoute((int) $m[1]);
    exit;
}
if (preg_match('#^inventory/delete/(\d+)$#', $route, $m)) {
    require_post();
    $controller->deleteAsset((int) $m[1]);
    exit;
}
if (preg_match('#^gates/(\d+)/delete$#', $route, $m)) {
    require_post();
    $controller->deleteGate((int) $m[1]);
    exit;
}
if (preg_match('#^authorized-pickups/(\d+)/delete$#', $route, $m)) {
    require_post();
    $controller->deleteAuthorizedPickup((int) $m[1]);
    exit;
}

// Exact Switch routes
switch ($route) {
    case 'login':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->authenticate() : $controller->login();
        break;
    case 'dashboard':
        $controller->dashboard();
        break;
    case 'applications':
        $controller->applications();
        break;
    case 'classes':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->saveClass() : $controller->classes();
        break;
    case 'settings':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->saveSettings() : $controller->settings();
        break;
    case 'settings/test-smtp':
        $controller->testSmtp();
        break;
    case 'settings/save-id-card-color':
        $controller->saveIdCardColor();
        break;
    case 'form-builder':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->saveFormBuilder() : $controller->formBuilder();
        break;
    case 'payments':
        $controller->payments();
        break;
    case 'payments/approve':
        $controller->approvePayment();
        break;
    case 'payments/reject':
        $controller->rejectPayment();
        break;
    case 'reports':
        $controller->reports();
        break;
    case 'exams':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->saveExamQuestion() : $controller->exams();
        break;
    case 'interviews':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->saveInterview() : $controller->interviews();
        break;
    case 'roles':
        $controller->roles();
        break;
    case 'export':
        $controller->exportCsv();
        break;
    case 'logout':
        $controller->logout();
        break;

    // School Fee Management
    case 'fee-structures':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->saveFeeStructure() : $controller->feeStructures();
        break;
    case 'student-fees':
        $controller->studentFees();
        break;
    case 'student-fees/pay-balance':
        $controller->saveBalancePayment();
        break;

    // Attendance Management
    case 'attendance':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $attendance->save() : $attendance->index();
        break;
    case 'attendance-report':
        $attendance->report();
        break;
    case 'attendance-settings':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->saveAttendanceSettings() : $controller->attendanceSettings();
        break;
    case 'attendance-settings/test-sms':
        $controller->testSms();
        break;
    case 'attendance-settings/run-auto-absent':
        $attendance->runAutoAbsent();
        break;
    case 'devices':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->saveDevice() : $controller->devices();
        break;

    // Student Exit & Gate Verification
    case 'exit-scanner':
        $controller->exitScanner();
        break;
    case 'exit-scanner/scan':
        $controller->exitScanAjax();
        break;
    case 'exit-scanner/confirm':
        $controller->exitConfirmAjax();
        break;
    case 'exit-scanner/student-lookup':
        $controller->studentLookupAjax();
        break;
    case 'exit-scanner/manual':
        $controller->exitManualAjax();
        break;
    case 'exit-logs':
        $controller->exitLogs();
        break;
    case 'exit-logs/export':
        $controller->exportExitLogsCsv();
        break;
    case 'exit-logs/sms-retry':
        $controller->retryExitSms();
        break;
    case 'gates':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->saveGate() : $controller->gates();
        break;
    case 'authorized-pickups':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->saveAuthorizedPickup() : $controller->authorizedPickups();
        break;

    // Academic / Results
    case 'subjects':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $academic->saveSubject() : $academic->subjects();
        break;
    case 'class-subjects':
        $academic->classSubjects();
        break;
    case 'class-subjects/save':
        require_post();
        $academic->saveClassSubjects();
        break;
    case 'assessment-components':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $academic->saveAssessmentComponents() : $academic->assessmentComponents();
        break;
    case 'grading-rules':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $academic->saveGradingRules() : $academic->gradingRules();
        break;
    case 'results':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $academic->enterResults() : $academic->results();
        break;
    case 'result-sheet/save-remark':
        $academic->saveTermRemark();
        break;

    // Promotion
    case 'promotion':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->promoteStudents() : $controller->promotion();
        break;

    // Communications
    case 'communications':
        $controller->communications();
        break;
    case 'announcements':
        $controller->saveAnnouncement();
        break;
    case 'send-sms':
        $controller->sendBulkSms();
        break;

    // Staff
    case 'staff':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->saveStaff() : $controller->staff();
        break;

    // Secondary Modules
    case 'library':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->saveBook() : $controller->library();
        break;
    case 'transport':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->saveRoute() : $controller->transport();
        break;
    case 'inventory':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->saveAsset() : $controller->inventory();
        break;

    // Software Updates
    case 'updates':
        $controller->updates();
        break;

    default:
        http_response_code(404);
        render('public/404');
}

