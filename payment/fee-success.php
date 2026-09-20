<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../controllers/PaymentGateway.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

$db = Database::connect();
$reference = trim((string) ($_GET['reference'] ?? $_GET['trxref'] ?? ''));

if ($reference === '') {
    flash('danger', 'Payment reference is missing.');
    redirect('');
}

$gateway = new PaymentGateway($db);
$payment = $gateway->findFeePaymentByReference($reference);

if (!$payment) {
    flash('danger', 'Fee payment record not found.');
    redirect('');
}

if (!in_array($payment['payment_status'], ['Paid', 'Partial', 'Manual'], true) || empty($payment['receipt_number'])) {
    $verification = $gateway->verify($reference, $payment['payment_method'] ?: null);
    if ($verification['success']) {
        $gateway->completeFeePayment($reference, (array) ($verification['data'] ?? []), $verification['gateway'] ?? null);
        flash('success', 'Fee payment confirmed successfully! Your official payment receipt has been generated.');
    } else {
        flash('warning', 'Payment has not been confirmed yet by the gateway. If debited, your transaction will update shortly.');
    }
} else {
    flash('success', 'Fee payment already confirmed.');
}

// Redirect appropriately based on user session
if (!empty($_SESSION['parent'])) {
    redirect('parent/payment-history');
} elseif (!empty($_SESSION['student'])) {
    redirect('student/payment-history');
} else {
    // If student or parent is logged out, redirect to receipt or login
    if (!empty($payment['id'])) {
        redirect('parent/login');
    } else {
        redirect('');
    }
}
