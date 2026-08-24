<?php
/**
 * POST /backend/payments/payment-status.php
 *
 * Updates a payment's status (pending -> paid / failed).
 * Landlord (of the related agreement) or admin only.
 *
 * -------------------------------------------------------------------
 * >>> INTEGRATION POINT FOR MEMBER 5 (Payments/QR/Notifications) <<<
 * This is where a real/simulated payment confirmation (e.g. after a
 * QR code is scanned and "paid") should call back to mark a payment
 * as paid. It can also be called manually by a landlord for
 * cash/offline payments.
 * -------------------------------------------------------------------
 *
 * Request JSON:
 * { "id": 1, "status": "paid" }
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('POST');
$currentUser = requireLogin();
requireRole($currentUser, ['landlord', 'admin']);

$input = getJsonInput();
requireFields($input, ['id', 'status']);

$id     = (int) $input['id'];
$status = sanitizeString($input['status']);

if (!in_array($status, ['pending', 'paid', 'failed'], true)) {
    sendError('Invalid status. Must be one of: pending, paid, failed.', 422);
}

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

    if ($currentUser['role'] === 'landlord' && $currentUser['id'] !== (int)$payment['landlord_id']) {
        sendError('You do not have permission to update this payment.', 403);
    }

    $update = $pdo->prepare('UPDATE payments SET status = :status WHERE id = :id');
    $update->execute(['status' => $status, 'id' => $id]);

    // Notify the payer when status changes
    $notify = $pdo->prepare(
        'INSERT INTO notifications (user_id, title, message, type, is_read)
         VALUES (:user_id, :title, :message, "payment", 0)'
    );
    $notify->execute([
        'user_id' => $payment['payer_id'],
        'title'   => 'Payment Status Updated',
        'message' => "Your payment of {$payment['amount']} is now marked as {$status}.",
    ]);

    sendSuccess('Payment status updated successfully.', ['id' => $id, 'status' => $status]);

} catch (PDOException $e) {
    error_log('PAYMENT STATUS ERROR: ' . $e->getMessage());
    sendError('Failed to update payment status.', 500);
}
