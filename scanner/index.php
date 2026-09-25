<?php
declare(strict_types=1);

// scanner/index.php — Dedicated EduCore Scanner Portal Router
require_once __DIR__ . '/../controllers/ScannerController.php';

$route      = trim($_GET['route'] ?? 'dashboard', '/');
$route      = preg_replace('#^scanner/?#', '', $route);
$controller = new ScannerController();

switch ($route) {
    case '':
    case 'dashboard':
        $controller->dashboard();
        break;

    case 'scan':
    case 'process-scan':
        $controller->processScanAjax();
        break;

    case 'logs':
    case 'today-logs':
        $controller->todayLogsAjax();
        break;

    case 'stats':
        $controller->statsAjax();
        break;

    case 'logout':
        $controller->logout();
        break;

    default:
        http_response_code(404);
        render('public/404');
}
