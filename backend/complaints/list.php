<?php
/**
 * GET /backend/complaints/list.php
 * Optional query param: agreement_id (filters to one agreement)
 *
 * - tenant/landlord: see complaints on agreements they are part of
 * - admin: sees all complaints
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
        $sql = 'SELECT c.* FROM complaints c';
        $params = [];
        if ($agreementFilter) {
            $sql .= ' WHERE c.agreement_id = :agreement_id';
            $params['agreement_id'] = $agreementFilter;
        }
        $sql .= ' ORDER BY c.created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        $sql = 'SELECT c.* FROM complaints c
                JOIN agreements a ON a.id = c.agreement_id
                WHERE (a.landlord_id = :uid1 OR a.tenant_id = :uid2)';
        $params = ['uid1' => $currentUser['id'], 'uid2' => $currentUser['id']];
        if ($agreementFilter) {
            $sql .= ' AND c.agreement_id = :agreement_id';
            $params['agreement_id'] = $agreementFilter;
        }
        $sql .= ' ORDER BY c.created_at DESC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    sendSuccess('Complaints retrieved successfully.', $stmt->fetchAll());

} catch (PDOException $e) {
    error_log('COMPLAINT LIST ERROR: ' . $e->getMessage());
    sendError('Failed to retrieve complaints.', 500);
}
