<?php
/**
 * =====================================================================
 * EMAIL / OTP DELIVERY — INTEGRATION POINT
 * =====================================================================
 * This backend does NOT send real emails yet — no SMTP/PHPMailer
 * credentials are configured here, per the "no fake external services"
 * rule. This file is the single, clean place to wire up real email
 * delivery later.
 *
 * -------------------------------------------------------------------
 * >>> TO CONNECT REAL EMAIL DELIVERY (PHPMailer + SMTP) <<<
 * 1. composer require phpmailer/phpmailer   (or download manually)
 * 2. Add SMTP credentials to a config file that is NEVER committed to
 *    git and NEVER exposed to the frontend, e.g. backend/config/mail.php:
 *      define('SMTP_HOST', 'smtp.yourprovider.com');
 *      define('SMTP_USER', 'YOUR_API_KEY_HERE');
 *      define('SMTP_PASS', 'YOUR_API_KEY_HERE');
 *      define('SMTP_PORT', 587);
 * 3. Replace the body of sendOtpEmail() below with a real PHPMailer send.
 * -------------------------------------------------------------------
 *
 * Until then, sendOtpEmail() logs the OTP server-side only (so you can
 * see it in your PHP error log during development) and returns true to
 * simulate a successful send. It does NOT expose the OTP to the caller
 * — see config/app.php + auth/forgot-password.php for how OTPs are
 * surfaced for *development testing only*, behind an explicit
 * APP_ENV === 'development' check.
 */

if (!function_exists('sendOtpEmail')) {
    /**
     * Sends (or, currently, logs) a password-reset OTP to the given email.
     *
     * @param string $toEmail
     * @param string $otp
     * @return bool true if "sent" (always true in this placeholder)
     */
    function sendOtpEmail(string $toEmail, string $otp): bool
    {
        // Placeholder — replace with a real PHPMailer/SMTP call.
        error_log("[DEV MAILER] Password reset OTP for {$toEmail}: {$otp} (valid " . OTP_EXPIRY_MINUTES . " minutes)");
        return true;
    }
}
