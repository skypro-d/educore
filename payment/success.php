<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../controllers/PaymentGateway.php';
require_once __DIR__ . '/../controllers/NotificationController.php';
require_once __DIR__ . '/../models/Payment.php';

$db = Database::connect();
$reference = trim($_GET['reference'] ?? $_GET['trxref'] ?? '');

if ($reference === '') {
    flash('danger', 'Payment reference is missing.');
    redirect('track');
}

$payment = (new Payment($db))->findByReference($reference);
if (!$payment) {
    flash('danger', 'Payment record not found.');
    redirect('track');
}

if ($payment['payment_status'] !== 'Paid') {
    $gateway = new PaymentGateway($db, $payment['gateway'] ?: null);
    $verification = $gateway->verify($reference, $payment['gateway'] ?: null);
    if ($verification['success']) {
        $gateway->completePayment($reference, (array) $verification['data'], $verification['gateway'] ?? null);
        (new NotificationController($db))->sendPaymentReceipt((int) $payment['applicant_id'], $reference);
        flash('success', 'Payment verified successfully.');
    } else {
        flash('warning', 'Payment has not been confirmed yet. Please check again shortly.');
    }
} else {
    flash('success', 'Payment already confirmed.');
}

$stmt = $db->prepare('SELECT application_number FROM applicants WHERE id = ?');
$stmt->execute([(int) $payment['applicant_id']]);
$number = (string) $stmt->fetchColumn();
redirect('track?number=' . urlencode($number));
