<?php
/**
 * POST /backend/complaints/update.php
 *
 * Updates a complaint. Two use cases:
 *
 * 1) AI SERVICE (Member 3) writes back an AI-generated suggestion:
 *    {
 *      "complaint_id": 5,
 *      "ai_suggestion": "Suggested resolution text...",
 *      "status": "in_review"
 *    }
 *
 * 2) LANDLORD/ADMIN updates status (e.g. resolve a complaint):
 *    { "complaint_id": 5, "status": "resolved" }
 *
 * -------------------------------------------------------------------
 * >>> INTEGRATION POINT FOR MEMBER 3 (AI/NLP) <<<
 * This endpoint is the write-back target after Member 3's AI/LLM
 * service processes a complaint (speech-to-text -> NLP -> suggestion).
 * Member 3's service should call this endpoint with the generated
 * `ai_suggestion` text once processing completes. This project does
 * NOT invent or simulate AI output — ai_suggestion stays NULL until
 * Member 3's service supplies it.
 * -------------------------------------------------------------------
 *
 * NOTE: In a production system this endpoint would be protected by a
 * dedicated service-to-service credential (e.g. an internal API key)
 * rather than a user session, since the AI service is a backend
 * process, not a logged-in browser user. For this university
 * prototype it is protected by requiring an authenticated
 * landlord/admin session — update the auth check here once Member 3's
 * service architecture (e.g. shared secret header) is finalized.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('POST');
$currentUser = requireLogin();
requireRole($currentUser, ['landlord', 'admin']);

$input = getJsonInput();
requireFields($input, ['complaint_id']);

$complaintId = (int) $input['complaint_id'];
$validStatuses = ['open', 'in_review', 'resolved', 'rejected'];

$pdo = getDbConnection();

try {
    $stmt = $pdo->prepare(
        'SELECT c.*, a.landlord_id, a.tenant_id
         FROM complaints c
         JOIN agreements a ON a.id = c.agreement_id
         WHERE c.id = :id LIMIT 1'
    );
    $stmt->execute(['id' => $complaintId]);
    $complaint = $stmt->fetch();

    if (!$complaint) {
        sendError('Complaint not found.', 404);
    }

    requireAgreementAccess($currentUser, (int)$complaint['landlord_id'], (int)$complaint['tenant_id']);

    $updates = [];
    $params  = ['id' => $complaintId];

    if (array_key_exists('ai_suggestion', $input)) {
        $updates[] = 'ai_suggestion = :ai_suggestion';
        $params['ai_suggestion'] = sanitizeString((string)$input['ai_suggestion']);
    }

    if (array_key_exists('status', $input)) {
        if (!in_array($input['status'], $validStatuses, true)) {
            sendError('Invalid status. Must be one of: ' . implode(', ', $validStatuses), 422);
        }
        $updates[] = 'status = :status';
        $params['status'] = $input['status'];
    }

    if (empty($updates)) {
        sendError('No valid fields provided to update (ai_suggestion or status).', 422);
    }

    $sql = 'UPDATE complaints SET ' . implode(', ', $updates) . ' WHERE id = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    sendSuccess('Complaint updated successfully.', ['id' => $complaintId]);

} catch (PDOException $e) {
    error_log('COMPLAINT UPDATE ERROR: ' . $e->getMessage());
    sendError('Failed to update complaint.', 500);
}
