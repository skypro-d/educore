<?php
declare(strict_types=1);

require_once __DIR__ . '/NotificationChannelInterface.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/SmsService.php';

final class SmsNotificationChannel implements NotificationChannelInterface
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function getName(): string
    {
        return 'sms';
    }

    public function isEnabled(): bool
    {
        return setting('attendance_sms_enabled', '1') === '1';
    }

    public function isParentOptedIn(array $student): bool
    {
        if (isset($student['notify_sms'])) {
            return (int) $student['notify_sms'] === 1;
        }
        return true;
    }

    public function send(array $student, string $event, array $context): array
    {
        $phone = trim($student['parent_phone'] ?? '');
        if ($phone === '') {
            return [
                'success'   => false,
                'status'    => 'failed',
                'recipient' => '',
                'message'   => '',
                'response'  => null,
                'error'     => 'Parent phone number not provided'
            ];
        }

        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        $schoolName  = setting('school_name', APP_NAME);

        if ($event === 'check_in') {
            if (setting('checkin_sms_enabled', '1') === '0') {
                return [
                    'success'   => true,
                    'status'    => 'skipped',
                    'recipient' => $phone,
                    'message'   => 'Check-in SMS is globally disabled in school settings',
                    'response'  => 'skipped',
                    'error'     => null
                ];
            }

            $timeIn       = $context['time'] ?? date('H:i');
            $status       = $context['status'] ?? 'Present';
            $attendanceId = (int) ($context['attendance_id'] ?? 0);
            $dateStr      = $context['date_formatted'] ?? date('D, M j Y');
            $timeFmt      = date('g:i A', strtotime($timeIn));

            $message = SmsService::buildCheckinMessage($studentName, $schoolName, $dateStr, $timeFmt, $status);
            $res = SmsService::send($phone, $message, 'checkin', $attendanceId > 0 ? $attendanceId : null);

            if ($attendanceId > 0 && $res['success']) {
                try {
                    $this->db->prepare("UPDATE attendance SET alert_sent = 1 WHERE id = ?")->execute([$attendanceId]);
                } catch (Throwable $e) {}
            }

            return [
                'success'   => (bool) ($res['success'] ?? false),
                'status'    => !empty($res['success']) ? 'sent' : 'failed',
                'recipient' => $phone,
                'message'   => $message,
                'response'  => $res['response'] ?? '',
                'error'     => !empty($res['success']) ? null : ($res['response'] ?? 'SMS dispatch failed')
            ];
        }

        if ($event === 'check_out') {
            if (setting('exit_sms_enabled', '1') === '0') {
                return [
                    'success'   => true,
                    'status'    => 'skipped',
                    'recipient' => $phone,
                    'message'   => 'Exit SMS is globally disabled in school settings',
                    'response'  => 'skipped',
                    'error'     => null
                ];
            }

            $exitData  = $context['exit_data'] ?? [];
            $exitLogId = (int) ($context['exit_log_id'] ?? 0);
            $exitType  = $exitData['exit_type'] ?? 'normal';

            if ($exitType === 'early' && setting('early_exit_sms_enabled', '1') === '0') {
                return [
                    'success'   => true,
                    'status'    => 'skipped',
                    'recipient' => $phone,
                    'message'   => 'Early exit SMS is disabled in school settings',
                    'response'  => 'skipped',
                    'error'     => null
                ];
            }

            $dateStr      = $context['date_formatted'] ?? (!empty($exitData['exit_date']) ? date('D, M j Y', strtotime($exitData['exit_date'])) : date('D, M j Y'));
            $timeFmt      = $context['time_formatted'] ?? (!empty($exitData['exit_time']) ? date('g:i A', strtotime($exitData['exit_time'])) : (!empty($context['time']) ? date('g:i A', strtotime($context['time'])) : date('g:i A')));
            $reason       = $exitData['exit_reason'] ?? null;
            $pickupPerson = $exitData['pickup_person_name'] ?? null;
            $className    = $student['class_name'] ?? '';

            $message = SmsService::buildExitMessage($studentName, $schoolName, $dateStr, $timeFmt, $exitType, $reason, $pickupPerson, $className);
            $res = SmsService::send($phone, $message, 'exit', null, $exitLogId > 0 ? $exitLogId : null);

            if ($exitLogId > 0) {
                try {
                    $this->db->prepare("UPDATE student_exit_logs SET sms_status = ? WHERE id = ?")
                        ->execute([!empty($res['success']) ? 'sent' : 'failed', $exitLogId]);
                } catch (Throwable $e) {}
            }

            return [
                'success'   => (bool) ($res['success'] ?? false),
                'status'    => !empty($res['success']) ? 'sent' : 'failed',
                'recipient' => $phone,
                'message'   => $message,
                'response'  => $res['response'] ?? '',
                'error'     => !empty($res['success']) ? null : ($res['response'] ?? 'SMS dispatch failed')
            ];
        }

        return [
            'success'   => false,
            'status'    => 'failed',
            'recipient' => $phone,
            'message'   => '',
            'response'  => null,
            'error'     => "Unsupported event: {$event}"
        ];
    }
}
