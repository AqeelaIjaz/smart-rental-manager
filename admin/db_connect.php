<?php
/*
============================================
DATABASE CONNECTION FILE
Include this at the top of every page using:
include 'db_connect.php';

NOTE: Matches Fatima's updated schema (V2):
- Admins table: admin_id, name, email, phone, password, status
- PasswordResets table: reset_id, identifier, otp, expiry, used
- Users table (tenant/landlord): user_id, name, phone, email, role, password, status
============================================
*/

$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "rental_app";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
?>
