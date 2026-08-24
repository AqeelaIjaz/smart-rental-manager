<?php
/**
 * GET /backend/risk/get.php?id=1
 *   (id = risk_scores.id)
 *
 * OR
 *
 * GET /backend/risk/get.php?agreement_id=1
 *   (returns the latest risk score for the agreement)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('GET');
$currentUser = requireLogin();

$pdo = getDbConnection();

try {
    if (!empty($_GET['id']) && ctype_digit((string)$_GET['id'])) {
        $stmt = $pdo->prepare(
            'SELECT rs.*, a.landlord_id, a.tenant_id
             FROM risk_scores rs
             JOIN agreements a ON a.id = rs.agreement_id
             WHERE rs.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => (int) $_GET['id']]);
        $row = $stmt->fetch();

        if (!$row) {
            sendError('Risk score not found.', 404);
        }

        requireAgreementAccess($currentUser, (int)$row['landlord_id'], (int)$row['tenant_id']);
        unset($row['landlord_id'], $row['tenant_id']);

        sendSuccess('Risk score retrieved successfully.', $row);

    } elseif (!empty($_GET['agreement_id']) && ctype_digit((string)$_GET['agreement_id'])) {
        $agreementId = (int) $_GET['agreement_id'];

        $stmt = $pdo->prepare('SELECT landlord_id, tenant_id FROM agreements WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $agreementId]);
        $agreement = $stmt->fetch();

        if (!$agreement) {
            sendError('Agreement not found.', 404);
        }

        requireAgreementAccess($currentUser, (int)$agreement['landlord_id'], (int)$agreement['tenant_id']);

        $stmt = $pdo->prepare(
            'SELECT * FROM risk_scores WHERE agreement_id = :agreement_id
             ORDER BY created_at DESC LIMIT 1'
        );
        $stmt->execute(['agreement_id' => $agreementId]);
        $row = $stmt->fetch();

        if (!$row) {
            sendError('No risk score found for this agreement.', 404);
        }

        sendSuccess('Risk score retrieved successfully.', $row);

    } else {
        sendError('Provide either an id or agreement_id parameter.', 422);
    }

} catch (PDOException $e) {
    error_log('RISK GET ERROR: ' . $e->getMessage());
    sendError('Failed to retrieve risk score.', 500);
}
