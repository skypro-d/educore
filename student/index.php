<?php
require_once __DIR__ . '/../controllers/StudentController.php';

$route      = trim($_GET['route'] ?? 'dashboard', '/');
$route      = preg_replace('#^student/?#', '', $route);
$controller = new StudentController();

// Parameterized / POST routes
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
    case 'logout':
        $controller->logout();
        break;
    case 'dashboard':
        $controller->dashboard();
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
    default:
        http_response_code(404);
        render('public/404');
}
