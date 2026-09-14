<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';

final class WhatsAppService
{
    /**
     * Send a WhatsApp message via the configured business gateway.
     *
     * @param string $phone   Recipient phone number
     * @param string $message Formatted text message
     * @param array  $options Optional provider-specific params (e.g. template_name, media_url)
     * @return array{success:bool, response:string, error:?string}
     */
    public static function send(string $phone, string $message, array $options = []): array
    {
        $phone = normalize_phone_number($phone);
        if ($phone === '') {
            return [
                'success'  => false,
                'response' => '',
                'error'    => 'Invalid or empty phone number for WhatsApp'
            ];
        }

        $provider = self::setting('whatsapp_provider', 'stub');

        switch ($provider) {
            case 'meta':
                return self::sendViaMeta($phone, $message, $options);
            case 'termii':
                return self::sendViaTermii($phone, $message, $options);
            case 'custom':
                return self::sendViaCustom($phone, $message, $options);
            case 'stub':
            default:
                return self::sendViaStub($phone, $message);
        }
    }

    /**
     * Send diagnostic test WhatsApp message (used in admin settings).
     */
    public static function test(string $phone): array
    {
        $school  = self::setting('school_name', APP_NAME);
        $message = "EduCore Alert\n\nThis is a test WhatsApp message from {$school}. Your attendance WhatsApp configuration is active and operational.\n\nThank you.";
        return self::send($phone, $message, ['type' => 'test']);
    }

    // ── Providers ────────────────────────────────────────────────────────────

    /**
     * Meta / Facebook WhatsApp Cloud API
     */
    private static function sendViaMeta(string $phone, string $message, array $options): array
    {
        $apiUrl   = trim(self::setting('whatsapp_api_url', ''));
        $token    = trim(self::setting('whatsapp_api_token', ''));
        $phoneId  = trim(self::setting('whatsapp_phone_number_id', ''));

        if ($apiUrl === '') {
            $apiUrl = "https://graph.facebook.com/v20.0/{$phoneId}/messages";
        }

        if ($token === '') {
            return [
                'success'  => false,
                'response' => '',
                'error'    => 'Meta WhatsApp API Token is not configured'
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $phone,
            'type'              => 'text',
            'text'              => [
                'preview_url' => false,
                'body'        => $message
            ]
        ];

        return self::executeHttp($apiUrl, $payload, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json'
        ]);
    }

    /**
     * Termii WhatsApp API
     */
    private static function sendViaTermii(string $phone, string $message, array $options): array
    {
        $apiKey = trim(self::setting('whatsapp_api_token', self::setting('termii_api_key', '')));
        $sender = trim(self::setting('whatsapp_sender_id', self::setting('termii_sender_id', 'School')));
        $apiUrl = trim(self::setting('whatsapp_api_url', 'https://api.ng.termii.com/api/sms/send'));

        if ($apiKey === '') {
            return [
                'success'  => false,
                'response' => '',
                'error'    => 'Termii WhatsApp API Key is not configured'
            ];
        }

        $payload = [
            'to'      => $phone,
            'from'    => $sender,
            'sms'     => $message,
            'type'    => 'plain',
            'channel' => 'whatsapp',
            'api_key' => $apiKey
        ];

        return self::executeHttp($apiUrl, $payload, [
            'Content-Type: application/json'
        ]);
    }

    /**
     * Generic Custom HTTP REST / Webhook
     */
    private static function sendViaCustom(string $phone, string $message, array $options): array
    {
        $apiUrl = trim(self::setting('whatsapp_api_url', ''));
        $token  = trim(self::setting('whatsapp_api_token', ''));
        $sender = trim(self::setting('whatsapp_sender_id', ''));

        if ($apiUrl === '') {
            return [
                'success'  => false,
                'response' => '',
                'error'    => 'Custom WhatsApp API Endpoint URL is not configured'
            ];
        }

        $headers = ['Content-Type: application/json'];
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $payload = [
            'recipient' => $phone,
            'phone'     => $phone,
            'message'   => $message,
            'text'      => $message,
            'sender'    => $sender,
            'timestamp' => time()
        ];

        return self::executeHttp($apiUrl, $payload, $headers);
    }

    /**
     * Stub mode (development / simulation)
     */
    private static function sendViaStub(string $phone, string $message): array
    {
        error_log("[WhatsAppService][STUB] To: {$phone} | Message: " . str_replace("\n", ' ', $message));
        return [
            'success'  => true,
            'response' => 'stub: logged to error_log',
            'error'    => null
        ];
    }

    // ── HTTP Helper ──────────────────────────────────────────────────────────

    private static function executeHttp(string $url, array $payload, array $headers): array
    {
        $json = json_encode($payload);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success'  => false,
                'response' => '',
                'error'    => 'cURL error: ' . $error
            ];
        }

        $success = ($httpCode >= 200 && $httpCode < 300);
        $decoded = json_decode((string) $response, true);
        $errDetail = null;

        if (!$success) {
            $errDetail = "HTTP {$httpCode}: " . ($decoded['error']['message'] ?? ($decoded['message'] ?? substr((string) $response, 0, 200)));
        }

        return [
            'success'  => $success,
            'response' => (string) $response,
            'error'    => $errDetail
        ];
    }

    private static function setting(string $key, string $default = ''): string
    {
        if (function_exists('setting')) {
            return (string) setting($key, $default);
        }
        return $default;
    }
}
