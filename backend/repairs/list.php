<?php
/**
 * GET /backend/repairs/list.php
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
        $sql = 'SELECT r.* FROM repairs r';
        $params = [];
        if ($agreementFilter) {
            $sql .= ' WHERE r.agreement_id = :agreement_id';
            $params['agreement_id'] = $agreementFilter;
        }
        $sql .= ' ORDER BY r.created_at DESC';
    } else {
        $sql = 'SELECT r.* FROM repairs r
                JOIN agreements a ON a.id = r.agreement_id
                WHERE (a.landlord_id = :uid1 OR a.tenant_id = :uid2)';
        $params = ['uid1' => $currentUser['id'], 'uid2' => $currentUser['id']];
        if ($agreementFilter) {
            $sql .= ' AND r.agreement_id = :agreement_id';
            $params['agreement_id'] = $agreementFilter;
        }
        $sql .= ' ORDER BY r.created_at DESC';
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    sendSuccess('Repairs retrieved successfully.', $stmt->fetchAll());

} catch (PDOException $e) {
    error_log('REPAIR LIST ERROR: ' . $e->getMessage());
    sendError('Failed to retrieve repairs.', 500);
}
