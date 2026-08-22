<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Logout - Smart Rental Manager</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{ --primary:#1F5A52; --primary-dark:#17423C; --warn:#C96A56; --grey:#8A8A8A; --line:#E7E0D3; }
  *{ box-sizing:border-box; margin:0; padding:0; }
  body{
    font-family:'Poppins', sans-serif;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    min-height:100vh; display:flex; align-items:center; justify-content:center;
  }
  .confirm-card{ width:380px; background:#fff; border-radius:18px; padding:40px 36px; text-align:center; box-shadow:0 20px 50px rgba(0,0,0,.25); }
  .icon-circle{
    width:56px; height:56px; border-radius:50%; background:#FBE4E0; margin:0 auto 18px;
    display:flex; align-items:center; justify-content:center; font-size:26px; font-weight:700; color:#C96A56;
  }
  .confirm-card h2{ font-size:19px; color:var(--primary-dark); margin-bottom:8px; }
  .confirm-card p{ font-size:13px; color:var(--grey); margin-bottom:26px; line-height:1.5; }
  .btn-row{ display:flex; gap:10px; }
  .btn{
    flex:1; padding:12px; border-radius:10px; font-weight:700; font-size:13.5px;
    text-decoration:none; cursor:pointer; border:none; font-family:'Poppins';
  }
  .btn-cancel{ background:#F5EFE6; color:var(--primary-dark); }
  .btn-cancel:hover{ background:#ecdfc9; }
  .btn-confirm{ background:var(--warn); color:#fff; }
  .btn-confirm:hover{ background:#b4573f; }
</style>
</head>
<body>
  <div class="confirm-card">
    <div class="icon-circle">!</div>
    <h2>Logout</h2>
    <p>Are you sure you want to sign out of the Admin Panel?</p>
    <div class="btn-row">
      <a href="dashboard.php" class="btn btn-cancel">Cancel</a>
      <a href="logout.php" class="btn btn-confirm">Logout</a>
    </div>
  </div>
</body>
</html>
