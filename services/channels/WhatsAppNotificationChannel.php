<?php
declare(strict_types=1);

require_once __DIR__ . '/NotificationChannelInterface.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../WhatsAppService.php';

final class WhatsAppNotificationChannel implements NotificationChannelInterface
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function getName(): string
    {
        return 'whatsapp';
    }

    public function isEnabled(): bool
    {
        return setting('attendance_whatsapp_enabled', '0') === '1';
    }

    public function isParentOptedIn(array $student): bool
    {
        if (isset($student['notify_whatsapp'])) {
            return (int) $student['notify_whatsapp'] === 1;
        }
        return true;
    }

    public function send(array $student, string $event, array $context): array
    {
        $phone = trim($student['parent_whatsapp'] ?? '');
        if ($phone === '') {
            $phone = trim($student['parent_phone'] ?? '');
        }

        $phone = normalize_phone_number($phone);
        if ($phone === '') {
            return [
                'success'   => false,
                'status'    => 'failed',
                'recipient' => '',
                'message'   => '',
                'response'  => null,
                'error'     => 'Parent WhatsApp phone number not provided'
            ];
        }

        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        $parentName  = trim($student['parent_name'] ?? ($student['guardian_name'] ?? 'Parent/Guardian'));
        $schoolName  = setting('school_name', APP_NAME);
        $date        = $context['date_formatted'] ?? date('F j, Y');
        $time        = $context['time_formatted'] ?? date('g:i A', strtotime($context['time'] ?? date('H:i')));

        $replacements = [
            '{{student_name}}' => $studentName,
            '{{parent_name}}'  => $parentName,
            '{{school_name}}'  => $schoolName,
            '{{date}}'         => $date,
            '{{time}}'         => $time,
        ];

        if ($event === 'check_in') {
            $defaultTpl = "EduCore Attendance Alert\nHello {{parent_name}}, your child {{student_name}} has checked into {{school_name}}.\nDate: {{date}}\nTime: {{time}}";
            $template   = setting('attendance_whatsapp_template_checkin', $defaultTpl);
        } else {
            $defaultTpl = "EduCore Attendance Alert\nHello {{parent_name}}, your child {{student_name}} has left {{school_name}}.\nDate: {{date}}\nTime: {{time}}";
            $template   = setting('attendance_whatsapp_template_checkout', $defaultTpl);
        }

        $message = strtr($template, $replacements);

        try {
            $res = WhatsAppService::send($phone, $message, [
                'event' => $event,
                'template_name' => ($event === 'check_in')
                    ? setting('whatsapp_template_name_checkin', '')
                    : setting('whatsapp_template_name_checkout', '')
            ]);

            return [
                'success'   => (bool) ($res['success'] ?? false),
                'status'    => !empty($res['success']) ? 'sent' : 'failed',
                'recipient' => $phone,
                'message'   => $message,
                'response'  => $res['response'] ?? '',
                'error'     => !empty($res['success']) ? null : ($res['error'] ?? 'WhatsApp dispatch failed')
            ];
        } catch (Throwable $e) {
            return [
                'success'   => false,
                'status'    => 'failed',
                'recipient' => $phone,
                'message'   => $message,
                'response'  => null,
                'error'     => 'WhatsApp Exception: ' . $e->getMessage()
            ];
        }
    }
}
