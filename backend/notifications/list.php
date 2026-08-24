<?php
/**
 * GET /backend/notifications/list.php
 *
 * Returns the current user's notifications (admins see their own
 * notifications too — admins are just regular users of the system
 * for notification purposes).
 * Optional query param: unread_only=1
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('GET');
$currentUser = requireLogin();

$pdo = getDbConnection();

try {
    $sql = 'SELECT * FROM notifications WHERE user_id = :uid';
    $params = ['uid' => $currentUser['id']];

    if (!empty($_GET['unread_only']) && $_GET['unread_only'] == '1') {
        $sql .= ' AND is_read = 0';
    }

    $sql .= ' ORDER BY created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    sendSuccess('Notifications retrieved successfully.', $stmt->fetchAll());

} catch (PDOException $e) {
    error_log('NOTIFICATION LIST ERROR: ' . $e->getMessage());
    sendError('Failed to retrieve notifications.', 500);
}
