<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';

requireMethod('POST');

$input = getJsonInput();
requireFields($input, ['name', 'phone', 'email', 'password', 'role', 'language']);

$name     = sanitizeString($input['name']);
$phone    = sanitizeString($input['phone']);
$email    = strtolower(trim($input['email']));
$password = $input['password'];
$role     = sanitizeString($input['role']);
$language = sanitizeString($input['language']);

if (strlen($name) < 2) {
    sendError('Name must be at least 2 characters long.', 422);
}
if (!isValidPhone($phone)) {
    sendError('Invalid phone number format.', 422);
}
if (!isValidEmail($email)) {
    sendError('Invalid email address.', 422);
}
if (!isValidPassword($password)) {
    sendError('Password must be at least 8 characters long.', 422);
}
if (!isValidRole($role)) {
    sendError('Invalid role. Must be tenant or landlord.', 422);
}
if (!isValidLanguage($language)) {
    sendError('Invalid language. Must be en, ur, or roman_ur.', 422);
}

$pdo = getDbConnection();

try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        sendError('An account with this email already exists.', 409);
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE phone = :phone LIMIT 1');
    $stmt->execute(['phone' => $phone]);
    if ($stmt->fetch()) {
        sendError('An account with this phone number already exists.', 409);
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $pdo->prepare(
        'INSERT INTO users (name, phone, email, password, role, language)
         VALUES (:name, :phone, :email, :password, :role, :language)'
    );
    $stmt->execute([
        'name'     => $name,
        'phone'    => $phone,
        'email'    => $email,
        'password' => $hashedPassword,
        'role'     => $role,
        'language' => $language,
    ]);

    $newUserId = (int) $pdo->lastInsertId();

    sendSuccess('Account created successfully.', [
        'id'       => $newUserId,
        'name'     => $name,
        'email'    => $email,
        'role'     => $role,
        'language' => $language,
    ], 201);

} catch (PDOException $e) {
    error_log('SIGNUP ERROR: ' . $e->getMessage());
    sendError('Failed to create account. Please try again later.', 500);
}