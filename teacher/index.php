<?php
declare(strict_types=1);

// teacher/index.php — Staff & Teacher Portal Router
require_once __DIR__ . '/../controllers/TeacherController.php';

$route      = trim($_GET['route'] ?? 'dashboard', '/');
$route      = preg_replace('#^teacher/?#', '', $route);
$controller = new TeacherController();

// ── Dynamic & Parameterized Routes ─────────────────────────────

// Class Detail: classes/5
if (preg_match('#^classes/(\d+)$#', $route, $m)) {
    $controller->classView((int) $m[1]);
    exit;
}

// Student Profile: students/12
if (preg_match('#^students/(\d+)$#', $route, $m)) {
    $controller->studentProfile((int) $m[1]);
    exit;
}

// Assignment Details & Submissions: assignments/4
if (preg_match('#^assignments/(\d+)$#', $route, $m)) {
    $controller->assignmentView((int) $m[1]);
    exit;
}

// Assignment Grade: assignments/4/grade
if (preg_match('#^assignments/(\d+)/grade$#', $route, $m)) {
    require_post();
    $controller->assignmentGrade((int) $m[1]);
    exit;
}

// Password Change
if (preg_match('#^change-password$#', $route)) {
    $_SERVER['REQUEST_METHOD'] === 'POST'
        ? $controller->changePasswordSave()
        : $controller->changePasswordForm();
    exit;
}

// Attendance Endpoints
if (preg_match('#^attendance/save$#', $route)) {
    require_post();
    $controller->attendanceSave();
    exit;
}

if (preg_match('#^attendance/scan-qr$#', $route)) {
    require_post();
    $controller->attendanceScanQRAjax();
    exit;
}

if (preg_match('#^attendance/report$#', $route)) {
    $controller->attendanceReport();
    exit;
}

if (preg_match('#^students/(\d+)/report-card$#', $route, $m)) {
    $controller->studentReportCard((int) $m[1]);
    exit;
}

// Results Endpoints
if (preg_match('#^results/print$#', $route)) {
    $controller->resultsPrint();
    exit;
}

if (preg_match('#^results/save$#', $route)) {
    require_post();
    $controller->resultsSave();
    exit;
}

if (preg_match('#^results/submit$#', $route)) {
    require_post();
    $controller->resultsSubmit();
    exit;
}

if (preg_match('#^results/approve$#', $route)) {
    require_post();
    $controller->resultsApprove();
    exit;
}

if (preg_match('#^results/publish$#', $route)) {
    require_post();
    $controller->resultsPublish();
    exit;
}

// Assignments Endpoints
if (preg_match('#^assignments/create$#', $route)) {
    $_SERVER['REQUEST_METHOD'] === 'POST'
        ? $controller->assignmentSave()
        : $controller->assignmentCreate();
    exit;
}

// Messaging Endpoints
if (preg_match('#^messages/send$#', $route)) {
    require_post();
    $controller->sendMessage();
    exit;
}

// Announcements Endpoints
if (preg_match('#^announcements/create$#', $route)) {
    require_post();
    $controller->createAnnouncement();
    exit;
}

// Profile Endpoints
if (preg_match('#^profile$#', $route)) {
    $_SERVER['REQUEST_METHOD'] === 'POST'
        ? $controller->profileSave()
        : $controller->profile();
    exit;
}

// ── Standard Exact Switch Routes ────────────────────────────────
switch ($route) {
    case 'login':
        $_SERVER['REQUEST_METHOD'] === 'POST'
            ? $controller->authenticate()
            : $controller->login();
        break;
    case 'logout':
        $controller->logout();
        break;
    case 'dashboard':
        $controller->dashboard();
        break;
    case 'classes':
    case 'class-list':
        $controller->classList();
        break;
    case 'students':
        $controller->students();
        break;
    case 'attendance':
        $controller->attendanceForm();
        break;
    case 'results':
        $controller->resultsForm();
        break;
    case 'assignments':
        $controller->assignmentsList();
        break;
    case 'timetable':
        $controller->timetable();
        break;
    case 'messages':
        $controller->messages();
        break;
    case 'announcements':
        $controller->announcements();
        break;
    default:
        http_response_code(404);
        render('public/404');
}
