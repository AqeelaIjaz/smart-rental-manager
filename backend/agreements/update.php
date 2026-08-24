<?php
/**
 * POST /backend/agreements/update.php
 *
 * Updates an existing agreement's mutable fields. Only the owning
 * landlord or an admin may update.
 *
 * Request JSON:
 * {
 *   "id": 1,
 *   "rent_amount": 27000,
 *   "due_date": "2026-09-01",
 *   "penalty": 500,
 *   "status": "active",
 *   "extracted_text": "..."
 * }
 * (All fields except "id" are optional; only supplied fields are updated.)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('POST');
$currentUser = requireLogin();
requireRole($currentUser, ['landlord', 'admin']);

$input = getJsonInput();
requireFields($input, ['id']);
$id = (int) $input['id'];

$pdo = getDbConnection();

try {
    $stmt = $pdo->prepare('SELECT * FROM agreements WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $agreement = $stmt->fetch();

    if (!$agreement) {
        sendError('Agreement not found.', 404);
    }

    if ($currentUser['role'] === 'landlord' && $currentUser['id'] !== (int)$agreement['landlord_id']) {
        sendError('You do not have permission to update this agreement.', 403);
    }

    $allowedFields = ['rent_amount', 'due_date', 'penalty', 'status', 'extracted_text'];
    $updates = [];
    $params  = ['id' => $id];

    foreach ($allowedFields as $field) {
        if (array_key_exists($field, $input)) {
            if ($field === 'rent_amount' && !isPositiveNumber($input[$field])) {
                sendError('rent_amount must be a positive number.', 422);
            }
            if ($field === 'due_date' && !isValidDate($input[$field])) {
                sendError('due_date must be a valid date in YYYY-MM-DD format.', 422);
            }
            if ($field === 'status' && !in_array($input[$field], ['active', 'pending', 'terminated', 'expired'], true)) {
                sendError('Invalid status value.', 422);
            }
            $updates[] = "$field = :$field";
            $params[$field] = $input[$field];
        }
    }

    if (empty($updates)) {
        sendError('No valid fields provided to update.', 422);
    }

    $sql = 'UPDATE agreements SET ' . implode(', ', $updates) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    sendSuccess('Agreement updated successfully.', ['id' => $id]);

} catch (PDOException $e) {
    error_log('AGREEMENT UPDATE ERROR: ' . $e->getMessage());
    sendError('Failed to update agreement.', 500);
}
