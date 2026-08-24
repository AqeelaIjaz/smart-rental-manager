<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';
$active = "dashboard";

// FR-7.3: platform-wide activity reports
// Ye counts Users, Complaints, RiskScores tables se aa rahe hain
// (Fatima ke banaye gaye schema ke mutabiq)

$total_users = $conn->query("SELECT COUNT(*) as c FROM Users")->fetch_assoc()['c'] ?? 0;
$pending_verifications = $conn->query("SELECT COUNT(*) as c FROM Users WHERE status='pending'")->fetch_assoc()['c'] ?? 0;
$open_disputes = $conn->query("SELECT COUNT(*) as c FROM Complaints WHERE status='open'")->fetch_assoc()['c'] ?? 0;
$high_risk = $conn->query("SELECT COUNT(*) as c FROM RiskScores WHERE risk_level='High'")->fetch_assoc()['c'] ?? 0;

$recent = $conn->query("SELECT * FROM Complaints ORDER BY complaint_id DESC LIMIT 4");
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
    <thead><tr><th>Recent Complaints</th><th>Status</th></tr></thead>
    <tbody>
      <?php if ($recent && $recent->num_rows > 0) { while ($row = $recent->fetch_assoc()) { ?>
      <tr>
        <td><?php echo $row['transcribed_text'] ?? 'Complaint #' . $row['complaint_id']; ?></td>
        <td><span class="badge pending"><?php echo $row['status']; ?></span></td>
      </tr>
      <?php } } else { ?>
      <tr><td colspan="2">No complaints yet</td></tr>
      <?php } ?>
    </tbody>
  </table>
</div>

</body>
</html>
