<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';
$active = "reports";

// FR-7.3: Admin shall be able to view platform-wide activity reports
// Ye counts Users, Agreements, Payments, Complaints, Repairs tables se aa rahe hain

$total_agreements = $conn->query("SELECT COUNT(*) as c FROM Agreements")->fetch_assoc()['c'] ?? 0;
$total_payments = $conn->query("SELECT COUNT(*) as c FROM Payments")->fetch_assoc()['c'] ?? 0;
$total_collected = $conn->query("SELECT SUM(amount) as s FROM Payments")->fetch_assoc()['s'] ?? 0;
$total_complaints = $conn->query("SELECT COUNT(*) as c FROM Complaints")->fetch_assoc()['c'] ?? 0;
$resolved_complaints = $conn->query("SELECT COUNT(*) as c FROM Complaints WHERE status='resolved'")->fetch_assoc()['c'] ?? 0;
$total_repairs = $conn->query("SELECT COUNT(*) as c FROM Repairs")->fetch_assoc()['c'] ?? 0;
$high_risk = $conn->query("SELECT COUNT(*) as c FROM RiskScores WHERE risk_level='High'")->fetch_assoc()['c'] ?? 0;

// Late payments (Payments table mein "verified" column ya "paid_date" vs "due_date" compare karke nikal sakte hain,
// simple placeholder for now - Manahil should update this once her payment logic is ready)
$late_payments = $conn->query("SELECT COUNT(*) as c FROM Payments WHERE verified = 0")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reports - Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
  <div class="topbar">
    <h1>Reports &amp; Analytics</h1>
    <div class="admin-chip"><span class="av">A</span> <?php echo $_SESSION['admin_name']; ?> (Admin)</div>
  </div>

  <div class="stat-row">
    <div class="stat"><div class="num"><?php echo $total_agreements; ?></div><div class="lbl">Total Agreements</div></div>
    <div class="stat"><div class="num">Rs. <?php echo number_format($total_collected); ?></div><div class="lbl">Total Rent Collected</div></div>
    <div class="stat"><div class="num"><?php echo $total_payments; ?></div><div class="lbl">Total Payments Recorded</div></div>
    <div class="stat"><div class="num"><?php echo $late_payments; ?></div><div class="lbl">Unverified / Late Payments</div></div>
  </div>

  <div class="stat-row">
    <div class="stat"><div class="num"><?php echo $total_complaints; ?></div><div class="lbl">Total Complaints</div></div>
    <div class="stat"><div class="num"><?php echo $resolved_complaints; ?></div><div class="lbl">Resolved Complaints</div></div>
    <div class="stat"><div class="num"><?php echo $total_repairs; ?></div><div class="lbl">Repair Requests</div></div>
    <div class="stat"><div class="num"><?php echo $high_risk; ?></div><div class="lbl">High Risk Agreements</div></div>
  </div>

  <table>
    <thead><tr><th>Metric</th><th>Value</th></tr></thead>
    <tbody>
      <tr><td>Complaint Resolution Rate</td><td><?php echo $total_complaints > 0 ? round(($resolved_complaints / $total_complaints) * 100) : 0; ?>%</td></tr>
      <tr><td>Average Payment Amount</td><td>Rs. <?php echo $total_payments > 0 ? number_format($total_collected / $total_payments) : 0; ?></td></tr>
    </tbody>
  </table>
</div>

</body>
</html>
