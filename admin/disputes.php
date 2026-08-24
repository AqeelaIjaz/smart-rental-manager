<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';
$active = "disputes";

// FR-7.2: Admin shall be able to monitor open disputes and escalations
// Complaints table: complaint_id, agreement_id, raised_by, voice_file, transcribed_text, ai_suggestion, status
// (Kashaf ke rule-based suggestion se "ai_suggestion" column bharega)

$disputes = $conn->query("
    SELECT c.complaint_id, c.transcribed_text, c.status, r.risk_level
    FROM Complaints c
    LEFT JOIN RiskScores r ON c.agreement_id = r.agreement_id
    WHERE c.status = 'open'
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Disputes - Admin Panel</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main">
  <div class="topbar">
    <h1>Open Disputes &amp; Risk Alerts</h1>
    <div class="admin-chip"><span class="av">A</span> <?php echo $_SESSION['admin_name']; ?> (Admin)</div>
  </div>

  <table>
    <thead><tr><th>Complaint</th><th>Status</th><th>Risk Level</th><th>Action</th></tr></thead>
    <tbody>
      <?php if ($disputes && $disputes->num_rows > 0) { while ($d = $disputes->fetch_assoc()) {
          $risk = $d['risk_level'] ?? 'low';
          $risk_class = strtolower($risk);
      ?>
      <tr>
        <td><?php echo $d['transcribed_text'] ?? 'Complaint #' . $d['complaint_id']; ?></td>
        <td><span class="badge pending"><?php echo $d['status']; ?></span></td>
        <td><span class="badge <?php echo $risk_class; ?>"><?php echo ucfirst($risk); ?></span></td>
        <td><button class="btn-sm approve">Escalate to Mediator</button></td>
      </tr>
      <?php } } else { ?>
      <tr><td colspan="4">No open disputes right now</td></tr>
      <?php } ?>
    </tbody>
  </table>
</div>

</body>
</html>
