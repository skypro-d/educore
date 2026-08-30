<?php
// teacher/index.php
require_once __DIR__ . '/../controllers/TeacherController.php';

$route      = trim($_GET['route'] ?? 'dashboard', '/');
$route      = preg_replace('#^teacher/?#', '', $route);
$controller = new TeacherController();

// Parameterized / POST routes
if (preg_match('#^change-password$#', $route)) {
    $_SERVER['REQUEST_METHOD'] === 'POST'
        ? $controller->changePasswordSave()
        : $controller->changePasswordForm();
    exit;
}

if (preg_match('#^attendance/save$#', $route)) {
    $controller->attendanceSave();
    exit;
}

if (preg_match('#^attendance/scan-qr$#', $route)) {
    $controller->attendanceScanQRAjax();
    exit;
}

if (preg_match('#^results/save$#', $route)) {
    $controller->resultsSave();
    exit;
}

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
    case 'class-list':
        $controller->classList();
        break;
    case 'attendance':
        $controller->attendanceForm();
        break;
    case 'results':
        $controller->resultsForm();
        break;
    default:
        http_response_code(404);
        render('public/404');
}
