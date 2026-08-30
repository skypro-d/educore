<?php
declare(strict_types=1);

final class PaymentConfig
{
    public static function defaultGateway(): string
    {
        $gateway = strtolower(platform_gateway_setting('active_payment_gateway', 'paystack'));
        return in_array($gateway, ['paystack', 'monnify', 'flutterwave'], true) ? $gateway : 'paystack';
    }

    public static function admissionFee(): float
    {
        return (float) setting('admission_fee', '5000');
    }

    public static function acceptanceFee(): float
    {
        return (float) setting('acceptance_fee', '25000');
    }

    public static function enrollmentFee(): float
    {
        return (float) setting('enrollment_fee', '50000');
    }

    public static function feeAmount(string $feeType): float
    {
        switch ($feeType) {
            case 'acceptance_fee':
                return self::acceptanceFee();
            case 'enrollment_fee':
                return self::enrollmentFee();
            default:
                return self::admissionFee();
        }
    }

    public static function feeLabel(string $feeType): string
    {
        switch ($feeType) {
            case 'acceptance_fee':
                return 'Acceptance Fee';
            case 'enrollment_fee':
                return 'Enrollment Fee';
            default:
                return 'Admission Fee';
        }
    }

    public static function currency(): string
    {
        return platform_gateway_setting('payment_currency', 'NGN');
    }

    public static function environment(): string
    {
        $environment = strtolower(platform_gateway_setting('payment_environment', PAYMENT_ENVIRONMENT));
        return in_array($environment, ['test', 'live'], true) ? $environment : 'test';
    }

    public static function isTestMode(): bool
    {
        return self::environment() === 'test';
    }

    public static function paystackPublicKey(): string
    {
        return platform_gateway_setting('paystack_public_key', PAYSTACK_PUBLIC_KEY);
    }

    public static function paystackSecretKey(): string
    {
        return platform_gateway_setting('paystack_secret_key', PAYSTACK_SECRET_KEY);
    }

    public static function monnifyApiKey(): string
    {
        return platform_gateway_setting('monnify_api_key', MONNIFY_API_KEY);
    }

    public static function monnifySecretKey(): string
    {
        return platform_gateway_setting('monnify_secret_key', MONNIFY_SECRET_KEY);
    }

    public static function monnifyContractCode(): string
    {
        return platform_gateway_setting('monnify_contract_code', MONNIFY_CONTRACT_CODE);
    }

    public static function monnifyBaseUrl(): string
    {
        return self::isTestMode() ? 'https://sandbox.monnify.com' : 'https://api.monnify.com';
    }

    public static function successUrl(string $reference): string
    {
        return self::absoluteUrl('payment/success.php?reference=' . rawurlencode($reference));
    }

    public static function absoluteUrl(string $path = ''): string
    {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $scheme . '://' . $host . url($path);
    }
}
