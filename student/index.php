<?php
require_once __DIR__ . '/../controllers/StudentController.php';

$route      = trim($_GET['route'] ?? 'dashboard', '/');
$route      = preg_replace('#^student/?#', '', $route);
$controller = new StudentController();

// Parameterized / POST routes
if (preg_match('#^reset$#', $route)) {
    $_SERVER['REQUEST_METHOD'] === 'POST'
        ? $controller->resetSave()
        : $controller->resetForm();
    exit;
}

if (preg_match('#^reset-request$#', $route)) {
    $_SERVER['REQUEST_METHOD'] === 'POST'
        ? $controller->resetSend()
        : $controller->resetRequest();
    exit;
}

if (preg_match('#^change-password$#', $route)) {
    $_SERVER['REQUEST_METHOD'] === 'POST'
        ? $controller->changePasswordSave()
        : $controller->changePasswordForm();
    exit;
}

switch ($route) {
    case 'login':
        $_SERVER['REQUEST_METHOD'] === 'POST'
            ? $controller->authenticate()
            : $controller->login();
        break;
    case 'reset-request':
        $_SERVER['REQUEST_METHOD'] === 'POST'
            ? $controller->resetSend()
            : $controller->resetRequest();
        break;
    case 'reset':
        $_SERVER['REQUEST_METHOD'] === 'POST'
            ? $controller->resetSave()
            : $controller->resetForm();
        break;
    case 'logout':
        $controller->logout();
        break;
    case 'dashboard':
        $controller->dashboard();
        break;
    case 'fees':
    case 'fee-schedule':
        if (!empty($_GET['receipt'])) {
            $controller->receipt();
        } else {
            $controller->fees();
        }
        break;
    case 'pay-fee':
        $controller->payFee();
        break;
    case 'payment-history':
        $controller->paymentHistory();
        break;
    case 'receipt':
        $controller->receipt();
        break;
    case 'timetable':
        $controller->timetable();
        break;
    case 'id-card':
        redirect('student/dashboard');
        break;
    case 'notifications':
        $controller->notifications();
        break;
    default:
        http_response_code(404);
        render('public/404');
}
