<?php
/**
 * GET /backend/agreements/list.php
 *
 * Lists agreements visible to the current user:
 *  - tenant: agreements where they are the tenant
 *  - landlord: agreements where they are the landlord
 *  - admin: all agreements
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('GET');
$currentUser = requireLogin();

$pdo = getDbConnection();

try {
    if ($currentUser['role'] === 'admin') {
        $stmt = $pdo->query('SELECT * FROM agreements ORDER BY created_at DESC');
        $agreements = $stmt->fetchAll();
    } elseif ($currentUser['role'] === 'landlord') {
        $stmt = $pdo->prepare('SELECT * FROM agreements WHERE landlord_id = :id ORDER BY created_at DESC');
        $stmt->execute(['id' => $currentUser['id']]);
        $agreements = $stmt->fetchAll();
    } else { // tenant
        $stmt = $pdo->prepare('SELECT * FROM agreements WHERE tenant_id = :id ORDER BY created_at DESC');
        $stmt->execute(['id' => $currentUser['id']]);
        $agreements = $stmt->fetchAll();
    }

    sendSuccess('Agreements retrieved successfully.', $agreements);

} catch (PDOException $e) {
    error_log('AGREEMENT LIST ERROR: ' . $e->getMessage());
    sendError('Failed to retrieve agreements.', 500);
}
