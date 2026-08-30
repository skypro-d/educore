<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/PaymentConfig.php';
require_once __DIR__ . '/../controllers/PaymentGateway.php';
require_once __DIR__ . '/../controllers/NotificationController.php';
require_once __DIR__ . '/../models/Payment.php';

$input = file_get_contents('php://input') ?: '';
$signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';
$expected = hash_hmac('sha512', $input, PaymentConfig::paystackSecretKey());

if (!hash_equals($expected, $signature)) {
    http_response_code(401);
    exit('Unauthorized');
}

$event = json_decode($input, true) ?: [];
if (($event['event'] ?? '') === 'charge.success') {
    $reference = (string) ($event['data']['reference'] ?? '');
    if ($reference !== '') {
        $db = Database::connect();
        $gateway = new PaymentGateway($db, 'paystack');
        $verification = $gateway->verify($reference, 'paystack');
        if ($verification['success']) {
            $gateway->completePayment($reference, (array) $verification['data'], 'paystack');
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
