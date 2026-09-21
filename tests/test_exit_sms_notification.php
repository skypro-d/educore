<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/SmsService.php';
require_once __DIR__ . '/../services/NotificationService.php';
require_once __DIR__ . '/../services/AttendanceService.php';

echo "========================================================\n";
echo "Testing Exit SMS Notification & buildExitMessage\n";
echo "========================================================\n\n";

$db = Database::connect();

// 1. Test SmsService::buildExitMessage with array argument (new syntax)
echo "--- 1. Testing buildExitMessage with array argument ---\n";
$exitArray = [
    'exit_type'          => 'normal',
    'exit_date'          => '2026-09-21',
    'exit_time'          => '15:45:00',
    'exit_reason'        => 'School Dismissal',
    'pickup_person_name' => 'John Doe (Father)',
    'class_name'         => 'Primary 2'
];
$msg1 = SmsService::buildExitMessage('eyitayo Azzan', 'EduCore Academy', $exitArray);
echo "Message 1:\n{$msg1}\n\n";
if (empty($msg1)) {
    echo "[FAIL] Message 1 is empty\n";
    exit(1);
}
echo "[OK] buildExitMessage with array argument passed!\n\n";

// 2. Test SmsService::buildExitMessage with individual arguments (legacy syntax)
echo "--- 2. Testing buildExitMessage with individual string arguments ---\n";
$msg2 = SmsService::buildExitMessage('eyitayo Azzan', 'EduCore Academy', 'Mon, Sep 21 2026', '3:45 PM', 'early', 'Doctor Appointment', 'Jane Doe', 'Primary 2');
echo "Message 2:\n{$msg2}\n\n";
if (empty($msg2)) {
    echo "[FAIL] Message 2 is empty\n";
    exit(1);
}
echo "[OK] buildExitMessage with string arguments passed!\n\n";

// 3. Test NotificationService sending checkout event
echo "--- 3. Testing NotificationService sendAttendanceNotification on check_out ---\n";
$student = [
    'id'              => 1,
    'first_name'      => 'Eyitayo',
    'last_name'       => 'Azzan',
    'parent_name'     => 'Mr. Azzan',
    'parent_phone'    => '08012345678',
    'parent_email'    => 'parent@example.com',
    'notify_sms'      => 1,
    'notify_email'    => 1,
    'notify_whatsapp' => 0,
    'class_name'      => 'Primary 2'
];

$context = [
    'time'                => '15:45:00',
    'time_formatted'      => '3:45 PM',
    'date_formatted'      => 'September 21, 2026',
    'status'              => 'Checked Out',
    'exit_log_id'         => 9999,
    'exit_data'           => $exitArray,
    'template_identifier' => 'check_out'
];

$notifService = new NotificationService($db);
$results = $notifService->sendAttendanceNotification($student, 'check_out', $context);
echo "Notification results: " . json_encode($results, JSON_PRETTY_PRINT) . "\n\n";

if (isset($results['sms']) && $results['sms']['error'] && str_contains($results['sms']['error'], 'must be of type string')) {
    echo "[FAIL] TypeError still occurred!\n";
    exit(1);
}

echo "========================================================\n";
echo "ALL EXIT SMS TESTS PASSED PERFECTLY! ✓\n";
echo "========================================================\n";
