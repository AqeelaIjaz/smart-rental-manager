<?php
/**
 * =====================================================================
 * VALIDATION HELPER
 * =====================================================================
 * Small, dependency-free validation utilities used across endpoints.
 */

require_once __DIR__ . '/../config/app.php'; // OTP_LENGTH, OTP_EXPIRY_MINUTES, APP_ENV

if (!function_exists('requireFields')) {
    /**
     * Ensures all $fields exist and are non-empty (non-whitespace) in
     * $input. Sends a 422 error response and exits if any are missing.
     *
     * @param array $input  Associative array (usually from getJsonInput())
     * @param array $fields List of required field names
     */
    function requireFields(array $input, array $fields): void
    {
        $missing = [];
        foreach ($fields as $field) {
            if (!array_key_exists($field, $input) ||
                (is_string($input[$field]) && trim($input[$field]) === '') ||
                $input[$field] === null) {
                $missing[] = $field;
            }
        }
        if (!empty($missing)) {
            sendError('Missing required field(s): ' . implode(', ', $missing), 422);
        }
    }
}

if (!function_exists('isValidEmail')) {
    function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('isValidPhone')) {
    /**
     * Simple phone validation: digits, optional leading +, 7-15 digits.
     * Kept intentionally loose to support Pakistani and international formats.
     */
    function isValidPhone(string $phone): bool
    {
        return (bool) preg_match('/^\+?[0-9]{7,15}$/', $phone);
    }
}

if (!function_exists('isValidRole')) {
    /**
     * [UPDATED v2] Public signup only allows tenant/landlord. Admin
     * accounts are no longer created through signup.php — they live in
     * the separate `admins` table and are created only by an existing
     * admin via backend/admin/create-admin.php.
     */
    function isValidRole(string $role): bool
    {
        return in_array($role, ['tenant', 'landlord'], true);
    }
}

if (!function_exists('isValidOtp')) {
    function isValidOtp(string $otp): bool
    {
        return (bool) preg_match('/^[0-9]{' . OTP_LENGTH . '}$/', $otp);
    }
}

if (!function_exists('isValidAccountType')) {
    function isValidAccountType(string $type): bool
    {
        return in_array($type, ['user', 'admin'], true);
    }
}

if (!function_exists('isValidLanguage')) {
    function isValidLanguage(string $lang): bool
    {
        return in_array($lang, ['en', 'ur', 'roman_ur'], true);
    }
}

if (!function_exists('isValidPassword')) {
    /**
     * Minimum 8 characters. Kept simple for a university project but
     * still enforces a basic minimum strength requirement.
     */
    function isValidPassword(string $password): bool
    {
        return strlen($password) >= 8;
    }
}

if (!function_exists('sanitizeString')) {
    function sanitizeString(string $value): string
    {
        return trim(strip_tags($value));
    }
}

if (!function_exists('isPositiveNumber')) {
    function isPositiveNumber($value): bool
    {
        return is_numeric($value) && (float)$value > 0;
    }
}

if (!function_exists('isValidDate')) {
    function isValidDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
}
