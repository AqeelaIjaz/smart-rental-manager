<?php
/**
 * GET /backend/repairs/rates-list.php
 *
 * Returns the local market repair-rate reference data (Member 5).
 * Visible to any logged-in user (tenants/landlords use it to sanity
 * check estimated repair costs).
 * Optional query param: category
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('GET');
requireLogin(); // any logged-in role can view

$pdo = getDbConnection();
$categoryFilter = isset($_GET['category']) ? trim($_GET['category']) : null;

try {
    $sql = 'SELECT id, item_name, category, low_cost, high_cost, unit, region, updated_at FROM repair_rates';
    $params = [];

    if (!empty($categoryFilter)) {
        $sql .= ' WHERE category = :category';
        $params['category'] = $categoryFilter;
    }

    $sql .= ' ORDER BY category, item_name';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    sendSuccess('Repair rates retrieved successfully.', $stmt->fetchAll());

} catch (PDOException $e) {
    error_log('REPAIR RATES LIST ERROR: ' . $e->getMessage());
    sendError('Failed to retrieve repair rates.', 500);
}
