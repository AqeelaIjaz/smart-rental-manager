<?php
/**
 * POST /backend/payments/payment-status.php
 *
 * Updates a payment's status (pending -> paid / failed).
 * Landlord (of the related agreement) or admin only.
 *
 * -------------------------------------------------------------------
 * MEMBER 5 INTEGRATION (Payments/QR/Notifications):
 * When a payment is marked "paid", this now:
 *   1. Generates a QR code encoding the receipt details and saves it
 *      to payments.qr_receipt.
 *   2. Creates the existing in-app notification (unchanged).
 *   3. Sends a WhatsApp confirmation to the payer (dev-mode logs it
 *      if no WhatsApp API credentials are configured yet).
 * -------------------------------------------------------------------
 *
 * Request JSON:
 * { "id": 1, "status": "paid" }
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/qrcode.php';
require_once __DIR__ . '/../helpers/notifier.php';
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
        'SELECT p.*, a.landlord_id, a.tenant_id, u.phone AS payer_phone, u.name AS payer_name
         FROM payments p
         JOIN agreements a ON a.id = p.agreement_id
         JOIN users u ON u.id = p.payer_id
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

    // ---------------------------------------------------------------
    // Generate QR receipt when a payment newly becomes "paid"
    // ---------------------------------------------------------------
    $qrPath = $payment['qr_receipt']; // keep existing value by default
    if ($status === 'paid' && empty($payment['qr_receipt'])) {
        $qrText = buildReceiptQrText(
            (int) $payment['id'],
            $payment['transaction_reference'],
            $payment['amount'],
            $payment['payment_date']
        );
        $qrResult = generateQrReceipt((int) $payment['id'], $qrText);
        if ($qrResult['success']) {
            $qrPath = $qrResult['path'];
        } else {
            // Don't fail the whole request just because QR generation
            // failed (e.g. no internet access) — payment status update
            // still succeeds, qr_receipt just stays null.
            error_log('QR generation skipped for payment ' . $id . ': ' . $qrResult['message']);
        }
    }

    $update = $pdo->prepare('UPDATE payments SET status = :status, qr_receipt = :qr_receipt WHERE id = :id');
    $update->execute(['status' => $status, 'qr_receipt' => $qrPath, 'id' => $id]);

    // Notify the payer in-app when status changes (unchanged behavior)
    $notify = $pdo->prepare(
        'INSERT INTO notifications (user_id, title, message, type, is_read)
         VALUES (:user_id, :title, :message, "payment", 0)'
    );
    $notify->execute([
        'user_id' => $payment['payer_id'],
        'title'   => 'Payment Status Updated',
        'message' => "Your payment of {$payment['amount']} is now marked as {$status}.",
    ]);

    // WhatsApp confirmation on successful payment (non-blocking)
    if ($status === 'paid' && !empty($payment['payer_phone'])) {
        $waMessage = "Hi {$payment['payer_name']}, your rent payment of Rs. {$payment['amount']} "
            . "(Txn: {$payment['transaction_reference']}) has been received. Thank you!";
        sendWhatsAppMessage($payment['payer_phone'], $waMessage);
    }

    sendSuccess('Payment status updated successfully.', [
        'id'         => $id,
        'status'     => $status,
        'qr_receipt' => $qrPath,
    ]);

} catch (PDOException $e) {
    error_log('PAYMENT STATUS ERROR: ' . $e->getMessage());
    sendError('Failed to update payment status.', 500);
}
