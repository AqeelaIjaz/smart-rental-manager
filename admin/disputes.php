<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: signin.php");
    exit();
}
include 'db_connect.php';
$active = "disputes";

// NOTE: Abhi dummy data hai. Baad mein yahan real query aayegi, jaise:
// $result = $conn->query("SELECT * FROM complaints WHERE status='open'");
$disputes = [
    ["tenant" => "Faraz Ahmed", "landlord" => "Bilal Rentals", "issue" => "Late payment dispute", "risk" => "high"],
    ["tenant" => "Zainab Tariq", "landlord" => "Sara Khan", "issue" => "Repair delay complaint", "risk" => "pending"],
    ["tenant" => "Usman Ali", "landlord" => "Hamza Iqbal", "issue" => "Penalty clause disagreement", "risk" => "verified"],
];
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
    <thead><tr><th>Tenant</th><th>Landlord</th><th>Issue</th><th>Risk Level</th><th>Action</th></tr></thead>
    <tbody>
      <?php foreach ($disputes as $d) { ?>
      <tr>
        <td><?php echo $d['tenant']; ?></td>
        <td><?php echo $d['landlord']; ?></td>
        <td><?php echo $d['issue']; ?></td>
        <td><span class="badge <?php echo $d['risk']; ?>"><?php echo ucfirst($d['risk']); ?></span></td>
        <td><button class="btn-sm approve">Review</button></td>
      </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

</body>
</html>
