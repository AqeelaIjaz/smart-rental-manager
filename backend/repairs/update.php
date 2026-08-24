<?php
/**
 * POST /backend/repairs/update.php
 *
 * Updates a repair record's status/priority/cost. Landlord or admin only.
 *
 * Request JSON:
 * { "id": 1, "status": "in_progress", "priority": "high", "estimated_cost": 4000 }
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
    $stmt = $pdo->prepare(
        'SELECT r.*, a.landlord_id, a.tenant_id
         FROM repairs r
         JOIN agreements a ON a.id = r.agreement_id
         WHERE r.id = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $repair = $stmt->fetch();

    if (!$repair) {
        sendError('Repair record not found.', 404);
    }

    if ($currentUser['role'] === 'landlord' && $currentUser['id'] !== (int)$repair['landlord_id']) {
        sendError('You do not have permission to update this repair.', 403);
    }

    $updates = [];
    $params  = ['id' => $id];

    if (array_key_exists('status', $input)) {
        if (!in_array($input['status'], ['reported', 'in_progress', 'completed', 'cancelled'], true)) {
            sendError('Invalid status value.', 422);
        }
        $updates[] = 'status = :status';
        $params['status'] = $input['status'];
    }
    if (array_key_exists('priority', $input)) {
        if (!in_array($input['priority'], ['low', 'medium', 'high', 'urgent'], true)) {
            sendError('Invalid priority value.', 422);
        }
        $updates[] = 'priority = :priority';
        $params['priority'] = $input['priority'];
    }
    if (array_key_exists('estimated_cost', $input)) {
        if (!is_numeric($input['estimated_cost']) || (float)$input['estimated_cost'] < 0) {
            sendError('estimated_cost must be a non-negative number.', 422);
        }
        $updates[] = 'estimated_cost = :estimated_cost';
        $params['estimated_cost'] = $input['estimated_cost'];
    }

    if (empty($updates)) {
        sendError('No valid fields provided to update.', 422);
    }

    $sql = 'UPDATE repairs SET ' . implode(', ', $updates) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    sendSuccess('Repair record updated successfully.', ['id' => $id]);

} catch (PDOException $e) {
    error_log('REPAIR UPDATE ERROR: ' . $e->getMessage());
    sendError('Failed to update repair record.', 500);
}
