<?php
session_start();
// Agar login nahi hai to seedha login page pe bhej do
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: signin.php");
    exit();
}
include 'db_connect.php';
$active = "dashboard";

// NOTE: Abhi dummy numbers hain. Baad mein yahan real queries aayengi, jaise:
// $result = $conn->query("SELECT COUNT(*) FROM users");
$total_users = 128;
$pending_verifications = 6;
$open_disputes = 3;
$high_risk = 2;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard - Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
  <div class="topbar">
    <h1>Dashboard Overview</h1>
    <div class="admin-chip"><span class="av">A</span> <?php echo $_SESSION['admin_name']; ?> (Admin)</div>
  </div>

  <div class="stat-row">
    <div class="stat"><div class="num"><?php echo $total_users; ?></div><div class="lbl">Total Users</div></div>
    <div class="stat"><div class="num"><?php echo $pending_verifications; ?></div><div class="lbl">Pending Verifications</div></div>
    <div class="stat"><div class="num"><?php echo $open_disputes; ?></div><div class="lbl">Open Disputes</div></div>
    <div class="stat"><div class="num"><?php echo $high_risk; ?></div><div class="lbl">High Risk Agreements</div></div>
  </div>

  <table>
    <thead><tr><th>Recent Activity</th><th>Type</th><th>Status</th><th>Date</th></tr></thead>
    <tbody>
      <tr><td>Faraz Ahmed - fixture repair request</td><td>Repair</td><td><span class="badge pending">Pending</span></td><td>13 Aug</td></tr>
      <tr><td>Sara Khan - landlord signup</td><td>Verification</td><td><span class="badge pending">Pending</span></td><td>13 Aug</td></tr>
      <tr><td>Bilal Rentals - complaint escalation</td><td>Dispute</td><td><span class="badge high">High</span></td><td>12 Aug</td></tr>
      <tr><td>Ayesha Malik - tenant verified</td><td>Verification</td><td><span class="badge verified">Verified</span></td><td>11 Aug</td></tr>
    </tbody>
  </table>
</div>

</body>
</html>
