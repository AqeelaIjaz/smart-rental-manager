<?php
/**
 * POST /backend/auth/forgot-password.php
 *
 * [NEW v2] Starts the password-reset flow for a Tenant, Landlord, or
 * Admin account. Generates a 6-digit OTP valid for 10 minutes, stores
 * it in `password_resets`, and "sends" it via helpers/mailer.php
 * (currently a logged placeholder — see that file for how to wire up
 * real SMTP/PHPMailer delivery).
 *
 * Request JSON:
 * {
 *   "email": "bilal.tenant@example.com",
 *   "account_type": "user"   // optional, defaults to "user". Use "admin" for admin accounts.
 * }
 *
 * -------------------------------------------------------------------
 * DEVELOPMENT-ONLY CONVENIENCE:
 * When APP_ENV === 'development' (see config/app.php), the generated
 * OTP is included directly in the JSON response so the whole flow can
 * be tested via Postman/curl without a working email server. This is
 * NEVER safe for production — switch APP_ENV to 'production' before
 * any real deployment, which removes the OTP from the response.
 * -------------------------------------------------------------------
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/mailer.php';

requireMethod('POST');

$input = getJsonInput();
requireFields($input, ['email']);

$email       = strtolower(trim($input['email']));
$accountType = isset($input['account_type']) ? sanitizeString($input['account_type']) : 'user';

if (!isValidEmail($email)) {
    sendError('Invalid email address.', 422);
}
if (!isValidAccountType($accountType)) {
    sendError('account_type must be "user" or "admin".', 422);
}

$pdo = getDbConnection();

try {
    // Confirm the account actually exists in the right table.
    $table = $accountType === 'admin' ? 'admins' : 'users';
    $stmt = $pdo->prepare("SELECT id FROM {$table} WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    if (!$stmt->fetch()) {
        sendError('No account found with this email.', 404);
    }

    // Generate a numeric OTP of length OTP_LENGTH (default 6).
    $otp = '';
    for ($i = 0; $i < OTP_LENGTH; $i++) {
        $otp .= random_int(0, 9);
    }

    $expiresAt = (new DateTime())->modify('+' . OTP_EXPIRY_MINUTES . ' minutes')->format('Y-m-d H:i:s');

    $insert = $pdo->prepare(
        'INSERT INTO password_resets (identifier, account_type, otp, expires_at, used)
         VALUES (:identifier, :account_type, :otp, :expires_at, 0)'
    );
    $insert->execute([
        'identifier'   => $email,
        'account_type' => $accountType,
        'otp'          => $otp,
        'expires_at'   => $expiresAt,
    ]);

    sendOtpEmail($email, $otp);

    $responseData = [
        'email'        => $email,
        'expires_in'   => OTP_EXPIRY_MINUTES . ' minutes',
    ];

    // DEV-ONLY: surface the OTP for testing. Removed automatically once
    // APP_ENV is set to 'production'.
    if (APP_ENV === 'development') {
        $responseData['dev_otp'] = $otp;
        $responseData['dev_note'] = 'This field only appears because APP_ENV=development. It will not appear in production.';
    }

    sendSuccess('If the account exists, a reset code has been sent to the email.', $responseData);

} catch (PDOException $e) {
    error_log('FORGOT PASSWORD ERROR: ' . $e->getMessage());
    sendError('Failed to process password reset request.', 500);
}
