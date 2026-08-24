<?php
/**
 * POST /backend/repairs/upload.php
 *
 * Uploads/replaces the photo for an existing repair record.
 * (repairs/create.php also accepts a photo inline; this endpoint is
 * for adding/replacing a photo afterward.)
 *
 * multipart/form-data:
 *   repair_id : int
 *   photo     : image file (jpg, jpeg, png, webp)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/upload.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('POST');
$currentUser = requireLogin();

if (empty($_POST['repair_id']) || !isset($_FILES['photo'])) {
    sendError('repair_id and photo are required.', 422);
}

$repairId = (int) $_POST['repair_id'];

$pdo = getDbConnection();

try {
    $stmt = $pdo->prepare(
        'SELECT r.id, a.landlord_id, a.tenant_id
         FROM repairs r
         JOIN agreements a ON a.id = r.agreement_id
         WHERE r.id = :id LIMIT 1'
    );
    $stmt->execute(['id' => $repairId]);
    $repair = $stmt->fetch();

    if (!$repair) {
        sendError('Repair record not found.', 404);
    }

    requireAgreementAccess($currentUser, (int)$repair['landlord_id'], (int)$repair['tenant_id']);

    $result = handleFileUpload($_FILES['photo'], 'repairs', getPhotoAllowedMime(), 8 * 1024 * 1024);
    if (!$result['success']) {
        sendError($result['message'], 422);
    }

    $update = $pdo->prepare('UPDATE repairs SET photo = :photo WHERE id = :id');
    $update->execute(['photo' => $result['filename'], 'id' => $repairId]);

    sendSuccess('Repair photo uploaded successfully.', [
        'repair_id' => $repairId,
        'photo'     => $result['filename'],
    ], 201);

} catch (PDOException $e) {
    error_log('REPAIR UPLOAD ERROR: ' . $e->getMessage());
    sendError('Failed to upload repair photo.', 500);
}
