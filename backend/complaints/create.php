<?php
/**
 * POST /backend/complaints/create.php
 *
 * Creates a complaint tied to an agreement. Accepts EITHER:
 *  A) multipart/form-data (when uploading a voice file):
 *       agreement_id, complaint_text (optional), voice_file (optional file)
 *  B) application/json (no voice file):
 *       { "agreement_id": 1, "complaint_text": "..." }
 *
 * ai_suggestion is always NULL at creation time — it is filled in
 * later by Member 3's AI service via complaints/update.php.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/upload.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('POST');
$currentUser = requireLogin();
// [v2] Admins live in a separate `admins` table now, so an admin's
// session id does not correspond to a row in `users` — filing a
// complaint "as" an admin would violate the user_id foreign key.
// Admins can still VIEW all complaints (see list.php/get.php); they
// just don't file them.
requireRole($currentUser, ['tenant', 'landlord']);

$isMultipart = stripos($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data') !== false;

if ($isMultipart) {
    $agreementId   = isset($_POST['agreement_id']) ? (int) $_POST['agreement_id'] : 0;
    $complaintText = isset($_POST['complaint_text']) ? sanitizeString($_POST['complaint_text']) : null;
} else {
    $input = getJsonInput();
    requireFields($input, ['agreement_id']);
    $agreementId   = (int) $input['agreement_id'];
    $complaintText = isset($input['complaint_text']) ? sanitizeString($input['complaint_text']) : null;
}

if ($agreementId <= 0) {
    sendError('A valid agreement_id is required.', 422);
}

// Must have either text or a voice file
$hasVoiceFile = $isMultipart && isset($_FILES['voice_file']) && $_FILES['voice_file']['error'] !== UPLOAD_ERR_NO_FILE;
if (empty($complaintText) && !$hasVoiceFile) {
    sendError('Either complaint_text or voice_file must be provided.', 422);
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

    $voiceFilename = null;
    if ($hasVoiceFile) {
        // Max 15MB for voice recordings
        $result = handleFileUpload($_FILES['voice_file'], 'voice', getVoiceAllowedMime(), 15 * 1024 * 1024);
        if (!$result['success']) {
            sendError($result['message'], 422);
        }
        $voiceFilename = $result['filename'];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO complaints (user_id, agreement_id, voice_file, complaint_text, ai_suggestion, status)
         VALUES (:user_id, :agreement_id, :voice_file, :complaint_text, NULL, "open")'
    );
    $stmt->execute([
        'user_id'        => $currentUser['id'],
        'agreement_id'   => $agreementId,
        'voice_file'     => $voiceFilename,
        'complaint_text' => $complaintText,
    ]);

    $newId = (int) $pdo->lastInsertId();

    sendSuccess('Complaint submitted successfully.', [
        'id'         => $newId,
        'voice_file' => $voiceFilename,
    ], 201);

} catch (PDOException $e) {
    error_log('COMPLAINT CREATE ERROR: ' . $e->getMessage());
    sendError('Failed to submit complaint.', 500);
}
