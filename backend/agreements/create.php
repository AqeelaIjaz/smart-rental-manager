<?php
/**
 * POST /backend/agreements/create.php
 *
 * Creates a new rental agreement. Only landlords (for themselves) or
 * admins may create agreements.
 *
 * Request JSON:
 * {
 *   "tenant_id": 4,
 *   "landlord_id": 2,
 *   "rent_amount": 25000,
 *   "due_date": "2026-09-01",
 *   "penalty": 500
 * }
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('POST');
$currentUser = requireLogin();
requireRole($currentUser, ['landlord', 'admin']);

$input = getJsonInput();
requireFields($input, ['tenant_id', 'landlord_id', 'rent_amount', 'due_date']);

$tenantId   = (int) $input['tenant_id'];
$landlordId = (int) $input['landlord_id'];
$rentAmount = $input['rent_amount'];
$dueDate    = sanitizeString($input['due_date']);
$penalty    = $input['penalty'] ?? 0;

// A landlord may only create agreements for themselves; admin can for anyone.
if ($currentUser['role'] === 'landlord' && $currentUser['id'] !== $landlordId) {
    sendError('Landlords can only create agreements for themselves.', 403);
}

if (!isPositiveNumber($rentAmount)) {
    sendError('rent_amount must be a positive number.', 422);
}
if (!isValidDate($dueDate)) {
    sendError('due_date must be a valid date in YYYY-MM-DD format.', 422);
}
if (!is_numeric($penalty) || (float)$penalty < 0) {
    sendError('penalty must be a non-negative number.', 422);
}

$pdo = getDbConnection();

try {
    // Verify tenant and landlord exist and have correct roles
    $stmt = $pdo->prepare('SELECT id, role FROM users WHERE id = :id LIMIT 1');

    $stmt->execute(['id' => $tenantId]);
    $tenant = $stmt->fetch();
    if (!$tenant || $tenant['role'] !== 'tenant') {
        sendError('Invalid tenant_id: user not found or is not a tenant.', 422);
    }

    $stmt->execute(['id' => $landlordId]);
    $landlord = $stmt->fetch();
    if (!$landlord || $landlord['role'] !== 'landlord') {
        sendError('Invalid landlord_id: user not found or is not a landlord.', 422);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO agreements (landlord_id, tenant_id, rent_amount, due_date, penalty, status)
         VALUES (:landlord_id, :tenant_id, :rent_amount, :due_date, :penalty, "pending")'
    );
    $stmt->execute([
        'landlord_id' => $landlordId,
        'tenant_id'   => $tenantId,
        'rent_amount' => $rentAmount,
        'due_date'    => $dueDate,
        'penalty'     => $penalty,
    ]);

    $newId = (int) $pdo->lastInsertId();

    sendSuccess('Agreement created successfully.', ['id' => $newId], 201);

} catch (PDOException $e) {
    error_log('AGREEMENT CREATE ERROR: ' . $e->getMessage());
    sendError('Failed to create agreement.', 500);
}
