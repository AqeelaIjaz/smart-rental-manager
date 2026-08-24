<?php
/**
 * backend/demo_seed.php
 *
 * OPTIONAL helper script. The main database/smart_rental_manager.sql
 * file already includes demo users, but bcrypt hashes are randomized
 * per-generation, so if the pre-filled hash in the .sql file does not
 * verify on your machine's PHP build, run this script once to
 * generate correct, verifiable password hashes for all demo accounts.
 *
 * USAGE:
 *   1. Import database/smart_rental_manager.sql first (creates tables + demo rows).
 *   2. Run this script once via browser or CLI:
 *        http://localhost/smart-rental-manager/backend/demo_seed.php
 *      or
 *        php backend/demo_seed.php
 *   3. It updates all demo users' passwords to a fresh bcrypt hash of
 *      "Test12345".
 *
 * >>> DEVELOPMENT ONLY — do not run this against a production database. <<<
 */

require_once __DIR__ . '/config/database.php';

$pdo = getDbConnection();
$demoPassword = 'Test12345';
$hash = password_hash($demoPassword, PASSWORD_BCRYPT);

// Tenant/Landlord demo accounts (users table)
$userEmails = [
    'ali.landlord@example.com',
    'sara.landlord@example.com',
    'bilal.tenant@example.com',
    'ayesha.tenant@example.com',
    'usman.tenant@example.com',
];

// [NEW v2] Admin demo accounts now live in the separate `admins` table.
$adminEmails = [
    'admin@example.com',
    'inactive.admin@example.com',
];

$updated = 0;

$userStmt = $pdo->prepare('UPDATE users SET password = :password WHERE email = :email');
foreach ($userEmails as $email) {
    $userStmt->execute(['password' => $hash, 'email' => $email]);
    if ($userStmt->rowCount() > 0) {
        $updated++;
    }
}

$adminStmt = $pdo->prepare('UPDATE admins SET password = :password WHERE email = :email');
foreach ($adminEmails as $email) {
    $adminStmt->execute(['password' => $hash, 'email' => $email]);
    if ($adminStmt->rowCount() > 0) {
        $updated++;
    }
}

$isCli = (php_sapi_name() === 'cli');
$message = "Demo passwords reset for {$updated} account(s) (users + admins). All demo accounts now use password: {$demoPassword}";

if ($isCli) {
    echo $message . "\n";
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => $message]);
}
