<?php
/**
 * POST /backend/users/update.php   (suggested location - confirm with Meeral)
 *
 * Updates the logged-in user's own profile.
 *
 * application/json:
 *   { "name": "...", "email": "...", "phone": "..." }
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('POST');
$currentUser = requireLogin();

$input = getJsonInput();
requireFields($input, ['name', 'email']);

$name  = sanitizeString($input['name']);
$email = strtolower(trim($input['email']));
$phone = isset($input['phone']) ? sanitizeString($input['phone']) : null;

if (empty($name)) {
    sendError('Name is required.', 422);
}
if (!isValidEmail($email)) {
    sendError('Invalid email address.', 422);
}

$pdo = getDbConnection();

try {
    // Make sure the new email isn't already used by someone else
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email AND id != :id LIMIT 1');
    $stmt->execute(['email' => $email, 'id' => $currentUser['id']]);
    if ($stmt->fetch()) {
        sendError('This email is already in use.', 422);
    }

    $stmt = $pdo->prepare(
        'UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :id'
    );
    $stmt->execute([
        'name'  => $name,
        'email' => $email,
        'phone' => $phone,
        'id'    => $currentUser['id'],
    ]);

    // keep the session in sync
    $_SESSION['name']  = $name;
    $_SESSION['email'] = $email;

    sendSuccess('Profile updated successfully.', [
        'id'    => $currentUser['id'],
        'name'  => $name,
        'email' => $email,
        'phone' => $phone,
    ]);

} catch (PDOException $e) {
    error_log('PROFILE UPDATE ERROR: ' . $e->getMessage());
    sendError('Failed to update profile.', 500);
}