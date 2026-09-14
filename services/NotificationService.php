<?php
declare(strict_types=1);

require_once __DIR__ . '/channels/NotificationChannelInterface.php';
require_once __DIR__ . '/channels/SmsNotificationChannel.php';
require_once __DIR__ . '/channels/EmailNotificationChannel.php';
require_once __DIR__ . '/channels/WhatsAppNotificationChannel.php';
require_once __DIR__ . '/WhatsAppService.php';
require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/Email.php';

final class NotificationService
{
    private PDO $db;
    /** @var NotificationChannelInterface[] */
    private array $channels;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::connect();
        $this->channels = [
            'sms'      => new SmsNotificationChannel($this->db),
            'email'    => new EmailNotificationChannel($this->db),
            'whatsapp' => new WhatsAppNotificationChannel($this->db),
        ];
    }

    /**
     * Dispatch attendance notifications across all enabled and opted-in channels.
     * Guaranteed to never throw — attendance records are preserved even if notifications fail.
     *
     * @param array  $student Applicant/student record
     * @param string $event   'check_in' | 'check_out'
     * @param array  $context Event contextual data (time, date, status, attendance_id, exit_log_id, etc.)
     * @return array<string, array> Map of channel name => dispatch result
     */
    public function sendAttendanceNotification(array $student, string $event, array $context): array
    {
        $results = [];

        // 1. Global event gate check
        if ($event === 'check_in' && setting('notify_on_checkin', '1') === '0') {
            return $results;
        }
        if ($event === 'check_out' && setting('notify_on_checkout', '1') === '0') {
            return $results;
        }

        // 2. School tenant ID
        $schoolId = (int) ($student['school_id'] ?? (SchoolContext::id() ?: 1));

        // 3. Process each channel independently in isolated try-catch blocks
        foreach ($this->channels as $name => $channel) {
            try {
                // Check if channel is globally enabled in school settings
                if (!$channel->isEnabled()) {
                    continue;
                }

                // Check if parent has opted in for this student
                if (!$channel->isParentOptedIn($student)) {
                    continue;
                }

                $res = $channel->send($student, $event, $context);
                $results[$name] = $res;

                // Log result to DB (best-effort)
                $this->logNotification([
                    'school_id'            => $schoolId,
                    'student_id'           => (int) ($student['id'] ?? 0),
                    'parent_id'            => null,
                    'attendance_id'        => !empty($context['attendance_id']) ? (int) $context['attendance_id'] : null,
                    'exit_log_id'          => !empty($context['exit_log_id']) ? (int) $context['exit_log_id'] : null,
                    'notification_type'    => 'attendance',
                    'channel'              => $name,
                    'recipient'            => $res['recipient'] ?? '',
                    'event'                => $event,
                    'template_identifier'  => $context['template_identifier'] ?? "{$event}_{$name}",
                    'message'              => $res['message'] ?? '',
                    'status'               => $res['status'] ?? ($res['success'] ? 'sent' : 'failed'),
                    'provider_response'    => substr((string) ($res['response'] ?? ''), 0, 1000),
                    'error_message'        => substr((string) ($res['error'] ?? ''), 0, 1000),
                    'sent_at'              => !empty($res['success']) ? date('Y-m-d H:i:s') : null,
                ]);

            } catch (Throwable $e) {
                $results[$name] = [
                    'success'   => false,
                    'status'    => 'failed',
                    'recipient' => '',
                    'message'   => '',
                    'response'  => null,
                    'error'     => $e->getMessage()
                ];

                try {
                    $this->logNotification([
                        'school_id'            => $schoolId,
                        'student_id'           => (int) ($student['id'] ?? 0),
                        'parent_id'            => null,
                        'attendance_id'        => !empty($context['attendance_id']) ? (int) $context['attendance_id'] : null,
                        'exit_log_id'          => !empty($context['exit_log_id']) ? (int) $context['exit_log_id'] : null,
                        'notification_type'    => 'attendance',
                        'channel'              => $name,
                        'recipient'            => '',
                        'event'                => $event,
                        'template_identifier'  => "{$event}_{$name}",
                        'message'              => '',
                        'status'               => 'failed',
                        'provider_response'    => null,
                        'error_message'        => 'Unexpected Exception: ' . $e->getMessage(),
                        'sent_at'              => null,
                    ]);
                } catch (Throwable) {}

                error_log("[NotificationService] Channel {$name} failed: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Retry a failed notification log
     */
    public function retry(int $logId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM attendance_notification_logs WHERE id = ? LIMIT 1");
        $stmt->execute([$logId]);
        $log = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$log) {
            return ['success' => false, 'message' => 'Notification log record not found'];
        }

        // Reload student record
        $studStmt = $this->db->prepare(
            "SELECT a.*, c.name AS class_name 
             FROM applicants a 
             LEFT JOIN classes c ON c.id = a.class_id 
             WHERE a.id = ? LIMIT 1"
        );
        $studStmt->execute([(int) $log['student_id']]);
        $student = $studStmt->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            $student = [
                'id'              => (int) ($log['student_id'] ?? 0),
                'school_id'       => (int) ($log['school_id'] ?? 1),
                'first_name'      => 'Student',
                'last_name'       => '#' . ($log['student_id'] ?? ''),
                'parent_name'     => 'Parent/Guardian',
                'parent_phone'    => $log['recipient'] ?? '',
                'parent_whatsapp' => $log['recipient'] ?? '',
                'parent_email'    => $log['recipient'] ?? '',
                'notify_sms'      => 1,
                'notify_email'    => 1,
                'notify_whatsapp' => 1,
            ];
        }

        $channelName = $log['channel'];
        if (!isset($this->channels[$channelName])) {
            return ['success' => false, 'message' => "Channel {$channelName} not supported for retry"];
        }

        $channel = $this->channels[$channelName];
        $event   = $log['event'];

        $context = [
            'attendance_id' => $log['attendance_id'],
            'exit_log_id'   => $log['exit_log_id'],
            'time'          => date('H:i', strtotime($log['created_at'])),
            'date_formatted'=> date('F j, Y', strtotime($log['created_at'])),
            'status'        => ($event === 'check_in' ? 'Checked In' : 'Checked Out'),
        ];

        // If exit_log_id exists, load exit details
        if (!empty($log['exit_log_id'])) {
            $exitStmt = $this->db->prepare("SELECT * FROM student_exit_logs WHERE id = ? LIMIT 1");
            $exitStmt->execute([(int) $log['exit_log_id']]);
            $exitRow = $exitStmt->fetch(PDO::FETCH_ASSOC);
            if ($exitRow) {
                $context['exit_data'] = [
                    'exit_type'          => $exitRow['exit_type'],
                    'exit_date'          => $exitRow['exit_date'],
                    'exit_time'          => $exitRow['exit_time'],
                    'exit_reason'        => $exitRow['exit_reason'],
                    'pickup_person_name' => $exitRow['pickup_person_name']
                ];
            }
        }

        $res = $channel->send($student, $event, $context);

        // Update log row
        $newStatus = !empty($res['success']) ? 'sent' : 'failed';
        $upd = $this->db->prepare(
            "UPDATE attendance_notification_logs 
             SET status = ?, 
                 recipient = ?, 
                 provider_response = ?, 
                 error_message = ?, 
                 sent_at = ?,
                 message = COALESCE(?, message)
             WHERE id = ?"
        );
        $upd->execute([
            $newStatus,
            $res['recipient'] ?? $log['recipient'],
            substr((string) ($res['response'] ?? ''), 0, 1000),
            substr((string) ($res['error'] ?? ''), 0, 1000),
            !empty($res['success']) ? date('Y-m-d H:i:s') : null,
            $res['message'] ?? null,
            $logId
        ]);

        return [
            'success'  => !empty($res['success']),
            'status'   => $newStatus,
            'response' => $res['response'] ?? '',
            'error'    => $res['error'] ?? null,
            'message'  => !empty($res['success'])
                ? "Notification retried and delivered successfully via " . strtoupper($channelName)
                : "Retry failed: " . ($res['error'] ?? 'Provider delivery error')
        ];
    }

    /**
     * Send test Email diagnostic
     */
    public function testEmail(string $recipient): array
    {
        $recipient = trim($recipient);
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please enter a valid email address for testing.'];
        }

        $schoolName = setting('school_name', APP_NAME);
        $subject    = "EduCore Attendance Alert — Test Email";
        $body       = "<p>Dear Parent,</p>"
                    . "<p>This is a test attendance notification from <strong>" . htmlspecialchars($schoolName, ENT_QUOTES, 'UTF-8') . "</strong>.</p>"
                    . "<p>Your attendance email alert system is active and functioning properly.</p>"
                    . "<hr style='border:none;border-top:1px solid #e2e8f0;margin:15px 0;'>"
                    . "<p><small style='color:#64748b;'>Powered by EduCore</small></p>";

        $res = Email::send($recipient, $subject, $body);
        if (!empty($res['sent'])) {
            return ['success' => true, 'message' => "Test email successfully sent to {$recipient}."];
        }

        return ['success' => false, 'message' => "Failed to send test email: " . ($res['error'] ?? 'SMTP/mail delivery error.')];
    }

    /**
     * Send test WhatsApp diagnostic
     */
    public function testWhatsApp(string $recipient): array
    {
        $recipient = normalize_phone_number($recipient);
        if ($recipient === '') {
            return ['success' => false, 'message' => 'Please enter a valid phone number for testing.'];
        }

        $res = WhatsAppService::test($recipient);
        if (!empty($res['success'])) {
            return ['success' => true, 'message' => "Test WhatsApp message initiated successfully to {$recipient}."];
        }

        return ['success' => false, 'message' => "Failed to send WhatsApp message: " . ($res['error'] ?? 'API delivery error.')];
    }

    // ── Private Helpers ──────────────────────────────────────────────────────

    private function logNotification(array $data): void
    {
        $sql = "INSERT INTO attendance_notification_logs
                (school_id, student_id, parent_id, attendance_id, exit_log_id, notification_type,
                 channel, recipient, event, template_identifier, message, status, provider_response,
                 error_message, sent_at, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $this->db->prepare($sql)->execute([
            $data['school_id'],
            $data['student_id'],
            $data['parent_id'],
            $data['attendance_id'],
            $data['exit_log_id'],
            $data['notification_type'],
            $data['channel'],
            $data['recipient'],
            $data['event'],
            $data['template_identifier'],
            $data['message'],
            $data['status'],
            $data['provider_response'],
            $data['error_message'],
            $data['sent_at']
        ]);
    }
}
