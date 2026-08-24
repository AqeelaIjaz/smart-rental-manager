<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
include 'db_connect.php';
$active = "verify";

// FR-7.1: Admin shall be able to verify new landlord/tenant accounts
// Users table: user_id, name, phone, role, password_hash, language_pref, status

// Approve/Reject button dabane par is file ka POST handle karega
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $action = $_POST['action']; // "approve" ya "reject"
    $new_status = ($action == "approve") ? "verified" : "rejected";

    $stmt = $conn->prepare("UPDATE Users SET status = ? WHERE user_id = ?");
    $stmt->bind_param("si", $new_status, $user_id);
    $stmt->execute();
}

$pending_users = $conn->query("SELECT * FROM Users WHERE status = 'pending'");
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
      <?php if ($pending_users && $pending_users->num_rows > 0) { while ($user = $pending_users->fetch_assoc()) { ?>
      <tr>
        <td><?php echo $user['name']; ?></td>
        <td><?php echo ucfirst($user['role']); ?></td>
        <td><?php echo $user['phone']; ?></td>
        <td><span class="badge pending">Pending</span></td>
        <td>
          <form method="POST" style="display:inline">
            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
            <input type="hidden" name="action" value="approve">
            <button type="submit" class="btn-sm approve">Approve</button>
          </form>
          <form method="POST" style="display:inline">
            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
            <input type="hidden" name="action" value="reject">
            <button type="submit" class="btn-sm reject">Reject</button>
          </form>
        </td>
      </tr>
      <?php } } else { ?>
      <tr><td colspan="5">No pending users right now</td></tr>
      <?php } ?>
    </tbody>
  </table>
</div>

</body>
</html>
