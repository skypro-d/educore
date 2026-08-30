<?php
require_once __DIR__ . '/controllers/PublicController.php';

$route = trim($_GET['route'] ?? 'home', '/');
$controller = new PublicController();

switch ($route) {
    case '':
    case 'home':
        $controller->home();
        break;
    case 'apply':
    case 'register':
    case 'admission/register':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->submitApplication() : $controller->apply();
        break;
    default:
        if (preg_match('#^(?:register|admission/register|apply)/([a-z0-9_-]+)$#i', $route, $m)) {
            $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->submitApplication() : $controller->apply($m[1]);
            break;
        }
        http_response_code(404);
        render('public/404');
        break;
    case 'register-school':
        $_SERVER['REQUEST_METHOD'] === 'POST' ? $controller->submitSchoolRegistration() : $controller->registerSchool();
        break;
    case 'track':
        $controller->track();
        break;
    case 'verify-payment':
        $controller->verifyPayment();
        break;
    case 'attendance/scan':
        $controller->attendanceScan();
        break;
}

