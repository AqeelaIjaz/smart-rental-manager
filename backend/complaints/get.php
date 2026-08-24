<?php
/**
 * GET /backend/complaints/get.php?id=1
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
        'SELECT c.*, a.landlord_id, a.tenant_id
         FROM complaints c
         JOIN agreements a ON a.id = c.agreement_id
         WHERE c.id = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $complaint = $stmt->fetch();

    if (!$complaint) {
        sendError('Complaint not found.', 404);
    }

    requireAgreementAccess($currentUser, (int)$complaint['landlord_id'], (int)$complaint['tenant_id']);

    unset($complaint['landlord_id'], $complaint['tenant_id']);

    sendSuccess('Complaint retrieved successfully.', $complaint);

} catch (PDOException $e) {
    error_log('COMPLAINT GET ERROR: ' . $e->getMessage());
    sendError('Failed to retrieve complaint.', 500);
}
