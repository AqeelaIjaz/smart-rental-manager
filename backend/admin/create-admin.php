<?php
/**
 * POST /backend/admin/create-admin.php
 *
 * [NEW v2] Creates a new Admin account. Only an existing, logged-in
 * Admin may call this — this is how the system grows beyond the single
 * bootstrap admin seeded in the database, without ever hardcoding
 * credentials in code.
 *
 * Request JSON:
 * {
 *   "name": "New Admin",
 *   "email": "new.admin@example.com",
 *   "phone": "03001112222",
 *   "password": "Test12345"
 * }
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('POST');
$currentUser = requireLogin();
requireRole($currentUser, ['admin']);

$input = getJsonInput();
requireFields($input, ['name', 'email', 'phone', 'password']);

$name     = sanitizeString($input['name']);
$email    = strtolower(trim($input['email']));
$phone    = sanitizeString($input['phone']);
$password = $input['password'];

if (strlen($name) < 2) {
    sendError('Name must be at least 2 characters long.', 422);
}
if (!isValidEmail($email)) {
    sendError('Invalid email address.', 422);
}
if (!isValidPhone($phone)) {
    sendError('Invalid phone number format.', 422);
}
if (!isValidPassword($password)) {
    sendError('Password must be at least 8 characters long.', 422);
}

$pdo = getDbConnection();

try {
    $stmt = $pdo->prepare('SELECT id FROM admins WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        sendError('An admin account with this email already exists.', 409);
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare(
        'INSERT INTO admins (name, email, phone, password, status)
         VALUES (:name, :email, :phone, :password, "active")'
    );
    $stmt->execute([
        'name'     => $name,
        'email'    => $email,
        'phone'    => $phone,
        'password' => $hashedPassword,
    ]);

    sendSuccess('Admin account created successfully.', [
        'id'    => (int) $pdo->lastInsertId(),
        'name'  => $name,
        'email' => $email,
    ], 201);

} catch (PDOException $e) {
    error_log('CREATE ADMIN ERROR: ' . $e->getMessage());
    sendError('Failed to create admin account.', 500);
}
