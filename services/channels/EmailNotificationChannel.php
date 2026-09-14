<?php
declare(strict_types=1);

require_once __DIR__ . '/NotificationChannelInterface.php';
require_once __DIR__ . '/../../config/helpers.php';
require_once __DIR__ . '/../../config/Email.php';

final class EmailNotificationChannel implements NotificationChannelInterface
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function getName(): string
    {
        return 'email';
    }

    public function isEnabled(): bool
    {
        return setting('attendance_email_enabled', '0') === '1';
    }

    public function isParentOptedIn(array $student): bool
    {
        if (isset($student['notify_email'])) {
            return (int) $student['notify_email'] === 1;
        }
        return true;
    }

    public function send(array $student, string $event, array $context): array
    {
        $email = trim($student['parent_email'] ?? '');
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success'   => false,
                'status'    => 'failed',
                'recipient' => $email,
                'message'   => '',
                'response'  => null,
                'error'     => 'Valid parent email address not provided'
            ];
        }

        $studentName = trim(($student['first_name'] ?? '') . ' ' . ($student['last_name'] ?? ''));
        $parentName  = trim($student['parent_name'] ?? ($student['guardian_name'] ?? 'Parent/Guardian'));
        $schoolName  = setting('school_name', APP_NAME);
        $date        = $context['date_formatted'] ?? date('F j, Y');
        $time        = $context['time_formatted'] ?? date('g:i A', strtotime($context['time'] ?? date('H:i')));
        $status      = $context['status'] ?? ($event === 'check_in' ? 'Checked In' : 'Checked Out');

        $replacements = [
            '{{student_name}}' => $studentName,
            '{{parent_name}}'  => $parentName,
            '{{school_name}}'  => $schoolName,
            '{{date}}'         => $date,
            '{{time}}'         => $time,
            '{{status}}'       => $status,
        ];

        if ($event === 'check_in') {
            $defaultSubject = 'EduCore Attendance Alert — {{student_name}}';
            $defaultBody = "Dear {{parent_name}},\n\nYour child, {{student_name}}, has checked into {{school_name}}.\n\nDate: {{date}}\nTime: {{time}}\nStatus: {{status}}\n\nThank you,\n{{school_name}}\nPowered by EduCore";

            $subjectTemplate = setting('attendance_email_subject_checkin', $defaultSubject);
            $bodyTemplate    = setting('attendance_email_template_checkin', $defaultBody);
        } else {
            $defaultSubject = 'EduCore Attendance Alert — {{student_name}}';
            $defaultBody = "Dear {{parent_name}},\n\nYour child, {{student_name}}, has left the school premises.\n\nDate: {{date}}\nTime: {{time}}\nStatus: Checked Out\n\nThank you,\n{{school_name}}\nPowered by EduCore";

            $subjectTemplate = setting('attendance_email_subject_checkout', $defaultSubject);
            $bodyTemplate    = setting('attendance_email_template_checkout', $defaultBody);
        }

        $subject = strtr($subjectTemplate, $replacements);
        $rawBody = strtr($bodyTemplate, $replacements);

        // Convert newlines to HTML paragraphs/breaks for modern email display
        $htmlMessage = nl2br(htmlspecialchars($rawBody, ENT_QUOTES, 'UTF-8'));

        try {
            $res = Email::send($email, $subject, $htmlMessage);
            $sent = (bool) ($res['sent'] ?? false);

            return [
                'success'   => $sent,
                'status'    => $sent ? 'sent' : 'failed',
                'recipient' => $email,
                'message'   => $rawBody,
                'response'  => 'Method: ' . ($res['method'] ?? 'none'),
                'error'     => $sent ? null : ($res['error'] ?? 'Email dispatch failed')
            ];
        } catch (Throwable $e) {
            return [
                'success'   => false,
                'status'    => 'failed',
                'recipient' => $email,
                'message'   => $rawBody,
                'response'  => null,
                'error'     => 'Email Exception: ' . $e->getMessage()
            ];
        }
    }
}
