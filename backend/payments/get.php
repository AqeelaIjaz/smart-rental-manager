<?php
/**
 * GET /backend/payments/get.php?id=1
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
        'SELECT p.*, a.landlord_id, a.tenant_id
         FROM payments p
         JOIN agreements a ON a.id = p.agreement_id
         WHERE p.id = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $payment = $stmt->fetch();

    if (!$payment) {
        sendError('Payment not found.', 404);
    }

    requireAgreementAccess($currentUser, (int)$payment['landlord_id'], (int)$payment['tenant_id']);

    unset($payment['landlord_id'], $payment['tenant_id']);

    sendSuccess('Payment retrieved successfully.', $payment);

} catch (PDOException $e) {
    error_log('PAYMENT GET ERROR: ' . $e->getMessage());
    sendError('Failed to retrieve payment.', 500);
}
