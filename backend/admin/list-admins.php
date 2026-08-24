<?php
/**
 * GET /backend/admin/list-admins.php
 *
 * [NEW v2] Lists all admin accounts (excluding password hashes).
 * Admin only.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('GET');
$currentUser = requireLogin();
requireRole($currentUser, ['admin']);

$pdo = getDbConnection();

try {
    $stmt = $pdo->query(
        'SELECT id, name, email, phone, status, created_at, updated_at
         FROM admins ORDER BY created_at DESC'
    );
    sendSuccess('Admins retrieved successfully.', $stmt->fetchAll());

} catch (PDOException $e) {
    error_log('LIST ADMINS ERROR: ' . $e->getMessage());
    sendError('Failed to retrieve admins.', 500);
}
