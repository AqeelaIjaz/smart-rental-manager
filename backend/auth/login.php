<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../middleware/auth.php';

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

    $stmt = $pdo->prepare(
        'SELECT id, name, email, password, status
         FROM admins
         WHERE email = :email
         LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $admin = $stmt->fetch();

    if ($admin) {

        if ($admin['status'] !== 'active') {
            sendError('This admin account is inactive.', 403);
        }

        if (!password_verify($password, $admin['password'])) {
            sendError('Invalid email or password.', 401);
        }

        session_regenerate_id(true);

        $_SESSION['user_id']  = (int) $admin['id'];
        $_SESSION['role']     = 'admin';
        $_SESSION['name']     = $admin['name'];
        $_SESSION['email']    = $admin['email'];
        $_SESSION['language'] = 'en';

        sendSuccess('Login successful', [
            'id'       => (int) $admin['id'],
            'name'     => $admin['name'],
            'role'     => 'admin',
            'language' => 'en',
        ]);
    }

    $stmt = $pdo->prepare(
        'SELECT id, name, email, password, role, language
         FROM users
         WHERE email = :email
         LIMIT 1'
    );
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        sendError('Invalid email or password.', 401);
    }

    session_regenerate_id(true);

    $_SESSION['user_id']  = (int) $user['id'];
    $_SESSION['role']     = $user['role'];
    $_SESSION['name']     = $user['name'];
    $_SESSION['email']    = $user['email'];
    $_SESSION['language'] = $user['language'];

    sendSuccess('Login successful', [
        'id'       => (int) $user['id'],
        'name'     => $user['name'],
        'role'     => $user['role'],
        'language' => $user['language'],
    ]);

} catch (PDOException $e) {
    error_log('LOGIN ERROR: ' . $e->getMessage());
    sendError('Login failed. Please try again later.', 500);
}
