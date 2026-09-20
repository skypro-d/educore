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

    public function initiateForStudentFee(int $applicantId, int $feeStructureId, float $amount, string $payerType = 'student', ?string $gateway = null): array
    {
        $selectedGateway = strtolower($gateway ?: $this->gateway);
        $selectedGateway = in_array($selectedGateway, ['paystack', 'monnify'], true) ? $selectedGateway : 'paystack';

        $applicant = $this->findApplicant($applicantId);
        if (!$applicant) {
            return ['success' => false, 'error' => 'Student record not found.'];
        }

        $stmtFs = $this->db->prepare('SELECT * FROM fee_structures WHERE id = ? AND is_active = 1 LIMIT 1');
        $stmtFs->execute([$feeStructureId]);
        $feeStructure = $stmtFs->fetch();
        if (!$feeStructure) {
            return ['success' => false, 'error' => 'Fee structure item not found or is currently inactive.'];
        }

        // Calculate total amount already paid towards this fee structure
        $stmtPaid = $this->db->prepare(
            "SELECT COALESCE(SUM(amount_paid), 0) FROM student_fee_payments 
             WHERE applicant_id = ? AND fee_structure_id = ? AND payment_status IN ('Paid', 'Partial', 'Manual')"
        );
        $stmtPaid->execute([$applicantId, $feeStructureId]);
        $alreadyPaid = (float) $stmtPaid->fetchColumn();

        $standardFee = (float) $feeStructure['amount'];
        $currentBalance = max(0.00, $standardFee - $alreadyPaid);

        if ($currentBalance <= 0.00 && $standardFee > 0.00) {
            return ['success' => false, 'error' => 'This fee has already been fully settled.'];
        }

        $payAmount = ($amount > 0.00) ? min($amount, $currentBalance) : $currentBalance;
        if ($payAmount <= 0.00) {
            return ['success' => false, 'error' => 'Please provide a valid payment amount greater than zero.'];
        }

        $reference = 'FEE-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(3)));
        $remainingBalance = max(0.00, $currentBalance - $payAmount);
        $notes = "Online fee payment initiated via " . ucfirst($selectedGateway) . " by " . ucfirst($payerType);

        // Record pending student_fee_payments entry
        $stmtIns = $this->db->prepare(
            "INSERT INTO student_fee_payments 
             (applicant_id, fee_structure_id, amount_paid, balance, payment_reference, payment_status, payment_method, notes, created_at)
             VALUES (?, ?, ?, ?, ?, 'Pending', ?, ?, NOW())"
        );
        $stmtIns->execute([
            $applicantId,
            $feeStructureId,
            $payAmount,
            $remainingBalance,
            $reference,
            $selectedGateway,
            $notes
        ]);

        $callbackUrl = PaymentConfig::absoluteUrl('payment/fee-success.php?reference=' . rawurlencode($reference));

        if ($selectedGateway === 'monnify') {
            $token = $this->monnifyToken();
            if ($token === '') {
                return ['success' => false, 'error' => 'Monnify payment gateway credentials are not configured properly.'];
            }

            $payload = [
                'amount'             => $payAmount,
                'customerName'       => trim($applicant['first_name'] . ' ' . $applicant['last_name']),
                'customerEmail'      => !empty($applicant['parent_email']) ? $applicant['parent_email'] : 'student@' . ($_SERVER['HTTP_HOST'] ?? 'school.portal'),
                'paymentReference'   => $reference,
                'paymentDescription' => $feeStructure['fee_name'] . ' (' . $feeStructure['term'] . ' Term)',
                'currencyCode'       => PaymentConfig::currency(),
                'contractCode'       => PaymentConfig::monnifyContractCode(),
                'redirectUrl'        => $callbackUrl,
                'metadata'           => [
                    'payment_category' => 'student_fee',
                    'applicant_id'     => $applicantId,
                    'fee_structure_id' => $feeStructureId,
                    'payer_type'       => $payerType,
                    'reference'        => $reference
                ],
            ];

            $result = $this->postJson(PaymentConfig::monnifyBaseUrl() . '/api/v1/merchant/transactions/init-transaction', $payload, [
                'Authorization: Bearer ' . $token,
                'Content-Type: application/json',
            ]);

            if (($result['requestSuccessful'] ?? false) && !empty($result['responseBody']['checkoutUrl'])) {
                return [
                    'success'      => true,
                    'redirect_url' => $result['responseBody']['checkoutUrl'],
                    'reference'    => $reference,
                ];
            }

            return ['success' => false, 'error' => $result['responseMessage'] ?? 'Monnify initialization failed.'];
        }

        // Default: Paystack
        $payload = [
            'amount'       => (int) round($payAmount * 100),
            'email'        => !empty($applicant['parent_email']) ? $applicant['parent_email'] : 'student@' . ($_SERVER['HTTP_HOST'] ?? 'school.portal'),
            'reference'    => $reference,
            'callback_url' => $callbackUrl,
            'metadata'     => [
                'payment_category' => 'student_fee',
                'applicant_id'     => $applicantId,
                'fee_structure_id' => $feeStructureId,
                'payer_type'       => $payerType,
                'reference'        => $reference,
                'name'             => trim($applicant['first_name'] . ' ' . $applicant['last_name']),
                'fee_name'         => $feeStructure['fee_name']
            ],
        ];

        $result = $this->postJson('https://api.paystack.co/transaction/initialize', $payload, [
            'Authorization: Bearer ' . PaymentConfig::paystackSecretKey(),
            'Content-Type: application/json',
        ]);

        if (($result['status'] ?? false) && !empty($result['data']['authorization_url'])) {
            return [
                'success'      => true,
                'redirect_url' => $result['data']['authorization_url'],
                'reference'    => $reference,
            ];
        }

        return ['success' => false, 'error' => $result['message'] ?? 'Paystack initialization failed.'];
    }

    public function completeFeePayment(string $reference, array $gatewayResponse, ?string $gateway = null): bool
    {
        $stmt = $this->db->prepare("SELECT * FROM student_fee_payments WHERE payment_reference = ? LIMIT 1");
        $stmt->execute([$reference]);
        $payment = $stmt->fetch();

        if (!$payment) {
            return false;
        }

        // If already paid, return true idempotently
        if (in_array($payment['payment_status'], ['Paid', 'Partial', 'Manual'], true) && !empty($payment['receipt_number'])) {
            return true;
        }

        $selectedGateway = strtolower($gateway ?: ($payment['payment_method'] ?? $this->gateway));
        $rcpt = generate_receipt_number($this->db);
        $status = ((float)$payment['balance'] <= 0.00) ? 'Paid' : 'Partial';

        $stmtUpd = $this->db->prepare(
            "UPDATE student_fee_payments 
             SET payment_status = ?, 
                 payment_date = NOW(), 
                 receipt_number = ?, 
                 payment_method = ?, 
                 notes = CONCAT(COALESCE(notes, ''), '\nPayment confirmed online via ', ?, ' (Ref: ', ?, ')')
             WHERE id = ?"
        );
        $stmtUpd->execute([
            $status,
            $rcpt,
            $selectedGateway,
            ucfirst($selectedGateway),
            $reference,
            $payment['id']
        ]);

        // Auto-create parent portal account if not already created
        $this->autoCreateParentAccount((int) $payment['applicant_id']);

        // Send email receipt to parent
        try {
            (new NotificationController($this->db))->sendFeePaymentReceipt((int) $payment['applicant_id'], (int) $payment['id']);
        } catch (Throwable $e) {
            Logger::error('Failed to send fee payment receipt email: ' . $e->getMessage());
        }

        // Create in-app notifications
        try {
            $stmtFee = $this->db->prepare("SELECT fee_name, term FROM fee_structures WHERE id = ?");
            $stmtFee->execute([(int) $payment['fee_structure_id']]);
            $feeInfo = $stmtFee->fetch();
            $feeTitle = $feeInfo ? ($feeInfo['fee_name'] . ' (' . $feeInfo['term'] . ' Term)') : 'School Fee';

            $formattedAmt = number_format((float) $payment['amount_paid'], 2);
            $msg = "A fee payment of ₦{$formattedAmt} for {$feeTitle} was successfully processed online. Receipt: {$rcpt}";

            // Notify parent
            $stmtP = $this->db->prepare("SELECT id FROM parent_accounts WHERE applicant_id = ? LIMIT 1");
            $stmtP->execute([(int) $payment['applicant_id']]);
            $parentId = $stmtP->fetchColumn();
            if ($parentId) {
                create_notification($this->db, 'parent', (int) $parentId, 'Fee Payment Successful', $msg);
            }

            // Notify student
            $stmtS = $this->db->prepare("SELECT id FROM student_accounts WHERE applicant_id = ? LIMIT 1");
            $stmtS->execute([(int) $payment['applicant_id']]);
            $studentId = $stmtS->fetchColumn();
            if ($studentId) {
                create_notification($this->db, 'student', (int) $studentId, 'Fee Payment Successful', $msg);
            }
        } catch (Throwable $e) {
            Logger::warn('Failed to insert in-app fee notification: ' . $e->getMessage());
        }

        Logger::info("Student fee payment confirmed online", [
            'reference'        => $reference,
            'receipt'          => $rcpt,
            'applicant_id'     => $payment['applicant_id'],
            'fee_structure_id' => $payment['fee_structure_id'],
            'amount'           => $payment['amount_paid'],
            'status'           => $status
        ]);

        return true;
    }

    public function findFeePaymentByReference(string $reference): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM student_fee_payments WHERE payment_reference = ? LIMIT 1");
        $stmt->execute([$reference]);
        return $stmt->fetch() ?: null;
    }

    private function autoCreateParentAccount(int $applicantId): void
    {
        $stmt = $this->db->prepare("SELECT id FROM parent_accounts WHERE applicant_id = ? LIMIT 1");
        $stmt->execute([$applicantId]);
        if ($stmt->fetch()) {
            return;
        }

        $stmtApp = $this->db->prepare("SELECT * FROM applicants WHERE id = ? LIMIT 1");
        $stmtApp->execute([$applicantId]);
        $app = $stmtApp->fetch();
        if ($app && !empty($app['parent_phone']) && !empty($app['parent_email'])) {
            $hash = password_hash($app['parent_phone'], PASSWORD_BCRYPT);
            $this->db->prepare(
                "INSERT INTO parent_accounts (applicant_id, phone, email, password_hash, created_at) 
                 VALUES (?, ?, ?, ?, NOW())"
            )->execute([$applicantId, $app['parent_phone'], $app['parent_email'], $hash]);
        }
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
