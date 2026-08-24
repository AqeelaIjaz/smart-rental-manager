<?php
/**
 * GET /backend/payments/receipt.php?id=1
 *
 * Returns receipt data for a payment: tenant name, landlord name,
 * amount, date, method, transaction reference, and status.
 *
 * -------------------------------------------------------------------
 * >>> INTEGRATION POINT FOR MEMBER 5 (QR receipts) <<<
 * The `qr_receipt` column on the payments table stores the filename/
 * path of a generated QR image. This endpoint returns that path as-is
 * (nullable) so Member 5's QR generation logic can populate it (e.g.
 * via payments/payment-status.php or a dedicated endpoint) and the
 * frontend can render/download it.
 * -------------------------------------------------------------------
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
        'SELECT
            p.id, p.amount, p.payment_date, p.payment_method,
            p.transaction_reference, p.qr_receipt, p.status,
            a.landlord_id, a.tenant_id,
            tenant.name AS tenant_name,
            landlord.name AS landlord_name
         FROM payments p
         JOIN agreements a ON a.id = p.agreement_id
         JOIN users tenant ON tenant.id = a.tenant_id
         JOIN users landlord ON landlord.id = a.landlord_id
         WHERE p.id = :id LIMIT 1'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();

    if (!$row) {
        sendError('Payment not found.', 404);
    }

    requireAgreementAccess($currentUser, (int)$row['landlord_id'], (int)$row['tenant_id']);

    $receipt = [
        'payment_id'             => (int) $row['id'],
        'tenant_name'            => $row['tenant_name'],
        'landlord_name'          => $row['landlord_name'],
        'amount'                 => $row['amount'],
        'payment_date'           => $row['payment_date'],
        'payment_method'         => $row['payment_method'],
        'transaction_reference'  => $row['transaction_reference'],
        'status'                 => $row['status'],
        'qr_receipt'             => $row['qr_receipt'], // null until Member 5 generates it
    ];

    sendSuccess('Receipt retrieved successfully.', $receipt);

} catch (PDOException $e) {
    error_log('RECEIPT ERROR: ' . $e->getMessage());
    sendError('Failed to retrieve receipt.', 500);
}
