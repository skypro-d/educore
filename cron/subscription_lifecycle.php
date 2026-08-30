<?php
/**
 * cron/subscription_lifecycle.php
 * Run via cron: php /path/to/EduCore/cron/subscription_lifecycle.php
 * Suggested schedule: daily at 06:00 AM
 *
 * Tasks:
 * 1. Mark expired customer accounts
 * 2. Send renewal reminders (30, 7, 1 day before expiry)
 * 3. Suspend accounts 7 days after expiry
 */

define('RUNNING_AS_CLI', PHP_SAPI === 'cli');

// Bootstrap
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/helpers.php';

$db  = Database::connect();
$now = new DateTime();

echo "[" . $now->format('Y-m-d H:i:s') . "] EduCore Subscription Lifecycle Cron Starting...\n";

// ── 1. SEND RENEWAL REMINDERS ─────────────────────────────────────────────────

$reminderDays = [30, 7, 1];
$supportEmail = platform_setting('support_email', 'support@skysavingtech.com.ng');
$companyName  = platform_setting('company_name', 'SkySavingTech Hub');
$fromEmail    = platform_setting('smtp_from_email', $supportEmail);

foreach ($reminderDays as $days) {
    $targetDate = (new DateTime())->modify("+{$days} days")->format('Y-m-d');

    $stmt = $db->prepare(
        "SELECT ca.* FROM customer_accounts ca
         WHERE DATE(ca.subscription_expires_at) = ?
           AND ca.status = 'active'
           AND ca.id NOT IN (
               SELECT customer_id FROM subscription_reminder_log
               WHERE days_before = ?
           )"
    );
    $stmt->execute([$targetDate, $days]);
    $accounts = $stmt->fetchAll();

    foreach ($accounts as $acc) {
        $subject = "Your EduCore subscription expires in {$days} day" . ($days > 1 ? 's' : '') . " — Renew Now";
        $expiryDate = date('F j, Y', strtotime($acc['subscription_expires_at']));
        $portalUrl  = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . BASE_URL . '/portal/subscription';

        $body = "Dear {$acc['contact_name']},\n\n"
              . "Your EduCore {$acc['plan']} subscription expires on {$expiryDate} ({$days} day" . ($days > 1 ? 's' : '') . " remaining).\n\n"
              . "To avoid any service interruption, please log in to your Customer Portal and renew your subscription:\n"
              . "{$portalUrl}\n\n"
              . "If you have any questions, reply to this email or visit your support portal.\n\n"
              . "Best regards,\n{$companyName} Team";

        send_email_notice($acc['email'], $subject, $body);

        // Log that reminder was sent
        try {
            $db->prepare(
                "INSERT IGNORE INTO subscription_reminder_log (customer_id, days_before) VALUES (?, ?)"
            )->execute([$acc['id'], $days]);
        } catch (Throwable $e) {}

        echo "  [REMINDER] Sent {$days}-day reminder to {$acc['email']}\n";
    }
}

// ── 2. MARK EXPIRED ACCOUNTS ──────────────────────────────────────────────────

$expiredStmt = $db->prepare(
    "SELECT id, email, contact_name FROM customer_accounts
     WHERE subscription_expires_at < CURRENT_DATE()
       AND status = 'active'"
);
$expiredStmt->execute();
$expired = $expiredStmt->fetchAll();

foreach ($expired as $acc) {
    $db->prepare("UPDATE customer_accounts SET status = 'suspended' WHERE id = ?")->execute([$acc['id']]);

    // Also deactivate the school license
    $db->prepare(
        "UPDATE school_licenses SET is_active = 0 WHERE school_id = (
             SELECT school_id FROM customer_accounts WHERE id = ?
         )"
    )->execute([$acc['id']]);

    // Send suspension email
    $subject = "EduCore Subscription Expired — Service Suspended";
    $body    = "Dear {$acc['contact_name']},\n\n"
             . "Your EduCore subscription has expired and your account has been suspended.\n\n"
             . "To restore access, please renew your subscription at:\n"
             . "https://skysavingtech.com.ng/portal/subscription\n\n"
             . "Your data is safe and will be retained for 30 days.\n\n"
             . "Best regards,\n{$companyName} Team";

    send_email_notice($acc['email'], $subject, $body);

    echo "  [EXPIRED] Suspended account for {$acc['email']}\n";
}

// ── 3. RESET REMINDER LOG FOR RENEWED ACCOUNTS ────────────────────────────────
// If a customer renews, their reminders should reset for the new expiry date
$db->exec(
    "DELETE srl FROM subscription_reminder_log srl
     JOIN customer_accounts ca ON ca.id = srl.customer_id
     WHERE ca.subscription_expires_at > CURRENT_DATE()
       AND ca.status = 'active'"
);

echo "[" . (new DateTime())->format('Y-m-d H:i:s') . "] Lifecycle cron completed.\n";
echo "  Reminders sent: " . array_sum(array_map('count', array_map(fn($d) => $expired, $reminderDays))) . "\n";
echo "  Accounts suspended: " . count($expired) . "\n";
