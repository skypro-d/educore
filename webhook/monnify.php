<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../controllers/PaymentGateway.php';
require_once __DIR__ . '/../controllers/NotificationController.php';
require_once __DIR__ . '/../models/Payment.php';

$input = file_get_contents('php://input') ?: '';
$secret = PaymentConfig::monnifyWebhookSecret();
$signature = $_SERVER['HTTP_MONNIFY_SIGNATURE'] ?? $_SERVER['HTTP_X_MONNIFY_SIGNATURE'] ?? '';

if ($secret !== '') {
    $expected = hash_hmac('sha512', $input, $secret);
    if ($signature === '' || !hash_equals($expected, $signature)) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => 'Invalid webhook signature']);
        exit;
    }
}

$event = json_decode($input, true) ?: [];
$eventType = (string) ($event['eventType'] ?? $event['event'] ?? '');

if (in_array($eventType, ['SUCCESSFUL_TRANSACTION', 'SUCCESS'], true)) {
    $data = $event['eventData'] ?? $event['data'] ?? [];
    $reference = (string) ($data['paymentReference'] ?? $data['transactionReference'] ?? '');
    if ($reference !== '') {
        $db = Database::connect();
        $gateway = new PaymentGateway($db, 'monnify');
        $verification = $gateway->verify($reference, 'monnify');
        if ($verification['success']) {
            $gateway->completePayment($reference, (array) $verification['data'], 'monnify');
            $payment = (new Payment($db))->findByReference($reference);
            if ($payment) {
                (new NotificationController($db))->sendPaymentReceipt((int) $payment['applicant_id'], $reference);
            }
        }
    }
}

http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['status' => 'success']);
