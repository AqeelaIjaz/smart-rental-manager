<?php
/**
 * POST /backend/admin/update-status.php
 *
 * [NEW v2] Activates or deactivates an Admin account. Admin only.
 * An admin cannot deactivate their own account (safety check, to avoid
 * accidentally locking everyone out if there was only one active admin).
 *
 * Request JSON:
 * { "id": 3, "status": "inactive" }
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('POST');
$currentUser = requireLogin();
requireRole($currentUser, ['admin']);

$input = getJsonInput();
requireFields($input, ['id', 'status']);

$id     = (int) $input['id'];
$status = $input['status'];

if (!in_array($status, ['active', 'inactive'], true)) {
    sendError('status must be "active" or "inactive".', 422);
}

if ($id === $currentUser['id'] && $status === 'inactive') {
    sendError('You cannot deactivate your own admin account.', 403);
}

$pdo = getDbConnection();

try {
    $stmt = $pdo->prepare('SELECT id FROM admins WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    if (!$stmt->fetch()) {
        sendError('Admin not found.', 404);
    }

    $update = $pdo->prepare('UPDATE admins SET status = :status WHERE id = :id');
    $update->execute(['status' => $status, 'id' => $id]);

    sendSuccess('Admin status updated successfully.', ['id' => $id, 'status' => $status]);

} catch (PDOException $e) {
    error_log('UPDATE ADMIN STATUS ERROR: ' . $e->getMessage());
    sendError('Failed to update admin status.', 500);
}
