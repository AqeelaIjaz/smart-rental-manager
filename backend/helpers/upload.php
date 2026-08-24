<?php
/**
 * =====================================================================
 * FILE UPLOAD HELPER
 * =====================================================================
 * Centralized, secure file upload handling used by agreements,
 * complaints (voice), and repairs (photos).
 *
 * Security measures implemented:
 *  - MIME type validated via finfo (not just the client-supplied type)
 *  - Extension whitelist cross-checked against detected MIME type
 *  - File size limit enforced
 *  - Original filename is NEVER trusted or reused
 *  - Randomly generated filename prevents path traversal / overwrite
 *  - Upload target directory is fixed, not derived from user input
 */

/**
 * Handles a single uploaded file safely.
 *
 * @param array  $file           A single entry from $_FILES (e.g. $_FILES['file'])
 * @param string $subDirectory   One of: agreements | complaints | repairs | voice
 * @param array  $allowedMime    Map of allowed extension => mime type(s), e.g.
 *                                ['pdf' => ['application/pdf']]
 * @param int    $maxSizeBytes   Maximum allowed file size in bytes
 * @return array{success:bool, message:string, filename?:string, path?:string}
 */
function handleFileUpload(array $file, string $subDirectory, array $allowedMime, int $maxSizeBytes): array
{
    // 1. Basic upload error check
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'message' => 'Invalid file upload parameters.'];
    }

    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['success' => false, 'message' => 'No file was uploaded.'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['success' => false, 'message' => 'File exceeds the maximum allowed size.'];
        default:
            return ['success' => false, 'message' => 'File upload failed (error code ' . $file['error'] . ').'];
    }

    // 2. Size check
    if ($file['size'] > $maxSizeBytes) {
        $maxMb = round($maxSizeBytes / (1024 * 1024), 1);
        return ['success' => false, 'message' => "File exceeds maximum allowed size of {$maxMb}MB."];
    }

    // 3. Verify it is genuinely an uploaded file (prevents path injection)
    if (!is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'Invalid upload source.'];
    }

    // 4. Detect real MIME type from file contents (never trust $_FILES['type'])
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $detectedMime = $finfo->file($file['tmp_name']);

    $matchedExt = null;
    foreach ($allowedMime as $ext => $mimeList) {
        if (in_array($detectedMime, (array)$mimeList, true)) {
            $matchedExt = $ext;
            break;
        }
    }

    if ($matchedExt === null) {
        return ['success' => false, 'message' => 'Unsupported file type: ' . $detectedMime];
    }

    // 5. Build safe upload directory (fixed base path, no user input in path)
    $baseUploadDir = realpath(__DIR__ . '/../uploads');
    if ($baseUploadDir === false) {
        return ['success' => false, 'message' => 'Upload directory is not configured correctly.'];
    }

    $allowedSubDirs = ['agreements', 'complaints', 'repairs', 'voice'];
    if (!in_array($subDirectory, $allowedSubDirs, true)) {
        return ['success' => false, 'message' => 'Invalid upload category.'];
    }

    $targetDir = $baseUploadDir . DIRECTORY_SEPARATOR . $subDirectory;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // 6. Generate a random, safe filename (original name is discarded)
    $randomName = bin2hex(random_bytes(16)) . '_' . time() . '.' . $matchedExt;
    $destination = $targetDir . DIRECTORY_SEPARATOR . $randomName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'message' => 'Failed to save uploaded file.'];
    }

    return [
        'success'  => true,
        'message'  => 'File uploaded successfully.',
        'filename' => $randomName,
        'path'     => 'backend/uploads/' . $subDirectory . '/' . $randomName,
    ];
}

/** Allowed types for agreement documents */
function getAgreementAllowedMime(): array
{
    return [
        'pdf'  => ['application/pdf'],
        'doc'  => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip'],
    ];
}

/** Allowed types for repair photos */
function getPhotoAllowedMime(): array
{
    return [
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'webp' => ['image/webp'],
    ];
}

/** Allowed types for voice complaint recordings */
function getVoiceAllowedMime(): array
{
    return [
        'mp3'  => ['audio/mpeg'],
        'wav'  => ['audio/wav', 'audio/x-wav'],
        'ogg'  => ['audio/ogg'],
        'm4a'  => ['audio/mp4', 'audio/x-m4a'],
        'webm' => ['audio/webm'],
    ];
}
