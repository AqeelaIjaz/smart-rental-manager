<?php
/**
 * POST /backend/auth/logout.php
 *
 * Destroys the current session securely.
 */

require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php'; // starts the session

requireMethod('POST');

// Clear all session variables
$_SESSION = [];

// Delete the session cookie itself
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the session data on the server
session_destroy();

sendSuccess('Logout successful.');
