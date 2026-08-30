<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/PaymentConfig.php';

use PHPMailer\PHPMailer\PHPMailer;

final class NotificationController
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?: Database::connect();
    }

    public function sendApplicationConfirmation(int $applicantId): bool
    {
        $applicant = $this->findApplicant($applicantId);
        if (!$applicant) {
            return false;
        }

        $subject = 'Application Received - ' . $applicant['application_number'];
        $message = "Dear {$applicant['first_name']},\n\n"
            . "Your admission application has been received.\n"
            . "Application Number: {$applicant['application_number']}\n"
            . "Next step: complete the admission fee payment of NGN " . number_format((float) setting('admission_fee', '5000'), 2) . ".\n"
            . "Payment link: " . PaymentConfig::absoluteUrl('payment/process.php?applicant_id=' . (int) $applicant['id']) . "\n\n"
            . setting('school_name', APP_NAME);

        return $this->send($applicantId, $applicant['parent_email'], $subject, $message, 'application_confirmation');
    }

    public function sendPaymentReceipt(int $applicantId, string $reference): bool
    {
        $stmt = $this->db->prepare('SELECT a.*, p.amount, p.transaction_reference, p.payment_date FROM applicants a JOIN payments p ON p.applicant_id = a.id WHERE a.id = ? AND p.transaction_reference = ? LIMIT 1');
        $stmt->execute([$applicantId, $reference]);
        $data = $stmt->fetch();
        if (!$data) {
            return false;
        }

        $subject = 'Payment Receipt - ' . $reference;
        $message = "Dear {$data['first_name']},\n\n"
            . "Your admission fee payment was successful.\n"
            . "Application Number: {$data['application_number']}\n"
            . "Reference: {$reference}\n"
            . "Amount: NGN " . number_format((float) $data['amount'], 2) . "\n"
            . "Payment Date: " . ($data['payment_date'] ?: date('Y-m-d H:i:s')) . "\n\n"
            . "Your application is now ready for review.";

        return $this->send($applicantId, $data['parent_email'], $subject, $message, 'payment_receipt');
    }

    public function sendStatusUpdate(int $applicantId, string $status): bool
    {
        $applicant = $this->findApplicant($applicantId);
        if (!$applicant) {
            return false;
        }

        $subject = 'Admission Application ' . $status . ' - ' . $applicant['application_number'];
        $message = $this->statusMessage($applicant, $status);

        return $this->send($applicantId, $applicant['parent_email'], $subject, $message, 'application_status_' . strtolower(str_replace(' ', '_', $status)));
    }

    public function sendWelcomeNotice(int $applicantId, string $to, string $subject, string $message): bool
    {
        return $this->send($applicantId, $to, $subject, $message, 'welcome_credentials');
    }

    private function findApplicant(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM applicants WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    private function statusMessage(array $applicant, string $status): string
    {
        $schoolName = setting('school_name', APP_NAME);
        $studentName = trim($applicant['first_name'] . ' ' . $applicant['last_name']);
        $dashboardUrl = PaymentConfig::absoluteUrl('student?number=' . rawurlencode($applicant['application_number']));
        $trackUrl = PaymentConfig::absoluteUrl('track?number=' . rawurlencode($applicant['application_number']));

        if ($status === 'Approved') {
            return "Dear Parent/Guardian,\n\n"
                . "Congratulations. The admission application for {$studentName} has been approved.\n\n"
                . "Application Number: {$applicant['application_number']}\n"
                . "Next step: please open the parent dashboard to continue with the admission process and download the admission letter when available.\n\n"
                . "Parent Dashboard: {$dashboardUrl}\n\n"
                . "{$schoolName}";
        }

        if ($status === 'Rejected') {
            return "Dear Parent/Guardian,\n\n"
                . "Thank you for applying to {$schoolName}. After reviewing the application for {$studentName}, we are unable to offer admission at this time.\n\n"
                . "Application Number: {$applicant['application_number']}\n\n"
                . "{$schoolName}";
        }

        if ($status === 'Terminated') {
            return "Dear Parent/Guardian,\n\n"
                . "This is to notify you that the admission offer for {$studentName} has been terminated.\n\n"
                . "Application Number: {$applicant['application_number']}\n\n"
                . "Please contact the admission office if you need further clarification.\n\n"
                . "{$schoolName}";
        }

        return "Dear Parent/Guardian,\n\n"
            . "The admission application for {$studentName} has been updated.\n\n"
            . "Application Number: {$applicant['application_number']}\n"
            . "Current Status: {$status}\n\n"
            . "Track the application here: {$trackUrl}\n\n"
            . "{$schoolName}";
    }

    private function send(int $applicantId, string $to, string $subject, string $message, string $type): bool
    {
        $result = Email::send($to, $subject, $message);
        $this->log($applicantId, $type, $to, $subject, $result['sent'] ? 'sent' : 'failed', $result['error']);
        return $result['sent'];
    }

    private function log(int $applicantId, string $type, string $to, string $subject, string $status, ?string $error): void
    {
        try {
            $stmt = $this->db->prepare('INSERT INTO email_logs (applicant_id, email_type, recipient_email, subject, status, error_message, sent_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
            $stmt->execute([$applicantId, $type, $to, $subject, $status, $error]);
        } catch (Throwable $e) {
            error_log('Email log failed: ' . $e->getMessage());
        }
    }
}
