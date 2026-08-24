<?php
/**
 * POST /backend/notifications/mark-read.php
 *
 * Marks one notification (or all of the current user's notifications)
 * as read.
 *
 * Request JSON:
 * { "id": 3 }
 * OR
 * { "all": true }
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('POST');
$currentUser = requireLogin();

$input = getJsonInput();
$pdo = getDbConnection();

try {
    if (!empty($input['all'])) {
        $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = :uid');
        $stmt->execute(['uid' => $currentUser['id']]);
        sendSuccess('All notifications marked as read.');
    }

    if (empty($input['id'])) {
        sendError('Either "id" or "all": true is required.', 422);
    }

    $id = (int) $input['id'];

    $stmt = $pdo->prepare('SELECT id, user_id FROM notifications WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $notification = $stmt->fetch();

    if (!$notification) {
        sendError('Notification not found.', 404);
    }

    requireOwnershipOrAdmin($currentUser, (int)$notification['user_id']);

    $update = $pdo->prepare('UPDATE notifications SET is_read = 1 WHERE id = :id');
    $update->execute(['id' => $id]);

    sendSuccess('Notification marked as read.', ['id' => $id]);

} catch (PDOException $e) {
    error_log('NOTIFICATION MARK-READ ERROR: ' . $e->getMessage());
    sendError('Failed to update notification.', 500);
}
