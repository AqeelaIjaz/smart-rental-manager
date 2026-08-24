<?php
/**
 * GET /backend/repairs/get.php?id=1
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('GET');
$currentUser = requireLogin();

if (empty($_GET['id']) || !ctype_digit((string)$_GET['id'])) {
    sendError('A valid id parameter is required.', 422);
}
$id = (int) $_GET['id'];

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

    requireAgreementAccess($currentUser, (int)$repair['landlord_id'], (int)$repair['tenant_id']);

    unset($repair['landlord_id'], $repair['tenant_id']);

    sendSuccess('Repair record retrieved successfully.', $repair);

} catch (PDOException $e) {
    error_log('REPAIR GET ERROR: ' . $e->getMessage());
    sendError('Failed to retrieve repair record.', 500);
}
