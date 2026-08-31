<?php
/**
 * POST /backend/repairs/rates-upsert.php
 *
 * Creates or updates a local market repair-rate entry (Member 5).
 * Landlord or admin only, so tenants can't tamper with reference data.
 *
 * Request JSON:
 * {
 *   "item_name": "Tap Repair",
 *   "category": "Plumbing",
 *   "low_cost": 500,
 *   "high_cost": 1500,
 *   "unit": "per fixture",
 *   "region": "Lahore"
 * }
 * Include "id" to update an existing row instead of creating a new one.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../helpers/response.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../middleware/auth.php';

requireMethod('POST');
$currentUser = requireLogin();
requireRole($currentUser, ['landlord', 'admin']);

$input = getJsonInput();
requireFields($input, ['item_name', 'low_cost', 'high_cost']);

$itemName = sanitizeString($input['item_name']);
$category = isset($input['category']) ? sanitizeString($input['category']) : null;
$lowCost  = $input['low_cost'];
$highCost = $input['high_cost'];
$unit     = isset($input['unit']) ? sanitizeString($input['unit']) : null;
$region   = isset($input['region']) ? sanitizeString($input['region']) : 'Lahore';
$id       = isset($input['id']) ? (int) $input['id'] : null;

if (!isPositiveNumber($lowCost) || !isPositiveNumber($highCost)) {
    sendError('low_cost and high_cost must be positive numbers.', 422);
}
if ((float)$highCost < (float)$lowCost) {
    sendError('high_cost must be greater than or equal to low_cost.', 422);
}

$pdo = getDbConnection();

try {
    if ($id) {
        $stmt = $pdo->prepare(
            'UPDATE repair_rates
             SET item_name = :item_name, category = :category, low_cost = :low_cost,
                 high_cost = :high_cost, unit = :unit, region = :region, updated_by = :updated_by
             WHERE id = :id'
        );
        $stmt->execute([
            'item_name'  => $itemName,
            'category'   => $category,
            'low_cost'   => $lowCost,
            'high_cost'  => $highCost,
            'unit'       => $unit,
            'region'     => $region,
            'updated_by' => $currentUser['id'],
            'id'         => $id,
        ]);
        sendSuccess('Repair rate updated successfully.', ['id' => $id]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO repair_rates (item_name, category, low_cost, high_cost, unit, region, updated_by)
             VALUES (:item_name, :category, :low_cost, :high_cost, :unit, :region, :updated_by)'
        );
        $stmt->execute([
            'item_name'  => $itemName,
            'category'   => $category,
            'low_cost'   => $lowCost,
            'high_cost'  => $highCost,
            'unit'       => $unit,
            'region'     => $region,
            'updated_by' => $currentUser['id'],
        ]);
        sendSuccess('Repair rate added successfully.', ['id' => (int) $pdo->lastInsertId()], 201);
    }
} catch (PDOException $e) {
    error_log('REPAIR RATES UPSERT ERROR: ' . $e->getMessage());
    sendError('Failed to save repair rate.', 500);
}
