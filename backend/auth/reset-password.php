<?php
/**
 * POST /backend/auth/reset-password.php
 *
 * [NEW v2] Completes the password-reset flow: verifies the OTP sent by
 * forgot-password.php, and if valid + unused + not expired, updates
 * the account's password (Tenant/Landlord in `users`, or Admin in
 * `admins`).
 *
 * Request JSON:
 * {
 *   "email": "bilal.tenant@example.com",
 *   "otp": "123456",
 *   "new_password": "NewPass123",
 *   "account_type": "user"   // optional, defaults to "user"
 * }
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';

requireMethod('POST');

$input = getJsonInput();
requireFields($input, ['email', 'otp', 'new_password']);

$email        = strtolower(trim($input['email']));
$otp          = trim($input['otp']);
$newPassword  = $input['new_password'];
$accountType  = isset($input['account_type']) ? sanitizeString($input['account_type']) : 'user';

if (!isValidEmail($email)) {
    sendError('Invalid email address.', 422);
}
if (!isValidOtp($otp)) {
    sendError('Invalid OTP format. Must be ' . OTP_LENGTH . ' digits.', 422);
}
if (!isValidPassword($newPassword)) {
    sendError('New password must be at least 8 characters long.', 422);
}
if (!isValidAccountType($accountType)) {
    sendError('account_type must be "user" or "admin".', 422);
}

$pdo = getDbConnection();

try {
    $pdo->beginTransaction();

    // Find the most recent matching, unused, unexpired OTP for this email.
    $stmt = $pdo->prepare(
        'SELECT id FROM password_resets
         WHERE identifier = :email
           AND account_type = :account_type
           AND otp = :otp
           AND used = 0
           AND expires_at >= NOW()
         ORDER BY created_at DESC
         LIMIT 1
         FOR UPDATE'
    );
    $stmt->execute([
        'email'        => $email,
        'account_type' => $accountType,
        'otp'          => $otp,
    ]);
    $resetRow = $stmt->fetch();

    if (!$resetRow) {
        $pdo->rollBack();
        sendError('Invalid or expired OTP. Please request a new one.', 400);
    }

    // Mark this OTP as used immediately so it cannot be replayed.
    $markUsed = $pdo->prepare('UPDATE password_resets SET used = 1 WHERE id = :id');
    $markUsed->execute(['id' => $resetRow['id']]);

    // Update the actual account password in the correct table.
    $table = $accountType === 'admin' ? 'admins' : 'users';
    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    $updateStmt = $pdo->prepare("UPDATE {$table} SET password = :password WHERE email = :email");
    $updateStmt->execute(['password' => $hashedPassword, 'email' => $email]);

    if ($updateStmt->rowCount() === 0) {
        // Extremely unlikely (account existed at forgot-password time),
        // but guard against a deleted account in between.
        $pdo->rollBack();
        sendError('Account not found.', 404);
    }

    $pdo->commit();

    sendSuccess('Password has been reset successfully. You can now log in with your new password.');

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('RESET PASSWORD ERROR: ' . $e->getMessage());
    sendError('Failed to reset password.', 500);
}
