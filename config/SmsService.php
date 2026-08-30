<?php
/**
 * SmsService — EduCore Attendance SMS Integration via BulkSMSNigeria
 *
 * Primary Gateway: BulkSMSNigeria (https://www.bulksmsnigeria.com)
 * Supported gateways:
 *   - bulksms: BulkSMSNigeria (Default - School enters their own API Token & Sender ID)
 *   - termii : Termii SMS API
 *   - stub   : Logs to PHP error_log (development / simulation)
 *
 * Usage:
 *   SmsService::send($phone, $message, $type, $attendanceId);
 *   SmsService::test($phone);
 *   SmsService::buildCheckinMessage($studentName, $schoolName, $date, $time, $status);
 *   SmsService::buildAbsentMessage($studentName, $schoolName, $date);
 */
final class SmsService
{
    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Send an SMS via the configured gateway (Default: BulkSMSNigeria).
     *
     * @param string   $phone        Recipient phone number
     * @param string   $message      SMS body text
     * @param string   $type         SMS type: checkin | absent | exit | bulk | test | general
     * @param int|null $attendanceId Optional attendance record reference
     * @param int|null $exitLogId    Optional student exit log record reference
     * @return array{success:bool, response:string, sms_log_id:?int}
     */
    public static function send(
        string $phone,
        string $message,
        string $type = 'general',
        ?int $attendanceId = null,
        ?int $exitLogId = null
    ): array {
        $gateway = self::setting('sms_gateway', 'bulksms');

        switch ($gateway) {
            case 'termii':
                $result = self::sendViaTermii($phone, $message);
                break;
            case 'stub':
                $result = self::sendViaStub($phone, $message);
                break;
            case 'bulksms':
            default:
                $result = self::sendViaBulkSms($phone, $message);
                break;
        }

        $result['sms_log_id'] = null;

        // Log to DB (best-effort — never throw)
        try {
            $result['sms_log_id'] = self::logToDb($phone, $message, $type, $attendanceId, $exitLogId, $gateway, $result);
        } catch (Throwable $e) {
            error_log('[SmsService] DB log failed: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Send a test SMS (used in admin panel).
     */
    public static function test(string $phone): array
    {
        $school  = self::setting('school_name', 'Your School');
        $message = "EduCore Alert\n\nThis is a test SMS from {$school} via BulkSMSNigeria. Your attendance & exit SMS configuration is active and working correctly.\n\nThank you.";
        return self::send($phone, $message, 'test');
    }

    /**
     * Build the check-in (arrival) SMS message.
     */
    public static function buildCheckinMessage(
        string $studentName,
        string $schoolName,
        string $date,
        string $time,
        string $status
    ): string {
        return "EduCore Alert\n\nDear Parent,\nYour child, {$studentName}, has successfully arrived at {$schoolName}.\n\nDate: {$date}\nTime In: {$time}\nStatus: {$status}\n\nThank you.";
    }

    /**
     * Build the absent SMS message.
     */
    public static function buildAbsentMessage(
        string $studentName,
        string $schoolName,
        string $date
    ): string {
        return "EduCore Alert\n\nDear Parent,\nYour child, {$studentName}, has not been marked present at {$schoolName} today and is currently recorded as Absent.\n\nDate: {$date}\n\nIf you believe this is an error, please contact the school.\n\nThank you.";
    }

    /**
     * Build the exit SMS message using configured school templates.
     */
    public static function buildExitMessage(
        string $studentName,
        string $schoolName,
        string $date,
        string $time,
        string $exitType = 'normal',
        ?string $reason = null,
        ?string $pickupPerson = null,
        string $className = ''
    ): string {
        $isEarly = ($exitType === 'early');
        $templateKey = $isEarly ? 'exit_sms_template_early' : 'exit_sms_template_normal';
        
        $defaultTemplate = $isEarly
            ? "EduCore Alert: {student_name} has left {school_name} today at {exit_time} as an early exit ({reason})."
            : "EduCore Alert: {student_name} has left {school_name} today at {exit_time}. Status: {exit_status}.";

        $template = trim(self::setting($templateKey, $defaultTemplate));
        if ($template === '') {
            $template = $defaultTemplate;
        }

        $reasonText = $reason ?: 'Authorized';
        if ($pickupPerson) {
            $reasonText .= ' (Picked up by: ' . $pickupPerson . ')';
        }

        $replacements = [
            '{student_name}'   => $studentName,
            '{school_name}'    => $schoolName,
            '{exit_time}'      => $time,
            '{exit_date}'      => $date,
            '{exit_status}'    => ucfirst($exitType) . ' Exit',
            '{class_name}'     => $className,
            '{reason}'         => $reasonText,
            '{pickup_person}'  => $pickupPerson ?: 'Authorized',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    // ── Gateway Implementations ───────────────────────────────────────────────

    /**
     * BulkSMSNigeria API v2 Implementation
     * Docs: https://www.bulksmsnigeria.com/api
     */
    private static function sendViaBulkSms(string $phone, string $message): array
    {
        $apiKey   = self::setting('sms_api_key', self::setting('bulksms_api_key', ''));
        $senderId = self::setting('sms_sender_id', self::setting('bulksms_sender_id', 'EduCore'));

        if (empty($apiKey)) {
            error_log('[SmsService] BulkSMSNigeria API key not configured by school.');
            return ['success' => false, 'response' => 'BulkSMS API Key missing. Please configure your BulkSMSNigeria API Key in Attendance Settings.'];
        }

        $phone = self::normalizePhone($phone);

        // BulkSMSNigeria API v2 Endpoint
        $endpoint = 'https://www.bulksmsnigeria.com/api/v2/sms';
        
        $params = [
            'api_token' => $apiKey,
            'from'      => substr($senderId, 0, 11),
            'to'        => $phone,
            'body'      => $message,
            'dnd'       => 2, // Direct Hosted DND Route for Nigerian Corporate/School SMS
        ];

        // 1. Primary Attempt: cURL POST
        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($params),
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        // 2. Fallback Attempt: cURL GET if POST fails
        if ($error || $httpCode !== 200) {
            $url = $endpoint . '?' . http_build_query($params);
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error    = curl_error($ch);
            curl_close($ch);
        }

        if ($error) {
            error_log('[SmsService] BulkSMS cURL error: ' . $error);
            return ['success' => false, 'response' => 'cURL error: ' . $error];
        }

        $decoded = json_decode((string) $response, true);
        $statusStr = strtolower((string)($decoded['data']['status'] ?? $decoded['status'] ?? ''));
        $success = ($httpCode === 200 && ($statusStr === 'success' || str_contains(strtolower((string)$response), 'success')));

        if (!$success) {
            error_log('[SmsService] BulkSMS failure: ' . $response);
        }

        return ['success' => $success, 'response' => (string) $response];
    }

    /**
     * Termii SMS API
     * Docs: https://developers.termii.com/messaging
     */
    private static function sendViaTermii(string $phone, string $message): array
    {
        $apiKey   = self::setting('sms_api_key', self::setting('termii_api_key', ''));
        $senderId = self::setting('sms_sender_id', self::setting('termii_sender_id', 'EduCore'));

        if ($apiKey === '') {
            error_log('[SmsService] Termii API key not configured.');
            return ['success' => false, 'response' => 'Termii API key not configured.'];
        }

        $phone = self::normalizePhone($phone);

        $payload = json_encode([
            'to'       => $phone,
            'from'     => $senderId,
            'sms'      => $message,
            'type'     => 'plain',
            'api_key'  => $apiKey,
            'channel'  => 'generic',
        ]);

        $ch = curl_init('https://api.ng.termii.com/api/sms/send');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            error_log('[SmsService] Termii cURL error: ' . $error);
            return ['success' => false, 'response' => 'cURL error: ' . $error];
        }

        $decoded = json_decode((string) $response, true);
        $success = ($httpCode === 200 && isset($decoded['message_id']));

        if (!$success) {
            error_log('[SmsService] Termii failure: ' . $response);
        }

        return ['success' => $success, 'response' => (string) $response];
    }

    /**
     * Stub / Development mode — just log to PHP error_log.
     */
    private static function sendViaStub(string $phone, string $message): array
    {
        $logEntry = '[SmsService][STUB] To: ' . $phone . ' | Message: ' . $message;
        error_log($logEntry);
        return ['success' => true, 'response' => 'stub: logged to error_log'];
    }

    // ── Database Logging ──────────────────────────────────────────────────────

    private static function logToDb(
        string $phone,
        string $message,
        string $type,
        ?int $attendanceId,
        ?int $exitLogId,
        string $gateway,
        array $result
    ): ?int {
        $db = Database::connect();
        $db->prepare(
            "INSERT INTO sms_logs
                (recipient_phone, recipient_name, message, status, gateway, sms_type, attendance_id, exit_log_id, gateway_response, sent_at, created_at)
             VALUES (?, NULL, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
        )->execute([
            $phone,
            $message,
            $result['success'] ? 'sent' : 'failed',
            $gateway,
            $type,
            $attendanceId,
            $exitLogId,
            substr((string) $result['response'], 0, 500),
        ]);
        $id = (int) $db->lastInsertId();
        return $id > 0 ? $id : null;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Normalize phone to international format (Nigerian numbers: 08xxx → 2348xxx).
     */
    private static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Strip leading + if present
        $phone = ltrim($phone, '+');

        // 08012345678 → 2348012345678
        if (strlen($phone) === 11 && str_starts_with($phone, '0')) {
            $phone = '234' . substr($phone, 1);
        }

        // Already has 234 prefix
        if (str_starts_with($phone, '234')) {
            return $phone;
        }

        return $phone;
    }

    /**
     * Read a value from school_settings via the global setting() helper.
     * Falls back to $default if helper is not loaded yet.
     */
    private static function setting(string $key, string $default = ''): string
    {
        if (function_exists('setting')) {
            return setting($key, $default);
        }
        return $default;
    }
}
