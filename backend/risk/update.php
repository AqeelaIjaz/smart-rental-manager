<?php
/**
 * POST /backend/risk/update.php
 *
 * Stores an AI-generated risk score for a user/agreement.
 *
 * -------------------------------------------------------------------
 * >>> INTEGRATION POINT FOR MEMBER 3 (AI/NLP - Risk Prediction) <<<
 * This endpoint is backend storage ONLY. It does NOT calculate or
 * invent risk scores — Member 3's model computes risk_level, score,
 * and reason, then calls this endpoint (or a shared internal call)
 * to persist the result.
 * -------------------------------------------------------------------
 *
 * Request JSON:
 * {
 *   "user_id": 4,
 *   "agreement_id": 1,
 *   "risk_level": "low",
 *   "score": 12.5,
 *   "reason": "Consistent on-time payment history."
 * }
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('POST');
$currentUser = requireLogin();
// Landlords/admins store risk assessments; tenants should not set their own risk score.
requireRole($currentUser, ['landlord', 'admin']);

$input = getJsonInput();
requireFields($input, ['user_id', 'agreement_id', 'risk_level', 'score']);

$userId      = (int) $input['user_id'];
$agreementId = (int) $input['agreement_id'];
$riskLevel   = sanitizeString($input['risk_level']);
$score       = $input['score'];
$reason      = isset($input['reason']) ? sanitizeString($input['reason']) : null;

if (!in_array($riskLevel, ['low', 'medium', 'high'], true)) {
    sendError('risk_level must be one of: low, medium, high.', 422);
}
if (!is_numeric($score) || (float)$score < 0) {
    sendError('score must be a non-negative number.', 422);
}

$pdo = getDbConnection();

try {
    $stmt = $pdo->prepare('SELECT id, landlord_id, tenant_id FROM agreements WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $agreementId]);
    $agreement = $stmt->fetch();

    if (!$agreement) {
        sendError('Agreement not found.', 404);
    }

    if ($currentUser['role'] === 'landlord' && $currentUser['id'] !== (int)$agreement['landlord_id']) {
        sendError('You do not have permission to set risk data for this agreement.', 403);
    }

    // user_id should be a participant in the agreement
    if ($userId !== (int)$agreement['tenant_id'] && $userId !== (int)$agreement['landlord_id']) {
        sendError('user_id must be a participant (tenant or landlord) of this agreement.', 422);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO risk_scores (user_id, agreement_id, risk_level, reason, score)
         VALUES (:user_id, :agreement_id, :risk_level, :reason, :score)'
    );
    $stmt->execute([
        'user_id'      => $userId,
        'agreement_id' => $agreementId,
        'risk_level'   => $riskLevel,
        'reason'       => $reason,
        'score'        => $score,
    ]);

    sendSuccess('Risk score recorded successfully.', ['id' => (int) $pdo->lastInsertId()], 201);

} catch (PDOException $e) {
    error_log('RISK UPDATE ERROR: ' . $e->getMessage());
    sendError('Failed to record risk score.', 500);
}
