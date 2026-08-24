<?php
/**
 * =====================================================================
 * DATABASE CONNECTION (PDO)
 * =====================================================================
 * Smart Rental Manager — Backend
 *
 * This file returns a shared PDO instance connected to MySQL.
 * Include it with: require_once __DIR__ . '/../config/database.php';
 * and then use the global $pdo variable, or call getDbConnection().
 *
 * ---------------------------------------------------------------------
 * >>> CHANGE THESE VALUES FOR YOUR LOCAL XAMPP SETUP <<<
 * ---------------------------------------------------------------------
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'smart_rental_manager');
define('DB_USER', 'root');       // Default XAMPP MySQL user
define('DB_PASSWORD', '');       // Default XAMPP MySQL password is empty

/**
 * Returns a shared PDO connection instance.
 * Uses a static variable so the same connection is reused within a
 * single request (simple "singleton" pattern, no framework needed).
 *
 * @return PDO
 */
function getDbConnection(): PDO
{
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false, // real prepared statements
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Never leak DB credentials or raw exception details to the client.
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed. Please check server configuration.'
        ]);
        // Log the real error server-side only.
        error_log('DB CONNECTION ERROR: ' . $e->getMessage());
        exit;
    }
}

// Convenience global for scripts that prefer $pdo directly.
$pdo = getDbConnection();
