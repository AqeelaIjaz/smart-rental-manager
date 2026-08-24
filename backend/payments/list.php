<?php
/**
 * GET /backend/payments/list.php
 * Optional query param: agreement_id
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('GET');
$currentUser = requireLogin();

$pdo = getDbConnection();
$agreementFilter = isset($_GET['agreement_id']) && ctype_digit((string)$_GET['agreement_id'])
    ? (int) $_GET['agreement_id']
    : null;

try {
    if ($currentUser['role'] === 'admin') {
        $sql = 'SELECT p.* FROM payments p';
        $params = [];
        if ($agreementFilter) {
            $sql .= ' WHERE p.agreement_id = :agreement_id';
            $params['agreement_id'] = $agreementFilter;
        }
        $sql .= ' ORDER BY p.created_at DESC';
    } else {
        $sql = 'SELECT p.* FROM payments p
                JOIN agreements a ON a.id = p.agreement_id
                WHERE (a.landlord_id = :uid1 OR a.tenant_id = :uid2)';
        $params = ['uid1' => $currentUser['id'], 'uid2' => $currentUser['id']];
        if ($agreementFilter) {
            $sql .= ' AND p.agreement_id = :agreement_id';
            $params['agreement_id'] = $agreementFilter;
        }
        $sql .= ' ORDER BY p.created_at DESC';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    sendSuccess('Payments retrieved successfully.', $stmt->fetchAll());

} catch (PDOException $e) {
    error_log('PAYMENT LIST ERROR: ' . $e->getMessage());
    sendError('Failed to retrieve payments.', 500);
}
