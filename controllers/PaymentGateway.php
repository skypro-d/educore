<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/helpers.php';
require_once __DIR__ . '/../config/PaymentConfig.php';
require_once __DIR__ . '/../models/Payment.php';

final class PaymentGateway
{
    private PDO $db;
    private Payment $payments;
    private string $gateway;

    public function __construct(?PDO $db = null, ?string $gateway = null)
    {
        $this->db = $db ?: Database::connect();
        $this->payments = new Payment($this->db);
        $selected = strtolower($gateway ?: PaymentConfig::defaultGateway());
        $this->gateway = in_array($selected, ['paystack', 'monnify'], true) ? $selected : 'paystack';
    }

    public function initiateForApplicant(int $applicantId, string $feeType = 'admission_fee'): array
    {
        $feeType = $this->normalizeFeeType($feeType);
        $applicant = $this->findApplicant($applicantId);
        if (!$applicant) {
            return ['success' => false, 'error' => 'Application not found.'];
        }

        $payment = $this->payments->findForApplicant($applicantId, $feeType);
        if (!$payment || $payment['payment_status'] === 'Failed') {
            $reference = $this->newReference();
            $this->payments->create($applicantId, $reference, PaymentConfig::feeAmount($feeType), $this->gateway, $feeType);
            $payment = $this->payments->findByReference($reference);
        }

        if (($payment['payment_status'] ?? '') === 'Paid') {
            return [
                'success' => true,
                'redirect_url' => PaymentConfig::successUrl((string) $payment['transaction_reference']),
                'reference' => $payment['transaction_reference'],
            ];
        }

        $reference = (string) $payment['transaction_reference'];
        $amount = (float) $payment['amount'];

        return $this->gateway === 'monnify'
            ? $this->initiateMonnify($applicant, $amount, $reference, $feeType)
            : $this->initiatePaystack($applicant, $amount, $reference, $feeType);
    }

    public function verify(string $reference, ?string $gateway = null): array
    {
        $selected = strtolower($gateway ?: $this->gateway);
        return $selected === 'monnify' ? $this->verifyMonnify($reference) : $this->verifyPaystack($reference);
    }

    public function completePayment(string $reference, array $gatewayResponse, ?string $gateway = null): bool
    {
        $payment = $this->payments->findByReference($reference);
        if (!$payment) {
            return false;
        }

        $selected = strtolower($gateway ?: ($payment['gateway'] ?? $this->gateway));
        $this->payments->markPaid($reference, $selected, $gatewayResponse);
        Logger::info("Payment transaction successful", [
            'reference' => $reference,
            'gateway' => $selected,
            'applicant_id' => $payment['applicant_id'],
            'amount' => $payment['amount']
        ]);
        return true;
    }

    public function failPayment(string $reference, array $gatewayResponse = []): void
    {
        $this->payments->markFailed($reference, $gatewayResponse);
        Logger::warn("Payment transaction failed", [
            'reference' => $reference
        ]);
    }

    private function initiatePaystack(array $applicant, float $amount, string $reference, string $feeType): array
    {
        $payload = [
            'amount' => (int) round($amount * 100),
            'email' => $applicant['parent_email'],
            'reference' => $reference,
            'callback_url' => PaymentConfig::successUrl($reference),
            'metadata' => [
                'applicant_id' => (int) $applicant['id'],
                'application_number' => $applicant['application_number'],
                'name' => trim($applicant['first_name'] . ' ' . $applicant['last_name']),
                'type' => $feeType,
            ],
        ];

        $result = $this->postJson('https://api.paystack.co/transaction/initialize', $payload, [
            'Authorization: Bearer ' . PaymentConfig::paystackSecretKey(),
            'Content-Type: application/json',
        ]);

        if (($result['status'] ?? false) && !empty($result['data']['authorization_url'])) {
            return [
                'success' => true,
                'redirect_url' => $result['data']['authorization_url'],
                'reference' => $reference,
            ];
        }

        return ['success' => false, 'error' => $result['message'] ?? 'Payment initialization failed.'];
    }

    private function initiateMonnify(array $applicant, float $amount, string $reference, string $feeType): array
    {
        $token = $this->monnifyToken();
        if ($token === '') {
            return ['success' => false, 'error' => 'Monnify credentials are not configured correctly.'];
        }

        $payload = [
            'amount' => $amount,
            'customerName' => trim($applicant['first_name'] . ' ' . $applicant['last_name']),
            'customerEmail' => $applicant['parent_email'],
            'paymentReference' => $reference,
            'paymentDescription' => PaymentConfig::feeLabel($feeType),
            'currencyCode' => PaymentConfig::currency(),
            'contractCode' => PaymentConfig::monnifyContractCode(),
            'redirectUrl' => PaymentConfig::successUrl($reference),
            'metadata' => [
                'applicant_id' => (int) $applicant['id'],
                'application_number' => $applicant['application_number'],
                'type' => $feeType,
            ],
        ];

        $result = $this->postJson(PaymentConfig::monnifyBaseUrl() . '/api/v1/merchant/transactions/init-transaction', $payload, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ]);

        if (($result['requestSuccessful'] ?? false) && !empty($result['responseBody']['checkoutUrl'])) {
            return [
                'success' => true,
                'redirect_url' => $result['responseBody']['checkoutUrl'],
                'reference' => $reference,
            ];
        }

        return ['success' => false, 'error' => $result['responseMessage'] ?? 'Payment initialization failed.'];
    }

    private function verifyPaystack(string $reference): array
    {
        $result = $this->getJson('https://api.paystack.co/transaction/verify/' . rawurlencode($reference), [
            'Authorization: Bearer ' . PaymentConfig::paystackSecretKey(),
        ]);

        $success = (bool) (($result['status'] ?? false) && (($result['data']['status'] ?? '') === 'success'));
        return ['success' => $success, 'data' => $result['data'] ?? $result, 'gateway' => 'paystack'];
    }

    private function verifyMonnify(string $reference): array
    {
        $token = $this->monnifyToken();
        if ($token === '') {
            return ['success' => false, 'data' => ['message' => 'Monnify credentials missing.'], 'gateway' => 'monnify'];
        }

        $result = $this->getJson(PaymentConfig::monnifyBaseUrl() . '/api/v2/transactions/' . rawurlencode($reference), [
            'Authorization: Bearer ' . $token,
        ]);

        $status = $result['responseBody']['paymentStatus'] ?? $result['responseBody']['transactionStatus'] ?? '';
        return [
            'success' => ($result['requestSuccessful'] ?? false) && in_array($status, ['PAID', 'SUCCESS'], true),
            'data' => $result['responseBody'] ?? $result,
            'gateway' => 'monnify',
        ];
    }

    private function monnifyToken(): string
    {
        $apiKey = PaymentConfig::monnifyApiKey();
        $secret = PaymentConfig::monnifySecretKey();
        if ($apiKey === '' || $secret === '') {
            return '';
        }

        $ch = curl_init(PaymentConfig::monnifyBaseUrl() . '/api/v1/auth/login');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Authorization: Basic ' . base64_encode($apiKey . ':' . $secret)],
            CURLOPT_TIMEOUT => 20,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $payload = json_decode((string) $response, true);
        return (string) ($payload['responseBody']['accessToken'] ?? '');
    }

    private function findApplicant(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM applicants WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    private function newReference(): string
    {
        return 'PAY' . date('YmdHis') . random_int(1000, 9999);
    }

    private function normalizeFeeType(string $feeType): string
    {
        return in_array($feeType, ['admission_fee', 'acceptance_fee', 'enrollment_fee'], true) ? $feeType : 'admission_fee';
    }

    private function postJson(string $url, array $payload, array $headers): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 25,
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['status' => false, 'message' => $error];
        }

        return json_decode((string) $response, true) ?: ['status' => false, 'message' => 'Invalid gateway response.'];
    }

    private function getJson(string $url, array $headers): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 25,
        ]);
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['status' => false, 'message' => $error];
        }

        return json_decode((string) $response, true) ?: ['status' => false, 'message' => 'Invalid gateway response.'];
    }
}
