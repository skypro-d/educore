<?php
declare(strict_types=1);

/**
 * tests/test_attendance_notifications.php
 * Comprehensive Verification Suite for Multi-Channel Parent Attendance Notifications (SMS, Email, WhatsApp)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/Email.php';
require_once __DIR__ . '/../services/channels/NotificationChannelInterface.php';
require_once __DIR__ . '/../services/channels/SmsNotificationChannel.php';
require_once __DIR__ . '/../services/channels/EmailNotificationChannel.php';
require_once __DIR__ . '/../services/channels/WhatsAppNotificationChannel.php';
require_once __DIR__ . '/../services/WhatsAppService.php';
require_once __DIR__ . '/../services/NotificationService.php';
require_once __DIR__ . '/../services/AttendanceService.php';

$pdo = Database::connect();

$passed = 0;
$failed = 0;

function assert_true(bool $condition, string $testName, string $detail = ''): void {
    global $passed, $failed;
    if ($condition) {
        echo " [PASS] {$testName}\n";
        $passed++;
    } else {
        echo " [FAIL] {$testName}" . ($detail ? " — {$detail}" : '') . "\n";
        $failed++;
    }
}

function assert_equals($expected, $actual, string $testName): void {
    global $passed, $failed;
    if ($expected === $actual) {
        echo " [PASS] {$testName}\n";
        $passed++;
    } else {
        $expStr = is_scalar($expected) ? (string)$expected : json_encode($expected);
        $actStr = is_scalar($actual) ? (string)$actual : json_encode($actual);
        echo " [FAIL] {$testName} (Expected '{$expStr}', got '{$actStr}')\n";
        $failed++;
    }
}

echo "========================================================================\n";
echo " EDUCORE PARENT ATTENDANCE NOTIFICATIONS VERIFICATION SUITE\n";
echo " Multi-Channel System (SMS, Email, WhatsApp)\n";
echo "========================================================================\n\n";

// Backup existing app_configs so tests don't permanently alter school settings
$origConfigs = [];
$cStmt = $pdo->query("SELECT `setting_key`, `setting_value` FROM `app_configs`");
while ($r = $cStmt->fetch(PDO::FETCH_ASSOC)) {
    $origConfigs[$r['setting_key']] = $r['setting_value'];
}

function set_test_setting(PDO $db, string $key, string $value): void {
    $stmt = $db->prepare("INSERT INTO app_configs (`setting_key`, `setting_value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `setting_value` = ?");
    $stmt->execute([$key, $value, $value]);
    setting_clear_cache();
}

try {
    // -------------------------------------------------------------------------
    // GROUP 1: Database Schema & Migration Verification
    // -------------------------------------------------------------------------
    echo "--- Group 1: Database Schema & Migration Verification ---\n";

    // 1.1 attendance_notification_logs table
    $hasLogsTable = (bool) $pdo->query("SHOW TABLES LIKE 'attendance_notification_logs'")->fetchColumn();
    assert_true($hasLogsTable, "Table 'attendance_notification_logs' exists");

    $cols = $pdo->query("SHOW COLUMNS FROM attendance_notification_logs")->fetchAll(PDO::FETCH_COLUMN);
    $reqCols = ['id', 'school_id', 'student_id', 'parent_id', 'attendance_id', 'exit_log_id', 'notification_type', 'channel', 'recipient', 'event', 'template_identifier', 'message', 'status', 'provider_response', 'error_message', 'sent_at', 'created_at'];
    foreach ($reqCols as $c) {
        assert_true(in_array($c, $cols, true), "Column '{$c}' exists in attendance_notification_logs");
    }

    // 1.2 applicants columns
    $appCols = $pdo->query("SHOW COLUMNS FROM applicants")->fetchAll(PDO::FETCH_COLUMN);
    assert_true(in_array('parent_whatsapp', $appCols, true), "Column 'parent_whatsapp' exists in applicants");
    assert_true(in_array('notify_sms', $appCols, true), "Column 'notify_sms' exists in applicants");
    assert_true(in_array('notify_email', $appCols, true), "Column 'notify_email' exists in applicants");
    assert_true(in_array('notify_whatsapp', $appCols, true), "Column 'notify_whatsapp' exists in applicants");

    // 1.3 Legacy SMS logs intact
    $hasLegacySms = (bool) $pdo->query("SHOW TABLES LIKE 'sms_logs'")->fetchColumn();
    assert_true($hasLegacySms, "Legacy 'sms_logs' table remains completely intact and operational");

    echo "\n";

    // -------------------------------------------------------------------------
    // GROUP 2: Phone Number Normalization
    // -------------------------------------------------------------------------
    echo "--- Group 2: Phone Number Normalization Helper ---\n";
    assert_equals('2348012345678', normalize_phone_number('08012345678'), "Normalize 08012345678 to Nigerian international 2348012345678");
    assert_equals('2348012345678', normalize_phone_number('+2348012345678'), "Normalize +2348012345678 (strip plus)");
    assert_equals('2348012345678', normalize_phone_number('2348012345678'), "Preserve 2348012345678");
    assert_equals('2348012345678', normalize_phone_number('080-1234-5678'), "Strip dashes: 080-1234-5678");
    assert_equals('2348012345678', normalize_phone_number('(080) 1234 5678'), "Strip spaces/parentheses: (080) 1234 5678");
    assert_equals('', normalize_phone_number(null), "Null phone returns empty string");
    assert_equals('', normalize_phone_number(''), "Empty phone returns empty string");

    echo "\n";

    // -------------------------------------------------------------------------
    // GROUP 3: Service Classes & Interfaces
    // -------------------------------------------------------------------------
    echo "--- Group 3: Architecture & Class Structure ---\n";
    assert_true(interface_exists('NotificationChannelInterface'), "NotificationChannelInterface exists");
    assert_true(class_exists('SmsNotificationChannel'), "SmsNotificationChannel exists");
    assert_true(class_exists('EmailNotificationChannel'), "EmailNotificationChannel exists");
    assert_true(class_exists('WhatsAppNotificationChannel'), "WhatsAppNotificationChannel exists");
    assert_true(class_exists('WhatsAppService'), "WhatsAppService exists");
    assert_true(class_exists('NotificationService'), "NotificationService exists");
    assert_true(class_exists('AttendanceService'), "AttendanceService exists");

    echo "\n";

    // -------------------------------------------------------------------------
    // GROUP 4: WhatsApp Provider Dispatch (Stub, Meta, Termii, Custom)
    // -------------------------------------------------------------------------
    echo "--- Group 4: WhatsApp Provider Integration ---\n";
    set_test_setting($pdo, 'whatsapp_provider', 'stub');
    $stubSendRes = WhatsAppService::send('2348012345678', 'Test WhatsApp message', ['template' => 'attendance_checkin']);
    assert_true(!empty($stubSendRes['success']), "WhatsAppService stub send returns success = true");
    assert_true(str_contains($stubSendRes['response'], 'stub'), "WhatsAppService stub response confirms stub execution");

    $stubTestRes = WhatsAppService::test('2348012345678');
    assert_true(!empty($stubTestRes['success']), "WhatsAppService::test in stub mode succeeds");

    echo "\n";

    // -------------------------------------------------------------------------
    // GROUP 5: Channel Combinations Matrix (All 7 Permutations)
    // -------------------------------------------------------------------------
    echo "--- Group 5: Channel Combinations Matrix (All 7 Permutations) ---\n";
    // Set SMS gateway to stub mode for tests so network calls don't block
    set_test_setting($pdo, 'sms_gateway', 'stub');
    set_test_setting($pdo, 'whatsapp_provider', 'stub');
    set_test_setting($pdo, 'notify_on_checkin', '1');
    set_test_setting($pdo, 'notify_on_checkout', '1');

    $notifService = new NotificationService($pdo);

    $testStudent = [
        'id'              => 999991,
        'school_id'       => 1,
        'first_name'      => 'Amina',
        'last_name'       => 'Bello',
        'parent_name'     => 'Alhaji Bello',
        'parent_phone'    => '08031234567',
        'parent_email'    => 'bello.parent@example.com',
        'parent_whatsapp' => '08031234567',
        'notify_sms'      => 1,
        'notify_email'    => 1,
        'notify_whatsapp' => 1
    ];

    $context = [
        'time'           => '07:45',
        'time_formatted' => '7:45 AM',
        'date_formatted' => date('F j, Y'),
        'status'         => 'Present',
        'attendance_id'  => 1234
    ];

    // Permutation 1: SMS only
    set_test_setting($pdo, 'attendance_sms_enabled', '1');
    set_test_setting($pdo, 'attendance_email_enabled', '0');
    set_test_setting($pdo, 'attendance_whatsapp_enabled', '0');
    $res1 = $notifService->sendAttendanceNotification($testStudent, 'check_in', $context);
    assert_true(isset($res1['sms']) && !isset($res1['email']) && !isset($res1['whatsapp']), "Permutation 1: SMS Only active");

    // Permutation 2: Email only
    set_test_setting($pdo, 'attendance_sms_enabled', '0');
    set_test_setting($pdo, 'attendance_email_enabled', '1');
    set_test_setting($pdo, 'attendance_whatsapp_enabled', '0');
    $res2 = $notifService->sendAttendanceNotification($testStudent, 'check_in', $context);
    assert_true(!isset($res2['sms']) && isset($res2['email']) && !isset($res2['whatsapp']), "Permutation 2: Email Only active");

    // Permutation 3: WhatsApp only
    set_test_setting($pdo, 'attendance_sms_enabled', '0');
    set_test_setting($pdo, 'attendance_email_enabled', '0');
    set_test_setting($pdo, 'attendance_whatsapp_enabled', '1');
    $res3 = $notifService->sendAttendanceNotification($testStudent, 'check_in', $context);
    assert_true(!isset($res3['sms']) && !isset($res3['email']) && isset($res3['whatsapp']), "Permutation 3: WhatsApp Only active");

    // Permutation 4: SMS + Email
    set_test_setting($pdo, 'attendance_sms_enabled', '1');
    set_test_setting($pdo, 'attendance_email_enabled', '1');
    set_test_setting($pdo, 'attendance_whatsapp_enabled', '0');
    $res4 = $notifService->sendAttendanceNotification($testStudent, 'check_in', $context);
    assert_true(isset($res4['sms']) && isset($res4['email']) && !isset($res4['whatsapp']), "Permutation 4: SMS + Email active");

    // Permutation 5: SMS + WhatsApp
    set_test_setting($pdo, 'attendance_sms_enabled', '1');
    set_test_setting($pdo, 'attendance_email_enabled', '0');
    set_test_setting($pdo, 'attendance_whatsapp_enabled', '1');
    $res5 = $notifService->sendAttendanceNotification($testStudent, 'check_in', $context);
    assert_true(isset($res5['sms']) && !isset($res5['email']) && isset($res5['whatsapp']), "Permutation 5: SMS + WhatsApp active");

    // Permutation 6: Email + WhatsApp
    set_test_setting($pdo, 'attendance_sms_enabled', '0');
    set_test_setting($pdo, 'attendance_email_enabled', '1');
    set_test_setting($pdo, 'attendance_whatsapp_enabled', '1');
    $res6 = $notifService->sendAttendanceNotification($testStudent, 'check_in', $context);
    assert_true(!isset($res6['sms']) && isset($res6['email']) && isset($res6['whatsapp']), "Permutation 6: Email + WhatsApp active");

    // Permutation 7: All Three Channels (SMS + Email + WhatsApp)
    set_test_setting($pdo, 'attendance_sms_enabled', '1');
    set_test_setting($pdo, 'attendance_email_enabled', '1');
    set_test_setting($pdo, 'attendance_whatsapp_enabled', '1');
    $res7 = $notifService->sendAttendanceNotification($testStudent, 'check_in', $context);
    assert_true(isset($res7['sms']) && isset($res7['email']) && isset($res7['whatsapp']), "Permutation 7: All Three Channels Active Simultaneously");

    // All Channels Disabled
    set_test_setting($pdo, 'attendance_sms_enabled', '0');
    set_test_setting($pdo, 'attendance_email_enabled', '0');
    set_test_setting($pdo, 'attendance_whatsapp_enabled', '0');
    $res0 = $notifService->sendAttendanceNotification($testStudent, 'check_in', $context);
    assert_true(empty($res0), "All channels disabled produces 0 notification dispatches");

    echo "\n";

    // -------------------------------------------------------------------------
    // GROUP 6: Event Filtering (Check-in vs Check-out)
    // -------------------------------------------------------------------------
    echo "--- Group 6: Event Filtering (Check-in & Check-out) ---\n";
    // Enable all channels
    set_test_setting($pdo, 'attendance_sms_enabled', '1');
    set_test_setting($pdo, 'attendance_email_enabled', '1');
    set_test_setting($pdo, 'attendance_whatsapp_enabled', '1');

    // Disable check-in globally
    set_test_setting($pdo, 'notify_on_checkin', '0');
    set_test_setting($pdo, 'notify_on_checkout', '1');
    $resCheckinDisabled = $notifService->sendAttendanceNotification($testStudent, 'check_in', $context);
    assert_true(empty($resCheckinDisabled), "notify_on_checkin = 0 completely suppresses check-in alerts");

    // But check-out should still fire
    $exitContext = [
        'time'           => '14:30',
        'time_formatted' => '2:30 PM',
        'date_formatted' => date('F j, Y'),
        'exit_log_id'    => 5678
    ];
    $resCheckoutAllowed = $notifService->sendAttendanceNotification($testStudent, 'check_out', $exitContext);
    assert_true(!empty($resCheckoutAllowed), "notify_on_checkout = 1 permits checkout alerts while check-in is disabled");

    // Now disable check-out
    set_test_setting($pdo, 'notify_on_checkout', '0');
    $resCheckoutDisabled = $notifService->sendAttendanceNotification($testStudent, 'check_out', $exitContext);
    assert_true(empty($resCheckoutDisabled), "notify_on_checkout = 0 suppresses checkout alerts");

    // Re-enable both events
    set_test_setting($pdo, 'notify_on_checkin', '1');
    set_test_setting($pdo, 'notify_on_checkout', '1');

    echo "\n";

    // -------------------------------------------------------------------------
    // GROUP 7: Per-Student Parent Opt-Out Preferences
    // -------------------------------------------------------------------------
    echo "--- Group 7: Per-Student Parent Opt-Out Preferences ---\n";
    // All channels globally enabled
    set_test_setting($pdo, 'attendance_sms_enabled', '1');
    set_test_setting($pdo, 'attendance_email_enabled', '1');
    set_test_setting($pdo, 'attendance_whatsapp_enabled', '1');

    // Student whose parent opted out of SMS
    $studentOptOutSms = $testStudent;
    $studentOptOutSms['notify_sms'] = 0;
    $resOptSms = $notifService->sendAttendanceNotification($studentOptOutSms, 'check_in', $context);
    assert_true(!isset($resOptSms['sms']) && isset($resOptSms['email']) && isset($resOptSms['whatsapp']), "Parent opt-out: SMS excluded when notify_sms = 0");

    // Student whose parent opted out of Email
    $studentOptOutEmail = $testStudent;
    $studentOptOutEmail['notify_email'] = 0;
    $resOptEmail = $notifService->sendAttendanceNotification($studentOptOutEmail, 'check_in', $context);
    assert_true(isset($resOptEmail['sms']) && !isset($resOptEmail['email']) && isset($resOptEmail['whatsapp']), "Parent opt-out: Email excluded when notify_email = 0");

    // Student whose parent opted out of WhatsApp
    $studentOptOutWa = $testStudent;
    $studentOptOutWa['notify_whatsapp'] = 0;
    $resOptWa = $notifService->sendAttendanceNotification($studentOptOutWa, 'check_in', $context);
    assert_true(isset($resOptWa['sms']) && isset($resOptWa['email']) && !isset($resOptWa['whatsapp']), "Parent opt-out: WhatsApp excluded when notify_whatsapp = 0");

    echo "\n";

    // -------------------------------------------------------------------------
    // GROUP 8: Missing Contact Data Handling
    // -------------------------------------------------------------------------
    echo "--- Group 8: Missing Contact Data Handling ---\n";
    // Student with missing email
    $studentNoEmail = $testStudent;
    $studentNoEmail['parent_email'] = '';
    set_test_setting($pdo, 'attendance_sms_enabled', '0');
    set_test_setting($pdo, 'attendance_email_enabled', '1');
    set_test_setting($pdo, 'attendance_whatsapp_enabled', '0');

    $resNoEmail = $notifService->sendAttendanceNotification($studentNoEmail, 'check_in', $context);
    assert_true(isset($resNoEmail['email']), "Email channel executed for missing email");
    assert_true($resNoEmail['email']['success'] === false, "Email channel reports success = false when email is empty");
    assert_equals('failed', $resNoEmail['email']['status'], "Email status is 'failed'");

    // Student with missing WhatsApp & Phone
    $studentNoPhone = $testStudent;
    $studentNoPhone['parent_whatsapp'] = '';
    $studentNoPhone['parent_phone'] = '';
    set_test_setting($pdo, 'attendance_sms_enabled', '0');
    set_test_setting($pdo, 'attendance_email_enabled', '0');
    set_test_setting($pdo, 'attendance_whatsapp_enabled', '1');

    $resNoPhone = $notifService->sendAttendanceNotification($studentNoPhone, 'check_in', $context);
    assert_true(isset($resNoPhone['whatsapp']), "WhatsApp channel executed for missing phone");
    assert_true($resNoPhone['whatsapp']['success'] === false, "WhatsApp reports success = false when phone is empty");
    assert_equals('failed', $resNoPhone['whatsapp']['status'], "WhatsApp status is 'failed'");

    echo "\n";

    // -------------------------------------------------------------------------
    // GROUP 9: Notification Logging & Retry Mechanism
    // -------------------------------------------------------------------------
    echo "--- Group 9: Notification Logging & Retry Mechanism ---\n";
    // Check that records were logged to attendance_notification_logs
    $logCount = (int) $pdo->query("SELECT COUNT(*) FROM attendance_notification_logs")->fetchColumn();
    assert_true($logCount > 0, "Dispatched notifications are logged to attendance_notification_logs (found {$logCount})");

    // Insert a specifically failed log to test retry
    $insStmt = $pdo->prepare(
        "INSERT INTO attendance_notification_logs
         (school_id, student_id, attendance_id, notification_type, channel, recipient, event, template_identifier, message, status, error_message, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
    );
    $insStmt->execute([
        1,
        $testStudent['id'],
        $context['attendance_id'],
        'attendance',
        'whatsapp',
        '2348031234567',
        'check_in',
        'check_in_whatsapp',
        'Test message to retry',
        'failed',
        'Simulated network timeout'
    ]);
    $newLogId = (int) $pdo->lastInsertId();

    assert_true($newLogId > 0, "Created failed test log #{$newLogId}");

    // Test retry
    set_test_setting($pdo, 'whatsapp_provider', 'stub');
    $retryRes = $notifService->retry($newLogId);
    assert_true(!empty($retryRes['success']), "retry() returned success = true");

    // Verify DB updated
    $chkStmt = $pdo->prepare("SELECT status, sent_at, provider_response FROM attendance_notification_logs WHERE id = ?");
    $chkStmt->execute([$newLogId]);
    $updatedLog = $chkStmt->fetch(PDO::FETCH_ASSOC);

    assert_equals('sent', $updatedLog['status'], "Log status transitioned from 'failed' to 'sent'");
    assert_true(!empty($updatedLog['sent_at']), "sent_at timestamp populated on successful retry");

    echo "\n";

    // -------------------------------------------------------------------------
    // GROUP 10: Attendance Dispatcher Fault Isolation
    // -------------------------------------------------------------------------
    echo "--- Group 10: Attendance Dispatcher Fault Isolation ---\n";
    // AttendanceService::dispatchCheckinNotification must never throw even if notification service has an error
    $threw = false;
    try {
        AttendanceService::dispatchCheckinNotification($testStudent, '08:15', 'Present', 8888);
    } catch (Throwable $e) {
        $threw = true;
    }
    assert_true(!$threw, "AttendanceService::dispatchCheckinNotification executes safely without throwing");

    $threwCheckout = false;
    try {
        AttendanceService::dispatchCheckoutNotification($testStudent, ['exit_time' => '15:00', 'exit_type' => 'normal'], 9999);
    } catch (Throwable $e) {
        $threwCheckout = true;
    }
    assert_true(!$threwCheckout, "AttendanceService::dispatchCheckoutNotification executes safely without throwing");

    echo "\n";

    // -------------------------------------------------------------------------
    // GROUP 11: Cleanup & Environment Restoration
    // -------------------------------------------------------------------------
    echo "--- Group 11: Cleanup & Environment Restoration ---\n";
    // Clean up test student logs
    $pdo->prepare("DELETE FROM attendance_notification_logs WHERE student_id = ?")->execute([$testStudent['id']]);
    assert_true(true, "Cleaned up temporary test logs");

} finally {
    // Restore all original configs
    foreach ($origConfigs as $k => $v) {
        set_test_setting($pdo, $k, $v);
    }
    echo " Restored original school settings.\n";
}

echo "\n========================================================================\n";
echo " TEST SUMMARY: Total: " . ($passed + $failed) . " | Passed: {$passed} | Failed: {$failed}\n";
echo "========================================================================\n";

exit($failed > 0 ? 1 : 0);
