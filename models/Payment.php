<?php
final class Payment
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(int $applicantId, string $reference, float $amount, string $gateway = 'paystack', string $feeType = 'admission_fee'): void
    {
        $stmt = $this->db->prepare('INSERT INTO payments (applicant_id, transaction_reference, amount, payment_status, gateway, fee_type, created_at) VALUES (?, ?, ?, "Pending", ?, ?, NOW())');
        $stmt->execute([$applicantId, $reference, $amount, $gateway, $feeType]);
    }

    public function markPaid(string $reference, string $gateway = 'paystack', array $response = []): void
    {
        $stmt = $this->db->prepare('UPDATE payments SET payment_status="Paid", payment_date=NOW(), gateway=?, gateway_response=?, updated_at=NOW() WHERE transaction_reference=?');
        $stmt->execute([$gateway, json_encode($response), $reference]);
    }

    public function markFailed(string $reference, array $response = []): void
    {
        $stmt = $this->db->prepare('UPDATE payments SET payment_status="Failed", gateway_response=?, updated_at=NOW() WHERE transaction_reference=?');
        $stmt->execute([json_encode($response), $reference]);
    }

    public function findForApplicant(int $applicantId, string $feeType = ''): ?array
    {
        if ($feeType !== '') {
            $stmt = $this->db->prepare('SELECT * FROM payments WHERE applicant_id=? AND fee_type=? ORDER BY id DESC LIMIT 1');
            $stmt->execute([$applicantId, $feeType]);
            return $stmt->fetch() ?: null;
        }

        $stmt = $this->db->prepare('SELECT * FROM payments WHERE applicant_id=? ORDER BY id DESC LIMIT 1');
        $stmt->execute([$applicantId]);
        return $stmt->fetch() ?: null;
    }

    public function allForApplicant(int $applicantId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM payments WHERE applicant_id=? ORDER BY created_at DESC, id DESC');
        $stmt->execute([$applicantId]);
        return $stmt->fetchAll();
    }

    public function findByReference(string $reference): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM payments WHERE transaction_reference=? LIMIT 1');
        $stmt->execute([$reference]);
        return $stmt->fetch() ?: null;
    }

    public function verifyWithPaystack(string $reference): bool
    {
        $ch = curl_init('https://api.paystack.co/transaction/verify/' . rawurlencode($reference));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . gateway_setting('paystack_secret_key', PAYSTACK_SECRET_KEY)],
            CURLOPT_TIMEOUT => 15,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);
        if (!$response) {
            return false;
        }
        $payload = json_decode($response, true);
        return (bool) (($payload['status'] ?? false) && (($payload['data']['status'] ?? '') === 'success'));
    }
}
