<?php
/**
 * POST /backend/agreements/upload.php
 *
 * Uploads an agreement document (PDF/DOC/DOCX) and links it to an
 * existing agreement record.
 *
 * multipart/form-data fields:
 *   agreement_id : int
 *   file         : the document file
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/upload.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('POST');
$currentUser = requireLogin();
requireRole($currentUser, ['landlord', 'admin']);

if (empty($_POST['agreement_id']) || !isset($_FILES['file'])) {
    sendError('agreement_id and file are required.', 422);
}

$agreementId = (int) $_POST['agreement_id'];

$pdo = getDbConnection();

try {
    $stmt = $pdo->prepare('SELECT id, landlord_id, tenant_id FROM agreements WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $agreementId]);
    $agreement = $stmt->fetch();

    if (!$agreement) {
        sendError('Agreement not found.', 404);
    }

    requireAgreementAccess($currentUser, (int)$agreement['landlord_id'], (int)$agreement['tenant_id']);

    // Max 10MB for agreement documents
    $result = handleFileUpload($_FILES['file'], 'agreements', getAgreementAllowedMime(), 10 * 1024 * 1024);

    if (!$result['success']) {
        sendError($result['message'], 422);
    }

    $update = $pdo->prepare('UPDATE agreements SET agreement_file = :file WHERE id = :id');
    $update->execute(['file' => $result['filename'], 'id' => $agreementId]);

    sendSuccess('Agreement document uploaded successfully.', [
        'agreement_id' => $agreementId,
        'file'         => $result['filename'],
    ], 201);

} catch (PDOException $e) {
    error_log('AGREEMENT UPLOAD ERROR: ' . $e->getMessage());
    sendError('Failed to upload agreement document.', 500);
}
