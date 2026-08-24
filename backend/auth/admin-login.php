<?php
/**
 * POST /backend/auth/admin-login.php
 *
 * [NEW v2] Authenticates an Admin against the dedicated `admins` table
 * (not `users`). This replaces a single hardcoded admin login — any
 * admin row with status = 'active' can log in here.
 *
 * Request JSON:
 * {
 *   "email": "admin@example.com",
 *   "password": "Test12345"
 * }
 *
 * On success, starts a session identical in shape to the regular
 * login.php response so the frontend can treat "role":"admin" the same
 * way regardless of which login endpoint was used.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../middleware/auth.php'; // starts the session

requireMethod('POST');

$input = getJsonInput();
requireFields($input, ['email', 'password']);

$email    = strtolower(trim($input['email']));
$password = $input['password'];

if (!isValidEmail($email)) {
    sendError('Invalid email address.', 422);
}

$pdo = getDbConnection();

try {
    $stmt = $pdo->prepare('SELECT id, name, email, password, status FROM admins WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $admin = $stmt->fetch();

    if (!$admin || !password_verify($password, $admin['password'])) {
        sendError('Invalid email or password.', 401);
    }

    if ($admin['status'] !== 'active') {
        sendError('This admin account has been deactivated.', 403);
    }

    session_regenerate_id(true);

    $_SESSION['user_id']  = $admin['id'];
    $_SESSION['role']     = 'admin';
    $_SESSION['name']     = $admin['name'];
    $_SESSION['email']    = $admin['email'];
    $_SESSION['language'] = 'en';

    sendSuccess('Admin login successful', [
        'id'       => (int) $admin['id'],
        'name'     => $admin['name'],
        'role'     => 'admin',
        'language' => 'en',
    ]);

} catch (PDOException $e) {
    error_log('ADMIN LOGIN ERROR: ' . $e->getMessage());
    sendError('Login failed. Please try again later.', 500);
}
