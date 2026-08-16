<?php
// ============================================
// DATABASE CONNECTION FILE
// Har page ke start mein isay "include" karna hai:
// <?php include 'db_connect.php'; ?>
// ============================================

$host = "localhost";
$db_user = "root";       // XAMPP ka default username
$db_pass = "";           // XAMPP ka default password (khali)
$db_name = "rental_app"; // phpMyAdmin mein yehi naam se database banao

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// Agar connection fail ho jaye to error dikhao
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
