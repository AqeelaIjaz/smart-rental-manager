<?php
/**
 * POST /backend/repairs/create.php
 *
 * Creates a repair report, with optional photo upload.
 *
 * multipart/form-data:
 *   agreement_id, issue_description, priority, estimated_cost (optional), photo (optional file)
 * OR application/json (no photo):
 *   { "agreement_id": 1, "issue_description": "...", "priority": "high", "estimated_cost": 3000 }
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/upload.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('POST');
$currentUser = requireLogin();
// [v2] Admins live in a separate `admins` table now — see the same
// note in complaints/create.php. Admins can still view all repairs.
requireRole($currentUser, ['tenant', 'landlord']);

$isMultipart = stripos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;

if ($isMultipart) {
    $agreementId      = isset($_POST['agreement_id']) ? (int) $_POST['agreement_id'] : 0;
    $issueDescription = isset($_POST['issue_description']) ? sanitizeString($_POST['issue_description']) : '';
    $priority          = isset($_POST['priority']) ? sanitizeString($_POST['priority']) : 'medium';
    $estimatedCost     = isset($_POST['estimated_cost']) ? $_POST['estimated_cost'] : null;
} else {
    $input = getJsonInput();
    requireFields($input, ['agreement_id', 'issue_description']);
    $agreementId      = (int) $input['agreement_id'];
    $issueDescription = sanitizeString($input['issue_description']);
    $priority          = sanitizeString($input['priority'] ?? 'medium');
    $estimatedCost     = $input['estimated_cost'] ?? null;
}

if ($agreementId <= 0) {
    sendError('A valid agreement_id is required.', 422);
}
if (empty($issueDescription)) {
    sendError('issue_description is required.', 422);
}
if (!in_array($priority, ['low', 'medium', 'high', 'urgent'], true)) {
    sendError('priority must be one of: low, medium, high, urgent.', 422);
}
if ($estimatedCost !== null && $estimatedCost !== '' && (!is_numeric($estimatedCost) || (float)$estimatedCost < 0)) {
    sendError('estimated_cost must be a non-negative number.', 422);
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

    $photoFilename = null;
    $hasPhoto = $isMultipart && isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE;
    if ($hasPhoto) {
        // Max 8MB for repair photos
        $result = handleFileUpload($_FILES['photo'], 'repairs', getPhotoAllowedMime(), 8 * 1024 * 1024);
        if (!$result['success']) {
            sendError($result['message'], 422);
        }
        $photoFilename = $result['filename'];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO repairs (reported_by, agreement_id, photo, issue_description, priority, estimated_cost, status)
         VALUES (:reported_by, :agreement_id, :photo, :issue_description, :priority, :estimated_cost, "reported")'
    );
    $stmt->execute([
        'reported_by'       => $currentUser['id'],
        'agreement_id'      => $agreementId,
        'photo'             => $photoFilename,
        'issue_description' => $issueDescription,
        'priority'          => $priority,
        'estimated_cost'    => ($estimatedCost === '' ? null : $estimatedCost),
    ]);

    $newId = (int) $pdo->lastInsertId();

    sendSuccess('Repair report submitted successfully.', [
        'id'    => $newId,
        'photo' => $photoFilename,
    ], 201);

} catch (PDOException $e) {
    error_log('REPAIR CREATE ERROR: ' . $e->getMessage());
    sendError('Failed to submit repair report.', 500);
}
