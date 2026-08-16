<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: signin.php");
    exit();
}
include 'db_connect.php';
$active = "verify";

// NOTE: Abhi dummy data hai. Baad mein yahan real query aayegi, jaise:
// $result = $conn->query("SELECT * FROM users WHERE status='pending'");
$pending_users = [
    ["name" => "Sara Khan", "role" => "Landlord", "phone" => "0301-1234567"],
    ["name" => "Hamza Iqbal", "role" => "Tenant", "phone" => "0333-9876543"],
    ["name" => "Bilal Rentals", "role" => "Landlord", "phone" => "0345-5551234"],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Verify Users - Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
  <div class="topbar">
    <h1>Verify New Users</h1>
    <div class="admin-chip"><span class="av">A</span> <?php echo $_SESSION['admin_name']; ?> (Admin)</div>
  </div>

  <table>
    <thead><tr><th>Name</th><th>Role</th><th>Phone</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach ($pending_users as $user) { ?>
      <tr>
        <td><?php echo $user['name']; ?></td>
        <td><?php echo $user['role']; ?></td>
        <td><?php echo $user['phone']; ?></td>
        <td><span class="badge pending">Pending</span></td>
        <td>
          <!-- Ye buttons abhi kaam nahi karte, backend ready hone par approve_user.php banayenge -->
          <button class="btn-sm approve">Approve</button>
          <button class="btn-sm reject">Reject</button>
        </td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

</body>
</html>
