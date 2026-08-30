<?php
declare(strict_types=1);

final class Validator
{
    /**
     * Validate email format.
     */
    public static function email(string $email): bool
    {
        return filter_var(trim($email), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate phone number (simple validation for digits and common symbols like +, -, spaces, parentheses).
     */
    public static function phone(string $phone): bool
    {
        $cleaned = preg_replace('/[+\-\s()]/', '', $phone);
        return ctype_digit($cleaned) && strlen($cleaned) >= 7 && strlen($cleaned) <= 15;
    }

    /**
     * Sanitize string (removes tags, escapes special chars, prevents basic XSS).
     */
    public static function sanitizeString(string $value): string
    {
        return htmlspecialchars(trim(strip_tags($value)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Check if a field is set and not empty.
     */
    public static function required(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }
        if (is_string($value)) {
            return trim($value) !== '';
        }
        if (is_array($value)) {
            return !empty($value);
        }
        return true;
    }

    /**
     * Validate string length.
     */
    public static function length(string $value, int $min, int $max): bool
    {
        $len = mb_strlen(trim($value));
        return $len >= $min && $len <= $max;
    }

    /**
     * Validate file upload size and MIME type.
     */
    public static function file(array $file, array $allowedMimeTypes, int $maxSizeBytes): bool
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if ($file['size'] > $maxSizeBytes) {
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        
        return in_array($mime, $allowedMimeTypes, true);
    }

    /**
     * Validate standard types.
     */
    public static function integer(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    public static function float(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false;
    }

    public static function boolean(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== null;
    }
}
