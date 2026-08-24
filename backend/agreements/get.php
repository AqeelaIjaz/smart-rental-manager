<?php
/**
 * GET /backend/agreements/get.php?id=1
 *
 * Retrieves a single agreement. Only accessible to the landlord,
 * tenant involved, or an admin.
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
    $stmt = $pdo->prepare('SELECT * FROM agreements WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $agreement = $stmt->fetch();

    if (!$agreement) {
        sendError('Agreement not found.', 404);
    }

    requireAgreementAccess($currentUser, (int)$agreement['landlord_id'], (int)$agreement['tenant_id']);

    sendSuccess('Agreement retrieved successfully.', $agreement);

} catch (PDOException $e) {
    error_log('AGREEMENT GET ERROR: ' . $e->getMessage());
    sendError('Failed to retrieve agreement.', 500);
}
