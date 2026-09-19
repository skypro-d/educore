<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/SchoolContext.php';
require_once __DIR__ . '/../config/AttendanceRules.php';
require_once __DIR__ . '/../services/AttendanceService.php';
require_once __DIR__ . '/../controllers/AttendanceController.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$_SESSION['admin'] = [
    'id'        => 1,
    'school_id' => 1,
    'username'  => 'test_admin',
    'role'      => 'superadmin'
];
SchoolContext::set(1);

$qrData = $argv[1] ?? '';
$_POST['qr_data'] = $qrData;

$ctrl = new AttendanceController();
$ctrl->processScanAjax();
