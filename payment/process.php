<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../controllers/PaymentGateway.php';
require_once __DIR__ . '/../models/Payment.php';

$db = Database::connect();
$applicantId = (int) ($_GET['applicant_id'] ?? 0);
$feeType = (string) ($_GET['fee'] ?? 'admission_fee');
$feeType = in_array($feeType, ['admission_fee', 'acceptance_fee', 'enrollment_fee'], true) ? $feeType : 'admission_fee';
$feeLabel = PaymentConfig::feeLabel($feeType);
$feeAmount = PaymentConfig::feeAmount($feeType);

if ($applicantId <= 0) {
    flash('danger', 'Invalid payment request.');
    redirect('track');
}

$stmt = $db->prepare('SELECT a.*, c.name AS class_name FROM applicants a LEFT JOIN classes c ON c.id = a.class_id WHERE a.id = ?');
$stmt->execute([$applicantId]);
$applicant = $stmt->fetch();

if (!$applicant) {
    flash('danger', 'Application not found.');
    redirect('track');
}

$paymentModel = new Payment($db);
$payment = $paymentModel->findForApplicant($applicantId, $feeType);
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $init = (new PaymentGateway($db))->initiateForApplicant($applicantId, $feeType);
    if ($init['success'] ?? false) {
        header('Location: ' . $init['redirect_url']);
        exit;
    }
    $error = $init['error'] ?? 'Payment initialization failed.';
    $payment = $paymentModel->findForApplicant($applicantId, $feeType);
}

render('public/payment_process', compact('applicant', 'payment', 'error', 'feeType', 'feeLabel', 'feeAmount'));
