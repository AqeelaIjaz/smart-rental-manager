<?php
/**
 * POST /backend/payments/create.php
 *
 * Records a rent payment against an agreement. This is a university
 * prototype — no real payment gateway is integrated; the QR
 * receipt/gateway wiring belongs to Member 5.
 *
 * Request JSON:
 * {
 *   "agreement_id": 1,
 *   "amount": 25000,
 *   "payment_method": "QR",
 *   "transaction_reference": "TXN001"
 * }
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('POST');
$currentUser = requireLogin();
// [v2] Admins live in a separate `admins` table now — see the same
// note in complaints/create.php. Admins don't pay rent as themselves;
// they can still view all payments (see list.php/get.php).
requireRole($currentUser, ['tenant', 'landlord']);

$input = getJsonInput();
requireFields($input, ['agreement_id', 'amount', 'payment_method', 'transaction_reference']);

$agreementId  = (int) $input['agreement_id'];
$amount       = $input['amount'];
$method       = sanitizeString($input['payment_method']);
$txnReference = sanitizeString($input['transaction_reference']);

if (!isPositiveNumber($amount)) {
    sendError('amount must be a positive number.', 422);
}
if (empty($method)) {
    sendError('payment_method is required.', 422);
}
if (empty($txnReference)) {
    sendError('transaction_reference is required.', 422);
}

$pdo = getDbConnection();

try {
    $stmt = $pdo->prepare('SELECT id, landlord_id, tenant_id FROM agreements WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $agreementId]);
    $agreement = $stmt->fetch();

    if (!$agreement) {
        sendError('Agreement not found.', 404);
    }

    requireAgreementAccess($currentUser, (int)$agreement['landlord_id'], (int)$agreement['tenant_id']);

    // Check for duplicate transaction reference
    $stmt = $pdo->prepare('SELECT id FROM payments WHERE transaction_reference = :ref LIMIT 1');
    $stmt->execute(['ref' => $txnReference]);
    if ($stmt->fetch()) {
        sendError('A payment with this transaction_reference already exists.', 409);
    }

    // Status starts as "pending"; Member 5's QR/payment flow can move
    // it to "paid" via a future confirm endpoint or direct DB update.
    $stmt = $pdo->prepare(
        'INSERT INTO payments (agreement_id, payer_id, amount, payment_method, transaction_reference, status)
         VALUES (:agreement_id, :payer_id, :amount, :payment_method, :transaction_reference, "pending")'
    );
    $stmt->execute([
        'agreement_id'          => $agreementId,
        'payer_id'              => $currentUser['id'],
        'amount'                => $amount,
        'payment_method'        => $method,
        'transaction_reference' => $txnReference,
    ]);

    $newId = (int) $pdo->lastInsertId();

    sendSuccess('Payment recorded successfully.', ['id' => $newId, 'status' => 'pending'], 201);

} catch (PDOException $e) {
    error_log('PAYMENT CREATE ERROR: ' . $e->getMessage());
    sendError('Failed to record payment.', 500);
}
