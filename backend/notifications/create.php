<?php
/**
 * POST /backend/notifications/create.php
 *
 * Creates a notification for a user. Typically called internally by
 * other endpoints (payment status change, complaint update, rent
 * reminder cron), but also exposed for admin/landlord manual use and
 * for Member 5's notification integrations (WhatsApp/SMS trigger
 * point can call this to log an in-app copy).
 *
 * Request JSON:
 * {
 *   "user_id": 4,
 *   "title": "Rent Reminder",
 *   "message": "Your rent is due in 5 days.",
 *   "type": "rent_reminder"
 * }
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('POST');
$currentUser = requireLogin();
requireRole($currentUser, ['landlord', 'admin']);

$input = getJsonInput();
requireFields($input, ['user_id', 'title', 'message', 'type']);

$userId  = (int) $input['user_id'];
$title   = sanitizeString($input['title']);
$message = sanitizeString($input['message']);
$type    = sanitizeString($input['type']);

$validTypes = ['rent_reminder', 'payment', 'complaint', 'repair', 'system'];
if (!in_array($type, $validTypes, true)) {
    sendError('Invalid type. Must be one of: ' . implode(', ', $validTypes), 422);
}

$pdo = getDbConnection();

try {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $userId]);
    if (!$stmt->fetch()) {
        sendError('Target user not found.', 404);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO notifications (user_id, title, message, type, is_read)
         VALUES (:user_id, :title, :message, :type, 0)'
    );
    $stmt->execute([
        'user_id' => $userId,
        'title'   => $title,
        'message' => $message,
        'type'    => $type,
    ]);

    sendSuccess('Notification created successfully.', ['id' => (int) $pdo->lastInsertId()], 201);

} catch (PDOException $e) {
    error_log('NOTIFICATION CREATE ERROR: ' . $e->getMessage());
    sendError('Failed to create notification.', 500);
}
