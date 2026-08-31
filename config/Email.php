<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

final class Email
{
    /**
     * Send email with SMTP and fallback to mail() if SMTP fails.
     *
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $message Email HTML content or raw text message
     * @return array{sent:bool, method:string, error:?string} Result status
     */
    public static function send(string $to, string $subject, string $message): array
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['sent' => false, 'method' => 'none', 'error' => 'Invalid recipient email address.'];
        }

        $sent = false;
        $error = null;
        $method = 'smtp';

        // 1. Try sending via SMTP (using PHPMailer)
        if (self::loadPhpMailer()) {
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = smtp_setting('smtp_host', SMTP_HOST);
                $mail->SMTPAuth = true;
                $mail->Username = smtp_setting('smtp_username', SMTP_USERNAME);
                $mail->Password = smtp_setting('smtp_password', SMTP_PASSWORD);
                $mail->Port = (int) smtp_setting('smtp_port', (string) SMTP_PORT);
                $mail->Timeout = 10;

                $secure = strtolower(smtp_setting('smtp_secure', SMTP_SECURE));
                if ($secure === 'tls') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                } elseif ($secure === 'smtps' || $secure === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                }

                $mail->setFrom(smtp_setting('smtp_from_email', SMTP_FROM_EMAIL), smtp_setting('smtp_from_name', SMTP_FROM_NAME));
                $mail->addAddress($to);
                $mail->isHTML(true);
                $mail->Subject = trim($subject);
                $mail->Body = self::wrapEmailHtml($subject, $message);
                $mail->AltBody = strip_tags($message);
                $mail->send();

                $sent = true;
            } catch (Throwable $e) {
                $error = 'SMTP Error: ' . $e->getMessage();
                Logger::warn("SMTP delivery to {$to} failed: " . $e->getMessage());
            }
        } else {
            $error = 'PHPMailer classes not available.';
            Logger::warn('PHPMailer classes not available for SMTP sending.');
        }

        // 2. Fall back to standard PHP mail() function if SMTP failed
        if (!$sent && function_exists('mail')) {
            $method = 'mail';
            try {
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                $headers .= 'From: ' . smtp_setting('smtp_from_name', SMTP_FROM_NAME) . ' <' . smtp_setting('smtp_from_email', SMTP_FROM_EMAIL) . ">\r\n";
                $headers .= 'Reply-To: ' . smtp_setting('smtp_from_email', SMTP_FROM_EMAIL) . "\r\n";
                
                $htmlBody = self::wrapEmailHtml($subject, $message);
                $sent = @mail($to, $subject, $htmlBody, $headers);
                if ($sent) {
                    $error = null; // Succeeded via fallback
                } else {
                    $error = ($error ? $error . ' | ' : '') . 'mail() returned false.';
                    Logger::error("Native mail() fallback delivery to {$to} failed.");
                }
            } catch (Throwable $ex) {
                $error = ($error ? $error . ' | ' : '') . 'mail() Exception: ' . $ex->getMessage();
                Logger::error("Native mail() fallback delivery exception to {$to}: " . $ex->getMessage());
            }
        }

        return ['sent' => $sent, 'method' => $method, 'error' => $error];
    }

    /**
     * Test SMTP configuration by sending a diagnostic test email.
     *
     * @param array $config Configuration dictionary (keys: smtp_host, smtp_port, smtp_username, smtp_password, smtp_secure, smtp_from_email, smtp_from_name)
     * @param string $to Recipient email address
     * @return array{success:bool, message:string, debug:string}
     */
    public static function testConnection(array $config, string $to): array
    {
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please provide a valid recipient email address for testing.', 'debug' => ''];
        }

        if (!self::loadPhpMailer()) {
            return ['success' => false, 'message' => 'PHPMailer library files could not be loaded from server.', 'debug' => ''];
        }

        $host = trim($config['smtp_host'] ?? smtp_setting('smtp_host', SMTP_HOST));
        $port = (int) ($config['smtp_port'] ?? smtp_setting('smtp_port', (string) SMTP_PORT));
        $username = trim($config['smtp_username'] ?? smtp_setting('smtp_username', SMTP_USERNAME));
        $password = (string) ($config['smtp_password'] ?? smtp_setting('smtp_password', SMTP_PASSWORD));
        $secure = strtolower(trim($config['smtp_secure'] ?? smtp_setting('smtp_secure', SMTP_SECURE)));
        $fromEmail = trim($config['smtp_from_email'] ?? smtp_setting('smtp_from_email', SMTP_FROM_EMAIL));
        $fromName = trim($config['smtp_from_name'] ?? smtp_setting('smtp_from_name', SMTP_FROM_NAME));

        if (empty($host)) {
            return ['success' => false, 'message' => 'SMTP Host is required to run test.', 'debug' => ''];
        }

        $debugOutput = '';
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $host;
            $mail->SMTPAuth = !empty($username);
            $mail->Username = $username;
            $mail->Password = $password;
            $mail->Port = $port > 0 ? $port : 465;
            $mail->Timeout = 12;

            // Capture SMTP debug output
            $mail->SMTPDebug = 2;
            $mail->Debugoutput = function ($str, $level) use (&$debugOutput) {
                $debugOutput .= trim($str) . "\n";
            };

            if ($secure === 'tls' || $port === 587) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            } elseif ($secure === 'smtps' || $secure === 'ssl' || $port === 465) {
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            } else {
                $mail->SMTPAutoTLS = false;
            }

            // Fallback options for SSL validation on various hosting environments
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];

            $senderEmail = !empty($fromEmail) ? $fromEmail : (!empty($username) ? $username : 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
            $senderName = !empty($fromName) ? $fromName : (defined('APP_NAME') ? APP_NAME : 'EduCore');

            $mail->setFrom($senderEmail, $senderName);
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = 'EduCore SMTP Delivery Test - ' . date('Y-m-d H:i:s');

            $bodyContent = '<p><strong>Great news!</strong></p>'
                . '<p>Your EduCore SMTP Mail Server is configured correctly and successfully dispatched this email.</p>'
                . '<div style="background:#f1f5f9; border-radius:8px; padding:16px; margin:16px 0; font-family:monospace; font-size:13px;">'
                . '<table style="width:100%; border-collapse:collapse;">'
                . '<tr><td style="padding:4px 0; color:#64748b; width:130px;">SMTP Host:</td><td style="font-weight:bold; color:#0f172a;">' . htmlspecialchars($host) . '</td></tr>'
                . '<tr><td style="padding:4px 0; color:#64748b;">Port / Encryption:</td><td style="font-weight:bold; color:#0f172a;">' . htmlspecialchars((string)$port) . ' (' . strtoupper($secure) . ')</td></tr>'
                . '<tr><td style="padding:4px 0; color:#64748b;">Sender Email:</td><td style="font-weight:bold; color:#0f172a;">' . htmlspecialchars($senderEmail) . '</td></tr>'
                . '<tr><td style="padding:4px 0; color:#64748b;">Timestamp:</td><td style="color:#0f172a;">' . date('Y-m-d H:i:s T') . '</td></tr>'
                . '</table></div>'
                . '<p style="color:#64748b; font-size:13px;">System notifications, application receipts, admission letters, and exam updates will now be delivered using these SMTP credentials.</p>';

            $mail->Body = self::wrapEmailHtml('SMTP Test Successful', $bodyContent);
            $mail->AltBody = "Great news! Your EduCore SMTP Mail Server is configured correctly. Tested at " . date('Y-m-d H:i:s');
            $mail->send();

            return [
                'success' => true,
                'message' => "SMTP Verified! Test email successfully delivered to {$to}.",
                'debug'   => $debugOutput
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'SMTP Delivery Failed: ' . $e->getMessage(),
                'debug'   => $debugOutput . "\nException Details: " . $e->getMessage()
            ];
        }
    }

    /**
     * Load PHPMailer files using existing autoload or manual fallbacks.
     */
    private static function loadPhpMailer(): bool
    {
        if (class_exists(PHPMailer::class)) {
            return true;
        }

        $autoload = __DIR__ . '/../vendor/autoload.php';
        if (is_file($autoload)) {
            require_once $autoload;
            if (class_exists(PHPMailer::class)) {
                return true;
            }
        }

        $manualRoots = [
            __DIR__ . '/../PHPMailer/src',
            __DIR__ . '/../auth/PHPMailer/src',
            __DIR__ . '/../../skysavings/auth/PHPMailer/src',
        ];

        foreach ($manualRoots as $root) {
            if (is_file($root . '/PHPMailer.php') && is_file($root . '/SMTP.php') && is_file($root . '/Exception.php')) {
                require_once $root . '/Exception.php';
                require_once $root . '/PHPMailer.php';
                require_once $root . '/SMTP.php';
                return class_exists(PHPMailer::class);
            }
        }

        return false;
    }

    /**
     * Wrap email body with standard HTML template.
     */
    public static function wrapEmailHtml(string $subject, string $message): string
    {
        $htmlMsg = $message;
        if ($message === strip_tags($message)) {
            $htmlMsg = '<p>' . implode('</p><p>', explode("\n", e($message))) . '</p>';
        }

        $primaryColor = setting('primary_color', '#1056c2');
        $logoLetter = strtoupper(substr(setting('school_name', APP_NAME), 0, 1));
        
        return '<!DOCTYPE html><html><head><meta charset="utf-8"><style>'
            . 'body{background:#f6f9fc;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Helvetica,Arial,sans-serif;margin:0;padding:20px;color:#333;}'
            . '.card{max-width:560px;margin:0 auto;background:#fff;border:1px solid #e1e6eb;border-radius:8px;padding:32px;box-shadow:0 4px 12px rgba(0,0,0,0.03);}'
            . '.logo{width:44px;height:44px;border-radius:50%;background:' . $primaryColor . ';color:#fff;font-weight:700;display:inline-flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:24px;border:1px solid rgba(255,255,255,0.1);}'
            . 'h2{color:#1a1a1a;margin-top:0;font-size:20px;font-weight:700;}'
            . 'p{line-height:1.6;font-size:14px;color:#525f7f;}'
            . '.footer{margin-top:32px;padding-top:20px;border-top:1px solid #e1e6eb;font-size:12px;color:#98a6ad;line-height:1.5;}'
            . '</style></head><body><div class="card">'
            . '<div class="logo">' . $logoLetter . '</div>'
            . '<h2>' . e($subject) . '</h2>'
            . $htmlMsg
            . '<div class="footer">&copy; ' . date('Y') . ' ' . e(setting('school_name', APP_NAME)) . '.<br>'
            . 'Need help? Contact ' . e(setting('school_email', SMTP_FROM_EMAIL)) . '</div></div></body></html>';
    }
}
