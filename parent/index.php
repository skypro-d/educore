<?php
require_once __DIR__ . '/../controllers/ParentController.php';

$route      = trim($_GET['route'] ?? 'login', '/');
$route      = preg_replace('#^parent/?#', '', $route);
$controller = new ParentController();

// Dynamic routes
if (preg_match('#^reset$#', $route)) {
    $_SERVER['REQUEST_METHOD'] === 'POST'
        ? $controller->resetSave()
        : $controller->resetForm();
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
    case 'child':
        $controller->child();
        break;
    case 'attendance':
        $controller->attendance();
        break;
    case 'results':
        $controller->results();
        break;
    case 'fees':
        $controller->fees();
        break;
    case 'timetable':
        $controller->timetable();
        break;
    case 'id-card':
        $controller->idCard();
        break;
    case 'notifications':
        $controller->notifications();
        break;
    case 'announcements':
        $controller->announcements();
        break;
    case 'change-password':
        $_SERVER['REQUEST_METHOD'] === 'POST'
            ? $controller->changePasswordSave()
            : $controller->changePasswordForm();
        break;
    case 'reset-request':
        $_SERVER['REQUEST_METHOD'] === 'POST'
            ? $controller->resetSend()
            : $controller->resetRequest();
        break;
    default:
        http_response_code(404);
        render('public/404');
}
